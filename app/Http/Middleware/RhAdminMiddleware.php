<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class RhAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
