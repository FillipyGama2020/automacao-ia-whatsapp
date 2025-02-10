<?php

namespace App\Services;

use App\Models\Campanha;
use App\Models\Cliente;
use App\Models\Conversa;
use App\Models\Lead;
use App\Models\MessageTemplate;
use App\Models\PrecoCampanha;
use Illuminate\Support\Collection;

class CampanhaService
{
    public function __construct(private EnviarMensagemWhatsappService $enviarMensagemService)
    {
    }

    public function enviarAgora(Campanha $campanha): void
    {
        $campanha->load('envios.lead', 'messageTemplate', 'cliente.whatsappIntegracao');
        $campanha->update(['status' => 'enviando']);

        $integracao = $campanha->cliente->whatsappIntegracao;
        $template = $campanha->messageTemplate;
        $mapeamento = $campanha->variaveis_mapeamento ?? [];

        if (! $integracao) {
            $campanha->envios()->where('status', 'pendente')->update([
                'status' => 'falhou',
                'erro' => 'WhatsApp não está conectado para este cliente.',
            ]);
            $campanha->update(['status' => 'concluida', 'enviado_em' => now()]);

            return;
        }

        foreach ($campanha->envios()->where('status', 'pendente')->get() as $envio) {
            $lead = $envio->lead;
            $valores = $this->resolverValores($lead, $template, $mapeamento);

            $resultado = $this->enviarMensagemService->enviarTemplate(
                $integracao, $lead->telefone, $template->nome, $template->idioma, $valores
            );

            if (! $resultado['ok']) {
                $envio->update(['status' => 'falhou', 'erro' => $resultado['message']]);

                continue;
            }

            $conversa = $this->resolverOuCriarConversa($campanha->cliente, $lead);

            $mensagem = $conversa->mensagens()->create([
                'remetente' => 'campanha',
                'tipo' => 'template',
                'conteudo' => $this->renderizarCorpo($template->corpo, $valores),
                'wamid' => $resultado['wamid'] ?? null,
                'status_entrega' => 'enviada',
                'enviada_em' => now(),
            ]);

            $conversa->update(['ultima_mensagem_em' => now()]);

            $envio->update(['status' => 'enviado', 'mensagem_id' => $mensagem->id]);
        }

        $campanha->update(['status' => 'concluida', 'enviado_em' => now()]);
    }

    private function resolverOuCriarConversa(Cliente $cliente, Lead $lead): Conversa
    {
        $conversa = Conversa::where('cliente_id', $cliente->id)
            ->where('contato_telefone', $lead->telefone)
            ->whereIn('status', ['em_andamento', 'transferida_humano'])
            ->where('ultima_mensagem_em', '>=', now()->subHours(config('conversas.janela_inatividade_horas')))
            ->latest('iniciada_em')
            ->first();

        if ($conversa) {
            return $conversa;
        }

        return Conversa::create([
            'cliente_id' => $cliente->id,
            'contato_telefone' => $lead->telefone,
            'contato_nome' => $lead->nome,
            'status' => 'em_andamento',
            'iniciada_em' => now(),
            'ultima_mensagem_em' => now(),
        ]);
    }

    private function renderizarCorpo(string $corpo, array $valores): string
    {
        foreach ($valores as $indice => $valor) {
            $corpo = str_replace('{{'.($indice + 1).'}}', $valor, $corpo);
        }

        return $corpo;
    }

    public function resolverDestinatarios(Cliente $cliente, string $tipoDestinatario, ?string $filtroLote, ?int $leadId): Collection
    {
        if ($tipoDestinatario === 'individual') {
            $lead = $cliente->leads()->where('id', $leadId)->where('aceita_campanhas', true)->first();

            return $lead ? collect([$lead]) : collect();
        }

        return $cliente->leads()
            ->where('aceita_campanhas', true)
            ->when($filtroLote === 'quente', fn ($q) => $q->where('classificacao', 'quente'))
            ->when($filtroLote === 'convertido', fn ($q) => $q->where('status', 'convertido'))
            ->get();
    }

    public function validarMapeamento(MessageTemplate $template, array $variaveis): ?string
    {
        foreach ($template->variaveisUsadas() as $posicao) {
            $mapeamento = $variaveis[$posicao] ?? null;

            if (! $mapeamento || empty($mapeamento['valor'])) {
                return "A variável {{{$posicao}}} não foi mapeada — escolha um campo do lead ou digite um valor fixo antes de confirmar.";
            }

            if ($mapeamento['tipo'] === 'campo' && ! array_key_exists($mapeamento['valor'], Lead::camposMapeaveisCampanha())) {
                return "A variável {{{$posicao}}} está mapeada pra um campo de lead inválido.";
            }
        }

        return null;
    }

    public function resolverValores(Lead $lead, MessageTemplate $template, array $variaveis): array
    {
        $valores = [];

        foreach ($template->variaveisUsadas() as $posicao) {
            $mapeamento = $variaveis[$posicao];

            $valores[] = $mapeamento['tipo'] === 'campo'
                ? (string) ($lead->{$mapeamento['valor']} ?? '')
                : (string) $mapeamento['valor'];
        }

        return $valores;
    }

    public function calcularValorCobrado(MessageTemplate $template, int $totalLeads): float
    {
        $precoPorLead = (float) (PrecoCampanha::where('categoria', $template->categoria)->value('preco_por_lead') ?? 0);

        return round($totalLeads * $precoPorLead, 2);
    }
}
