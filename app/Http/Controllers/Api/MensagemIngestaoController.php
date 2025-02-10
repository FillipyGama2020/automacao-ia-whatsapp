<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agente;
use App\Models\Conversa;
use App\Models\Mensagem;
use App\Models\PrecoModelo;
use App\Models\WhatsappIntegracao;
use App\Services\DownloadMidiaService;
use App\Services\ExtrairTextoDocumentoService;
use App\Services\MascaradorDadosSensiveis;
use App\Services\OptOutCampanhaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MensagemIngestaoController extends Controller
{
    public function store(Request $request)
    {
        $integracao = $request->attributes->get('integracao');

        $data = $request->validate([
            'contato_telefone' => 'required|string|max:30',
            'contato_nome' => 'nullable|string|max:191',
            'agente_id' => [
                'nullable',
                'integer',
                Rule::exists('agentes', 'id')->where('cliente_id', $integracao->cliente_id),
            ],
            'remetente' => 'required|in:contato,agente_ia,humano,sistema',
            'tipo' => 'nullable|in:texto,imagem,audio,documento,video,template',
            'conteudo' => 'nullable|string',
            'modelo' => 'nullable|string|max:100',
            'midia_url' => 'nullable|url',
            'wamid' => 'nullable|string|max:191',
            'tokens_prompt' => 'nullable|integer|min:0',
            'tokens_resposta' => 'nullable|integer|min:0',
            'status_entrega' => 'nullable|in:enviada,entregue,lida,falhou',
            'fora_horario' => 'boolean',
            'enviada_em' => 'nullable|date',
            'status_conversa' => 'nullable|in:em_andamento,resolvida_ia,transferida_humano,abandonada',
            'motivo_transferencia' => 'nullable|string|max:191',
            'avaliacao' => 'nullable|integer|min:1|max:5',
        ]);

        if (! empty($data['wamid'])) {
            $existente = Mensagem::where('wamid', $data['wamid'])
                ->whereHas('conversa', fn ($q) => $q->where('cliente_id', $integracao->cliente_id))
                ->first();

            if ($existente) {
                return response()->json([
                    'duplicada' => true,
                    'mensagem_id' => $existente->id,
                    'conversa_id' => $existente->conversa_id,
                ], 200);
            }

            if (Mensagem::where('wamid', $data['wamid'])->exists()) {
                return response()->json(['duplicada' => true], 200);
            }
        }

        if ($data['remetente'] === 'contato' && ! empty($data['conteudo'])) {
            try {
                app(OptOutCampanhaService::class)->detectarEProcessar(
                    $integracao->cliente_id,
                    $data['contato_telefone'],
                    $data['conteudo']
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $enviadaEm = $data['enviada_em'] ?? now();

        $lockKey = "conversa-lookup:{$integracao->cliente_id}:{$data['contato_telefone']}";

        $conversa = Cache::lock($lockKey, 10)->block(5, function () use ($integracao, $data, $enviadaEm) {
            $conversa = Conversa::where('cliente_id', $integracao->cliente_id)
                ->where('contato_telefone', $data['contato_telefone'])
                ->whereIn('status', ['em_andamento', 'transferida_humano'])
                ->where('ultima_mensagem_em', '>=', now()->subHours(config('conversas.janela_inatividade_horas')))
                ->latest('iniciada_em')
                ->first();

            if (! $conversa) {
                $conversa = Conversa::create([
                    'cliente_id' => $integracao->cliente_id,
                    'agente_id' => $data['agente_id'] ?? null,
                    'whatsapp_integracao_id' => $integracao->id,
                    'contato_telefone' => $data['contato_telefone'],
                    'contato_nome' => $data['contato_nome'] ?? null,
                    'status' => 'em_andamento',
                    'iniciada_em' => $enviadaEm,
                    'ultima_mensagem_em' => $enviadaEm,
                ]);
            } elseif (! empty($data['contato_nome']) && ! $conversa->contato_nome) {
                $conversa->contato_nome = $data['contato_nome'];
            }

            return $conversa;
        });

        $agenteResolvido = ($data['agente_id'] ?? $conversa->agente_id)
            ? Agente::find($data['agente_id'] ?? $conversa->agente_id)
            : null;

        $conteudo = $data['conteudo'] ?? null;

        if ($conteudo !== null) {
            $mascararCpf = $agenteResolvido?->mascarar_cpf ?? true;
            $mascararCartao = $agenteResolvido?->mascarar_cartao ?? true;

            $conteudo = MascaradorDadosSensiveis::mascarar($conteudo, $mascararCpf, $mascararCartao);
        }

        $tokensPrompt = $data['tokens_prompt'] ?? 0;
        $tokensResposta = $data['tokens_resposta'] ?? 0;
        $modeloUsado = $data['modelo'] ?? $agenteResolvido?->modelo;
        $custoMensagem = PrecoModelo::calcularCusto($modeloUsado, $tokensPrompt, $tokensResposta);

        $tipo = $data['tipo'] ?? 'texto';
        $midiaPath = null;
        $midiaTextoExtraido = null;

        if (! empty($data['midia_url']) && in_array($tipo, ['imagem', 'audio', 'video', 'documento'], true)) {
            $tiposPermitidos = $agenteResolvido?->tipos_anexos_permitidos ? explode(',', $agenteResolvido->tipos_anexos_permitidos) : [];
            $podeBaixar = $agenteResolvido?->permitir_anexos && in_array($tipo, $tiposPermitidos, true);

            if ($podeBaixar) {
                $midiaPath = app(DownloadMidiaService::class)->baixarEArmazenar(
                    $data['midia_url'],
                    $tipo,
                    $conversa->id,
                    $integracao->access_token
                );

                if ($midiaPath && $tipo === 'documento') {
                    $extensao = pathinfo($midiaPath, PATHINFO_EXTENSION);
                    $midiaTextoExtraido = app(ExtrairTextoDocumentoService::class)->extrair($midiaPath, $extensao);
                }
            }
        }

        $mensagem = $conversa->mensagens()->create([
            'remetente' => $data['remetente'],
            'tipo' => $tipo,
            'conteudo' => $conteudo,
            'modelo' => $modeloUsado,
            'midia_path' => $midiaPath,
            'midia_texto_extraido' => $midiaTextoExtraido,
            'wamid' => $data['wamid'] ?? null,
            'tokens_prompt' => $data['tokens_prompt'] ?? null,
            'tokens_resposta' => $data['tokens_resposta'] ?? null,
            'custo' => $custoMensagem,
            'status_entrega' => $data['status_entrega'] ?? null,
            'fora_horario' => $data['fora_horario'] ?? false,
            'enviada_em' => $enviadaEm,
        ]);

        $conversa->tokens_prompt_total += $tokensPrompt;
        $conversa->tokens_resposta_total += $tokensResposta;
        $conversa->custo_estimado += $custoMensagem;
        $conversa->ultima_mensagem_em = $enviadaEm;

        if (! empty($data['status_conversa'])) {
            $conversa->status = $data['status_conversa'];

            if ($data['status_conversa'] !== 'em_andamento') {
                $conversa->finalizada_em = $enviadaEm;
            } else {
                $conversa->retomada_em = $enviadaEm;
            }
        }

        if (! empty($data['motivo_transferencia'])) {
            $conversa->motivo_transferencia = $data['motivo_transferencia'];
        }

        if (! empty($data['agente_id']) && ! $conversa->agente_id) {
            $conversa->agente_id = $data['agente_id'];
        }

        if (! empty($data['avaliacao']) && ! $conversa->avaliada_em) {
            $conversa->avaliacao = $data['avaliacao'];
            $conversa->avaliada_em = $enviadaEm;
        }

        $conversa->save();

        return response()->json([
            'duplicada' => false,
            'mensagem_id' => $mensagem->id,
            'conversa_id' => $conversa->id,

            'link_avaliacao' => $conversa->linkAvaliacao(),

            'midia_url_publica' => $midiaPath ? Storage::disk('public')->url($midiaPath) : null,
        ], 201);
    }
}
