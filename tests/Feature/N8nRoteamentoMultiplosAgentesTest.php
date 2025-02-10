<?php

namespace Tests\Feature;

use App\Models\Agente;
use App\Models\Cliente;
use App\Models\Conversa;
use App\Models\Plano;
use App\Models\User;
use App\Models\WhatsappIntegracao;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class N8nRoteamentoMultiplosAgentesTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql'];

    protected function setUp(): void
    {
        foreach (['DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'painel_whatsapp_ia'] as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        parent::setUp();
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer '.config('services.n8n.master_token')];
    }

    private function agenteAtivo(Cliente $cliente, string $nome, ?WhatsappIntegracao $integracao = null, string $objetivo = 'Atendimento geral'): Agente
    {
        return $cliente->agentes()->create([
            'nome' => $nome,
            'objetivo' => $objetivo,
            'prompt_principal' => 'Prompt de teste',
            'modelo' => 'gpt-4o-mini',
            'temperatura' => 0.7,
            'ativo' => true,
            'whatsapp_integracao_id' => $integracao?->id,
        ]);
    }

    private function clienteComNumero(): array
    {
        $plano = Plano::create(['nome' => 'Plano Teste Fase3', 'preco_mensal' => 100]);
        $cliente = Cliente::create(['nome_empresa' => 'Cliente Teste Fase3 '.Str::random(6), 'plano_id' => $plano->id]);
        $integracao = $cliente->whatsappIntegracoes()->create([
            'phone_number_id' => 'fase3_'.Str::random(8),
            'status' => 'conectado',
        ]);

        return [$cliente, $integracao];
    }

    public function test_show_com_1_candidato_resolve_direto_sem_classificar(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $agente = $this->agenteAtivo($cliente, 'Único');

        $response = $this->withHeaders($this->headers())->getJson('/api/n8n/contexto?'.http_build_query([
            'phone_number_id' => $integracao->phone_number_id,
            'contato_telefone' => '5527999990001',
        ]));

        $response->assertOk();
        $response->assertJsonPath('agente.id', $agente->id);
        $response->assertJsonPath('candidatos_agentes', null);
    }

    public function test_show_com_2_candidatos_modo_equipe_off_escolhe_menor_id_sem_classificar(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $agenteMenor = $this->agenteAtivo($cliente, 'Financeiro');
        $this->agenteAtivo($cliente, 'Atendimento');

        $response = $this->withHeaders($this->headers())->getJson('/api/n8n/contexto?'.http_build_query([
            'phone_number_id' => $integracao->phone_number_id,
            'contato_telefone' => '5527999990002',
        ]));

        $response->assertOk();
        $response->assertJsonPath('agente.id', $agenteMenor->id);
        $response->assertJsonPath('candidatos_agentes', null);
    }

    public function test_show_com_2_candidatos_modo_equipe_on_devolve_candidatos_sem_agente(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $integracao->update(['modo_equipe_agentes' => true]);
        $financeiro = $this->agenteAtivo($cliente, 'Financeiro', null, 'Cuida de cobranças e boletos');
        $atendimento = $this->agenteAtivo($cliente, 'Atendimento', null, 'Agenda horários e tira dúvidas gerais');

        $response = $this->withHeaders($this->headers())->getJson('/api/n8n/contexto?'.http_build_query([
            'phone_number_id' => $integracao->phone_number_id,
            'contato_telefone' => '5527999990003',
        ]));

        $response->assertOk();
        $response->assertJsonPath('agente', null);
        $candidatos = $response->json('candidatos_agentes');
        $this->assertCount(2, $candidatos);
        $this->assertEqualsCanonicalizing([$financeiro->id, $atendimento->id], array_column($candidatos, 'id'));
    }

    public function test_show_segunda_chamada_com_agente_id_classificado_resolve_aquele_agente(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $integracao->update(['modo_equipe_agentes' => true]);
        $this->agenteAtivo($cliente, 'Financeiro');
        $atendimento = $this->agenteAtivo($cliente, 'Atendimento');

        $response = $this->withHeaders($this->headers())->getJson('/api/n8n/contexto?'.http_build_query([
            'phone_number_id' => $integracao->phone_number_id,
            'contato_telefone' => '5527999990004',
            'agente_id' => $atendimento->id,
        ]));

        $response->assertOk();
        $response->assertJsonPath('agente.id', $atendimento->id);
    }

    public function test_show_segunda_chamada_com_agente_id_de_outro_numero_do_mesmo_cliente_e_rejeitado(): void
    {
        [$cliente, $integracaoA] = $this->clienteComNumero();
        $integracaoB = $cliente->whatsappIntegracoes()->create(['phone_number_id' => 'fase3_'.Str::random(8), 'status' => 'conectado']);
        $integracaoA->update(['modo_equipe_agentes' => true]);

        $this->agenteAtivo($cliente, 'Financeiro A1', $integracaoA);
        $this->agenteAtivo($cliente, 'Atendimento A2', $integracaoA);
        $agenteDeB = $this->agenteAtivo($cliente, 'Agente do outro número', $integracaoB);

        $response = $this->withHeaders($this->headers())->getJson('/api/n8n/contexto?'.http_build_query([
            'phone_number_id' => $integracaoA->phone_number_id,
            'contato_telefone' => '5527999990005',
            'agente_id' => $agenteDeB->id,
        ]));

        $response->assertOk();
        $response->assertJsonPath('agente', null);
    }

    public function test_colegas_equipe_populado_para_cliente_de_1_numero_em_modo_equipe(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $integracao->update(['modo_equipe_agentes' => true]);

        $financeiro = $this->agenteAtivo($cliente, 'Financeiro');
        $this->agenteAtivo($cliente, 'Atendimento');

        $conversa = Conversa::create([
            'cliente_id' => $cliente->id,
            'agente_id' => $financeiro->id,
            'whatsapp_integracao_id' => $integracao->id,
            'contato_telefone' => '5527999990006',
            'status' => 'em_andamento',
            'iniciada_em' => now(),
            'ultima_mensagem_em' => now(),
        ]);

        $response = $this->withHeaders($this->headers())->getJson('/api/n8n/contexto?'.http_build_query([
            'phone_number_id' => $integracao->phone_number_id,
            'contato_telefone' => '5527999990006',
        ]));

        $response->assertOk();
        $response->assertJsonPath('agente.id', $financeiro->id);
        $colegas = $response->json('colegas_equipe');
        $this->assertCount(1, $colegas);
        $this->assertSame('Atendimento', $colegas[0]['nome']);
    }

    public function test_colegas_equipe_vazio_quando_modo_equipe_desligado(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $financeiro = $this->agenteAtivo($cliente, 'Financeiro');
        $this->agenteAtivo($cliente, 'Atendimento');

        $conversa = Conversa::create([
            'cliente_id' => $cliente->id,
            'agente_id' => $financeiro->id,
            'whatsapp_integracao_id' => $integracao->id,
            'contato_telefone' => '5527999990007',
            'status' => 'em_andamento',
            'iniciada_em' => now(),
            'ultima_mensagem_em' => now(),
        ]);

        $response = $this->withHeaders($this->headers())->getJson('/api/n8n/contexto?'.http_build_query([
            'phone_number_id' => $integracao->phone_number_id,
            'contato_telefone' => '5527999990007',
        ]));

        $response->assertOk();
        $response->assertJsonPath('colegas_equipe', []);
    }

    public function test_transferir_agente_atualiza_conversa(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $integracao->update(['modo_equipe_agentes' => true]);
        $financeiro = $this->agenteAtivo($cliente, 'Financeiro');
        $atendimento = $this->agenteAtivo($cliente, 'Atendimento');

        $conversa = Conversa::create([
            'cliente_id' => $cliente->id,
            'agente_id' => $financeiro->id,
            'whatsapp_integracao_id' => $integracao->id,
            'contato_telefone' => '5527999990008',
            'status' => 'em_andamento',
            'iniciada_em' => now(),
            'ultima_mensagem_em' => now(),
        ]);

        $response = $this->withHeaders($this->headers())->postJson('/api/n8n/transferir-agente', [
            'conversa_id' => $conversa->id,
            'agente_id' => $atendimento->id,
        ]);

        $response->assertOk();
        $this->assertSame($atendimento->id, $conversa->fresh()->agente_id);
    }

    public function test_transferir_agente_de_outro_cliente_e_rejeitado(): void
    {
        [$clienteA, $integracaoA] = $this->clienteComNumero();
        [$clienteB] = $this->clienteComNumero();
        $agenteA = $this->agenteAtivo($clienteA, 'Financeiro A');
        $agenteB = $this->agenteAtivo($clienteB, 'Agente de outro cliente');

        $conversa = Conversa::create([
            'cliente_id' => $clienteA->id,
            'agente_id' => $agenteA->id,
            'whatsapp_integracao_id' => $integracaoA->id,
            'contato_telefone' => '5527999990009',
            'status' => 'em_andamento',
            'iniciada_em' => now(),
            'ultima_mensagem_em' => now(),
        ]);

        $response = $this->withHeaders($this->headers())->postJson('/api/n8n/transferir-agente', [
            'conversa_id' => $conversa->id,
            'agente_id' => $agenteB->id,
        ]);

        $response->assertStatus(422);
        $this->assertSame($agenteA->id, $conversa->fresh()->agente_id);
    }

    public function test_transferir_agente_conversa_inexistente_404(): void
    {
        [$cliente] = $this->clienteComNumero();
        $agente = $this->agenteAtivo($cliente, 'Financeiro');

        $response = $this->withHeaders($this->headers())->postJson('/api/n8n/transferir-agente', [
            'conversa_id' => 999999999,
            'agente_id' => $agente->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_admin_liga_e_desliga_modo_equipe_pelo_painel(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [$cliente, $integracao] = $this->clienteComNumero();
        $this->assertFalse($integracao->fresh()->modo_equipe_agentes);

        $response = $this->actingAs($admin)->patch(route('clientes.whatsapp.modo-equipe', [$cliente, $integracao]));

        $response->assertRedirect();
        $this->assertTrue($integracao->fresh()->modo_equipe_agentes);

        $this->actingAs($admin)->patch(route('clientes.whatsapp.modo-equipe', [$cliente, $integracao]));
        $this->assertFalse($integracao->fresh()->modo_equipe_agentes);
    }

    public function test_admin_salva_instrucoes_extras_de_classificacao(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [$cliente, $integracao] = $this->clienteComNumero();

        $response = $this->actingAs($admin)->put(
            route('clientes.whatsapp.prompt-classificacao', [$cliente, $integracao]),
            ['prompt_classificacao_extra' => 'Se mencionar numero de pedido, prefira o Pos-venda.']
        );

        $response->assertRedirect();
        $this->assertSame(
            'Se mencionar numero de pedido, prefira o Pos-venda.',
            $integracao->fresh()->prompt_classificacao_extra
        );
    }

    public function test_prompt_classificacao_extra_aparece_na_resposta_de_show(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $integracao->update([
            'modo_equipe_agentes' => true,
            'prompt_classificacao_extra' => 'Instrucao customizada de teste.',
        ]);
        $this->agenteAtivo($cliente, 'Financeiro');
        $this->agenteAtivo($cliente, 'Atendimento');

        $response = $this->withHeaders($this->headers())->getJson('/api/n8n/contexto?'.http_build_query([
            'phone_number_id' => $integracao->phone_number_id,
            'contato_telefone' => '5527999990010',
        ]));

        $response->assertOk();
        $response->assertJsonPath('prompt_classificacao_extra', 'Instrucao customizada de teste.');
    }

    public function test_mensagens_quase_simultaneas_do_mesmo_contato_nao_criam_duas_conversas(): void
    {
        [$cliente, $integracao] = $this->clienteComNumero();
        $this->agenteAtivo($cliente, 'Único');
        $token = $integracao->gerarNovoApiToken();

        $payload = [
            'contato_telefone' => '5527999990011',
            'remetente' => 'contato',
            'tipo' => 'texto',
            'conteudo' => 'primeira mensagem',
        ];

        $r1 = $this->withHeaders(['Authorization' => 'Bearer '.$token])->postJson('/api/mensagens', $payload);
        $r2 = $this->withHeaders(['Authorization' => 'Bearer '.$token])->postJson('/api/mensagens', array_merge($payload, ['conteudo' => 'segunda mensagem', 'wamid' => 'wamid-teste-2']));

        $r1->assertCreated();
        $r2->assertCreated();
        $this->assertSame($r1->json('conversa_id'), $r2->json('conversa_id'));
        $this->assertSame(1, Conversa::where('cliente_id', $cliente->id)->where('contato_telefone', '5527999990011')->count());
    }
}
