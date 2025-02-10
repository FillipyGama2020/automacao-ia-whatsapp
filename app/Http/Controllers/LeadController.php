<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Lead;
use App\Services\ImportadorLeadsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct(private ImportadorLeadsService $importador)
    {
    }

    public function index(Request $request, Cliente $cliente)
    {
        $leads = $cliente->leads()
            ->with('agente')
            ->when($request->filled('classificacao'), fn ($q) => $q->where('classificacao', $request->classificacao))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('data_inicio'), fn ($q) => $q->whereDate('capturado_em', '>=', $request->data_inicio))
            ->when($request->filled('data_fim'), fn ($q) => $q->whereDate('capturado_em', '<=', $request->data_fim))
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = $request->string('busca');
                $q->where(function ($sub) use ($busca) {
                    $sub->where('nome', 'like', "%{$busca}%")
                        ->orWhere('telefone', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%");
                });
            })
            ->orderByDesc('capturado_em')
            ->paginate(20)
            ->withQueryString();

        $resumo = [
            'total' => $cliente->leads()->count(),
            'quentes' => $cliente->leads()->where('classificacao', 'quente')->count(),
            'convertidos' => $cliente->leads()->where('status', 'convertido')->count(),
        ];

        return view('clientes.leads.index', compact('cliente', 'leads', 'resumo'));
    }

    public function kanban(Request $request, Cliente $cliente)
    {
        $leads = $cliente->leads()
            ->with('agente')
            ->when($request->filled('classificacao'), fn ($q) => $q->where('classificacao', $request->classificacao))
            ->when($request->filled('data_inicio'), fn ($q) => $q->whereDate('capturado_em', '>=', $request->data_inicio))
            ->when($request->filled('data_fim'), fn ($q) => $q->whereDate('capturado_em', '<=', $request->data_fim))
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = $request->string('busca');
                $q->where(function ($sub) use ($busca) {
                    $sub->where('nome', 'like', "%{$busca}%")
                        ->orWhere('telefone', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%");
                });
            })
            ->orderByDesc('capturado_em')
            ->get()
            ->groupBy('status');

        $resumo = [
            'total' => $cliente->leads()->count(),
            'quentes' => $cliente->leads()->where('classificacao', 'quente')->count(),
            'convertidos' => $cliente->leads()->where('status', 'convertido')->count(),
        ];

        return view('clientes.leads.kanban', compact('cliente', 'leads', 'resumo'));
    }

    public function atualizarStatus(Request $request, Cliente $cliente, Lead $lead)
    {
        abort_if($lead->cliente_id !== $cliente->id, 404);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:novo,em_contato,convertido,perdido',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lead->update($validator->validated());

        return response()->json(['status' => $lead->status]);
    }

    public function create(Cliente $cliente)
    {
        $lead = new Lead();

        return view('clientes.leads.create', compact('cliente', 'lead'));
    }

    public function store(Request $request, Cliente $cliente)
    {
        $data = $this->validateLead($request, $cliente);
        $data['origem'] = 'manual';
        $data['capturado_em'] = now();
        $data['opt_out_em'] = $data['aceita_campanhas'] ? null : now();

        $cliente->leads()->create($data);

        return redirect()->route('clientes.leads.index', $cliente)->with('status', 'Lead cadastrado com sucesso.');
    }

    public function edit(Cliente $cliente, Lead $lead)
    {
        abort_if($lead->cliente_id !== $cliente->id, 404);

        $lead->load('conversa', 'agente');

        return view('clientes.leads.edit', compact('cliente', 'lead'));
    }

    public function update(Request $request, Cliente $cliente, Lead $lead)
    {
        abort_if($lead->cliente_id !== $cliente->id, 404);

        $data = $this->validateLead($request, $cliente, $lead->id);

        if ($data['aceita_campanhas'] !== $lead->aceita_campanhas) {
            $data['opt_out_em'] = $data['aceita_campanhas'] ? null : now();
        }

        $lead->update($data);

        return redirect()->route('clientes.leads.index', $cliente)->with('status', 'Lead atualizado com sucesso.');
    }

    public function importar(Request $request, Cliente $cliente)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $resumo = $this->importador->importar($cliente, $request->file('arquivo'));

        return redirect()->route('clientes.leads.index', $cliente)->with(
            'status',
            "Importação concluída: {$resumo['importados']} adicionados, {$resumo['duplicados']} já existiam, {$resumo['invalidos']} com telefone inválido."
        );
    }

    private function validateLead(Request $request, Cliente $cliente, ?int $leadId = null): array
    {
        $data = $request->validate([
            'nome' => 'nullable|string|max:191',
            'telefone' => [
                'required', 'string', 'max:30',
                Rule::unique('leads', 'telefone')->where('cliente_id', $cliente->id)->ignore($leadId),
            ],
            'email' => 'nullable|email|max:191',
            'interesse' => 'nullable|string',
            'classificacao' => 'nullable|in:frio,morno,quente',
            'status' => 'required|in:novo,em_contato,convertido,perdido',
            'observacoes' => 'nullable|string',
            'aceita_campanhas' => 'boolean',
        ]);

        $data['aceita_campanhas'] = $request->boolean('aceita_campanhas');

        return $data;
    }
}
