<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Plano;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgenteWhatsappIntegracaoTest extends TestCase
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

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    private function clienteComNumeros(int $quantidade): Cliente
    {
        $plano = Plano::create([
            'nome' => 'Plano Teste', 'preco_mensal' => 100, 'limite_conversas_mensais' => null,
            'limite_agentes' => null, 'preco_agente_adicional' => 0,
        ]);
        $cliente = Cliente::create(['nome_empresa' => 'Cliente Teste', 'plano_id' => $plano->id]);
        $prefixo = 'fase2_'.\Illuminate\Support\Str::random(6).'_';

        for ($i = 1; $i <= $quantidade; $i++) {
            $cliente->whatsappIntegracoes()->create(['phone_number_id' => "{$prefixo}{$i}", 'status' => 'conectado']);
        }

        return $cliente;
    }

    public function test_form_de_criar_agente_mostra_dropdown_de_numero_quando_cliente_tem_2_ou_mais(): void
    {
        $cliente = $this->clienteComNumeros(2);

        $response = $this->actingAs($this->admin())->get(route('clientes.agentes.create', $cliente));

        $response->assertOk();
        $response->assertSee('Qual número esse agente atende?');
        $response->assertSee('fase2_');
    }

    public function test_form_de_criar_agente_nao_mostra_dropdown_quando_cliente_tem_1_numero(): void
    {
        $cliente = $this->clienteComNumeros(1);

        $response = $this->actingAs($this->admin())->get(route('clientes.agentes.create', $cliente));

        $response->assertOk();
        $response->assertDontSee('Qual número esse agente atende?');
    }

    public function test_criar_agente_falha_sem_numero_quando_cliente_tem_2_numeros(): void
    {
        $cliente = $this->clienteComNumeros(2);

        $response = $this->actingAs($this->admin())->post(route('clientes.agentes.store', $cliente), [
            'nome' => 'Agente sem numero',
            'idioma' => 'pt-BR', 'timezone' => 'America/Sao_Paulo', 'cor' => '#6366f1',
            'prompt_principal' => 'Prompt', 'modelo' => 'gpt-4o-mini',
            'top_p' => 1, 'frequency_penalty' => 0, 'presence_penalty' => 0, 'timeout' => 30,
            'temperatura' => 0.7, 'emojis' => 'normal', 'tamanho_respostas' => 'medias',
            'forma_tratamento' => 'voce', 'memoria_dias_lembrar' => 30,
        ]);

        $response->assertSessionHasErrors('whatsapp_integracao_id');
        $this->assertSame(0, $cliente->agentes()->count());
    }

    public function test_criar_agente_com_numero_valido_funciona_e_salva_o_vinculo(): void
    {
        $cliente = $this->clienteComNumeros(2);
        $integracao = $cliente->whatsappIntegracoes()->first();

        $response = $this->actingAs($this->admin())->post(route('clientes.agentes.store', $cliente), [
            'nome' => 'Agente com numero',
            'whatsapp_integracao_id' => $integracao->id,
            'idioma' => 'pt-BR', 'timezone' => 'America/Sao_Paulo', 'cor' => '#6366f1',
            'prompt_principal' => 'Prompt', 'modelo' => 'gpt-4o-mini',
            'top_p' => 1, 'frequency_penalty' => 0, 'presence_penalty' => 0, 'timeout' => 30,
            'temperatura' => 0.7, 'emojis' => 'normal', 'tamanho_respostas' => 'medias',
            'forma_tratamento' => 'voce', 'memoria_dias_lembrar' => 30,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('clientes.agentes.index', $cliente));
        $this->assertDatabaseHas('agentes', ['nome' => 'Agente com numero', 'whatsapp_integracao_id' => $integracao->id]);
    }

    public function test_criar_agente_com_numero_de_outro_cliente_e_rejeitado(): void
    {
        $clienteA = $this->clienteComNumeros(2);
        $clienteB = $this->clienteComNumeros(1);
        $integracaoDeB = $clienteB->whatsappIntegracoes()->first();

        $response = $this->actingAs($this->admin())->post(route('clientes.agentes.store', $clienteA), [
            'nome' => 'Agente tentando roubar numero',
            'whatsapp_integracao_id' => $integracaoDeB->id,
            'idioma' => 'pt-BR', 'timezone' => 'America/Sao_Paulo', 'cor' => '#6366f1',
            'prompt_principal' => 'Prompt', 'modelo' => 'gpt-4o-mini',
            'top_p' => 1, 'frequency_penalty' => 0, 'presence_penalty' => 0, 'timeout' => 30,
            'temperatura' => 0.7, 'emojis' => 'normal', 'tamanho_respostas' => 'medias',
            'forma_tratamento' => 'voce', 'memoria_dias_lembrar' => 30,
        ]);

        $response->assertSessionHasErrors('whatsapp_integracao_id');
        $this->assertSame(0, $clienteA->agentes()->count());
    }

    public function test_criar_agente_funciona_sem_escolher_numero_quando_cliente_tem_so_1(): void
    {
        $cliente = $this->clienteComNumeros(1);

        $response = $this->actingAs($this->admin())->post(route('clientes.agentes.store', $cliente), [
            'nome' => 'Agente cliente com 1 numero',
            'idioma' => 'pt-BR', 'timezone' => 'America/Sao_Paulo', 'cor' => '#6366f1',
            'prompt_principal' => 'Prompt', 'modelo' => 'gpt-4o-mini',
            'top_p' => 1, 'frequency_penalty' => 0, 'presence_penalty' => 0, 'timeout' => 30,
            'temperatura' => 0.7, 'emojis' => 'normal', 'tamanho_respostas' => 'medias',
            'forma_tratamento' => 'voce', 'memoria_dias_lembrar' => 30,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('agentes', ['nome' => 'Agente cliente com 1 numero', 'whatsapp_integracao_id' => null]);
    }
}
