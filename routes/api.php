<?php

use App\Http\Controllers\Api\CobrancaApiController;
use App\Http\Controllers\Api\OrcamentoApiController;
use App\Http\Controllers\Api\V1\AgendaController;
use App\Http\Controllers\Api\V1\AtendimentoController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardTecnicoController;
use App\Http\Controllers\Api\V1\PerfilController;
use App\Http\Controllers\Api\V1\PontoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::get('perfil', [PerfilController::class, 'show']);
        Route::put('perfil', [PerfilController::class, 'update']);

        Route::get('dashboard/tecnico', [DashboardTecnicoController::class, 'index']);

        Route::get('atendimentos', [AtendimentoController::class, 'index']);
        Route::get('atendimentos/{id}', [AtendimentoController::class, 'show']);
        Route::post('atendimentos/{id}/iniciar', [AtendimentoController::class, 'iniciar']);
        Route::post('atendimentos/{id}/pausar', [AtendimentoController::class, 'pausar']);
        Route::post('atendimentos/{id}/retomar', [AtendimentoController::class, 'retomar']);
        Route::post('atendimentos/{id}/finalizar', [AtendimentoController::class, 'finalizar']);
        Route::get('atendimentos/{id}/tempo', [AtendimentoController::class, 'atualizarTempo']);

        Route::get('ponto', [PontoController::class, 'index']);
        Route::get('ponto/hoje', [PontoController::class, 'hoje']);
        Route::post('ponto/registrar', [PontoController::class, 'registrar']);

        Route::get('agenda', [AgendaController::class, 'index']);
        Route::get('agenda/hoje', [AgendaController::class, 'hoje']);
        Route::get('agenda/disponibilidade', [AgendaController::class, 'disponibilidade']);

        Route::apiResource('orcamentos', OrcamentoApiController::class)->names('api.orcamentos');
        Route::get('orcamentos/{orcamento}/cobrancas', [OrcamentoApiController::class, 'cobrancas'])->name('api.orcamentos.cobrancas');

        Route::apiResource('cobrancas', CobrancaApiController::class)->names('api.cobrancas');
        Route::post('cobrancas/{id}/baixa', [CobrancaApiController::class, 'baixa'])->name('api.cobrancas.baixa');
        Route::get('cobrancas/pendentes', [CobrancaApiController::class, 'pendentes'])->name('api.cobrancas.pendentes');
        Route::get('cobrancas/vencidas', [CobrancaApiController::class, 'vencidas'])->name('api.cobrancas.vencidas');
    });
});
