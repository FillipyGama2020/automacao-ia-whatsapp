<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ContatoBloqueado;
use App\Services\ImportadorContatosBloqueadosService;
use Illuminate\Http\Request;

class ContatoBloqueadoController extends Controller
{
    public function __construct(private ImportadorContatosBloqueadosService $importador)
    {
    }

    public function index(Request $request, Cliente $cliente)
    {
        $contatos = $cliente->contatosBloqueados()
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = $request->string('busca');
                $q->where(function ($sub) use ($busca) {
                    $sub->where('nome', 'like', "%{$busca}%")
                        ->orWhere('telefone', 'like', "%{$busca}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('clientes.contatos-bloqueados.index', compact('cliente', 'contatos'));
    }

    public function create(Cliente $cliente)
    {
        $contato = new ContatoBloqueado();

        return view('clientes.contatos-bloqueados.create', compact('cliente', 'contato'));
    }

    public function store(Request $request, Cliente $cliente)
    {
        $data = $this->validateContato($request);

        $cliente->contatosBloqueados()->create($data);

        return redirect()->route('clientes.contatos-bloqueados.index', $cliente)
            ->with('status', 'Contato adicionado à lista de bloqueio.');
    }

    public function edit(Cliente $cliente, ContatoBloqueado $contato)
    {
        abort_if($contato->cliente_id !== $cliente->id, 404);

        return view('clientes.contatos-bloqueados.edit', ['cliente' => $cliente, 'contato' => $contato]);
    }

    public function update(Request $request, Cliente $cliente, ContatoBloqueado $contato)
    {
        abort_if($contato->cliente_id !== $cliente->id, 404);

        $data = $this->validateContato($request);

        $contato->update($data);

        return redirect()->route('clientes.contatos-bloqueados.index', $cliente)
            ->with('status', 'Contato atualizado.');
    }

    public function destroy(Cliente $cliente, ContatoBloqueado $contato)
    {
        abort_if($contato->cliente_id !== $cliente->id, 404);

        $contato->delete();

        return redirect()->route('clientes.contatos-bloqueados.index', $cliente)
            ->with('status', 'Contato removido da lista de bloqueio.');
    }

    public function importar(Request $request, Cliente $cliente)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $resumo = $this->importador->importar($cliente, $request->file('arquivo'));

        return redirect()->route('clientes.contatos-bloqueados.index', $cliente)->with(
            'status',
            "Importação concluída: {$resumo['importados']} adicionados, {$resumo['duplicados']} já existiam, {$resumo['invalidos']} com telefone inválido."
        );
    }

    private function validateContato(Request $request): array
    {
        return $request->validate([
            'telefone' => 'required|string|max:30',
            'nome' => 'nullable|string|max:191',
            'observacoes' => 'nullable|string',
        ]);
    }
}
