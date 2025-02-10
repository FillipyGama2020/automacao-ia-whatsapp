<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SuporteTicket;
use Illuminate\Http\Request;

class PortalSuporteController extends Controller
{
    public function index(Request $request)
    {
        $tickets = $request->user()->cliente
            ->suporteTickets()
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('portal.suporte.index', compact('tickets'));
    }

    public function create()
    {
        return view('portal.suporte.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'assunto' => 'required|string|max:191',
            'mensagem' => 'required|string',
        ]);

        $ticket = SuporteTicket::create([
            'cliente_id' => $request->user()->cliente_id,
            'aberto_por_id' => $request->user()->id,
            'assunto' => $data['assunto'],
            'status' => 'aberto',
        ]);

        $ticket->mensagens()->create([
            'autor_id' => $request->user()->id,
            'remetente' => 'cliente',
            'mensagem' => $data['mensagem'],
        ]);

        return redirect()->route('portal.suporte.show', $ticket)->with('status', 'Chamado aberto com sucesso.');
    }

    public function show(Request $request, SuporteTicket $ticket)
    {
        abort_if($ticket->cliente_id !== $request->user()->cliente_id, 404);

        $ticket->load('mensagens.autor');

        return view('portal.suporte.show', compact('ticket'));
    }

    public function responder(Request $request, SuporteTicket $ticket)
    {
        abort_if($ticket->cliente_id !== $request->user()->cliente_id, 404);

        $data = $request->validate(['mensagem' => 'required|string']);

        $ticket->mensagens()->create([
            'autor_id' => $request->user()->id,
            'remetente' => 'cliente',
            'mensagem' => $data['mensagem'],
        ]);

        $ticket->update(['status' => 'aberto']);

        return redirect()->route('portal.suporte.show', $ticket)->with('status', 'Resposta enviada.');
    }
}
