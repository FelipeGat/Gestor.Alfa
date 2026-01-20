<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

use App\Models\Empresa;

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
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\PreClienteController;
use App\Http\Controllers\BuscaClienteController;
use App\Http\Controllers\ItemComercialController;




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
        ->middleware('dashboard.admin')
        ->name('dashboard');

    // DASHBOARD COMERCIAL
    Route::get( '/dashboard-comercial',
            [DashboardController::class, 'comercial'])
        ->middleware('dashboard.comercial')
        ->name('dashboard.comercial');
    
    // Orçamentos
    Route::resource('orcamentos', OrcamentoController::class)
        ->middleware('dashboard.comercial');

    Route::patch( '/orcamentos/{orcamento}/status',[OrcamentoController::class, 'updateStatus'])
        ->name('orcamentos.updateStatus');

    Route::get('/orcamentos/{id}/imprimir', [OrcamentoController::class, 'imprimir'])
        ->name('orcamentos.imprimir');

    // Serviços e Produtos
    Route::get('/itemcomercial/buscar', [ItemComercialController::class, 'buscar'])
        ->middleware(['auth', 'dashboard.comercial'])
        ->name('itemcomercial.buscar');

    Route::resource('itemcomercial', ItemComercialController::class)
        ->parameters([
            'itemcomercial' => 'item_comercial'
        ])
        ->except(['show'])
        ->middleware(['dashboard.comercial']);

    // Cobranças
    Route::patch('/cobrancas/{cobranca}/pagar',
        [CobrancaController::class, 'marcarComoPago']
    )->name('cobrancas.pagar');

    Route::resource('cobrancas', CobrancaController::class);

    // Busca unificada Cliente + Pré-Cliente (Orçamentos)
    Route::get('/busca-clientes', [BuscaClienteController::class, 'buscar'])
        ->middleware('dashboard.comercial')
        ->name('busca-clientes');

    // Clientes
    Route::get('/clientes/buscar', [ClienteController::class, 'buscar'])
        ->name('clientes.buscar');
        
    Route::resource('clientes', ClienteController::class);

    // Pré-Clientes (Admin e Comercial)
    Route::resource('pre-clientes', PreClienteController::class)
        ->middleware(['dashboard.comercial']);

    // Empresas
    Route::resource('empresas', EmpresaController::class);

    // Funcionários
    Route::resource('funcionarios', FuncionarioController::class);

    // Assuntos
    Route::resource('assuntos', AssuntoController::class);

    // Atendimentos
    Route::resource('atendimentos', AtendimentoController::class)
    ->except(['show']);

    // Usuarios
    Route::resource('usuarios', UsuarioController::class)
        ->middleware('dashboard.comercial');


    Route::get('/orcamentos/gerar-numero/{empresa}',
    [\App\Http\Controllers\OrcamentoController::class, 'gerarNumero']
    )->middleware('dashboard.comercial');


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

        // Lista de atendimentos do técnico
        Route::get('/atendimentos', [PortalFuncionarioController::class, 'atendimentos'])
            ->name('atendimentos.index');


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


    Route::get(
        '/empresas/{empresa}/assuntos',
            [EmpresaController::class, 'assuntos']
    );



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
            $user->tipo === 'comercial'
                ? 'dashboard.comercial'
                : ($user->isAdminPanel()
                    ? 'dashboard'
                    : ($user->tipo === 'cliente'
                        ? 'portal.index'
                        : 'portal-funcionario.dashboard'))
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