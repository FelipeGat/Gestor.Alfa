<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceiroMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Admin sempre pode
        if ($user->tipo === 'admin') {
            return $next($request);
        }

        // Verifica perfil financeiro
        $temPerfilFinanceiro = $user->perfis()
            ->where('slug', 'financeiro')
            ->exists();

        if (! $temPerfilFinanceiro) {
            abort(403);
        }

        return $next($request);
    }
}
