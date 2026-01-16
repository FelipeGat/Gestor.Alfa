<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (
            !$user ||
            !(
                $user->isAdminPanel() &&
                $user->tipo !== 'comercial'
            )
        ) {
            abort(403);
        }

        return $next($request);
    }
}