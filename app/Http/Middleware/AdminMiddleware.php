<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = Auth::user();

        // ADMIN tem acesso total
        if ($user->tipo === 'admin') {
            return $next($request);
        }

        // Administrativo precisa ter perfil válido
        if ($user->isAdminPanel()) {
            return $next($request);
        }

        abort(403, 'Acesso não autorizado.');
    }
}