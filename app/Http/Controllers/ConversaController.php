<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Conversa;
use App\Services\EnviarMensagemWhatsappService;
use App\Services\MascaradorDadosSensiveis;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversaController extends Controller
{
    public function index(Request $request, Cliente $cliente)
    {
        $conversas = $this->filtrarConversas($request, $cliente)
            ->withCount('mensagens')
            ->orderByDesc('ultima_mensagem_em')
            ->paginate(20)
            ->withQueryString();

        $agentes = $cliente->agentes()->orderBy('nome')->get(['id', 'nome']);

        $resumoMes = $cliente->conversas()
            ->whereBetween('iniciada_em', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('COUNT(*) as total_conversas, COALESCE(SUM(custo_estimado), 0) as total_custo, COUNT(CASE WHEN custo_estimado > 0 THEN 1 END) as conversas_com_ia')
            ->first();

        $limiteConversas = $cliente->limiteConversasMensaisInfo($resumoMes->conversas_com_ia);

        return view('clientes.conversas.index', compact('cliente', 'conversas', 'agentes', 'resumoMes', 'limiteConversas'));
    }

    public function show(Cliente $cliente, Conversa $conversa)
    {
        abort_if($conversa->cliente_id !== $cliente->id, 404);

        $conversa->load(['agente', 'mensagens' => fn ($q) => $q->orderBy('enviada_em')]);

        return view('clientes.conversas.show', compact('cliente', 'conversa'));
    }

    public function responder(Request $request, Cliente $cliente, Conversa $conversa, EnviarMensagemWhatsappService $servico)
    {
        abort_if($conversa->cliente_id !== $cliente->id, 404);

        $data = $request->validate([
            'mensagem' => 'required|string',
        ]);

        $integracao = $conversa->whatsappIntegracao ?? $cliente->whatsappIntegracao;

        if (! $integracao) {
            return back()->with('error', 'Este cliente ainda não tem o WhatsApp conectado.');
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
            'motivo_transferencia' => $conversa->motivo_transferencia ?: 'Atendimento assumido manualmente pelo painel.',
        ]);

        return back()->with('status', 'Mensagem enviada.');
    }

    public function retomarIa(Cliente $cliente, Conversa $conversa)
    {
        abort_if($conversa->cliente_id !== $cliente->id, 404);

        $conversa->mensagens()->create([
            'remetente' => 'sistema',
            'tipo' => 'texto',
            'conteudo' => 'Atendimento devolvido para a IA.',
            'enviada_em' => now(),
        ]);

        $conversa->update(['status' => 'em_andamento', 'finalizada_em' => null, 'retomada_em' => now()]);

        return back()->with('status', 'Atendimento devolvido para a IA.');
    }

    public function exportarCsv(Request $request, Cliente $cliente): StreamedResponse
    {
        $conversas = $this->filtrarConversas($request, $cliente)
            ->with('agente')
            ->withCount('mensagens')
            ->orderBy('iniciada_em')
            ->get();

        $nomeArquivo = 'conversas-'.$cliente->id.'-'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($conversas) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF");

            fputcsv($saida, [
                'ID', 'Contato Nome', 'Contato Telefone', 'Agente', 'Status', 'Avaliação',
                'Mensagens', 'Tokens Prompt', 'Tokens Resposta', 'Custo Estimado (R$)',
                'Iniciada em', 'Última mensagem em', 'Finalizada em', 'Motivo Transferência',
            ], ';');

            foreach ($conversas as $conversa) {
                fputcsv($saida, [
                    $conversa->id,
                    $conversa->contato_nome,
                    $conversa->contato_telefone,
                    $conversa->agente->nome ?? '',
                    Conversa::statusLabels()[$conversa->status] ?? $conversa->status,
                    $conversa->avaliacao,
                    $conversa->mensagens_count,
                    $conversa->tokens_prompt_total,
                    $conversa->tokens_resposta_total,
                    number_format($conversa->custo_estimado, 4, ',', ''),
                    $conversa->iniciada_em?->format('d/m/Y H:i'),
                    $conversa->ultima_mensagem_em?->format('d/m/Y H:i'),
                    $conversa->finalizada_em?->format('d/m/Y H:i'),
                    $conversa->motivo_transferencia,
                ], ';');
            }

            fclose($saida);
        }, $nomeArquivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filtrarConversas(Request $request, Cliente $cliente)
    {
        return $cliente->conversas()
            ->when($request->filled('agente_id'), fn ($q) => $q->where('agente_id', $request->agente_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('contato_telefone'), fn ($q) => $q->where('contato_telefone', 'like', '%'.$request->contato_telefone.'%'))
            ->when($request->filled('data_inicio'), fn ($q) => $q->whereDate('iniciada_em', '>=', $request->data_inicio))
            ->when($request->filled('data_fim'), fn ($q) => $q->whereDate('iniciada_em', '<=', $request->data_fim))
            ->when($request->filled('busca'), function ($q) use ($request) {
                $q->whereHas('mensagens', fn ($m) => $m->where('conteudo', 'like', '%'.$request->busca.'%'));
            });
    }
}
