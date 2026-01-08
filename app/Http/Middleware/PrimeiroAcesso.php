<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrimeiroAcesso
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Admin fora do escopo
        if (!$user || $user->tipo === 'admin') {
            return $next($request);
        }

        // Cliente ou Funcionário no primeiro acesso
        if (
            $user->primeiro_acesso &&
            !$request->routeIs('password.first', 'password.first.store', 'logout')
        ) {
            return redirect()->route('password.first');
        }

        return $next($request);
    }
}