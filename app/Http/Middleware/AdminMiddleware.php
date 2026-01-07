<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if (Auth::user()->tipo !== 'admin') {
        abort(403, 'Acesso não autorizado.');
    }

    return $next($request);
}
}