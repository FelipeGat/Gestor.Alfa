<?php

use App\Http\Controllers\Api\CobrancaApiController;
use App\Http\Controllers\Api\OrcamentoApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('orcamentos', OrcamentoApiController::class);
    Route::get('orcamentos/{orcamento}/cobrancas', [OrcamentoApiController::class, 'cobrancas']);

    Route::apiResource('cobrancas', CobrancaApiController::class);
    Route::post('cobrancas/{id}/baixa', [CobrancaApiController::class, 'baixa']);
    Route::get('cobrancas/pendentes', [CobrancaApiController::class, 'pendentes']);
    Route::get('cobrancas/vencidas', [CobrancaApiController::class, 'vencidas']);
});
