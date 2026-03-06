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
        
        // Prioriza funcionario_id da query string (mobile), senão usa do usuário logado
        $funcionarioId = $request->query('funcionario_id') ?? $user->funcionario?->id;

        if (!$funcionarioId) {
            return response()->json(['message' => 'Funcionário não encontrado'], 404);
        }

        $query = RegistroPontoPortal::where('funcionario_id', $funcionarioId);

        // Se passar ?data=YYYY-MM-DD, usa essa data específica
        if ($request->has('data')) {
            $query->whereDate('data_referencia', $request->data);
        } else {
            // Sem parâmetro de data: retorna o registro mais recente
            $query->orderByDesc('data_referencia');
        }

        $registro = $query->first();

        // Calcular próximo evento baseado nos campos já preenchidos
        $proximoEvento = null;
        if (!$registro) {
            $proximoEvento = 'entrada';
        } elseif (!$registro->entrada_em) {
            $proximoEvento = 'entrada';
        } elseif (!$registro->intervalo_inicio_em) {
            $proximoEvento = 'intervalo_inicio';
        } elseif (!$registro->intervalo_fim_em) {
            $proximoEvento = 'intervalo_fim';
        } elseif (!$registro->saida_em) {
            $proximoEvento = 'saida';
        }

        return response()->json([
            'id' => $registro?->id,
            'entrada_em' => $registro?->entrada_em,
            'intervalo_inicio_em' => $registro?->intervalo_inicio_em,
            'intervalo_fim_em' => $registro?->intervalo_fim_em,
            'saida_em' => $registro?->saida_em,
            'proximo_evento' => $proximoEvento,
            'proximo_evento_label' => $proximoEvento ? ucfirst(str_replace('_', ' ', $proximoEvento)) : null,
        ]);
    }

    public function registrar(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        if (!$funcionarioId) {
            return response()->json(['message' => 'Funcionário não encontrado'], 400);
        }

        $validated = $request->validate([
            'tipo' => 'required|in:entrada,intervalo_inicio,intervalo_fim,saida',
            'foto' => 'nullable|image|mimes:jpeg,png|max:2048',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $registro = RegistroPontoPortal::firstOrNew([
            'funcionario_id' => $funcionarioId,
            'data_referencia' => Carbon::today(),
        ]);

        $campoTempo = match($validated['tipo']) {
            'entrada' => 'entrada_em',
            'intervalo_inicio' => 'intervalo_inicio_em',
            'intervalo_fim' => 'intervalo_fim_em',
            'saida' => 'saida_em',
        };

        $campoLatitude = match($validated['tipo']) {
            'entrada' => 'entrada_latitude',
            'intervalo_inicio' => 'intervalo_inicio_latitude',
            'intervalo_fim' => 'intervalo_fim_latitude',
            'saida' => 'saida_latitude',
        };

        $campoLongitude = match($validated['tipo']) {
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

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('ponto/'.$funcionarioId.'/'.now()->format('Y/m'), 'public');

            if ($validated['tipo'] === 'entrada') {
                $registro->entrada_foto_path = $fotoPath;
            }
            if ($validated['tipo'] === 'saida') {
                $registro->saida_foto_path = $fotoPath;
            }
        }

        $registro->save();

        return response()->json($registro);
    }
}
