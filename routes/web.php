<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\BoletoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FuncionarioController;

/*
|--------------------------------------------------------------------------
| Redirecionamento inicial
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| ÁREA ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin', 'primeiro_acesso'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Cobranças
    Route::patch('/cobrancas/{cobranca}/pagar',
        [CobrancaController::class, 'marcarComoPago']
    )->name('cobrancas.pagar');

    Route::resource('cobrancas', CobrancaController::class);

    // Clientes
    Route::resource('clientes', ClienteController::class);

    // Empresas
    Route::resource('empresas', EmpresaController::class);

    // Funcionários
    Route::resource('funcionarios', FuncionarioController::class);

    // Upload de boletos
    Route::post('/boletos/{cliente}/upload',
        [BoletoController::class, 'upload']
    )->name('boletos.upload');
});

/*
|--------------------------------------------------------------------------
| PORTAL DO CLIENTE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'cliente', 'primeiro_acesso'])->group(function () {

    Route::get('/portal', [PortalController::class, 'index'])
        ->name('portal.index');

    Route::get('/portal/boletos/{boleto}/download',
        [PortalController::class, 'downloadBoleto']
    )->name('portal.boletos.download');

    Route::get('/portal/notas/{nota}/download',
        [PortalController::class, 'downloadNotaFiscal']
    )->name('portal.notas.download');
});

/*
|--------------------------------------------------------------------------
| PORTAL DO FUNCIONÁRIO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'funcionario', 'primeiro_acesso'])
    ->prefix('portal-funcionario')
    ->name('portal-funcionario.')
    ->group(function () {

        // Dashboard do funcionário
        Route::get('/dashboard', function () {
            return view('portal-funcionario.dashboard');
        })->name('dashboard');

        // Agenda (futuro)
        Route::get('/agenda', function () {
            return view('portal-funcionario.agenda');
        })->name('agenda');
    });

/*
|--------------------------------------------------------------------------
| PERFIL (todos logados)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| PRIMEIRO ACESSO – TROCA DE SENHA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/primeiro-acesso', function () {
        return view('auth.primeiro-acesso');
    })->name('password.first');

    Route::post('/primeiro-acesso', function (Request $request) {

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->update([
            'password' => bcrypt($request->password),
            'primeiro_acesso' => false,
        ]);

        return redirect()->route(
            $user->tipo === 'admin'
                ? 'dashboard'
                : ($user->tipo === 'cliente'
                    ? 'portal.index'
                    : 'portal-funcionario.dashboard')
        );
    })->name('password.first.store');
});

require __DIR__.'/auth.php';