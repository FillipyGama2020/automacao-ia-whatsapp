<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmbeddedSignupService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MetaAccountUpdateController extends Controller
{
    public function __construct(private EmbeddedSignupService $embeddedSignup)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::channel('account_update')->info('Meta account_update recebido', $payload);

        $valor = $payload['entry'][0]['changes'][0]['value'] ?? null;
        $wabaId = $valor['waba_info']['waba_id'] ?? null;

        if (($valor['event'] ?? null) === 'PARTNER_APP_INSTALLED' && $wabaId) {
            $resultado = ['ok' => false];

            for ($tentativa = 0; $tentativa < 4 && ! $resultado['ok']; $tentativa++) {
                if ($tentativa > 0) {
                    sleep(3);
                }

                $resultado = $this->embeddedSignup->resolverConexaoPendente($wabaId);
            }

            Log::channel('account_update')->info('Resultado da resolução da conexão pendente', $resultado);
        }

        return response()->json(['ok' => true]);
    }
}
