<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitUploads
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'upload:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'error' => 'Muitas tentativas de upload. Tente novamente em ' . RateLimiter::availableIn($key) . ' segundos.'
            ], 429);
        }
        
        RateLimiter::hit($key, 60); // 10 tentativas por minuto
        
        return $next($request);
    }
}
