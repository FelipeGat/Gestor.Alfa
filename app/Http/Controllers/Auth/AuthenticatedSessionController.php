<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Registrar evento de login na auditoria
        activity()->causedBy($user)->log('login');

        // Redirecionamento para Comercial
        if ($user->tipo === 'comercial') {
            return redirect()->route('dashboard.comercial');
        }

        // Redirecionamento para Administrativo -> Dashboard Técnico
        if ($user->isAdministrativo()) {
            return redirect()->route('dashboard.tecnico');
        }

        // Redirecionamento para Admin -> Dashboard Financeiro
        if ($user->isAdmin()) {
            return redirect()->route('financeiro.dashboard');
        }

        // Redirecionamento para Financeiro
        if ($user->tipo === 'financeiro' || $user->perfis()->where('slug', 'financeiro')->exists()) {
            return redirect()->route('financeiro.dashboard');
        }

        // Cliente
        if ($user->tipo === 'cliente') {
            return redirect()->route('portal.index');
        }

        // Funcion��rio
        return redirect()->route('portal-funcionario.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Capturar usuário antes do logout para registrar na auditoria
        $user = Auth::user();
        if ($user) {
            activity()->causedBy($user)->log('logout');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirecionar para login com headers para prevenir cache
        $response = redirect('/login');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
