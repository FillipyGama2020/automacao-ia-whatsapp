<?php

namespace Tests\Feature;

use App\Models\Agente;
use App\Models\Cliente;
use App\Models\Plano;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AgenteEstaAbertoTest extends TestCase
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

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function agente(): Agente
    {
        $plano = Plano::create(['nome' => 'Plano Teste Aberto', 'preco_mensal' => 100]);
        $cliente = Cliente::create(['nome_empresa' => 'Cliente Teste Aberto '.Str::random(6), 'plano_id' => $plano->id]);

        return $cliente->agentes()->create([
            'nome' => 'Agente Teste',
            'prompt_principal' => 'Prompt de teste',
            'modelo' => 'gpt-4o-mini',
            'temperatura' => 0.7,
            'ativo' => true,
            'timezone' => 'America/Sao_Paulo',
        ]);
    }

    public function test_aberto_dentro_do_horario_configurado(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-19 10:00:00', 'America/Sao_Paulo'));

        $agente = $this->agente();
        $agente->horarios()->create(['dia_semana' => 3, 'hora_inicio' => '08:00', 'hora_fim' => '18:00']);

        $this->assertTrue($agente->estaAberto());
    }

    public function test_fechado_fora_do_horario_configurado(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-19 22:00:00', 'America/Sao_Paulo'));

        $agente = $this->agente();
        $agente->horarios()->create(['dia_semana' => 3, 'hora_inicio' => '08:00', 'hora_fim' => '18:00']);

        $this->assertFalse($agente->estaAberto());
    }

    public function test_fechado_no_dia_marcado_como_fechado(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-19 10:00:00', 'America/Sao_Paulo'));

        $agente = $this->agente();
        $agente->horarios()->create(['dia_semana' => 3, 'fechado' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00']);

        $this->assertFalse($agente->estaAberto());
    }

    public function test_aberto_24h_quando_horario_em_branco(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-19 03:00:00', 'America/Sao_Paulo'));

        $agente = $this->agente();
        $agente->horarios()->create(['dia_semana' => 3, 'hora_inicio' => null, 'hora_fim' => null]);

        $this->assertTrue($agente->estaAberto());
    }

    public function test_fechado_em_feriado_cadastrado_mesmo_dentro_do_horario(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-19 10:00:00', 'America/Sao_Paulo'));

        $agente = $this->agente();
        $agente->horarios()->create(['dia_semana' => 3, 'hora_inicio' => '08:00', 'hora_fim' => '18:00']);
        $agente->feriados()->create(['data' => '2026-08-19', 'descricao' => 'Feriado de teste']);

        $this->assertFalse($agente->estaAberto());
    }

    public function test_fechado_quando_nao_ha_horario_cadastrado_para_o_dia(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-19 10:00:00', 'America/Sao_Paulo'));

        $agente = $this->agente();

        $agente->horarios()->create(['dia_semana' => 4, 'hora_inicio' => '08:00', 'hora_fim' => '18:00']);

        $this->assertFalse($agente->estaAberto());
    }
}
