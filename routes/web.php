<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\BoletoController;
use Illuminate\Support\Facades\Mail;
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
| ÁREA ADMIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    // DASHBOARD

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->name('dashboard');


    // MARCAR COBRANÇA COMO PAGA
    Route::patch('/cobrancas/{cobranca}/pagar',
    [CobrancaController::class, 'marcarComoPago']
    )->name('cobrancas.pagar');

    // Route::patch('/cobrancas/{cobranca}/pagar', 
    // [CobrancaController::class, 'pagar']
    // )->name('cobrancas.pagar');

    
    // CLIENTES
    Route::resource('clientes', ClienteController::class);

    // COBRANÇAS
    Route::resource('cobrancas', CobrancaController::class);
    
    // BOLETOS
    Route::post('/boletos/{cliente}/upload', [BoletoController::class, 'upload'])->name('boletos.upload');

    // EMPRESAS
    Route::resource('empresas', EmpresaController::class);

    // FUNCIONÁRIOS
    Route::resource('funcionarios', FuncionarioController::class);
});


/*
|--------------------------------------------------------------------------
| PORTAL DO CLIENTE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'cliente'])->group(function () {

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

    Route::middleware(['auth', 'funcionario'])->group(function () {

        Route::get('/portal-funcionario', function () {
            return view('portal-funcionario.index');
        })->name('portal-funcionario.index');

    });

/*
|--------------------------------------------------------------------------
| PERFIL (ambos podem acessar)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});



require __DIR__.'/auth.php';