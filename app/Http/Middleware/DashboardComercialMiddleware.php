<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardComercialMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (
            !$user ||
            !(
                $user->tipo === 'comercial' ||
                $user->isAdminPanel()
            )
        ) {
            abort(403);
        }

        return $next($request);
    }
}
