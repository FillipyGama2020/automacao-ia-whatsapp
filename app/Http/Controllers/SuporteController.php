<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\SuporteTicket;
use Illuminate\Http\Request;

class SuporteController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SuporteTicket::with('cliente')
            ->when($request->filled('cliente_id'), fn ($q) => $q->where('cliente_id', $request->cliente_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $clientes = Cliente::orderBy('nome_empresa')->get(['id', 'nome_empresa']);

        return view('suporte.index', compact('tickets', 'clientes'));
    }

    public function show(SuporteTicket $ticket)
    {
        $ticket->load('mensagens.autor', 'cliente');

        return view('suporte.show', compact('ticket'));
    }

    public function responder(Request $request, SuporteTicket $ticket)
    {
        $data = $request->validate(['mensagem' => 'required|string']);

        $ticket->mensagens()->create([
            'autor_id' => $request->user()->id,
            'remetente' => 'admin',
            'mensagem' => $data['mensagem'],
        ]);

        $ticket->update(['status' => 'respondido']);

        return redirect()->route('suporte.show', $ticket)->with('status', 'Resposta enviada.');
    }

    public function fechar(SuporteTicket $ticket)
    {
        $ticket->update(['status' => 'fechado']);

        return redirect()->route('suporte.show', $ticket)->with('status', 'Chamado fechado.');
    }
}
