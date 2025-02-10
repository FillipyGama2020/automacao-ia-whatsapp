<?php

namespace Tests\Feature;

use App\Jobs\GerarRespostaIaJob;
use App\Models\Agente;
use App\Models\Cliente;
use App\Models\Conversa;
use App\Models\Mensagem;
use App\Models\Plano;
use App\Models\WhatsappIntegracao;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

class RetomarConversasAoAbrirHorarioTest extends TestCase
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

        Carbon::setTestNow(Carbon::parse('2026-08-19 10:00:00', 'America/Sao_Paulo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function clienteComNumero(): array
    {
        $plano = Plano::create(['nome' => 'Plano Teste Reabertura', 'preco_mensal' => 100]);
        $cliente = Cliente::create(['nome_empresa' => 'Cliente Teste Reabertura '.Str::random(6), 'plano_id' => $plano->id]);
        $integracao = $cliente->whatsappIntegracoes()->create([
            'phone_number_id' => 'reab_'.Str::random(8),
            'status' => 'conectado',
        ]);

        return [$cliente, $integracao];
    }

    private function agenteAberto(Cliente $cliente, bool $retomarAoAbrirHorario = true): Agente
    {
        $agente = $cliente->agentes()->create([
            'nome' => 'Agente Reabertura',
            'prompt_principal' => 'Prompt de teste',
            'modelo' => 'gpt-4o-mini',
            'temperatura' => 0.7,
            'ativo' => true,
            'timezone' => 'America/Sao_Paulo',
            'retomar_ao_abrir_horario' => $retomarAoAbrirHorario,
        ]);

        $agente->horarios()->create(['dia_semana' => 3, 'hora_inicio' => '08:00', 'hora_fim' => '18:00']);

        return $agente;
    }

    private function conversaComAvisoDeForaDeHorario(Cliente $cliente, Agente $agente, WhatsappIntegracao $integracao, string $telefone): Conversa
    {
        $conversa = Conversa::create([
            'cliente_id' => $cliente->id,
            'agente_id' => $agente->id,
            'whatsapp_integracao_id' => $integracao->id,
            'contato_telefone' => $telefone,
            'status' => 'em_andamento',
            'iniciada_em' => now()->subHour(),
            'ultima_mensagem_em' => now()->subHour(),
        ]);

        $mensagemContato = $conversa->mensagens()->create([
            'remetente' => 'contato',
            'tipo' => 'texto',
            'conteudo' => 'Boa tarde, preciso de ajuda',
            'enviada_em' => now()->subHour(),
        ]);

        $conversa->mensagens()->create([
            'remetente' => 'agente_ia',
            'tipo' => 'texto',
            'conteudo' => 'Estamos fora do horário de atendimento no momento.',
            'fora_horario' => true,
            'enviada_em' => now()->subHour()->addSecond(),
        ]);

        return $conversa;
    }

    public function test_retoma_conversa_quando_horario_reabriu_e_opt_in_ligado(): void
    {
        Bus::fake();

        [$cliente, $integracao] = $this->clienteComNumero();
        $agente = $this->agenteAberto($cliente, retomarAoAbrirHorario: true);
        $conversa = $this->conversaComAvisoDeForaDeHorario($cliente, $agente, $integracao, '5527999991001');

        $this->artisan('app:retomar-conversas-ao-abrir-horario')->assertSuccessful();

        $ultimaMensagemContato = $conversa->mensagens()->where('remetente', 'contato')->latest('enviada_em')->first();
        Bus::assertDispatched(GerarRespostaIaJob::class, function (GerarRespostaIaJob $job) use ($conversa, $ultimaMensagemContato) {
            return $job->conversaId === $conversa->id && $job->mensagemId === $ultimaMensagemContato->id;
        });
    }

    public function test_nao_retoma_quando_opt_in_desligado(): void
    {
        Bus::fake();

        [$cliente, $integracao] = $this->clienteComNumero();
        $agente = $this->agenteAberto($cliente, retomarAoAbrirHorario: false);
        $this->conversaComAvisoDeForaDeHorario($cliente, $agente, $integracao, '5527999991002');

        $this->artisan('app:retomar-conversas-ao-abrir-horario')->assertSuccessful();

        Bus::assertNotDispatched(GerarRespostaIaJob::class);
    }

    public function test_nao_retoma_quando_ainda_fechado(): void
    {
        Bus::fake();

        [$cliente, $integracao] = $this->clienteComNumero();
        $agente = $this->agenteAberto($cliente, retomarAoAbrirHorario: true);
        $this->conversaComAvisoDeForaDeHorario($cliente, $agente, $integracao, '5527999991003');

        Carbon::setTestNow(Carbon::parse('2026-08-19 22:00:00', 'America/Sao_Paulo'));

        $this->artisan('app:retomar-conversas-ao-abrir-horario')->assertSuccessful();

        Bus::assertNotDispatched(GerarRespostaIaJob::class);
    }

    public function test_nao_retoma_quando_ultima_mensagem_nao_e_o_aviso_de_fora_de_horario(): void
    {
        Bus::fake();

        [$cliente, $integracao] = $this->clienteComNumero();
        $agente = $this->agenteAberto($cliente, retomarAoAbrirHorario: true);
        $conversa = $this->conversaComAvisoDeForaDeHorario($cliente, $agente, $integracao, '5527999991004');

        $conversa->mensagens()->create([
            'remetente' => 'agente_ia',
            'tipo' => 'texto',
            'conteudo' => 'Aqui está a resposta contextual.',
            'fora_horario' => false,
            'enviada_em' => now()->subMinutes(30),
        ]);
        $conversa->update(['ultima_mensagem_em' => now()->subMinutes(30)]);

        $this->artisan('app:retomar-conversas-ao-abrir-horario')->assertSuccessful();

        Bus::assertNotDispatched(GerarRespostaIaJob::class);
    }

    public function test_nao_retoma_fora_da_janela_de_24h(): void
    {
        Bus::fake();

        [$cliente, $integracao] = $this->clienteComNumero();
        $agente = $this->agenteAberto($cliente, retomarAoAbrirHorario: true);
        $conversa = $this->conversaComAvisoDeForaDeHorario($cliente, $agente, $integracao, '5527999991005');

        $conversa->update(['ultima_mensagem_em' => now()->subHours(25)]);

        $this->artisan('app:retomar-conversas-ao-abrir-horario')->assertSuccessful();

        Bus::assertNotDispatched(GerarRespostaIaJob::class);
    }

    public function test_nao_dispara_de_novo_depois_de_ja_ter_retomado(): void
    {
        Bus::fake();

        [$cliente, $integracao] = $this->clienteComNumero();
        $agente = $this->agenteAberto($cliente, retomarAoAbrirHorario: true);
        $conversa = $this->conversaComAvisoDeForaDeHorario($cliente, $agente, $integracao, '5527999991006');

        $this->artisan('app:retomar-conversas-ao-abrir-horario')->assertSuccessful();
        Bus::assertDispatchedTimes(GerarRespostaIaJob::class, 1);

        $conversa->mensagens()->create([
            'remetente' => 'agente_ia',
            'tipo' => 'texto',
            'conteudo' => 'Resposta contextual gerada pela retomada.',
            'fora_horario' => false,
            'enviada_em' => now(),
        ]);
        $conversa->update(['ultima_mensagem_em' => now()]);

        $this->artisan('app:retomar-conversas-ao-abrir-horario')->assertSuccessful();
        Bus::assertDispatchedTimes(GerarRespostaIaJob::class, 1);
    }
}
