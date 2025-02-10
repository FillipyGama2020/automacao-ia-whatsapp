<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ContatoBloqueado;
use App\Services\ImportadorContatosBloqueadosService;
use Illuminate\Http\Request;

class PortalContatoBloqueadoController extends Controller
{
    public function __construct(private ImportadorContatosBloqueadosService $importador)
    {
    }

    public function index(Request $request)
    {
        $contatos = $request->user()->cliente
            ->contatosBloqueados()
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

        return view('portal.contatos-bloqueados.index', compact('contatos'));
    }

    public function create()
    {
        $contato = new ContatoBloqueado();

        return view('portal.contatos-bloqueados.create', compact('contato'));
    }

    public function store(Request $request)
    {
        $data = $this->validateContato($request);

        $request->user()->cliente->contatosBloqueados()->create($data);

        return redirect()->route('portal.contatos-bloqueados.index')
            ->with('status', 'Contato adicionado à lista de bloqueio.');
    }

    public function edit(Request $request, ContatoBloqueado $contato)
    {
        abort_if($contato->cliente_id !== $request->user()->cliente_id, 404);

        return view('portal.contatos-bloqueados.edit', compact('contato'));
    }

    public function update(Request $request, ContatoBloqueado $contato)
    {
        abort_if($contato->cliente_id !== $request->user()->cliente_id, 404);

        $data = $this->validateContato($request);

        $contato->update($data);

        return redirect()->route('portal.contatos-bloqueados.index')->with('status', 'Contato atualizado.');
    }

    public function destroy(Request $request, ContatoBloqueado $contato)
    {
        abort_if($contato->cliente_id !== $request->user()->cliente_id, 404);

        $contato->delete();

        return redirect()->route('portal.contatos-bloqueados.index')
            ->with('status', 'Contato removido da lista de bloqueio.');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $resumo = $this->importador->importar($request->user()->cliente, $request->file('arquivo'));

        return redirect()->route('portal.contatos-bloqueados.index')->with(
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
