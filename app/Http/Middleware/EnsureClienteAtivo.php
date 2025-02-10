<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClienteAtivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $cliente = $request->user()?->cliente;

        if (! $cliente || $cliente->status !== 'ativo') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', 'Sua conta está temporariamente indisponível. Entre em contato com a agência.');
        }

        return $next($request);
    }
}
