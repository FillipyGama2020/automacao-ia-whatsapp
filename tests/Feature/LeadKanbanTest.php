<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Lead;
use App\Models\Plano;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadKanbanTest extends TestCase
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

    private function clienteComLeads(): array
    {
        $plano = Plano::create(['nome' => 'Plano Teste Kanban', 'preco_mensal' => 100]);
        $cliente = Cliente::create(['nome_empresa' => 'Cliente Teste Kanban '.Str::random(6), 'plano_id' => $plano->id]);

        $novo = $cliente->leads()->create(['telefone' => '5527900000001', 'status' => 'novo', 'origem' => 'manual']);
        $emContato = $cliente->leads()->create(['telefone' => '5527900000002', 'status' => 'em_contato', 'origem' => 'manual']);

        return [$cliente, $novo, $emContato];
    }

    public function test_kanban_agrupa_leads_por_status(): void
    {
        $admin = $this->admin();
        [$cliente, $novo, $emContato] = $this->clienteComLeads();

        $response = $this->actingAs($admin)->get(route('clientes.leads.kanban', $cliente));

        $response->assertOk();
        $response->assertSee($novo->telefone);
        $response->assertSee($emContato->telefone);
    }

    public function test_arrastar_lead_atualiza_status(): void
    {
        $admin = $this->admin();
        [$cliente, $novo] = $this->clienteComLeads();

        $response = $this->actingAs($admin)->patchJson(route('clientes.leads.status', [$cliente, $novo]), [
            'status' => 'convertido',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'convertido');
        $this->assertSame('convertido', $novo->fresh()->status);
    }

    public function test_atualizar_status_rejeita_valor_invalido(): void
    {
        $admin = $this->admin();
        [$cliente, $novo] = $this->clienteComLeads();

        $response = $this->actingAs($admin)->patchJson(route('clientes.leads.status', [$cliente, $novo]), [
            'status' => 'nao_existe',
        ]);

        $response->assertStatus(422);
        $this->assertSame('novo', $novo->fresh()->status);
    }

    public function test_atualizar_status_de_lead_de_outro_cliente_e_rejeitado(): void
    {
        $admin = $this->admin();
        [$clienteA, $leadA] = $this->clienteComLeads();
        [$clienteB] = $this->clienteComLeads();

        $response = $this->actingAs($admin)->patchJson(route('clientes.leads.status', [$clienteB, $leadA]), [
            'status' => 'convertido',
        ]);

        $response->assertStatus(404);
        $this->assertSame('novo', $leadA->fresh()->status);
    }

    public function test_kanban_respeita_filtro_de_classificacao(): void
    {
        $admin = $this->admin();
        [$cliente] = $this->clienteComLeads();
        $quente = $cliente->leads()->create(['telefone' => '5527900000099', 'status' => 'novo', 'classificacao' => 'quente', 'origem' => 'manual']);

        $response = $this->actingAs($admin)->get(route('clientes.leads.kanban', [$cliente, 'classificacao' => 'quente']));

        $response->assertOk();
        $response->assertSee($quente->telefone);
        $response->assertDontSee('5527900000001');
    }
}
