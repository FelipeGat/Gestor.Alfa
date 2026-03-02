<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Services\Relatorios\RelatorioTecnicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelatorioTecnicoController extends Controller
{
    public function __construct(private readonly RelatorioTecnicoService $service) {}

    public function __invoke(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'centro_custo_id' => ['nullable', 'integer', 'exists:centros_custo,id'],
        ]);

        return response()->json($this->service->gerar($filtros));
    }
}
