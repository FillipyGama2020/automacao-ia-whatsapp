<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Agente;
use App\Services\AgenteConteudoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortalAgenteController extends Controller
{
    public function __construct(private AgenteConteudoService $conteudo)
    {
    }

    public function index(Request $request)
    {
        $agentes = $request->user()->cliente
            ->agentes()
            ->with(['horarios', 'feriados', 'regras', 'faqs', 'produtos', 'politicas', 'documentos'])
            ->orderBy('nome')
            ->get();

        return view('portal.agente.index', compact('agentes'));
    }

    public function updateGeral(Request $request, Agente $agente)
    {
        abort_if($agente->cliente_id !== $request->user()->cliente_id, 404);

        $data = $request->validate([
            'nome' => 'required|string|max:191',
            'objetivo' => 'nullable|string',
            'departamento' => 'nullable|string|max:100',
            'cor' => 'required|string|max:7',
            'idioma' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'avatar' => 'nullable|image|max:8192',
            'nome_ia' => 'nullable|string|max:100',
            'papel' => 'nullable|string|max:100',
            'tom_voz' => 'nullable|string|max:100',
            'emojis' => 'required|in:nunca,poucos,normal,muito',
            'tamanho_respostas' => 'required|in:curtas,medias,longas',
            'forma_tratamento' => 'required|in:senhor,voce,primeiro_nome,personalizado',
            'forma_tratamento_personalizada' => 'nullable|required_if:forma_tratamento,personalizado|string|max:100',
        ]);

        if ($request->hasFile('avatar')) {
            if ($agente->avatar) {
                Storage::disk('public')->delete($agente->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('agentes', 'public');
        }

        $agente->update($data);

        return redirect()->route('portal.agente', ['agente' => $agente->id, 'tab' => 'geral'])
            ->with('status', 'Informações gerais atualizadas.');
    }

    public function updateHorarios(Request $request, Agente $agente)
    {
        abort_if($agente->cliente_id !== $request->user()->cliente_id, 404);

        $data = $request->validate([
            'mensagem_fora_horario' => 'nullable|string',
            'transferencia_automatica_fora_horario' => 'boolean',
            'retomar_ao_abrir_horario' => 'boolean',
            'horarios' => 'nullable|array',
            'horarios.*.fechado' => 'boolean',
            'horarios.*.hora_inicio' => 'nullable|date_format:H:i',
            'horarios.*.hora_fim' => 'nullable|date_format:H:i',
            'feriados' => 'nullable|array',
            'feriados.*.data' => 'nullable|date',
            'feriados.*.descricao' => 'nullable|string|max:191',
        ]);

        $agente->update([
            'mensagem_fora_horario' => $data['mensagem_fora_horario'] ?? null,
            'transferencia_automatica_fora_horario' => $data['transferencia_automatica_fora_horario'] ?? false,
            'retomar_ao_abrir_horario' => $data['retomar_ao_abrir_horario'] ?? false,
        ]);

        $this->conteudo->syncHorarios($agente, $request->input('horarios', []));
        $this->conteudo->syncFeriados($agente, $request->input('feriados', []));

        return redirect()->route('portal.agente', ['agente' => $agente->id, 'tab' => 'horarios'])
            ->with('status', 'Horário de funcionamento atualizado.');
    }

    public function updateRegras(Request $request, Agente $agente)
    {
        abort_if($agente->cliente_id !== $request->user()->cliente_id, 404);

        $request->validate([
            'regras' => 'nullable|array',
            'regras.*' => 'nullable|string|max:500',
        ]);

        $this->conteudo->syncRegras($agente, $request->input('regras', []));

        return redirect()->route('portal.agente', ['agente' => $agente->id, 'tab' => 'regras'])
            ->with('status', 'Regras de negócio atualizadas.');
    }

    public function updateConhecimento(Request $request, Agente $agente)
    {
        abort_if($agente->cliente_id !== $request->user()->cliente_id, 404);

        $request->validate([
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
        ]);

        $this->conteudo->syncFaqs($agente, $request->input('faqs', []));
        $this->conteudo->syncProdutos($agente, $request->input('produtos', []), $request->file('produtos', []));
        $this->conteudo->syncPoliticas($agente, $request->input('politicas', []));
        $this->conteudo->syncDocumentos($agente, $request->input('documentos', []), $request->file('documentos', []));

        return redirect()->route('portal.agente', ['agente' => $agente->id, 'tab' => 'conhecimento'])
            ->with('status', 'Conhecimento atualizado.');
    }
}
