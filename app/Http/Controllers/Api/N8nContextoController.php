<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GerarRespostaIaJob;
use App\Models\Agente;
use App\Models\Cliente;
use App\Models\Conversa;
use App\Models\Mensagem;
use App\Models\WhatsappIntegracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class N8nContextoController extends Controller
{
    public function show(Request $request)
    {
        $data = $request->validate([
            'phone_number_id' => 'required|string',
            'contato_telefone' => 'required|string',
            'agente_id' => 'nullable|integer',
        ]);

        $integracao = WhatsappIntegracao::where('phone_number_id', $data['phone_number_id'])->first();

        if (! $integracao) {
            return response()->json(['message' => 'Nenhum cliente encontrado para esse phone_number_id.'], 404);
        }

        $cliente = $integracao->cliente;

        $bloqueado = $cliente->contatosBloqueados()
            ->whereRaw(
                "RIGHT(REGEXP_REPLACE(telefone, '[^0-9]', ''), 11) = RIGHT(REGEXP_REPLACE(?, '[^0-9]', ''), 11)",
                [$data['contato_telefone']]
            )
            ->exists();

        $conversa = Conversa::where('cliente_id', $cliente->id)
            ->where('contato_telefone', $data['contato_telefone'])
            ->whereIn('status', ['em_andamento', 'transferida_humano'])
            ->where('ultima_mensagem_em', '>=', now()->subHours(config('conversas.janela_inatividade_horas')))
            ->latest('iniciada_em')
            ->first();

        $candidatosAgentes = null;

        if ($conversa && $conversa->agente_id) {
            $agente = Agente::where('id', $conversa->agente_id)
                ->where('ativo', true)
                ->with(['horarios', 'feriados', 'regras', 'faqs', 'produtos', 'politicas'])
                ->first();
        } else {
            $candidatos = $this->candidatosAgentes($cliente, $integracao);

            if (! empty($data['agente_id'])) {
                $agenteBase = $candidatos->firstWhere('id', (int) $data['agente_id']);
                $agente = $agenteBase
                    ? Agente::where('id', $agenteBase->id)
                        ->with(['horarios', 'feriados', 'regras', 'faqs', 'produtos', 'politicas'])
                        ->first()
                    : null;
            } elseif ($candidatos->count() <= 1) {
                $agente = $candidatos->first()?->load(['horarios', 'feriados', 'regras', 'faqs', 'produtos', 'politicas']);
            } elseif (! $integracao->modo_equipe_agentes) {
                $agente = $candidatos->first()->load(['horarios', 'feriados', 'regras', 'faqs', 'produtos', 'politicas']);
            } else {
                $agente = null;
                $candidatosAgentes = $candidatos->map(fn ($a) => [
                    'id' => $a->id,
                    'nome' => $a->nome,
                    'objetivo' => $a->objetivo,
                ])->values()->all();
            }
        }

        return response()->json($this->montarContexto($cliente, $integracao, $agente, $conversa, $bloqueado, null, null, $candidatosAgentes));
    }

    public function showPorConversa(Request $request)
    {
        $data = $request->validate([
            'conversa_id' => 'required|integer',
            'mensagem_id' => 'required|integer',
        ]);

        $conversa = Conversa::with('cliente')->find($data['conversa_id']);

        if (! $conversa) {
            return response()->json(['message' => 'Conversa não encontrada.'], 404);
        }

        $mensagem = Mensagem::where('conversa_id', $conversa->id)->find($data['mensagem_id']);

        if (! $mensagem) {
            return response()->json(['message' => 'Mensagem não encontrada.'], 404);
        }

        $cliente = $conversa->cliente;

        $integracao = $conversa->whatsappIntegracao ?? $cliente->whatsappIntegracao;

        $agente = Agente::where('id', $conversa->agente_id)
            ->where('ativo', true)
            ->with(['horarios', 'feriados', 'regras', 'faqs', 'produtos', 'politicas'])
            ->first();

        $bloqueado = $cliente->contatosBloqueados()
            ->whereRaw(
                "RIGHT(REGEXP_REPLACE(telefone, '[^0-9]', ''), 11) = RIGHT(REGEXP_REPLACE(?, '[^0-9]', ''), 11)",
                [$conversa->contato_telefone]
            )
            ->exists();

        $mensagemAtual = [
            'tipo' => $mensagem->tipo,
            'texto' => $mensagem->conteudo,
            'midia_url_publica' => $mensagem->midia_path ? Storage::disk('public')->url($mensagem->midia_path) : null,
            'midia_texto_extraido' => $mensagem->midia_texto_extraido,
            'contato_telefone' => $conversa->contato_telefone,
        ];

        return response()->json(
            $this->montarContexto($cliente, $integracao, $agente, $conversa, $bloqueado, $mensagemAtual, $mensagem->id)
        );
    }

    public function agendarResposta(Request $request)
    {
        $data = $request->validate([
            'conversa_id' => 'required|integer',
            'mensagem_id' => 'required|integer',
        ]);

        GerarRespostaIaJob::dispatch($data['conversa_id'], $data['mensagem_id'])
            ->delay(now()->addSeconds(7));

        return response()->json(['agendado' => true]);
    }

    public function marcarResolvida(Request $request)
    {
        $data = $request->validate([
            'conversa_id' => 'required|integer',
        ]);

        $conversa = Conversa::find($data['conversa_id']);

        if (! $conversa) {
            return response()->json(['message' => 'Conversa não encontrada.'], 404);
        }

        $conversa->update([
            'status' => 'resolvida_ia',
            'finalizada_em' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function transferirAgente(Request $request)
    {
        $data = $request->validate([
            'conversa_id' => 'required|integer',
            'agente_id' => 'required|integer',
        ]);

        $conversa = Conversa::find($data['conversa_id']);

        if (! $conversa) {
            return response()->json(['message' => 'Conversa não encontrada.'], 404);
        }

        $agenteValido = Agente::where('id', $data['agente_id'])
            ->where('cliente_id', $conversa->cliente_id)
            ->where('ativo', true)
            ->exists();

        if (! $agenteValido) {
            return response()->json(['message' => 'Agente inválido para esta conversa.'], 422);
        }

        $conversa->update(['agente_id' => $data['agente_id']]);

        return response()->json(['ok' => true]);
    }

    public function ultimaMensagem(Request $request)
    {
        $data = $request->validate([
            'conversa_id' => 'required|integer',
        ]);

        $ultimoId = Mensagem::where('conversa_id', $data['conversa_id'])
            ->where('remetente', 'contato')
            ->max('id');

        return response()->json(['mensagem_id' => $ultimoId]);
    }

    private function candidatosAgentes(Cliente $cliente, WhatsappIntegracao $integracao)
    {
        $query = $cliente->agentes()->where('ativo', true);

        if ($cliente->whatsappIntegracoes()->count() >= 2) {
            $query->where('whatsapp_integracao_id', $integracao->id);
        }

        return $query->orderBy('id')->get();
    }

    private function montarContexto(
        Cliente $cliente,
        ?WhatsappIntegracao $integracao,
        ?Agente $agente,
        ?Conversa $conversa,
        bool $bloqueado,
        ?array $mensagemAtual = null,
        ?int $excluirMensagemIdDoHistorico = null,
        ?array $candidatosAgentes = null,
    ): array {
        $colegasEquipe = [];
        if ($agente && $integracao && $integracao->modo_equipe_agentes) {
            $colegasEquipe = $this->candidatosAgentes($cliente, $integracao)
                ->reject(fn ($a) => $a->id === $agente->id)
                ->map(fn ($a) => ['id' => $a->id, 'nome' => $a->nome, 'objetivo' => $a->objetivo])
                ->values()
                ->all();
        }

        $historico = collect();
        if ($conversa && $agente && $agente->memoria_habilitada) {
            $desde = now()->subDays($agente->memoria_dias_lembrar ?: 30);

            $query = $conversa->mensagens()
                ->reorder()
                ->where('enviada_em', '>=', $desde);

            if ($excluirMensagemIdDoHistorico) {
                $query->where('id', '!=', $excluirMensagemIdDoHistorico);
            }

            $historico = $query->orderByDesc('enviada_em')
                ->orderByDesc('id')
                ->limit(30)
                ->get()
                ->sortBy('enviada_em')
                ->values();
        }

        $respostasIaDesdeRetomada = $conversa
            ? $conversa->mensagens()
                ->where('remetente', 'agente_ia')
                ->where('enviada_em', '>=', $conversa->retomada_em ?? $conversa->iniciada_em)
                ->get(['tokens_prompt', 'tokens_resposta'])
            : null;

        $ultimaMensagemHumanoEm = $conversa
            ? $conversa->mensagens()->reorder()->whereIn('remetente', ['humano', 'agente_ia'])->latest('enviada_em')->value('enviada_em')
            : null;

        return [
            'cliente' => [
                'id' => $cliente->id,
                'nome_empresa' => $cliente->nome_empresa,
            ],
            'bloqueado' => $bloqueado,
            'agente' => $agente ? $this->formatarAgente($agente) : null,

            'candidatos_agentes' => $candidatosAgentes,

            'prompt_classificacao_extra' => $integracao->prompt_classificacao_extra ?? null,

            'colegas_equipe' => $colegasEquipe,
            'whatsapp' => $integracao ? [
                'phone_number_id' => $integracao->phone_number_id,
                'access_token' => $integracao->access_token,
            ] : null,
            'conversa' => $conversa ? [
                'id' => $conversa->id,
                'status' => $conversa->status,
                'agente_id' => $conversa->agente_id,

                'mensagens_total' => $respostasIaDesdeRetomada->count(),
                'tokens_total' => $respostasIaDesdeRetomada->sum('tokens_prompt') + $respostasIaDesdeRetomada->sum('tokens_resposta'),
                'minutos_desde_ultima_mensagem_humano' => $ultimaMensagemHumanoEm?->diffInMinutes(now()),

                'link_avaliacao' => $conversa->linkAvaliacao(),

                'mensagem_atual' => $mensagemAtual,
            ] : null,
            'historico' => $historico->map(fn ($mensagem) => [
                'remetente' => $mensagem->remetente,
                'tipo' => $mensagem->tipo,
                'conteudo' => $mensagem->conteudo,
                'enviada_em' => $mensagem->enviada_em?->toIso8601String(),
            ])->all(),

            'uso_agente' => $agente ? [
                'mensagens_ultimo_minuto' => Mensagem::where('remetente', 'agente_ia')
                    ->whereHas('conversa', fn ($q) => $q->where('agente_id', $agente->id))
                    ->where('enviada_em', '>=', now()->subMinute())
                    ->count(),
                'mensagens_hoje' => Mensagem::where('remetente', 'agente_ia')
                    ->whereHas('conversa', fn ($q) => $q->where('agente_id', $agente->id))
                    ->whereDate('enviada_em', now()->toDateString())
                    ->count(),
            ] : null,
        ];
    }

    private function formatarAgente(Agente $agente): array
    {
        return [
            'id' => $agente->id,
            'nome' => $agente->nome,
            'objetivo' => $agente->objetivo,
            'nome_ia' => $agente->nome_ia,
            'papel' => $agente->papel,
            'tom_voz' => $agente->tom_voz,
            'emojis' => $agente->emojis,
            'tamanho_respostas' => $agente->tamanho_respostas,
            'forma_tratamento' => $agente->forma_tratamento === 'personalizado'
                ? $agente->forma_tratamento_personalizada
                : $agente->forma_tratamento,
            'idioma' => $agente->idioma,
            'timezone' => $agente->timezone,

            'prompt_principal' => $agente->prompt_principal,
            'prompt_complementar' => $agente->prompt_complementar,
            'prompts_especializados' => [
                'horario_fechado' => $agente->prompt_horario_fechado,
                'transferencia_humano' => $agente->prompt_transferencia_humano,
                'despedida' => $agente->prompt_despedida,
                'nao_sei_responder' => $agente->prompt_nao_sei_responder,
                'vendas' => $agente->prompt_vendas,
                'suporte' => $agente->prompt_suporte,
            ],
            'enviar_link_avaliacao' => $agente->enviar_link_avaliacao,

            'modelo' => $agente->modelo,
            'modelo_fallback' => $agente->modelo_fallback,
            'top_p' => $agente->top_p,
            'frequency_penalty' => $agente->frequency_penalty,
            'presence_penalty' => $agente->presence_penalty,
            'max_tokens' => $agente->max_tokens,
            'temperatura' => $agente->temperatura,
            'timeout' => $agente->timeout,

            'regras' => $agente->regras->pluck('regra')->all(),

            'horarios' => $agente->horarios->map(fn ($horario) => [
                'dia_semana' => $horario->dia_semana,
                'fechado' => $horario->fechado,
                'hora_inicio' => $horario->hora_inicio,
                'hora_fim' => $horario->hora_fim,
            ])->all(),
            'feriados' => $agente->feriados->map(fn ($feriado) => [
                'data' => $feriado->data?->toDateString(),
                'descricao' => $feriado->descricao,
            ])->all(),
            'mensagem_fora_horario' => $agente->mensagem_fora_horario,
            'transferencia_automatica_fora_horario' => $agente->transferencia_automatica_fora_horario,

            'limites' => [
                'max_mensagens_conversa' => $agente->limite_mensagens_conversa,
                'max_tokens_conversa' => $agente->limite_tokens_conversa,
                'prompt_limite_atingido' => $agente->prompt_limite_atingido,
                'max_mensagens_minuto' => $agente->limite_mensagens_minuto,
                'max_mensagens_dia' => $agente->limite_mensagens_dia,
                'retomada_automatica_minutos' => $agente->retomada_automatica_minutos,
                'prompt_retomada_automatica' => $agente->prompt_retomada_automatica,
            ],

            'memoria' => [
                'habilitada' => $agente->memoria_habilitada,
                'dias_lembrar' => $agente->memoria_dias_lembrar,
                'resumo_automatico' => $agente->memoria_resumo_automatico,
                'salvar_preferencias' => $agente->memoria_salvar_preferencias,
                'salvar_nome' => $agente->memoria_salvar_nome,
                'salvar_endereco' => $agente->memoria_salvar_endereco,
            ],

            'permitir_anexos' => $agente->permitir_anexos,
            'tipos_anexos_permitidos' => $agente->tipos_anexos_permitidos
                ? explode(',', $agente->tipos_anexos_permitidos)
                : [],
            'permitir_atendimento_humano' => $agente->permitir_atendimento_humano,
            'mascarar_cpf' => $agente->mascarar_cpf,
            'mascarar_cartao' => $agente->mascarar_cartao,

            'conhecimento' => [
                'faqs' => $agente->faqs->map(fn ($faq) => [
                    'pergunta' => $faq->pergunta,
                    'resposta' => $faq->resposta,
                ])->all(),
                'produtos' => $agente->produtos->map(fn ($produto) => [
                    'nome' => $produto->nome,
                    'preco' => $produto->preco,
                    'categoria' => $produto->categoria,
                    'descricao' => $produto->descricao,
                ])->all(),
                'politicas' => $agente->politicas->map(fn ($politica) => [
                    'titulo' => $politica->titulo,
                    'conteudo' => $politica->conteudo,
                ])->all(),
            ],
        ];
    }
}
