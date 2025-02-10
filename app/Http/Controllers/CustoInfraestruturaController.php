<?php

namespace App\Http\Controllers;

use App\Models\CustoInfraestrutura;
use Illuminate\Http\Request;

class CustoInfraestruturaController extends Controller
{
    public function index()
    {
        $custos = CustoInfraestrutura::orderByDesc('ativo')->orderBy('descricao')->get();

        $totalVigente = CustoInfraestrutura::totalVigenteEm(now());

        return view('custos-infraestrutura.index', compact('custos', 'totalVigente'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        CustoInfraestrutura::create($data);

        return redirect()->route('custos-infraestrutura.index')->with('status', 'Custo cadastrado com sucesso.');
    }

    public function update(Request $request, CustoInfraestrutura $custo)
    {
        $data = $this->validar($request);

        $custo->update($data);

        return redirect()->route('custos-infraestrutura.index')->with('status', 'Custo atualizado com sucesso.');
    }

    public function destroy(CustoInfraestrutura $custo)
    {
        $custo->delete();

        return redirect()->route('custos-infraestrutura.index')->with('status', 'Custo removido.');
    }

    private function validar(Request $request): array
    {
        $data = $request->validate([
            'descricao' => 'required|string|max:191',
            'categoria' => 'required|in:vps,dominio,outros',
            'valor_mensal' => 'required|numeric|min:0',
            'ativo' => 'nullable|boolean',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        $data['ativo'] = $request->boolean('ativo');

        return $data;
    }
}
