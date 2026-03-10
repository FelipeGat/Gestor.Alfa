<?php

use App\Http\Controllers\Api\CobrancaApiController;
use App\Http\Controllers\Api\OrcamentoApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PontoController;
use App\Http\Controllers\Api\V1\AgendaController;
use App\Http\Controllers\Api\V1\AtendimentoController;
use App\Http\Controllers\Api\V1\DashboardTecnicoController;
use App\Http\Controllers\Api\V1\PerfilController;
use App\Http\Controllers\Api\V1\RotaController;
use App\Http\Controllers\Api\V1\NotificacaoTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Rotas removidas por ausência dos controllers V1

    // Auth
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // Notificações (token FCM)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('notificacoes/token', [NotificacaoTokenController::class, 'store']);
    });

    // Ponto (protegido)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('ponto', [PontoController::class, 'index']);
        Route::get('ponto/hoje', [PontoController::class, 'hoje']);
        Route::get('ponto/banco-horas', [PontoController::class, 'bancoHoras']);
        Route::post('ponto/registrar', [PontoController::class, 'registrar']);
    });

    // Agenda
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('agenda', [AgendaController::class, 'index']);
        Route::get('agenda/hoje', [AgendaController::class, 'hoje']);
        Route::get('agenda/{id}', [AgendaController::class, 'show']);
    });

    // Atendimentos
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('atendimentos', [AtendimentoController::class, 'index']);
        Route::get('atendimentos/{id}', [AtendimentoController::class, 'show']);
        Route::get('atendimentos/{id}/tempo', [AtendimentoController::class, 'tempo']);
        Route::post('atendimentos/{id}/iniciar', [AtendimentoController::class, 'iniciar']);
        Route::post('atendimentos/{id}/pausar', [AtendimentoController::class, 'pausar']);
        Route::post('atendimentos/{id}/retomar', [AtendimentoController::class, 'retomar']);
        Route::post('atendimentos/{id}/finalizar', [AtendimentoController::class, 'finalizar']);
    });

    // Dashboard
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('dashboard/tecnico', [DashboardTecnicoController::class, 'index']);
    });

    // Perfil
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('perfil', [PerfilController::class, 'show']);
        Route::put('perfil', [PerfilController::class, 'update']);
    });

    Route::apiResource('orcamentos', OrcamentoApiController::class)->names('api.orcamentos');
    Route::get('orcamentos/{orcamento}/cobrancas', [OrcamentoApiController::class, 'cobrancas'])->name('api.orcamentos.cobrancas');

    Route::apiResource('cobrancas', CobrancaApiController::class)->names('api.cobrancas');
    Route::post('cobrancas/{id}/baixa', [CobrancaApiController::class, 'baixa'])->name('api.cobrancas.baixa');
    Route::get('cobrancas/pendentes', [CobrancaApiController::class, 'pendentes'])->name('api.cobrancas.pendentes');
    Route::get('cobrancas/vencidas', [CobrancaApiController::class, 'vencidas'])->name('api.cobrancas.vencidas');

    // Rota - pública (sem autenticação)
    Route::get('rota/consulta', [RotaController::class, 'consulta']);
});
