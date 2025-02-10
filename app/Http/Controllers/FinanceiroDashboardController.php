<?php

namespace App\Http\Controllers;

use App\Models\FechamentoFinanceiro;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceiroDashboardController extends Controller
{
    public function index(Request $request)
    {
        $competenciasDisponiveis = FechamentoFinanceiro::selectRaw('DISTINCT competencia')
            ->orderByDesc('competencia')
            ->pluck('competencia')
            ->map(fn ($c) => Carbon::parse($c));

        $competencia = $request->filled('competencia')
            ? Carbon::createFromFormat('Y-m', $request->competencia)->startOfMonth()
            : ($competenciasDisponiveis->first() ?? now()->subMonthNoOverflow()->startOfMonth());

        $fechamentos = FechamentoFinanceiro::with('cliente')
            ->whereDate('competencia', $competencia->toDateString())
            ->get()
            ->sortByDesc('lucro_bruto')
            ->values();

        $consolidado = [
            'receita_recorrente' => $fechamentos->sum('receita_recorrente'),
            'receita_implantacao' => $fechamentos->sum('receita_implantacao'),
            'receita_excedente' => $fechamentos->sum('receita_excedente'),
            'receita_campanhas' => $fechamentos->sum('receita_campanhas'),
            'custo_ia' => $fechamentos->sum('custo_ia'),
            'custo_meta' => $fechamentos->sum('custo_meta'),
            'custo_infra_rateado' => $fechamentos->sum('custo_infra_rateado'),
            'lucro_bruto' => $fechamentos->sum('lucro_bruto'),
        ];

        $consolidado['receita_total'] = $consolidado['receita_recorrente'] + $consolidado['receita_implantacao'] + $consolidado['receita_excedente'] + $consolidado['receita_campanhas'];
        $consolidado['custo_total'] = $consolidado['custo_ia'] + $consolidado['custo_meta'] + $consolidado['custo_infra_rateado'];
        $consolidado['margem_percentual'] = $consolidado['receita_total'] > 0
            ? round(($consolidado['lucro_bruto'] / $consolidado['receita_total']) * 100, 2)
            : null;

        return view('financeiro.dashboard', compact('fechamentos', 'consolidado', 'competencia', 'competenciasDisponiveis'));
    }
}
