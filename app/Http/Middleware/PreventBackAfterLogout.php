<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para prevenir navegação com botão voltar após logout
 * 
 * Adiciona headers HTTP que instruem o navegador a não cachear páginas
 * sensíveis, forçando recarregamento ao tentar navegar com voltar/avançar.
 */
class PreventBackAfterLogout
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Headers para prevenir cache do navegador
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Surrogate-Control', 'no-store');
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        return $response;
    }
}
