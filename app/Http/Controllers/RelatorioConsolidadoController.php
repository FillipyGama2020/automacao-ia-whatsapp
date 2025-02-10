<?php

namespace App\Http\Controllers;

use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RelatorioConsolidadoController extends Controller
{
    public function index(Request $request, RelatorioService $service)
    {
        $competencia = $this->resolverCompetencia($request);
        $relatorio = $service->gerarConsolidado($competencia);

        return view('relatorios.consolidado', compact('relatorio', 'competencia'));
    }

    public function pdf(Request $request, RelatorioService $service)
    {
        $competencia = $this->resolverCompetencia($request);
        $relatorio = $service->gerarConsolidado($competencia);

        $nomeArquivo = 'relatorio-consolidado-'.$competencia->format('Y-m').'.pdf';

        return Pdf::loadView('relatorios.consolidado-pdf', compact('relatorio', 'competencia'))
            ->setPaper('a4')
            ->download($nomeArquivo);
    }

    private function resolverCompetencia(Request $request): Carbon
    {
        return $request->filled('competencia')
            ? Carbon::createFromFormat('Y-m', $request->competencia)->startOfMonth()
            : now()->subMonthNoOverflow()->startOfMonth();
    }
}
