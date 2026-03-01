<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RegistrarSessaoAtivaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $session = $request->session();
            $agoraIso = now()->toIso8601String();

            if (!$session->has('sessao_login_iniciada_em')) {
                $session->put('sessao_login_iniciada_em', $agoraIso);
            }

            if (!$session->has('sessao_ultima_atividade_em')) {
                $session->put('sessao_ultima_atividade_em', $agoraIso);
            }
        } else {
            $request->session()->forget(['sessao_login_iniciada_em', 'sessao_ultima_atividade_em']);
        }

        $response = $next($request);

        if (Auth::check()) {
            $request->session()->put('sessao_ultima_atividade_em', now()->toIso8601String());
        }

        return $response;
    }
}
