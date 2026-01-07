<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\BoletoController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\DashboardController;

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
| DASHBOARD (somente ADMIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::patch('/cobrancas/{cobranca}/pagar', 
    [CobrancaController::class, 'pagar']
    )->name('cobrancas.pagar');

    Route::patch('/cobrancas/{cobranca}/pagar',
    [CobrancaController::class, 'marcarComoPago']
    )->name('cobrancas.pagar');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    // CLIENTES
    Route::resource('clientes', ClienteController::class);

    // COBRANÇAS
    Route::resource('cobrancas', CobrancaController::class);
    
    // BOLETOS
    Route::post('/boletos/{cliente}/upload', [BoletoController::class, 'upload'])->name('boletos.upload');
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
| PERFIL (ambos podem acessar)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});



require __DIR__.'/auth.php';