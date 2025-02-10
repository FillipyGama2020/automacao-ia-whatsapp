<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\FechamentoFinanceiro;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClienteFinanceiroController extends Controller
{
    public function show(Request $request, Cliente $cliente)
    {
        $competenciasDisponiveis = FechamentoFinanceiro::where('cliente_id', $cliente->id)
            ->orderByDesc('competencia')
            ->pluck('competencia')
            ->map(fn ($c) => Carbon::parse($c));

        $competencia = $request->filled('competencia')
            ? Carbon::createFromFormat('Y-m', $request->competencia)->startOfMonth()
            : ($competenciasDisponiveis->first() ?? now()->subMonthNoOverflow()->startOfMonth());

        $fechamento = FechamentoFinanceiro::where('cliente_id', $cliente->id)
            ->whereDate('competencia', $competencia->toDateString())
            ->first();

        return view('clientes.financeiro.show', compact('cliente', 'fechamento', 'competencia', 'competenciasDisponiveis'));
    }
}
