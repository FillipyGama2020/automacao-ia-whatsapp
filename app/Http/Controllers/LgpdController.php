<?php

namespace App\Http\Controllers;

use App\Models\ExclusaoLgpd;
use App\Services\ExclusaoLgpdService;
use Illuminate\Http\Request;

class LgpdController extends Controller
{
    public function index(Request $request, ExclusaoLgpdService $service)
    {
        $telefone = $request->query('telefone');
        $conversas = $telefone ? $service->buscarPorTelefone($telefone) : collect();
        $leads = $telefone ? $service->buscarLeadsPorTelefone($telefone) : collect();

        $historico = ExclusaoLgpd::with('executadoPor')
            ->orderByDesc('executado_em')
            ->limit(50)
            ->get();

        return view('lgpd.index', compact('telefone', 'conversas', 'leads', 'historico'));
    }

    public function destroy(Request $request, ExclusaoLgpdService $service)
    {
        $data = $request->validate([
            'telefone' => 'required|string|max:30',
            'confirmo' => 'required|accepted',
        ]);

        $conversas = $service->buscarPorTelefone($data['telefone']);
        $leads = $service->buscarLeadsPorTelefone($data['telefone']);

        if ($conversas->isEmpty() && $leads->isEmpty()) {
            return redirect()->route('lgpd.index', ['telefone' => $data['telefone']])
                ->with('error', 'Nenhuma conversa ou lead encontrado para esse telefone.');
        }

        $log = $service->excluir(
            $conversas,
            'solicitacao_titular',
            $data['telefone'],
            $request->user()->id
        );

        return redirect()->route('lgpd.index')
            ->with('status', "Excluído: {$log->quantidade_conversas} conversa(s), {$log->quantidade_mensagens} mensagem(ns) e {$log->quantidade_leads} lead(s) do telefone {$data['telefone']}.");
    }
}
