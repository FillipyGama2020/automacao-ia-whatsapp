<?php

namespace Database\Seeders;

use App\Models\Agente;
use App\Models\Cliente;
use App\Models\Conversa;
use App\Models\PrecoModelo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ConversaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PrecoModeloSeeder::class);

        $cliente = Cliente::first();

        if (! $cliente) {
            $this->command->warn('Nenhum cliente encontrado — cadastre um cliente antes de rodar este seeder.');

            return;
        }

        $agentes = $cliente->agentes()->get();
        $agentePrincipal = $agentes->first();
        $agenteSecundario = $agentes->skip(1)->first() ?? $agentePrincipal;

        foreach ($this->cenarios($agentePrincipal?->id, $agenteSecundario?->id) as $cenario) {
            $this->criarConversa($cliente->id, $cenario);
        }

        $this->command->info('Conversas de exemplo criadas.');
    }

    private function cenarios(?int $agenteA, ?int $agenteB): array
    {
        return [

            [
                'agente_id' => $agenteA,
                'contato_telefone' => '+5511987654321',
                'contato_nome' => 'Marina Souza',
                'status' => 'resolvida_ia',
                'avaliacao' => 5,
                'inicio' => now()->subDays(6)->setTime(9, 12),
                'mensagens' => [
                    ['contato', 'Oi, vocês abrem aos sábados?'],
                    ['agente_ia', 'Olá, Marina! Sim, atendemos aos sábados das 8h às 12h. De segunda a sexta funcionamos das 8h às 18h. Posso ajudar com mais alguma coisa?'],
                    ['contato', 'Perfeito, obrigada!'],
                    ['agente_ia', 'Por nada! Qualquer coisa é só chamar. 😊'],
                ],
            ],

            [
                'agente_id' => $agenteA,
                'contato_telefone' => '+5511998877665',
                'contato_nome' => 'Carlos Eduardo',
                'status' => 'resolvida_ia',
                'avaliacao' => 4,
                'inicio' => now()->subDays(4)->setTime(14, 30),
                'mensagens' => [
                    ['contato', 'Quanto custa o produto X?'],
                    ['agente_ia', 'O produto X está saindo por R$ 59,90. Quer que eu separe um pra você?'],
                    ['contato', 'Tem no tamanho M?'],
                    ['agente_ia', 'Temos sim! Tamanho M disponível em estoque.'],
                    ['contato', 'Show, vou passar aí amanhã'],
                    ['agente_ia', 'Combinado! Vamos te esperar. Até amanhã!'],
                ],
            ],

            [
                'agente_id' => $agenteB,
                'contato_telefone' => '+5521976543210',
                'contato_nome' => 'Roberta Lima',
                'status' => 'transferida_humano',
                'motivo_transferencia' => 'Cliente insatisfeito pediu atendimento humano',
                'avaliacao' => 2,
                'inicio' => now()->subDays(3)->setTime(11, 5),
                'mensagens' => [
                    ['contato', 'Meu pedido não chegou e já faz uma semana'],
                    ['agente_ia', 'Sinto muito pelo atraso! Pode me passar o número do pedido para eu verificar?'],
                    ['contato', 'Não tenho o número aqui, mas isso é um absurdo, quero falar com uma pessoa de verdade'],
                    ['agente_ia', 'Entendo sua frustração. Vou te transferir para um de nossos atendentes agora mesmo.'],
                    ['humano', 'Oi Roberta, aqui é o Diego. Vi seu caso, vou resolver isso agora.'],
                ],
            ],

            [
                'agente_id' => $agenteA,
                'contato_telefone' => '+5511911223344',
                'contato_nome' => null,
                'status' => 'em_andamento',
                'inicio' => now()->subHours(2),
                'mensagens' => [
                    ['contato', 'Boa tarde, vocês fazem entrega em Osasco?'],
                    ['agente_ia', 'Boa tarde! Sim, entregamos em Osasco. Qual bairro você está?'],
                    ['contato', 'Centro'],
                ],
            ],

            [
                'agente_id' => $agenteB,
                'contato_telefone' => '+5511955443322',
                'contato_nome' => 'José Almeida',
                'status' => 'abandonada',
                'inicio' => now()->subDays(9)->setTime(16, 45),
                'mensagens' => [
                    ['contato', 'Oi, vi o anúncio de vocês no Instagram'],
                    ['agente_ia', 'Olá, José! Que bom que chegou até a gente. Como posso ajudar?'],
                ],
            ],

            [
                'agente_id' => $agenteA,
                'contato_telefone' => '+5511933221100',
                'contato_nome' => 'Fernanda Costa',
                'status' => 'resolvida_ia',
                'avaliacao' => 5,
                'inicio' => now()->subDays(1)->setTime(10, 0),
                'mensagens' => [
                    ['contato', 'Preciso confirmar meu cadastro, meu CPF é 123.456.789-09'],
                    ['agente_ia', 'Obrigada! Confirmei seu cadastro aqui no sistema.'],
                    ['contato', 'Ótimo, era só isso'],
                ],
            ],
        ];
    }

    private function criarConversa(int $clienteId, array $cenario): void
    {
        $inicio = Carbon::parse($cenario['inicio']);
        $tokensPromptTotal = 0;
        $tokensRespostaTotal = 0;
        $custoTotal = 0;
        $ultimaMensagemEm = $inicio;
        $modelo = $cenario['agente_id'] ? Agente::find($cenario['agente_id'])?->modelo : null;

        $conversa = Conversa::create([
            'cliente_id' => $clienteId,
            'agente_id' => $cenario['agente_id'],
            'contato_telefone' => $cenario['contato_telefone'],
            'contato_nome' => $cenario['contato_nome'] ?? null,
            'status' => $cenario['status'],
            'motivo_transferencia' => $cenario['motivo_transferencia'] ?? null,
            'avaliacao' => $cenario['avaliacao'] ?? null,
            'iniciada_em' => $inicio,
            'ultima_mensagem_em' => $inicio,
            'finalizada_em' => in_array($cenario['status'], ['resolvida_ia', 'transferida_humano', 'abandonada'])
                ? $inicio->copy()->addMinutes(count($cenario['mensagens']) * 3)
                : null,
        ]);

        $momento = $inicio->copy();

        foreach ($cenario['mensagens'] as [$remetente, $texto]) {
            $momento = $momento->copy()->addMinutes(rand(1, 4));

            $tokensPrompt = null;
            $tokensResposta = null;
            $custoMensagem = 0;

            if ($remetente === 'agente_ia') {
                $tokensPrompt = (int) (str_word_count($texto) * 1.8) + rand(20, 60);
                $tokensResposta = (int) (str_word_count($texto) * 1.3) + rand(10, 30);
                $tokensPromptTotal += $tokensPrompt;
                $tokensRespostaTotal += $tokensResposta;
                $custoMensagem = PrecoModelo::calcularCusto($modelo, $tokensPrompt, $tokensResposta);
                $custoTotal += $custoMensagem;
            }

            $conversa->mensagens()->create([
                'remetente' => $remetente,
                'tipo' => 'texto',
                'conteudo' => $texto,
                'modelo' => $remetente === 'agente_ia' ? $modelo : null,
                'wamid' => 'seed-' . uniqid(),
                'tokens_prompt' => $tokensPrompt,
                'tokens_resposta' => $tokensResposta,
                'custo' => $custoMensagem,
                'status_entrega' => $remetente === 'contato' ? null : 'lida',
                'enviada_em' => $momento,
            ]);

            $ultimaMensagemEm = $momento;
        }

        $conversa->update([
            'tokens_prompt_total' => $tokensPromptTotal,
            'tokens_resposta_total' => $tokensRespostaTotal,
            'custo_estimado' => round($custoTotal, 6),
            'ultima_mensagem_em' => $ultimaMensagemEm,
        ]);
    }
}
