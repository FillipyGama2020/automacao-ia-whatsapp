<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RelatorioController extends Controller
{
    public function show(Request $request, Cliente $cliente, RelatorioService $service)
    {
        $competencia = $this->resolverCompetencia($request);
        $relatorio = $service->gerarParaCliente($cliente, $competencia);

        return view('clientes.relatorio.show', compact('cliente', 'relatorio', 'competencia'));
    }

    public function pdf(Request $request, Cliente $cliente, RelatorioService $service)
    {
        $competencia = $this->resolverCompetencia($request);
        $relatorio = $service->gerarParaCliente($cliente, $competencia);

        $nomeArquivo = 'relatorio-'.str($cliente->nome_empresa)->slug().'-'.$competencia->format('Y-m').'.pdf';

        return Pdf::loadView('clientes.relatorio.pdf', compact('cliente', 'relatorio', 'competencia'))
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
