<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutenticarTokenN8n
{
    public function handle(Request $request, Closure $next): Response
    {
        $tokenEsperado = config('services.n8n.master_token');
        $token = $request->bearerToken();

        if (! $tokenEsperado || ! $token || ! hash_equals($tokenEsperado, $token)) {
            return response()->json(['message' => 'Token de autenticação inválido.'], 401);
        }

        return $next($request);
    }
}
