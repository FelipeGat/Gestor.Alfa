<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

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

    // Comercial tem dashboard próprio
    if ($user->tipo === 'comercial') {
        return redirect()->route('dashboard.comercial');
    }

    // Admin / Administrativo
    if ($user->isAdminPanel()) {
        return redirect()->route('dashboard');
    }

    // Cliente
    if ($user->tipo === 'cliente') {
        return redirect()->route('portal.index');
    }

    // Funcionário
    return redirect()->route('portal-funcionario.dashboard');
}



    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}