<?php

namespace App\Http\Controllers;

use App\Models\Campanha;
use App\Models\CampanhaEnvio;
use App\Models\Cliente;
use App\Models\MessageTemplate;
use App\Services\CampanhaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampanhaController extends Controller
{
    public function __construct(private CampanhaService $campanhaService)
    {
    }

    public function index(Cliente $cliente)
    {
        $campanhas = $cliente->campanhas()
            ->with('messageTemplate')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('clientes.campanhas.index', compact('cliente', 'campanhas'));
    }

    public function create(Cliente $cliente)
    {
        abort_unless($cliente->mensagens_proativas_habilitado, 404, 'Mensagens proativas não estão habilitadas para este cliente.');

        $templates = MessageTemplate::where('cliente_id', $cliente->id)->where('status', 'aprovado')->get();
        $leads = $cliente->leads()->where('aceita_campanhas', true)->orderBy('nome')->get(['id', 'nome', 'telefone']);

        $templatesData = $templates->mapWithKeys(function (MessageTemplate $template) {
            return [$template->id => [
                'corpo' => $template->corpo,
                'posicoes' => $template->variaveisUsadas(),
                'labels' => $template->variaveis ?? [],
            ]];
        });

        return view('clientes.campanhas.create', compact('cliente', 'templates', 'leads', 'templatesData'));
    }

    public function store(Request $request, Cliente $cliente)
    {
        abort_unless($cliente->mensagens_proativas_habilitado, 404, 'Mensagens proativas não estão habilitadas para este cliente.');

        $data = $request->validate([
            'message_template_id' => 'required|exists:message_templates,id',
            'tipo_destinatario' => 'required|in:individual,lote',
            'lead_id' => 'required_if:tipo_destinatario,individual|nullable|exists:leads,id',
            'filtro_lote' => 'required_if:tipo_destinatario,lote|nullable|in:todos,quente,convertido',
            'variaveis' => 'nullable|array',
            'variaveis.*.tipo' => 'required|in:campo,fixo',
            'variaveis.*.valor' => 'required|string|max:191',
            'agendado_para' => 'nullable|date|after:now',
        ]);

        $template = MessageTemplate::where('id', $data['message_template_id'])
            ->where('cliente_id', $cliente->id)
            ->where('status', 'aprovado')
            ->firstOrFail();

        $variaveis = $data['variaveis'] ?? [];

        $erroMapeamento = $this->campanhaService->validarMapeamento($template, $variaveis);
        if ($erroMapeamento) {
            return back()->withInput()->with('error', $erroMapeamento);
        }

        $destinatarios = $this->campanhaService->resolverDestinatarios(
            $cliente,
            $data['tipo_destinatario'],
            $data['filtro_lote'] ?? null,
            $data['lead_id'] ?? null
        );

        if ($destinatarios->isEmpty()) {
            return back()->withInput()->with('error', 'Nenhum lead elegível encontrado — confira o opt-out e o filtro escolhido.');
        }

        $valorCobrado = $this->campanhaService->calcularValorCobrado($template, $destinatarios->count());

        $agendadoPara = ! empty($data['agendado_para']) ? $data['agendado_para'] : null;

        $campanha = DB::transaction(function () use ($cliente, $template, $data, $variaveis, $destinatarios, $valorCobrado, $agendadoPara, $request) {
            $campanha = Campanha::create([
                'cliente_id' => $cliente->id,
                'message_template_id' => $template->id,
                'criado_por' => $request->user()->id,
                'tipo_destinatario' => $data['tipo_destinatario'],
                'filtro_lote' => $data['filtro_lote'] ?? null,
                'variaveis_mapeamento' => $variaveis,
                'agendado_para' => $agendadoPara,
                'status' => $agendadoPara ? 'agendada' : 'rascunho',
                'total_leads' => $destinatarios->count(),
                'valor_cobrado' => $valorCobrado,
            ]);

            foreach ($destinatarios as $lead) {
                CampanhaEnvio::create([
                    'campanha_id' => $campanha->id,
                    'lead_id' => $lead->id,
                    'status' => 'pendente',
                ]);
            }

            return $campanha;
        });

        $status = $agendadoPara
            ? "Campanha agendada pra {$campanha->agendado_para->format('d/m/Y H:i')} — {$campanha->total_leads} destinatário(s), valor R$ ".number_format($valorCobrado, 2, ',', '.').'.'
            : "Campanha criada como rascunho — {$campanha->total_leads} destinatário(s), valor R$ ".number_format($valorCobrado, 2, ',', '.').'. Revise antes de confirmar o envio.';

        return redirect()->route('clientes.campanhas.show', [$cliente, $campanha])->with('status', $status);
    }

    public function show(Cliente $cliente, Campanha $campanha)
    {
        abort_if($campanha->cliente_id !== $cliente->id, 404);

        $campanha->load('messageTemplate');

        $resumoEnvios = $campanha->envios()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $envios = $campanha->envios()
            ->with('lead')
            ->orderByDesc('id')
            ->paginate(30);

        return view('clientes.campanhas.show', compact('cliente', 'campanha', 'envios', 'resumoEnvios'));
    }

    public function enviar(Cliente $cliente, Campanha $campanha)
    {
        abort_if($campanha->cliente_id !== $cliente->id, 404);
        abort_unless(in_array($campanha->status, ['rascunho', 'agendada'], true), 404, 'Esta campanha já foi processada.');

        $this->campanhaService->enviarAgora($campanha);

        $campanha->refresh();
        $falhas = $campanha->envios()->where('status', 'falhou')->count();
        $status = $falhas > 0
            ? "Campanha enviada — {$falhas} de {$campanha->total_leads} envio(s) falharam, confira o motivo abaixo."
            : 'Campanha enviada com sucesso pra todos os destinatários.';

        return redirect()->route('clientes.campanhas.show', [$cliente, $campanha])->with('status', $status);
    }

    public function cancelar(Cliente $cliente, Campanha $campanha)
    {
        abort_if($campanha->cliente_id !== $cliente->id, 404);
        abort_unless(in_array($campanha->status, ['rascunho', 'agendada'], true), 404, 'Esta campanha já foi processada.');

        $campanha->update(['status' => 'cancelada']);

        return redirect()->route('clientes.campanhas.show', [$cliente, $campanha])->with('status', 'Campanha cancelada — nenhuma mensagem foi enviada.');
    }
}
