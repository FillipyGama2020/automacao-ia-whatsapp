<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use Illuminate\Http\Request;

class PlanoController extends Controller
{
    public function index()
    {
        $planos = Plano::where('personalizado', false)
            ->withCount('clientes')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $personalizados = Plano::where('personalizado', true)
            ->with('clienteDono')
            ->orderBy('nome')
            ->get();

        return view('planos.index', compact('planos', 'personalizados'));
    }

    public function create()
    {
        return view('planos.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatePlano($request);

        $plano = Plano::create($data);

        $this->syncRecursos($plano, $request->input('recursos', []));

        return redirect()->route('planos.index')->with('status', 'Plano cadastrado com sucesso.');
    }

    public function edit(Plano $plano)
    {
        abort_if($plano->personalizado, 404);

        $plano->load('recursos');

        return view('planos.edit', compact('plano'));
    }

    public function update(Request $request, Plano $plano)
    {
        abort_if($plano->personalizado, 404);

        $data = $this->validatePlano($request);

        $plano->update($data);

        $this->syncRecursos($plano, $request->input('recursos', []));

        return redirect()->route('planos.index')->with('status', 'Plano atualizado com sucesso.');
    }

    public function destroy(Plano $plano)
    {
        abort_if($plano->personalizado, 404);

        if ($plano->clientes()->exists()) {
            return redirect()->route('planos.index')
                ->with('error', 'Este plano tem clientes atribuídos e não pode ser removido — desative-o em vez de excluir.');
        }

        $plano->delete();

        return redirect()->route('planos.index')->with('status', 'Plano removido.');
    }

    private function validatePlano(Request $request): array
    {
        $data = $request->validate([
            'nome' => 'required|string|max:191',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
            'ordem' => 'nullable|integer|min:0',
            'preco_mensal' => 'required|numeric|min:0',
            'preco_anual' => 'nullable|numeric|min:0',
            'taxa_implantacao' => 'nullable|numeric|min:0',
            'limite_conversas_mensais' => 'nullable|integer|min:1',
            'preco_conversa_excedente' => 'nullable|numeric|min:0',
            'limite_agentes' => 'nullable|integer|min:1',
            'preco_agente_adicional' => 'nullable|numeric|min:0',
            'permite_anexos' => 'nullable|boolean',
            'preco_anexos_adicional' => 'nullable|numeric|min:0',
            'recursos' => 'nullable|array',
            'recursos.*' => 'nullable|string|max:255',
        ]);

        $data['ativo'] = $request->boolean('ativo');
        $data['ordem'] = $data['ordem'] ?? 0;

        $data['limite_conversas_mensais'] = $request->filled('limite_conversas_mensais')
            ? (int) $request->input('limite_conversas_mensais')
            : null;
        $data['preco_conversa_excedente'] = $data['limite_conversas_mensais'] === null || ! $request->filled('preco_conversa_excedente')
            ? null
            : $data['preco_conversa_excedente'];

        $data['limite_agentes'] = $request->filled('limite_agentes')
            ? (int) $request->input('limite_agentes')
            : null;
        $data['preco_agente_adicional'] = $data['limite_agentes'] === null || ! $request->filled('preco_agente_adicional')
            ? null
            : $data['preco_agente_adicional'];

        $data['permite_anexos'] = $request->boolean('permite_anexos');
        $data['preco_anexos_adicional'] = $data['permite_anexos'] || ! $request->filled('preco_anexos_adicional')
            ? null
            : $data['preco_anexos_adicional'];

        unset($data['recursos']);

        return $data;
    }

    private function syncRecursos(Plano $plano, array $recursos): void
    {
        $plano->recursos()->delete();

        foreach (array_values($recursos) as $indice => $recurso) {
            if (trim((string) $recurso) === '') {
                continue;
            }

            $plano->recursos()->create(['descricao' => $recurso, 'ordem' => $indice]);
        }
    }
}
