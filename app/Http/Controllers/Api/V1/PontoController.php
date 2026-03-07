<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RegistroPontoPortal;
use App\Traits\CalculaPonto;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PontoController extends Controller
{
    use CalculaPonto;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        $registros = RegistroPontoPortal::where('funcionario_id', $funcionarioId)
            ->orderByDesc('data_referencia')
            ->paginate(30);

        // Adicionar campos calculados em cada registro
        $registros->getCollection()->transform(function ($registro) {
            $dataReferencia = Carbon::parse($registro->data_referencia);
            $segundosTrabalhados = $this->calcularSegundosTrabalhados($registro);

            return (object) [
                'id' => $registro->id,
                'funcionario_id' => $registro->funcionario_id,
                'data_referencia' => $registro->data_referencia,
                'entrada_em' => $registro->entrada_em?->toISOString(),
                'intervalo_inicio_em' => $registro->intervalo_inicio_em?->toISOString(),
                'intervalo_fim_em' => $registro->intervalo_fim_em?->toISOString(),
                'saida_em' => $registro->saida_em?->toISOString(),
                'entrada_foto_path' => $registro->entrada_foto_path,
                'saida_foto_path' => $registro->saida_foto_path,
                'entrada_latitude' => $registro->entrada_latitude,
                'entrada_longitude' => $registro->entrada_longitude,
                'intervalo_inicio_latitude' => $registro->intervalo_inicio_latitude,
                'intervalo_inicio_longitude' => $registro->intervalo_inicio_longitude,
                'intervalo_fim_latitude' => $registro->intervalo_fim_latitude,
                'intervalo_fim_longitude' => $registro->intervalo_fim_longitude,
                'saida_latitude' => $registro->saida_latitude,
                'saida_longitude' => $registro->saida_longitude,
                'registrado_fora_atendimento' => $registro->registrado_fora_atendimento,
                'distancia_atendimento_metros' => $registro->distancia_atendimento_metros,
                'justificativa_fora_atendimento' => $registro->justificativa_fora_atendimento,
                'tempo_trabalhado_segundos' => $segundosTrabalhados,
                'tempo_trabalhado_formatado' => $this->formatarSegundos($segundosTrabalhados),
                'status' => $this->resolverStatus($registro, $segundosTrabalhados, $dataReferencia),
                'eh_domingo' => $this->ehDomingo($dataReferencia),
                'eh_feriado' => $this->ehFeriado($dataReferencia) !== null,
                'feriado_nome' => $this->ehFeriado($dataReferencia),
                'created_at' => $registro->created_at?->toISOString(),
                'updated_at' => $registro->updated_at?->toISOString(),
            ];
        });

        return response()->json($registros);
    }

    public function hoje(Request $request): JsonResponse
    {
        $user = $request->user();

        // Prioriza funcionario_id da query string (mobile), senão usa do usuário logado
        $funcionarioId = $request->query('funcionario_id') ?? $user->funcionario?->id;

        if (! $funcionarioId) {
            return response()->json(['message' => 'Funcionário não encontrado'], 404);
        }

        $query = RegistroPontoPortal::where('funcionario_id', $funcionarioId);

        // Se passar ?data=YYYY-MM-DD, usa essa data específica
        if ($request->has('data')) {
            $query->whereDate('data_referencia', $request->data);
        } else {
            // PADRÃO: filtra por HOJE (não retorna registro de ontem)
            $query->whereDate('data_referencia', Carbon::today());
        }

        $registro = $query->first();

        // Calcular próximo evento baseado nos campos já preenchidos
        $proximoEvento = null;
        if (! $registro) {
            $proximoEvento = 'entrada';
        } elseif (! $registro->entrada_em) {
            $proximoEvento = 'entrada';
        } elseif (! $registro->intervalo_inicio_em) {
            $proximoEvento = 'intervalo_inicio';
        } elseif (! $registro->intervalo_fim_em) {
            $proximoEvento = 'intervalo_fim';
        } elseif (! $registro->saida_em) {
            $proximoEvento = 'saida';
        }

        // Calcular tempo trabalhado (usa mesma lógica do sistema web)
        $tempoTrabalhadoSegundos = $this->calcularSegundosTrabalhados($registro);
        $tempoTrabalhadoFormatado = $this->formatarSegundos($tempoTrabalhadoSegundos);

        // Determinar data de referência para resposta
        $dataReferencia = $registro?->data_referencia ?? Carbon::today()->toDateString();

        return response()->json([
            'id' => $registro?->id,
            'entrada_em' => $registro?->entrada_em?->toISOString(),
            'intervalo_inicio_em' => $registro?->intervalo_inicio_em?->toISOString(),
            'intervalo_fim_em' => $registro?->intervalo_fim_em?->toISOString(),
            'saida_em' => $registro?->saida_em?->toISOString(),
            'tempo_trabalhado_segundos' => $tempoTrabalhadoSegundos,
            'tempo_trabalhado_formatado' => $tempoTrabalhadoFormatado,
            'proximo_evento' => $proximoEvento,
            'proximo_evento_label' => $proximoEvento ? ucfirst(str_replace('_', ' ', $proximoEvento)) : null,
            'data_referencia' => $dataReferencia,
        ]);
    }

    public function registrar(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        if (! $funcionarioId) {
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

        $campoTempo = match ($validated['tipo']) {
            'entrada' => 'entrada_em',
            'intervalo_inicio' => 'intervalo_inicio_em',
            'intervalo_fim' => 'intervalo_fim_em',
            'saida' => 'saida_em',
        };

        $campoLatitude = match ($validated['tipo']) {
            'entrada' => 'entrada_latitude',
            'intervalo_inicio' => 'intervalo_inicio_latitude',
            'intervalo_fim' => 'intervalo_fim_latitude',
            'saida' => 'saida_latitude',
        };

        $campoLongitude = match ($validated['tipo']) {
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

    /**
     * Retorna o saldo de banco de horas de um mês específico
     * 
     * GET /api/v1/ponto/banco-horas?mes=MM/YYYY
     */
    public function bancoHoras(Request $request): JsonResponse
    {
        $user = $request->user();
        $funcionarioId = $user->funcionario?->id;

        if (!$funcionarioId) {
            return response()->json(['message' => 'Funcionário não encontrado'], 404);
        }

        // Valida formato do mês (MM/YYYY)
        $validated = $request->validate([
            'mes' => 'nullable|regex:/^\d{2}\/\d{4}$/',
        ]);

        $mesAno = $validated['mes'] ?? now()->format('m/Y');
        $saldo = $this->calcularSaldoBancoHorasMesUnico($funcionarioId, $mesAno);

        return response()->json([
            'mes' => $mesAno,
            'banco_horas' => $saldo,
        ]);
    }
}
