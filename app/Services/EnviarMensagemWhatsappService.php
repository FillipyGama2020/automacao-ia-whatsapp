<?php

namespace App\Services;

use App\Models\WhatsappIntegracao;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class EnviarMensagemWhatsappService
{
    public function enviar(WhatsappIntegracao $integracao, string $telefone, string $texto): array
    {
        return $this->enviarPayload($integracao, $telefone, [
            'type' => 'text',
            'text' => ['body' => $texto],
        ]);
    }

    public function enviarTemplate(WhatsappIntegracao $integracao, string $telefone, string $nomeTemplate, string $idioma, array $variaveis): array
    {
        $components = [];

        if (! empty($variaveis)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(fn ($valor) => ['type' => 'text', 'text' => (string) $valor], $variaveis),
            ];
        }

        return $this->enviarPayload($integracao, $telefone, [
            'type' => 'template',
            'template' => [
                'name' => $nomeTemplate,
                'language' => ['code' => $idioma],
                'components' => $components,
            ],
        ]);
    }

    private function enviarPayload(WhatsappIntegracao $integracao, string $telefone, array $tipoEConteudo): array
    {
        if (! $integracao->phone_number_id || ! $integracao->access_token) {
            return ['ok' => false, 'message' => 'WhatsApp não está conectado para este cliente.'];
        }

        $version = config('services.meta.graph_version');
        $numero = preg_replace('/\D/', '', $telefone);

        try {
            $response = Http::withToken($integracao->access_token)
                ->timeout(15)
                ->post("https://graph.facebook.com/{$version}/{$integracao->phone_number_id}/messages", array_merge([
                    'messaging_product' => 'whatsapp',
                    'to' => $numero,
                ], $tipoEConteudo));
        } catch (ConnectionException $e) {
            report($e);

            return ['ok' => false, 'message' => 'Não foi possível contatar a Meta. Tente novamente em instantes.'];
        }

        if (! $response->successful()) {
            return [
                'ok' => false,
                'message' => $response->json('error.message') ?? 'Falha ao enviar mensagem.',
            ];
        }

        return [
            'ok' => true,
            'wamid' => $response->json('messages.0.id'),
        ];
    }
}
