<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PrimeiroAcesso
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Admin e Administrativo NÃO passam por primeiro acesso
        if ($user->isAdminPanel()) {
            return $next($request);
        }

        // Cliente ou Funcionário
        if (
            $user->primeiro_acesso &&
            !$request->routeIs('password.first', 'password.first.store', 'logout')
        ) {
            return redirect()->route('password.first');
        }

        return $next($request);
    }
}