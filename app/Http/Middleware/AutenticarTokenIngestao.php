<?php

namespace App\Http\Middleware;

use App\Models\WhatsappIntegracao;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutenticarTokenIngestao
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Token de autenticação ausente.'], 401);
        }

        $tokenMestre = config('services.n8n.master_token');

        if ($tokenMestre && hash_equals($tokenMestre, $token)) {
            $phoneNumberId = $request->input('phone_number_id');
            $clienteId = $request->input('cliente_id');

            $integracao = $phoneNumberId
                ? WhatsappIntegracao::where('phone_number_id', $phoneNumberId)->first()
                : ($clienteId ? WhatsappIntegracao::where('cliente_id', $clienteId)->first() : null);

            if (! $integracao) {
                return response()->json(['message' => 'phone_number_id ou cliente_id ausente, ou sem integração de WhatsApp.'], 422);
            }
        } else {
            $integracao = WhatsappIntegracao::encontrarPorApiToken($token);

            if (! $integracao) {
                return response()->json(['message' => 'Token de autenticação inválido.'], 401);
            }
        }

        $request->attributes->set('integracao', $integracao);

        return $next($request);
    }
}
