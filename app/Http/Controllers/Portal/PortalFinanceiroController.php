<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\FechamentoFinanceiro;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PortalFinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $cliente = $request->user()->cliente;

        $consumoAtual = $cliente->limiteConversasMensaisInfo();

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

        return view('portal.financeiro', compact('fechamento', 'competencia', 'competenciasDisponiveis', 'consumoAtual'));
    }
}
