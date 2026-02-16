<?php

use App\Http\Controllers\Api\CobrancaApiController;
use App\Http\Controllers\Api\OrcamentoApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('orcamentos', OrcamentoApiController::class)->names('api.orcamentos');
    Route::get('orcamentos/{orcamento}/cobrancas', [OrcamentoApiController::class, 'cobrancas'])->name('api.orcamentos.cobrancas');

    Route::apiResource('cobrancas', CobrancaApiController::class)->names('api.cobrancas');
    Route::post('cobrancas/{id}/baixa', [CobrancaApiController::class, 'baixa'])->name('api.cobrancas.baixa');
    Route::get('cobrancas/pendentes', [CobrancaApiController::class, 'pendentes'])->name('api.cobrancas.pendentes');
    Route::get('cobrancas/vencidas', [CobrancaApiController::class, 'vencidas'])->name('api.cobrancas.vencidas');
});
