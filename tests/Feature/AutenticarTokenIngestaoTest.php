<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Plano;
use App\Models\WhatsappIntegracao;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AutenticarTokenIngestaoTest extends TestCase
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

    private function clienteComNumero(): array
    {
        $plano = Plano::create(['nome' => 'Plano Teste Ingestao', 'preco_mensal' => 100]);
        $cliente = Cliente::create(['nome_empresa' => 'Cliente Teste Ingestao '.Str::random(6), 'plano_id' => $plano->id]);
        $integracao = $cliente->whatsappIntegracoes()->create([
            'phone_number_id' => 'ingestao_'.Str::random(8),
            'status' => 'conectado',
        ]);

        return [$cliente, $integracao];
    }

    private function masterTokenHeaders(): array
    {
        return ['Authorization' => 'Bearer '.config('services.n8n.master_token')];
    }

    public function test_token_mestre_com_phone_number_id_resolve_direto(): void
    {
        [, $integracao] = $this->clienteComNumero();

        $response = $this->withHeaders($this->masterTokenHeaders())->postJson('/api/mensagens', [
            'phone_number_id' => $integracao->phone_number_id,
            'contato_telefone' => '5500000000001',
            'remetente' => 'contato',
            'tipo' => 'texto',
            'conteudo' => 'teste',
        ]);

        $response->assertCreated();
    }

    public function test_token_mestre_com_apenas_cliente_id_cai_no_fallback(): void
    {
        [$cliente] = $this->clienteComNumero();

        $response = $this->withHeaders($this->masterTokenHeaders())->postJson('/api/mensagens', [
            'cliente_id' => $cliente->id,
            'contato_telefone' => '5500000000002',
            'remetente' => 'contato',
            'tipo' => 'texto',
            'conteudo' => 'teste',
        ]);

        $response->assertCreated();
    }

    public function test_token_mestre_sem_phone_number_id_nem_cliente_id_e_rejeitado(): void
    {
        $response = $this->withHeaders($this->masterTokenHeaders())->postJson('/api/mensagens', [
            'contato_telefone' => '5500000000003',
            'remetente' => 'contato',
            'tipo' => 'texto',
            'conteudo' => 'teste',
        ]);

        $response->assertStatus(422);
    }

    public function test_token_por_integracao_continua_funcionando(): void
    {
        [, $integracao] = $this->clienteComNumero();
        $token = $integracao->gerarNovoApiToken();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->postJson('/api/mensagens', [
            'contato_telefone' => '5500000000004',
            'remetente' => 'contato',
            'tipo' => 'texto',
            'conteudo' => 'teste',
        ]);

        $response->assertCreated();
    }
}
