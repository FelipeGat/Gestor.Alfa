<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardComercialMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (
            !$user ||
            !(
                $user->tipo === 'comercial' ||
                $user->perfis()->where('slug', 'admin')->exists()
            )
        ) {
            abort(403);
        }

        return $next($request);
    }


}