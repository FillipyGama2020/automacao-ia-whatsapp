<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Conversa;
use App\Services\EnviarMensagemWhatsappService;
use App\Services\MascaradorDadosSensiveis;
use Illuminate\Http\Request;

class PortalConversaController extends Controller
{
    public function index(Request $request)
    {
        $cliente = $request->user()->cliente;

        $conversas = $this->filtrarConversas($request, $cliente)
            ->withCount('mensagens')
            ->orderByDesc('ultima_mensagem_em')
            ->paginate(20)
            ->withQueryString();

        $agentes = $cliente->agentes()->orderBy('nome')->get(['id', 'nome']);

        return view('portal.conversas.index', compact('conversas', 'agentes'));
    }

    public function show(Request $request, Conversa $conversa)
    {
        abort_if($conversa->cliente_id !== $request->user()->cliente_id, 404);

        $conversa->load(['agente', 'mensagens' => fn ($q) => $q->orderBy('enviada_em')]);

        return view('portal.conversas.show', compact('conversa'));
    }

    public function responder(Request $request, Conversa $conversa, EnviarMensagemWhatsappService $servico)
    {
        abort_if($conversa->cliente_id !== $request->user()->cliente_id, 404);

        $data = $request->validate([
            'mensagem' => 'required|string',
        ]);

        $cliente = $request->user()->cliente;

        $integracao = $conversa->whatsappIntegracao ?? $cliente->whatsappIntegracao;

        if (! $integracao) {
            return back()->with('error', 'O WhatsApp ainda não está conectado.');
        }

        $conversa->loadMissing('agente');
        $mascararCpf = $conversa->agente?->mascarar_cpf ?? true;
        $mascararCartao = $conversa->agente?->mascarar_cartao ?? true;
        $texto = MascaradorDadosSensiveis::mascarar($data['mensagem'], $mascararCpf, $mascararCartao);

        $resultado = $servico->enviar($integracao, $conversa->contato_telefone, $texto);

        if (! $resultado['ok']) {
            return back()->with('error', 'Não foi possível enviar: '.$resultado['message']);
        }

        $conversa->mensagens()->create([
            'remetente' => 'humano',
            'tipo' => 'texto',
            'conteudo' => $texto,
            'wamid' => $resultado['wamid'] ?? null,
            'status_entrega' => 'enviada',
            'enviada_em' => now(),
        ]);

        $conversa->update([
            'status' => 'transferida_humano',
            'ultima_mensagem_em' => now(),
            'finalizada_em' => now(),
            'motivo_transferencia' => $conversa->motivo_transferencia ?: 'Atendimento assumido manualmente pelo portal.',
        ]);

        return back()->with('status', 'Mensagem enviada.');
    }

    public function retomarIa(Request $request, Conversa $conversa)
    {
        abort_if($conversa->cliente_id !== $request->user()->cliente_id, 404);

        $conversa->mensagens()->create([
            'remetente' => 'sistema',
            'tipo' => 'texto',
            'conteudo' => 'Atendimento devolvido para a IA.',
            'enviada_em' => now(),
        ]);

        $conversa->update(['status' => 'em_andamento', 'finalizada_em' => null, 'retomada_em' => now()]);

        return back()->with('status', 'Atendimento devolvido para a IA.');
    }

    private function filtrarConversas(Request $request, $cliente)
    {
        return $cliente->conversas()
            ->when($request->filled('agente_id'), fn ($q) => $q->where('agente_id', $request->agente_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('data_inicio'), fn ($q) => $q->whereDate('iniciada_em', '>=', $request->data_inicio))
            ->when($request->filled('data_fim'), fn ($q) => $q->whereDate('iniciada_em', '<=', $request->data_fim));
    }
}
