<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PortalRelatorioController extends Controller
{
    public function show(Request $request, RelatorioService $service)
    {
        $competencia = $this->resolverCompetencia($request);
        $relatorio = $service->gerarParaCliente($request->user()->cliente, $competencia);

        return view('portal.relatorio.show', compact('relatorio', 'competencia'));
    }

    public function pdf(Request $request, RelatorioService $service)
    {
        $competencia = $this->resolverCompetencia($request);
        $cliente = $request->user()->cliente;
        $relatorio = $service->gerarParaCliente($cliente, $competencia);

        $nomeArquivo = 'relatorio-'.str($cliente->nome_empresa)->slug().'-'.$competencia->format('Y-m').'.pdf';

        return Pdf::loadView('portal.relatorio.pdf', compact('cliente', 'relatorio', 'competencia'))
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
