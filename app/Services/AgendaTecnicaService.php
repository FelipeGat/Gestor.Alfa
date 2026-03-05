<?php

namespace App\Services;

use App\Models\Atendimento;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AgendaTecnicaService
{
    /**
     * Agenda um atendimento (stub temporário)
     */
    public function agendarAtendimento(
        Atendimento $atendimento,
        int $funcionarioId,
        string $data,
        string $periodo,
        string $horaInicio,
        int $duracaoHoras,
        ?int $ignorarAtendimentoId = null
    ) {
        // Validação básica dos parâmetros
        if (!array_key_exists($periodo, self::PERIODOS)) {
            throw ValidationException::withMessages([
                'periodo_agendamento' => 'Período inválido.'
            ]);
        }

        $dataBase = Carbon::createFromFormat('Y-m-d', $data)->startOfDay();
        $inicio = Carbon::createFromFormat('Y-m-d H:i', $dataBase->format('Y-m-d') . ' ' . $horaInicio);
        $limites = self::PERIODOS[$periodo];
        $inicioLimite = Carbon::createFromFormat('Y-m-d H:i', $dataBase->format('Y-m-d') . ' ' . $limites['inicio']);
        $fimLimite = Carbon::createFromFormat('Y-m-d H:i', $dataBase->format('Y-m-d') . ' ' . $limites['fim']);

        // Validações de horário
        if ($inicio->lt($inicioLimite) || $inicio->gt($fimLimite)) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'Horário inicial fora do período selecionado.'
            ]);
        }

        $fim = $inicio->copy()->addHours($duracaoHoras);
        if ($fim->gt($fimLimite)) {
            throw ValidationException::withMessages([
                'duracao_horas' => 'A duração ultrapassa o limite do período selecionado.'
            ]);
        }

        // Salva os campos no atendimento
        $atendimento->data_inicio_agendamento = $inicio;
        $atendimento->data_fim_agendamento = $fim;
        $atendimento->periodo_agendamento = $periodo;
        $atendimento->funcionario_id = $funcionarioId;
        $atendimento->save();

        return $atendimento;
    }
    public const PERIODOS = [
        'manha' => ['inicio' => '08:00', 'fim' => '12:00'],
        'tarde' => ['inicio' => '13:00', 'fim' => '18:00'],
        'noite' => ['inicio' => '18:01', 'fim' => '21:59'],
        'dia_todo' => ['inicio' => '08:00', 'fim' => '17:00'],
    ];

    /**
     * Retorna os períodos disponíveis para agendamento de um técnico em uma data
     */
    public function periodosDisponiveis(int $funcionarioId, string $data): array
    {
        $ocupados = [];
        foreach (self::PERIODOS as $periodo => $limites) {
            $inicio = Carbon::createFromFormat('Y-m-d H:i', $data . ' ' . $limites['inicio']);
            $fim = Carbon::createFromFormat('Y-m-d H:i', $data . ' ' . $limites['fim']);
            if ($this->existeConflito($funcionarioId, $inicio, $fim)) {
                $ocupados[] = $periodo;
            }
        }
        // Retorna apenas os períodos livres
        return array_diff(array_keys(self::PERIODOS), $ocupados);
    }

    // ...restante da classe permanece igual...

    /**
     * Reprogramar atendimento para nova data/horário
     * Valida conflito com o próprio atendimento sendo reprogramado
     */
    public function reprogramarAtendimento(
        Atendimento $atendimento,
        int $funcionarioId,
        string $data,
        string $periodo,
        string $horaInicio,
        int $duracaoHoras
    ): Atendimento {
        return $this->agendarAtendimento(
            $atendimento,
            $funcionarioId,
            $data,
            $periodo,
            $horaInicio,
            $duracaoHoras,
            $atendimento->id
        );
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
