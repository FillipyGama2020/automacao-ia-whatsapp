<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\WhatsappIntegracao;
use App\Services\EmbeddedSignupService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappIntegracaoController extends Controller
{
    public function __construct(private EmbeddedSignupService $embeddedSignup)
    {
    }

    public function edit(Cliente $cliente)
    {
        $integracoes = $cliente->whatsappIntegracoes()->orderBy('id')->get();

        return view('clientes.whatsapp', compact('cliente', 'integracoes'));
    }

    public function conectarEmbedded(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'waba_id' => 'required|string',
            'phone_number_id' => 'required|string',
        ]);

        $resultado = $this->embeddedSignup->conectar($cliente, $data['code'], $data['waba_id'], $data['phone_number_id']);

        return response()->json(['message' => $resultado['message']], $resultado['ok'] ? 200 : 422);
    }

    public function iniciarConexao(Request $request, Cliente $cliente)
    {
        $data = $request->validate(['code' => 'required|string']);

        $resultado = $this->embeddedSignup->iniciarConexaoPendente($cliente, $data['code']);

        return response()->json(['message' => $resultado['message']], $resultado['ok'] ? 200 : 422);
    }

    public function desconectar(Cliente $cliente, WhatsappIntegracao $integracao)
    {
        abort_unless($integracao->cliente_id === $cliente->id, 404);

        $ok = $this->embeddedSignup->desconectar($integracao);

        return $ok
            ? redirect()->route('clientes.whatsapp.edit', $cliente)->with('status', 'WhatsApp desconectado do sistema.')
            : redirect()->route('clientes.whatsapp.edit', $cliente)->with('error', 'Nenhuma conexão ativa pra desconectar.');
    }

    public function toggleModoEquipe(Cliente $cliente, WhatsappIntegracao $integracao)
    {
        abort_unless($integracao->cliente_id === $cliente->id, 404);

        $integracao->update(['modo_equipe_agentes' => ! $integracao->modo_equipe_agentes]);

        return redirect()->route('clientes.whatsapp.edit', $cliente)->with('status', 'Modo equipe atualizado.');
    }

    public function atualizarPromptClassificacao(Request $request, Cliente $cliente, WhatsappIntegracao $integracao)
    {
        abort_unless($integracao->cliente_id === $cliente->id, 404);

        $data = $request->validate([
            'prompt_classificacao_extra' => 'nullable|string|max:2000',
        ]);

        $integracao->update(['prompt_classificacao_extra' => $data['prompt_classificacao_extra'] ?? null]);

        return redirect()->route('clientes.whatsapp.edit', $cliente)->with('status', 'Instruções de classificação salvas.');
    }

    public function store(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'app_id' => 'nullable|string|max:191',
            'app_secret' => 'nullable|string|max:191',
            'business_account_id' => 'nullable|string|max:191',
            'phone_number_id' => 'nullable|string|max:191',
            'access_token' => 'nullable|string',
        ]);

        $cliente->whatsappIntegracoes()->create($data);

        return redirect()->route('clientes.whatsapp.edit', $cliente)
            ->with('status', 'Número adicionado com sucesso.');
    }

    public function update(Request $request, Cliente $cliente, WhatsappIntegracao $integracao)
    {
        abort_unless($integracao->cliente_id === $cliente->id, 404);

        $data = $request->validate([
            'app_id' => 'nullable|string|max:191',
            'app_secret' => 'nullable|string|max:191',
            'business_account_id' => 'nullable|string|max:191',
            'phone_number_id' => 'nullable|string|max:191',
            'access_token' => 'nullable|string',
        ]);

        foreach (['app_secret', 'access_token'] as $secretField) {
            if (blank($data[$secretField])) {
                unset($data[$secretField]);
            }
        }

        $integracao->update($data);

        return redirect()->route('clientes.whatsapp.edit', $cliente)
            ->with('status', 'Credenciais salvas com sucesso.');
    }

    public function testar(Cliente $cliente, WhatsappIntegracao $integracao)
    {
        abort_unless($integracao->cliente_id === $cliente->id, 404);

        if (! $integracao->phone_number_id || ! $integracao->access_token) {
            return redirect()->route('clientes.whatsapp.edit', $cliente)
                ->with('error', 'Preencha o Phone Number ID e o Access Token antes de testar.');
        }

        $version = config('services.meta.graph_version');

        try {
            $response = Http::withToken($integracao->access_token)
                ->timeout(10)
                ->get("https://graph.facebook.com/{$version}/{$integracao->phone_number_id}");
        } catch (ConnectionException $e) {
            $integracao->update([
                'status' => 'erro',
                'last_checked_at' => now(),
                'last_error' => 'Não foi possível conectar ao servidor da Meta: '.$e->getMessage(),
            ]);

            return redirect()->route('clientes.whatsapp.edit', $cliente)
                ->with('error', 'Falha de conexão: '.$integracao->last_error);
        }

        if ($response->successful()) {
            $integracao->update([
                'status' => 'conectado',
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            return redirect()->route('clientes.whatsapp.edit', $cliente)
                ->with('status', 'Conexão validada com sucesso: '.($response->json('display_phone_number') ?? $integracao->phone_number_id));
        }

        $integracao->update([
            'status' => 'erro',
            'last_checked_at' => now(),
            'last_error' => $response->json('error.message') ?? 'Erro desconhecido ao conectar com a Meta.',
        ]);

        return redirect()->route('clientes.whatsapp.edit', $cliente)
            ->with('error', 'Falha ao conectar: '.$integracao->last_error);
    }

    public function enviarMensagemTeste(Request $request, Cliente $cliente, WhatsappIntegracao $integracao)
    {
        abort_unless($integracao->cliente_id === $cliente->id, 404);

        $data = $request->validate([
            'numero_teste' => 'required|string|max:20',
        ]);

        if (! $integracao->phone_number_id || ! $integracao->access_token) {
            return redirect()->route('clientes.whatsapp.edit', $cliente)
                ->with('error', 'Conecte o WhatsApp antes de enviar uma mensagem de teste.');
        }

        $version = config('services.meta.graph_version');
        $numero = preg_replace('/\D/', '', $data['numero_teste']);

        try {
            $response = Http::withToken($integracao->access_token)
                ->timeout(15)
                ->post("https://graph.facebook.com/{$version}/{$integracao->phone_number_id}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $numero,
                    'type' => 'text',
                    'text' => ['body' => 'Mensagem de teste enviada pelo WhatsApp AI Panel — se você recebeu isso, a conexão está funcionando corretamente.'],
                ]);
        } catch (ConnectionException $e) {
            return redirect()->route('clientes.whatsapp.edit', $cliente)
                ->with('error', 'Não foi possível contatar a Meta: '.$e->getMessage());
        }

        if ($response->successful()) {
            return redirect()->route('clientes.whatsapp.edit', $cliente)
                ->with('status', 'Mensagem de teste enviada para '.$data['numero_teste'].'.');
        }

        return redirect()->route('clientes.whatsapp.edit', $cliente)
            ->with('error', 'Falha ao enviar: '.($response->json('error.message') ?? 'erro desconhecido.'));
    }

    public function gerarToken(Cliente $cliente, WhatsappIntegracao $integracao)
    {
        abort_unless($integracao->cliente_id === $cliente->id, 404);

        $token = $integracao->gerarNovoApiToken();

        return redirect()->route('clientes.whatsapp.edit', $cliente)
            ->with('novo_api_token', $token)
            ->with('novo_api_token_integracao_id', $integracao->id);
    }
}
