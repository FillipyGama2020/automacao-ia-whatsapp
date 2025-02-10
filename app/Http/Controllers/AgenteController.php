<?php

namespace App\Http\Controllers;

use App\Models\Agente;
use App\Models\Cliente;
use App\Services\AgenteConteudoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AgenteController extends Controller
{
    public function __construct(private AgenteConteudoService $conteudo)
    {
    }

    public function index(Cliente $cliente)
    {
        $agentes = $cliente->agentes()->with('horarios')->orderBy('nome')->get();
        $limiteAgentes = $this->limiteAgentesInfo($cliente);

        return view('clientes.agentes.index', compact('cliente', 'agentes', 'limiteAgentes'));
    }

    public function create(Cliente $cliente)
    {
        if ($this->limiteAgentesInfo($cliente)['atingido']) {
            return redirect()->route('clientes.agentes.index', $cliente)
                ->with('error', $this->mensagemLimiteAgentes($cliente));
        }

        $agente = new Agente();
        $integracoes = $cliente->whatsappIntegracoes()->orderBy('id')->get();

        return view('clientes.agentes.create', compact('cliente', 'agente', 'integracoes'));
    }

    public function store(Request $request, Cliente $cliente)
    {
        if ($this->limiteAgentesInfo($cliente)['atingido']) {
            return redirect()->route('clientes.agentes.index', $cliente)
                ->with('error', $this->mensagemLimiteAgentes($cliente));
        }

        $data = $this->validateAgente($request, $cliente);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('agentes', 'public');
        }

        $agente = $cliente->agentes()->create($data);

        $this->conteudo->syncHorarios($agente, $request->input('horarios', []));
        $this->conteudo->syncFeriados($agente, $request->input('feriados', []));
        $this->conteudo->syncRegras($agente, $request->input('regras', []));
        $this->conteudo->syncFaqs($agente, $request->input('faqs', []));
        $this->conteudo->syncProdutos($agente, $request->input('produtos', []), $request->file('produtos', []));
        $this->conteudo->syncPoliticas($agente, $request->input('politicas', []));
        $this->conteudo->syncDocumentos($agente, $request->input('documentos', []), $request->file('documentos', []));

        return redirect()->route('clientes.agentes.index', $cliente)->with('status', 'Agente criado com sucesso.');
    }

    public function edit(Cliente $cliente, Agente $agente)
    {
        abort_if($agente->cliente_id !== $cliente->id, 404);

        $agente->load('horarios', 'feriados', 'regras', 'faqs', 'produtos', 'politicas', 'documentos');
        $integracoes = $cliente->whatsappIntegracoes()->orderBy('id')->get();

        return view('clientes.agentes.edit', compact('cliente', 'agente', 'integracoes'));
    }

    public function update(Request $request, Cliente $cliente, Agente $agente)
    {
        abort_if($agente->cliente_id !== $cliente->id, 404);

        $data = $this->validateAgente($request, $cliente);

        if ($request->hasFile('avatar')) {
            if ($agente->avatar) {
                Storage::disk('public')->delete($agente->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('agentes', 'public');
        }

        $agente->update($data);

        $this->conteudo->syncHorarios($agente, $request->input('horarios', []));
        $this->conteudo->syncFeriados($agente, $request->input('feriados', []));
        $this->conteudo->syncRegras($agente, $request->input('regras', []));
        $this->conteudo->syncFaqs($agente, $request->input('faqs', []));
        $this->conteudo->syncProdutos($agente, $request->input('produtos', []), $request->file('produtos', []));
        $this->conteudo->syncPoliticas($agente, $request->input('politicas', []));
        $this->conteudo->syncDocumentos($agente, $request->input('documentos', []), $request->file('documentos', []));

        return redirect()->route('clientes.agentes.index', $cliente)->with('status', 'Agente atualizado com sucesso.');
    }

    public function toggle(Cliente $cliente, Agente $agente)
    {
        abort_if($agente->cliente_id !== $cliente->id, 404);

        $agente->update(['ativo' => ! $agente->ativo]);

        return redirect()->route('clientes.agentes.index', $cliente)->with('status', 'Status do agente atualizado.');
    }

    public function destroy(Cliente $cliente, Agente $agente)
    {
        abort_if($agente->cliente_id !== $cliente->id, 404);

        if ($agente->avatar) {
            Storage::disk('public')->delete($agente->avatar);
        }

        Storage::disk('public')->delete($agente->produtos()->whereNotNull('imagem')->pluck('imagem')->all());
        Storage::disk('public')->delete($agente->documentos()->whereNotNull('arquivo')->pluck('arquivo')->all());

        $agente->delete();

        return redirect()->route('clientes.agentes.index', $cliente)->with('status', 'Agente removido.');
    }

    private function validateAgente(Request $request, Cliente $cliente): array
    {
        $request->merge(['ativo' => $request->boolean('ativo')]);

        $numerosDoCliente = $cliente->whatsappIntegracoes()->count();

        $data = $request->validate([
            'nome' => 'required|string|max:191',
            'objetivo' => 'nullable|string',
            'whatsapp_integracao_id' => [
                $numerosDoCliente >= 2 ? 'required' : 'nullable',
                'integer',
                Rule::exists('whatsapp_integracoes', 'id')->where('cliente_id', $cliente->id),
            ],
            'descricao_interna' => 'nullable|string',
            'departamento' => 'nullable|string|max:100',
            'idioma' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'avatar' => 'nullable|image|max:8192',
            'cor' => 'required|string|max:7',
            'prompt_principal' => 'required|string',
            'prompt_complementar' => 'nullable|string',
            'prompt_horario_fechado' => 'nullable|string',
            'prompt_transferencia_humano' => 'nullable|string',
            'prompt_despedida' => 'nullable|string',
            'enviar_link_avaliacao' => 'boolean',
            'prompt_nao_sei_responder' => 'nullable|string',
            'prompt_vendas' => 'nullable|string',
            'prompt_suporte' => 'nullable|string',
            'modelo' => 'required|string|max:100',
            'top_p' => 'required|numeric|min:0|max:1',
            'frequency_penalty' => 'required|numeric|min:-2|max:2',
            'presence_penalty' => 'required|numeric|min:-2|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:128000',
            'timeout' => 'required|integer|min:1|max:300',
            'modelo_fallback' => 'nullable|string|max:100',
            'temperatura' => 'required|numeric|min:0|max:2',
            'nome_ia' => 'nullable|string|max:100',
            'papel' => 'nullable|string|max:100',
            'tom_voz' => 'nullable|string|max:100',
            'emojis' => 'required|in:nunca,poucos,normal,muito',
            'tamanho_respostas' => 'required|in:curtas,medias,longas',
            'forma_tratamento' => 'required|in:senhor,voce,primeiro_nome,personalizado',
            'forma_tratamento_personalizada' => 'nullable|required_if:forma_tratamento,personalizado|string|max:100',
            'mensagem_fora_horario' => 'nullable|string',
            'transferencia_automatica_fora_horario' => 'boolean',
            'retomar_ao_abrir_horario' => 'boolean',
            'limite_mensagens_conversa' => 'nullable|integer|min:1',
            'limite_tokens_conversa' => 'nullable|integer|min:1',
            'prompt_limite_atingido' => 'nullable|string',
            'limite_mensagens_minuto' => 'nullable|integer|min:1',
            'limite_mensagens_dia' => 'nullable|integer|min:1',
            'retomada_automatica_minutos' => 'nullable|integer|min:1',
            'prompt_retomada_automatica' => 'nullable|string',
            'permitir_atendimento_humano' => 'boolean',
            'mascarar_cpf' => 'boolean',
            'mascarar_cartao' => 'boolean',
            'permitir_anexos' => 'boolean',
            'tipos_anexos_permitidos' => 'nullable|array',
            'tipos_anexos_permitidos.*' => 'string|in:imagem,documento,audio,video',
            'memoria_habilitada' => 'boolean',
            'memoria_dias_lembrar' => 'required|integer|min:1|max:365',
            'memoria_resumo_automatico' => 'boolean',
            'memoria_salvar_preferencias' => 'boolean',
            'memoria_salvar_nome' => 'boolean',
            'memoria_salvar_endereco' => 'boolean',
            'ferramentas_habilitadas' => 'nullable|array',
            'ferramentas_habilitadas.*' => ['string', Rule::in(array_keys(Agente::ferramentasDisponiveis()))],
            'horarios' => 'nullable|array',
            'horarios.*.fechado' => 'boolean',
            'horarios.*.hora_inicio' => 'nullable|date_format:H:i',
            'horarios.*.hora_fim' => 'nullable|date_format:H:i',
            'feriados' => 'nullable|array',
            'feriados.*.data' => 'nullable|date',
            'feriados.*.descricao' => 'nullable|string|max:191',
            'regras' => 'nullable|array',
            'regras.*' => 'nullable|string|max:500',
            'faqs' => 'nullable|array',
            'faqs.*.pergunta' => 'nullable|string|max:500',
            'faqs.*.resposta' => 'nullable|string',
            'produtos' => 'nullable|array',
            'produtos.*.id' => 'nullable|integer',
            'produtos.*.tipo' => 'nullable|in:produto,servico',
            'produtos.*.nome' => 'nullable|string|max:191',
            'produtos.*.preco' => 'nullable|numeric|min:0',
            'produtos.*.descricao' => 'nullable|string',
            'produtos.*.categoria' => 'nullable|string|max:100',
            'produtos.*.imagem' => 'nullable|image|max:8192',
            'politicas' => 'nullable|array',
            'politicas.*.titulo' => 'nullable|string|max:191',
            'politicas.*.conteudo' => 'nullable|string',
            'documentos' => 'nullable|array',
            'documentos.*.id' => 'nullable|integer',
            'documentos.*.tipo' => 'nullable|in:arquivo,link',
            'documentos.*.nome' => 'nullable|string|max:191',
            'documentos.*.arquivo' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240',
            'documentos.*.url' => 'nullable|url|max:500',
            'documentos.*.descricao' => 'nullable|string',
            'ativo' => 'boolean',
        ]);

        $data['tipos_anexos_permitidos'] = ! empty($data['tipos_anexos_permitidos'])
            ? implode(',', $data['tipos_anexos_permitidos'])
            : null;

        $data['ferramentas_habilitadas'] = ! empty($data['ferramentas_habilitadas'])
            ? implode(',', $data['ferramentas_habilitadas'])
            : null;

        unset($data['avatar'], $data['horarios'], $data['feriados'], $data['regras'], $data['faqs'], $data['produtos'], $data['politicas'], $data['documentos']);

        return $data;
    }

    private function limiteAgentesInfo(Cliente $cliente): array
    {
        $cliente->loadMissing('plano');

        $limite = $cliente->plano->limite_agentes ?? null;
        $atual = $cliente->agentes()->count();

        return [
            'limite' => $limite,
            'atual' => $atual,
            'atingido' => $limite !== null && $atual >= $limite,
        ];
    }

    private function mensagemLimiteAgentes(Cliente $cliente): string
    {
        $limite = $cliente->plano->limite_agentes;
        $plano = $cliente->plano->nome;

        return "Este cliente atingiu o limite de {$limite} agente(s) do plano {$plano} — faça upgrade de plano para adicionar mais.";
    }
}
