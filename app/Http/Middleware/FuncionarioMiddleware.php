<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FuncionarioMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            abort(403);
        }

        $user = Auth::user();

        $tipo = strtolower((string) $user->tipo);
        $tiposPermitidos = ['funcionario', 'admin', 'administrativo', 'financeiro', 'comercial'];

        $perfilFinanceiro = DB::table('perfil_user as pu')
            ->join('perfis as p', 'p.id', '=', 'pu.perfil_id')
            ->where('pu.user_id', $user->id)
            ->where('p.slug', 'financeiro')
            ->exists();

        $perfilComercial = DB::table('perfil_user as pu')
            ->join('perfis as p', 'p.id', '=', 'pu.perfil_id')
            ->where('pu.user_id', $user->id)
            ->where('p.slug', 'comercial')
            ->exists();

        $podeAcessar = in_array($tipo, $tiposPermitidos, true)
            || $perfilFinanceiro
            || $perfilComercial;

        if (!$podeAcessar) {
            abort(403);
        }

        if (empty($user->funcionario_id)) {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Seu usuário precisa estar vinculado a um funcionário para acessar o Portal do Funcionário.');
        }

        return $next($request);
    }
}
