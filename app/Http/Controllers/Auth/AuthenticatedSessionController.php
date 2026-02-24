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

        // Redirecionamento para Comercial
        if ($user->tipo === 'comercial') {
            return redirect()->route('dashboard.comercial');
        }

        // Redirecionamento para Admin/Administrativo -> Financeiro Dashboard
        if ($user->isAdminPanel()) {
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
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('clear_session_storage', true);
    }
}
