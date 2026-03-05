<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RegistroPontoPortal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PontoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        $registros = RegistroPontoPortal::where('funcionario_id', $funcionarioId)
            ->orderByDesc('data_referencia')
            ->paginate(30);

        return response()->json($registros);
    }

    public function hoje(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        $registro = RegistroPontoPortal::where('funcionario_id', $funcionarioId)
            ->whereDate('data_referencia', Carbon::today())
            ->first();

        return response()->json($registro);
    }

    public function registrar(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        if (!$funcionarioId) {
            return response()->json(['message' => 'Funcionário não encontrado'], 400);
        }

        $tipo = $request->validate([
            'tipo' => 'required|in:entrada,intervalo_inicio,intervalo_fim,saida',
        ])['tipo'];

        $registro = RegistroPontoPortal::firstOrNew([
            'funcionario_id' => $funcionarioId,
            'data_referencia' => Carbon::today(),
        ]);

        $campoTempo = match($tipo) {
            'entrada' => 'entrada_em',
            'intervalo_inicio' => 'intervalo_inicio_em',
            'intervalo_fim' => 'intervalo_fim_em',
            'saida' => 'saida_em',
        };

        $campoLatitude = match($tipo) {
            'entrada' => 'entrada_latitude',
            'intervalo_inicio' => 'intervalo_inicio_latitude',
            'intervalo_fim' => 'intervalo_fim_latitude',
            'saida' => 'saida_latitude',
        };

        $campoLongitude = match($tipo) {
            'entrada' => 'entrada_longitude',
            'intervalo_inicio' => 'intervalo_inicio_longitude',
            'intervalo_fim' => 'intervalo_fim_longitude',
            'saida' => 'saida_longitude',
        };

        $registro->{$campoTempo} = now();

        if ($request->has('latitude')) {
            $registro->{$campoLatitude} = $request->latitude;
        }
        if ($request->has('longitude')) {
            $registro->{$campoLongitude} = $request->longitude;
        }

        $registro->save();

        return response()->json($registro);
    }
}
