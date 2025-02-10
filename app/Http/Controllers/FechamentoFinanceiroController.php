<?php

namespace App\Http\Controllers;

use App\Models\FechamentoFinanceiro;
use App\Services\FechamentoFinanceiroService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FechamentoFinanceiroController extends Controller
{
    public function index()
    {
        $fechamentos = FechamentoFinanceiro::with('cliente', 'fechadoPor')
            ->orderByDesc('competencia')
            ->orderBy('cliente_id')
            ->paginate(30);

        return view('financeiro.fechamentos.index', compact('fechamentos'));
    }

    public function store(Request $request, FechamentoFinanceiroService $service)
    {
        $data = $request->validate([
            'competencia' => 'required|date_format:Y-m',
            'forcar' => 'nullable|boolean',
        ]);

        $competencia = Carbon::createFromFormat('Y-m', $data['competencia'])->startOfMonth();

        $resultado = $service->fecharCompetencia($competencia, $request->boolean('forcar'), $request->user()->id);

        return redirect()->route('financeiro.fechamentos.index')->with('status', sprintf(
            'Fechamento de %s: %d criado(s), %d atualizado(s), %d pulado(s) de %d cliente(s) ativo(s).',
            $competencia->format('m/Y'),
            $resultado['criados'],
            $resultado['atualizados'],
            $resultado['pulados'],
            $resultado['total_clientes']
        ));
    }
}
