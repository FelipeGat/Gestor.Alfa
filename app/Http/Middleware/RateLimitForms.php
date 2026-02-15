<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitForms
{
    public function handle(Request $request, Closure $next): Response
    {
        // Apenas para requisições POST, PUT, PATCH
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }
        
        $key = 'form:' . $request->ip() . ':' . $request->path();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Muitas tentativas. Aguarde ' . RateLimiter::availableIn($key) . ' segundos.'
                ], 429);
            }
            
            return back()->with('error', 'Muitas tentativas. Por favor, aguarde alguns segundos.');
        }
        
        RateLimiter::hit($key, 60); // 5 submissões por minuto
        
        return $next($request);
    }
}
