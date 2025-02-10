<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\WhatsappIntegracao;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddedSignupService
{
    public function conectar(Cliente $cliente, string $code, string $wabaId, string $phoneNumberId): array
    {
        $version = config('services.meta.graph_version');

        try {
            $tokenResponse = Http::timeout(15)->get("https://graph.facebook.com/{$version}/oauth/access_token", [
                'client_id' => config('services.meta.app_id'),
                'client_secret' => config('services.meta.app_secret'),
                'code' => $code,
            ]);
        } catch (ConnectionException $e) {
            report($e);

            return ['ok' => false, 'message' => 'Não foi possível contatar a Meta. Tente novamente em instantes.'];
        }

        if (! $tokenResponse->successful() || ! $tokenResponse->json('access_token')) {
            Log::error('Falha ao trocar code por access token no Embedded Signup', [
                'cliente_id' => $cliente->id,
                'resposta' => $tokenResponse->json(),
            ]);

            return ['ok' => false, 'message' => 'A Meta não confirmou a conexão. Tente conectar novamente.'];
        }

        $accessToken = $tokenResponse->json('access_token');

        try {
            $debug = Http::timeout(15)->get("https://graph.facebook.com/{$version}/debug_token", [
                'input_token' => $accessToken,
                'access_token' => $accessToken,
            ])->json('data');

            if ($debug && ($debug['is_valid'] ?? true) === false) {
                Log::error('debug_token confirmou token inválido após troca no Embedded Signup', [
                    'cliente_id' => $cliente->id,
                    'debug' => $debug,
                ]);
            }
        } catch (ConnectionException $e) {
            report($e);
        }

        $existente = WhatsappIntegracao::where('phone_number_id', $phoneNumberId)
            ->where('cliente_id', '!=', $cliente->id)
            ->first();

        if ($existente) {
            Log::error('Tentativa de conectar phone_number_id já registrado para outro cliente', [
                'cliente_id' => $cliente->id,
                'cliente_id_existente' => $existente->cliente_id,
                'phone_number_id' => $phoneNumberId,
            ]);

            return ['ok' => false, 'message' => 'Este número de WhatsApp já está conectado a outro cliente no sistema.'];
        }

        $cliente->whatsappIntegracoes()->updateOrCreate(
            ['phone_number_id' => $phoneNumberId],
            [
                'business_account_id' => $wabaId,
                'access_token' => $accessToken,
                'status' => 'conectado',
                'last_checked_at' => now(),
                'last_error' => null,
            ]
        );

        $inscricaoOk = false;

        try {
            $inscricao = Http::withToken($accessToken)
                ->timeout(15)
                ->post("https://graph.facebook.com/{$version}/{$wabaId}/subscribed_apps");

            $inscricaoOk = $inscricao->successful();

            if (! $inscricaoOk) {
                Log::error('Falha ao inscrever app na WABA após Embedded Signup', [
                    'cliente_id' => $cliente->id,
                    'waba_id' => $wabaId,
                    'resposta' => $inscricao->json(),
                ]);
            }
        } catch (ConnectionException $e) {
            report($e);
        }

        return [
            'ok' => true,
            'message' => $inscricaoOk
                ? 'WhatsApp conectado com sucesso.'
                : 'WhatsApp conectado, mas não foi possível ativar o recebimento de mensagens agora. Clique em "Reconectar WhatsApp" novamente em instantes.',
        ];
    }

    public function iniciarConexaoPendente(Cliente $cliente, string $code): array
    {
        $version = config('services.meta.graph_version');

        try {
            $tokenResponse = Http::timeout(15)->get("https://graph.facebook.com/{$version}/oauth/access_token", [
                'client_id' => config('services.meta.app_id'),
                'client_secret' => config('services.meta.app_secret'),
                'code' => $code,
            ]);
        } catch (ConnectionException $e) {
            report($e);

            return ['ok' => false, 'message' => 'Não foi possível contatar a Meta. Tente novamente em instantes.'];
        }

        if (! $tokenResponse->successful() || ! $tokenResponse->json('access_token')) {
            Log::error('Falha ao trocar code por access token (conexão pendente)', [
                'cliente_id' => $cliente->id,
                'resposta' => $tokenResponse->json(),
            ]);

            return ['ok' => false, 'message' => 'A Meta não confirmou a conexão. Tente conectar novamente.'];
        }

        $cliente->whatsappIntegracoes()->create([
            'conexao_pendente_token' => $tokenResponse->json('access_token'),
            'conexao_pendente_em' => now(),
        ]);

        return [
            'ok' => true,
            'message' => 'Conexão iniciada — a Meta pode levar alguns instantes pra confirmar. Atualize esta página em 1 ou 2 minutos.',
        ];
    }

    public function resolverConexaoPendente(string $wabaId): array
    {
        $candidatos = WhatsappIntegracao::query()
            ->whereNotNull('conexao_pendente_token')
            ->whereNotNull('conexao_pendente_em')
            ->where('conexao_pendente_em', '>=', now()->subMinutes(30))
            ->orderByDesc('conexao_pendente_em')
            ->limit(5)
            ->get();

        if ($candidatos->isEmpty()) {
            Log::error('Webhook account_update (PARTNER_APP_INSTALLED) sem conexão pendente correspondente', [
                'waba_id' => $wabaId,
            ]);

            return ['ok' => false, 'message' => 'Nenhuma conexão pendente encontrada.'];
        }

        $version = config('services.meta.graph_version');
        $integracao = null;
        $numeros = null;

        foreach ($candidatos as $candidata) {
            try {
                $resposta = Http::withToken($candidata->conexao_pendente_token)->timeout(15)
                    ->get("https://graph.facebook.com/{$version}/{$wabaId}/phone_numbers");
            } catch (ConnectionException $e) {
                report($e);

                continue;
            }

            if ($resposta->successful() && $resposta->json('data.0.id')) {
                $integracao = $candidata;
                $numeros = $resposta;

                break;
            }
        }

        if (! $integracao) {
            Log::warning('Nenhuma conexão pendente comprovadamente dona do waba_id — usando a mais recente do sistema como fallback', [
                'waba_id' => $wabaId,
            ]);

            $integracao = $candidatos->first();

            try {
                $numeros = Http::withToken($integracao->conexao_pendente_token)->timeout(15)
                    ->get("https://graph.facebook.com/{$version}/{$wabaId}/phone_numbers");
            } catch (ConnectionException $e) {
                report($e);

                return ['ok' => false, 'message' => 'Não foi possível contatar a Meta.'];
            }
        }

        $phoneNumberId = $numeros->json('data.0.id');

        if (! $numeros->successful() || ! $phoneNumberId) {
            Log::error('Falha ao buscar phone_numbers da WABA na conexão pendente', [
                'cliente_id' => $integracao->cliente_id,
                'waba_id' => $wabaId,
                'resposta' => $numeros->json(),
            ]);

            return ['ok' => false, 'message' => 'Não foi possível encontrar o número de telefone dessa conta.'];
        }

        $token = $integracao->conexao_pendente_token;

        $existente = WhatsappIntegracao::where('phone_number_id', $phoneNumberId)
            ->where('id', '!=', $integracao->id)
            ->first();

        if ($existente) {
            $integracao->delete();
            $integracao = $existente;
        }

        try {
            Http::withToken($token)->timeout(15)
                ->post("https://graph.facebook.com/{$version}/{$wabaId}/subscribed_apps");
        } catch (ConnectionException $e) {
            report($e);
        }

        $integracao->update([
            'business_account_id' => $wabaId,
            'phone_number_id' => $phoneNumberId,
            'access_token' => $token,
            'status' => 'conectado',
            'last_checked_at' => now(),
            'last_error' => null,
            'conexao_pendente_token' => null,
            'conexao_pendente_em' => null,
        ]);

        return ['ok' => true, 'cliente_id' => $integracao->cliente_id, 'waba_id' => $wabaId, 'phone_number_id' => $phoneNumberId];
    }

    public function desconectar(WhatsappIntegracao $integracao): bool
    {
        if (! $integracao->phone_number_id) {
            return false;
        }

        if ($integracao->business_account_id && $integracao->access_token) {
            $version = config('services.meta.graph_version');

            try {
                Http::withToken($integracao->access_token)
                    ->timeout(15)
                    ->delete("https://graph.facebook.com/{$version}/{$integracao->business_account_id}/subscribed_apps");
            } catch (ConnectionException $e) {
                report($e);

            }
        }

        $integracao->update([
            'phone_number_id' => null,
            'business_account_id' => null,
            'access_token' => null,
            'status' => 'nao_conectado',
            'last_checked_at' => now(),
            'last_error' => null,
        ]);

        return true;
    }
}
