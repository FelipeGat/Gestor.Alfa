<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminPanelMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user || !(
            $user->isAdminPanel() || $user->tipo === 'comercial'
            )) {
                abort(403);
            }

        return $next($request);
    }
}