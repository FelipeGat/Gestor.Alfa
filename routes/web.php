<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\BoletoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\AssuntoController;
use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\PortalFuncionarioController;
use App\Http\Controllers\AtendimentoAndamentoFotoController;


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
Route::middleware(['auth', 'primeiro_acesso'])->group(function () {

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

    // Assuntos
    Route::resource('assuntos', AssuntoController::class);

    // Atendimentos
    Route::resource('atendimentos', AtendimentoController::class)
    ->except(['show']);

    Route::patch('/atendimentos/{atendimento}/atualizar-campo',
    [AtendimentoController::class, 'atualizarCampo']);

    Route::post(
    '/atendimentos/{atendimento}/andamentos',
    [\App\Http\Controllers\AtendimentoAndamentoController::class, 'store']
    )->name('atendimentos.andamentos.store');

    Route::post(
    '/atendimentos/{atendimento}/atualizar-status',
    [\App\Http\Controllers\AtendimentoStatusController::class, 'update']
    )->name('atendimentos.status.update');

    Route::get('/teste-permissao', function () {
         /** @var \App\Models\User $user */
    $user = Auth::user();

    return [
        'clientes_ler' => $user->canPermissao('clientes', 'ler'),
        'clientes_excluir' => $user->canPermissao('clientes', 'excluir'),
        'empresas_ler' => $user->canPermissao('empresas', 'ler'),
    ];
    })->middleware('auth');



    // Upload de fotos
    Route::post(
    '/andamentos/{andamento}/fotos',
    [AtendimentoAndamentoFotoController::class, 'store']
    )->name('andamentos.fotos.store');
    

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

        Route::get('/dashboard', [PortalFuncionarioController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/agenda', [PortalFuncionarioController::class, 'agenda'])
            ->name('agenda');

        // Visualizar atendimento (somente leitura)
        Route::get('/atendimentos/{atendimento}', [PortalFuncionarioController::class, 'show'])
            ->name('atendimentos.show');

        // Técnico envia para FINALIZAÇÃO
        Route::post('/atendimentos/{atendimento}/finalizacao', [PortalFuncionarioController::class, 'enviarParaFinalizacao'])
            ->name('atendimentos.finalizacao');

        // Técnico aenxa Fotos
        Route::post('/andamentos/{andamento}/fotos', [AtendimentoAndamentoFotoController::class, 'store'])
            ->name('andamentos.fotos.store');

        // Técnico deleta Fotos
        Route::delete('/andamentos/fotos/{foto}',    [AtendimentoAndamentoFotoController::class, 'destroy'])
            ->name('andamentos.fotos.destroy');


            
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
            $user->isAdminPanel()
                ? 'dashboard'
                : ($user->tipo === 'cliente'
                    ? 'portal.index'
                    : 'portal-funcionario.dashboard')
        );

    })->name('password.first.store');
});


/*
|--------------------------------------------------------------------------
| API – Consulta CNPJ (Proxy ReceitaWS)
|--------------------------------------------------------------------------
*/
Route::get('/api/cnpj/{cnpj}', function ($cnpj) {

    $cnpj = preg_replace('/\D/', '', $cnpj);

    if (strlen($cnpj) !== 14) {
        return response()->json([
            'status' => 'ERROR',
            'message' => 'CNPJ inválido'
        ], 400);
    }

    try {

        $cacheKey = "cnpj_{$cnpj}";

        $data = Cache::remember($cacheKey, 60 * 60 * 24, function () use ($cnpj) {

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(15)
                ->get("https://www.receitaws.com.br/v1/cnpj/{$cnpj}");

            return $response->json();
        });

        return response()->json($data);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'message' => 'Erro ao consultar Receita Federal'
        ], 500);
    }
});


require __DIR__.'/auth.php';