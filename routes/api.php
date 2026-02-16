<?php

use App\Http\Controllers\Api\OrcamentoApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('orcamentos', OrcamentoApiController::class);
    Route::get('orcamentos/{orcamento}/cobrancas', [OrcamentoApiController::class, 'cobrancas']);
});
