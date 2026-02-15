<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'api:' . ($request->user()?->id ?? $request->ip());
        
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'error' => 'Limite de requisições excedido. Tente novamente em ' . RateLimiter::availableIn($key) . ' segundos.'
            ], 429);
        }
        
        RateLimiter::hit($key, 60); // 60 requisições por minuto
        
        return $next($request);
    }
}
