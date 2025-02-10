<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Lead;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $cliente = Cliente::first();

        if (! $cliente) {
            $this->command->warn('Nenhum cliente encontrado — cadastre um cliente antes de rodar este seeder.');

            return;
        }

        $conversas = $cliente->conversas()->get();

        if ($conversas->isEmpty()) {
            $this->command->warn('Nenhuma conversa encontrada para este cliente — rode o ConversaSeeder antes.');

            return;
        }

        foreach ($this->cenarios($conversas) as $cenario) {
            Lead::updateOrCreate(
                ['cliente_id' => $cliente->id, 'telefone' => $cenario['telefone']],
                $cenario['dados']
            );
        }

        $this->command->info('Leads de exemplo criados.');
    }

    private function cenarios($conversas): array
    {
        $c1 = $conversas->get(0);
        $c2 = $conversas->get(1);
        $c3 = $conversas->get(2);
        $c4 = $conversas->get(3);
        $c5 = $conversas->get(4);

        return [
            [
                'telefone' => $c1->contato_telefone,
                'dados' => [
                    'agente_id' => $c1->agente_id,
                    'conversa_id' => $c1->id,
                    'nome' => $c1->contato_nome,
                    'email' => 'marina.souza@example.com',
                    'interesse' => 'Quer agendar uma avaliação ainda esta semana.',
                    'classificacao' => 'quente',
                    'status' => 'em_contato',
                    'origem' => 'whatsapp_ia',
                    'observacoes' => 'Já perguntou sobre formas de pagamento duas vezes.',
                    'capturado_em' => Carbon::now()->subDays(3),
                ],
            ],
            [
                'telefone' => $c2->contato_telefone,
                'dados' => [
                    'agente_id' => $c2->agente_id,
                    'conversa_id' => $c2->id,
                    'nome' => $c2->contato_nome,
                    'email' => null,
                    'interesse' => 'Pediu tabela de preços, ainda não respondeu de volta.',
                    'classificacao' => 'morno',
                    'status' => 'novo',
                    'origem' => 'whatsapp_ia',
                    'observacoes' => null,
                    'capturado_em' => Carbon::now()->subDays(6),
                ],
            ],
            [
                'telefone' => $c3->contato_telefone,
                'dados' => [
                    'agente_id' => $c3->agente_id,
                    'conversa_id' => $c3->id,
                    'nome' => $c3->contato_nome,
                    'email' => 'fernanda.costa@example.com',
                    'interesse' => 'Fechou contrato do plano mensal.',
                    'classificacao' => 'quente',
                    'status' => 'convertido',
                    'origem' => 'whatsapp_ia',
                    'observacoes' => 'Cliente satisfeita, indicou uma amiga.',
                    'capturado_em' => Carbon::now()->subDays(10),
                ],
            ],
            [
                'telefone' => $c4->contato_telefone,
                'dados' => [
                    'agente_id' => $c4->agente_id,
                    'conversa_id' => $c4->id,
                    'nome' => $c4->contato_nome,
                    'email' => null,
                    'interesse' => 'Só queria saber o horário de funcionamento.',
                    'classificacao' => 'frio',
                    'status' => 'perdido',
                    'origem' => 'whatsapp_ia',
                    'observacoes' => 'Não demonstrou interesse real em contratar.',
                    'capturado_em' => Carbon::now()->subDays(15),
                ],
            ],
            [
                'telefone' => $c5->contato_telefone,
                'dados' => [
                    'agente_id' => $c5->agente_id,
                    'conversa_id' => $c5->id,
                    'nome' => $c5->contato_nome,
                    'email' => 'jose.almeida@example.com',
                    'interesse' => 'Quer entender se atende a região dele antes de decidir.',
                    'classificacao' => 'morno',
                    'status' => 'em_contato',
                    'origem' => 'whatsapp_ia',
                    'observacoes' => null,
                    'capturado_em' => Carbon::now()->subDays(1),
                ],
            ],
            [
                'telefone' => '+5511911112222',
                'dados' => [
                    'agente_id' => null,
                    'conversa_id' => null,
                    'nome' => 'Lead cadastrado manualmente (indicação)',
                    'email' => 'indicacao@example.com',
                    'interesse' => 'Veio de indicação de um cliente atual, ainda não foi contatado pelo WhatsApp.',
                    'classificacao' => null,
                    'status' => 'novo',
                    'origem' => 'manual',
                    'observacoes' => 'Cadastrado manualmente pelo admin para não perder o contato.',
                    'capturado_em' => Carbon::now()->subHours(5),
                ],
            ],
        ];
    }
}
