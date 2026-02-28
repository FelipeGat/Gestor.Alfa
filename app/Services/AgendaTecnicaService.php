<?php

namespace App\Services;

use App\Models\Atendimento;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AgendaTecnicaService
{
    public const PERIODOS = [
        'manha' => ['inicio' => '08:00', 'fim' => '12:00'],
        'tarde' => ['inicio' => '13:00', 'fim' => '18:00'],
        'noite' => ['inicio' => '18:01', 'fim' => '21:59'],
    ];

    public function agendarAtendimento(
        Atendimento $atendimento,
        int $funcionarioId,
        string $data,
        string $periodo,
        string $horaInicio,
        int $duracaoHoras,
        ?int $ignorarAtendimentoId = null
    ): Atendimento {
        [$inicio, $fim] = $this->resolverJanela($data, $periodo, $horaInicio, $duracaoHoras);

        if ($this->existeConflito($funcionarioId, $inicio, $fim, $ignorarAtendimentoId ?? $atendimento->id)) {
            throw ValidationException::withMessages([
                'agendamento' => 'Já existe atendimento agendado para este técnico no mesmo horário.',
            ]);
        }

        $atendimento->update([
            'funcionario_id' => $funcionarioId,
            'periodo_agendamento' => $periodo,
            'data_inicio_agendamento' => $inicio,
            'data_fim_agendamento' => $fim,
            'duracao_agendamento_minutos' => $duracaoHoras * 60,
            'data_atendimento' => $inicio,
        ]);

        return $atendimento->fresh();
    }

    public function resolverJanela(string $data, string $periodo, string $horaInicio, int $duracaoHoras): array
    {
        if (! array_key_exists($periodo, self::PERIODOS)) {
            throw ValidationException::withMessages([
                'periodo_agendamento' => 'Período inválido.',
            ]);
        }

        if ($duracaoHoras < 1 || $duracaoHoras > 4) {
            throw ValidationException::withMessages([
                'duracao_horas' => 'A duração deve ser entre 1 e 4 horas.',
            ]);
        }

        $dataBase = Carbon::createFromFormat('Y-m-d', $data)->startOfDay();
        $inicio = Carbon::createFromFormat('Y-m-d H:i', $dataBase->format('Y-m-d') . ' ' . $horaInicio);

        $limites = self::PERIODOS[$periodo];
        $inicioLimite = Carbon::createFromFormat('Y-m-d H:i', $dataBase->format('Y-m-d') . ' ' . $limites['inicio']);
        $fimLimite = Carbon::createFromFormat('Y-m-d H:i', $dataBase->format('Y-m-d') . ' ' . $limites['fim']);

        if ($inicio->lt($inicioLimite) || $inicio->gt($fimLimite)) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'Horário inicial fora do período selecionado.',
            ]);
        }

        $fim = $inicio->copy()->addHours($duracaoHoras);

        if ($fim->gt($fimLimite)) {
            throw ValidationException::withMessages([
                'duracao_horas' => 'A duração ultrapassa o limite do período selecionado.',
            ]);
        }

        return [$inicio, $fim];
    }

    public function existeConflito(
        int $funcionarioId,
        Carbon $inicio,
        Carbon $fim,
        ?int $ignorarAtendimentoId = null
    ): bool {
        $query = Atendimento::query()
            ->where('funcionario_id', $funcionarioId)
            ->whereNotNull('data_inicio_agendamento')
            ->whereNotNull('data_fim_agendamento')
            ->whereNull('deleted_at')
            ->whereNotIn('status_atual', ['concluido'])
            ->where(function ($q) use ($inicio, $fim) {
                $q->where('data_inicio_agendamento', '<', $fim)
                    ->where('data_fim_agendamento', '>', $inicio);
            });

        if ($ignorarAtendimentoId) {
            $query->where('id', '!=', $ignorarAtendimentoId);
        }

        return $query->exists();
    }
}
