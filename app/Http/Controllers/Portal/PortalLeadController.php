<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortalLeadController extends Controller
{
    public function index(Request $request)
    {
        $cliente = $request->user()->cliente;

        abort_unless($cliente->leads_portal_habilitado, 404);

        $leads = $cliente->leads()
            ->when($request->filled('classificacao'), fn ($q) => $q->where('classificacao', $request->classificacao))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('data_inicio'), fn ($q) => $q->whereDate('capturado_em', '>=', $request->data_inicio))
            ->when($request->filled('data_fim'), fn ($q) => $q->whereDate('capturado_em', '<=', $request->data_fim))
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = $request->string('busca');
                $q->where(function ($sub) use ($busca) {
                    $sub->where('nome', 'like', "%{$busca}%")
                        ->orWhere('telefone', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%");
                });
            })
            ->orderByDesc('capturado_em')
            ->paginate(20)
            ->withQueryString();

        $resumo = [
            'total' => $cliente->leads()->count(),
            'quentes' => $cliente->leads()->where('classificacao', 'quente')->count(),
            'convertidos' => $cliente->leads()->where('status', 'convertido')->count(),
        ];

        return view('portal.leads.index', compact('leads', 'resumo'));
    }
}
