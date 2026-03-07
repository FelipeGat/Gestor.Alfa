<?php

namespace App\Traits;

use App\Models\RegistroPontoPortal;
use Carbon\Carbon;

trait CalculaPonto
{
    /**
     * Meta de horas semanais em segundos (44 horas)
     */
    private const SEGUNDOS_META_SEMANAL = 158400;

    /**
     * Calcula os segundos trabalhados em um registro de ponto
     * Usa normalização para minuto (padrão de mercado - remove segundos)
     * Calcula horas parciais para registros incompletos (só manhã ou só tarde)
     */
    protected function calcularSegundosTrabalhados(RegistroPontoPortal $registro): int
    {
        if (!$registro) {
            return 0;
        }

        $segundos = 0;

        // Normaliza para minuto (padrão de mercado - remove segundos)
        $entrada = $this->normalizarBatidaParaMinuto($registro->entrada_em);
        $intervaloInicio = $this->normalizarBatidaParaMinuto($registro->intervalo_inicio_em);
        $intervaloFim = $this->normalizarBatidaParaMinuto($registro->intervalo_fim_em);
        $saida = $this->normalizarBatidaParaMinuto($registro->saida_em);

        // 1. Calcula período da manhã (entrada → início intervalo)
        if ($entrada && $intervaloInicio && $intervaloInicio->gt($entrada)) {
            $segundos += $intervaloInicio->diffInSeconds($entrada, true);
        }

        // 2. Calcula período da tarde (fim intervalo → saída)
        if ($intervaloFim && $saida && $saida->gt($intervaloFim)) {
            $segundos += $saida->diffInSeconds($intervaloFim, true);
        }

        // 3. Se conseguiu calcular períodos separados, retorna
        if ($segundos > 0) {
            return $segundos;
        }

        // 4. Fallback: calcula direto se tiver entrada e saída (sem intervalo)
        if ($entrada && $saida && $saida->gt($entrada)) {
            return $saida->diffInSeconds($entrada, true);
        }

        return 0;
    }

    /**
     * Normaliza horário para minuto (remove segundos)
     * Padrão usado pela maioria dos sistemas de ponto SaaS
     */
    protected function normalizarBatidaParaMinuto($valor): ?Carbon
    {
        if (!$valor) {
            return null;
        }

        return Carbon::parse($valor)->copy()->setSecond(0);
    }

    /**
     * Formata segundos para formato HH:MM
     */
    protected function formatarSegundos(int $segundos): string
    {
        $segundos = max(0, $segundos);
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);

        return sprintf('%02d:%02d', $horas, $minutos);
    }

    /**
     * Formata segundos para formato HH:MM:SS
     */
    protected function formatarSegundosCompletos(int $segundos): string
    {
        $segundos = max(0, $segundos);
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        $segs = $segundos % 60;

        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    }

    /**
     * Formata saldo de banco de horas (com sinal)
     */
    protected function formatarSaldoBancoHoras(int $segundos): string
    {
        $sinal = $segundos < 0 ? '-' : '+';
        $segundosAbsolutos = abs($segundos);
        $horas = intdiv($segundosAbsolutos, 3600);
        $minutos = intdiv($segundosAbsolutos % 3600, 60);

        return sprintf('%s%02d:%02d', $sinal, $horas, $minutos);
    }

    /**
     * Verifica se é domingo
     */
    protected function ehDomingo(Carbon $data): bool
    {
        return $data->isSunday();
    }

    /**
     * Verifica se é feriado nacional
     */
    protected function ehFeriado(Carbon $data): ?string
    {
        $ano = (int) $data->year;
        $dataChave = $data->toDateString();

        // Feriados fixos
        $fixos = [
            sprintf('%04d-01-01', $ano) => 'Confraternização Universal',
            sprintf('%04d-04-21', $ano) => 'Tiradentes',
            sprintf('%04d-05-01', $ano) => 'Dia do Trabalho',
            sprintf('%04d-09-07', $ano) => 'Independência do Brasil',
            sprintf('%04d-10-12', $ano) => 'Nossa Senhora Aparecida',
            sprintf('%04d-11-02', $ano) => 'Finados',
            sprintf('%04d-11-15', $ano) => 'Proclamação da República',
            sprintf('%04d-11-20', $ano) => 'Dia da Consciência Negra',
            sprintf('%04d-12-25', $ano) => 'Natal',
        ];

        if (array_key_exists($dataChave, $fixos)) {
            return $fixos[$dataChave];
        }

        // Feriados móveis (baseados na Páscoa)
        $pascoa = Carbon::createFromTimestamp(easter_date($ano))->startOfDay();
        $moveis = [
            $pascoa->copy()->subDays(48)->toDateString() => 'Carnaval',
            $pascoa->copy()->subDays(47)->toDateString() => 'Carnaval',
            $pascoa->copy()->subDays(2)->toDateString() => 'Sexta-feira Santa',
            $pascoa->copy()->toDateString() => 'Páscoa',
            $pascoa->copy()->addDays(60)->toDateString() => 'Corpus Christi',
        ];

        if (array_key_exists($dataChave, $moveis)) {
            return $moveis[$dataChave];
        }

        return null;
    }

    /**
     * Resolve o status do dia de trabalho
     */
    protected function resolverStatus(RegistroPontoPortal $registro, int $segundosTrabalhados, Carbon $data): string
    {
        $ehDomingo = $this->ehDomingo($data);
        $ehFeriado = $this->ehFeriado($data) !== null;

        $possuiBatidas = $registro->entrada_em || $registro->intervalo_inicio_em 
            || $registro->intervalo_fim_em || $registro->saida_em;

        if (!$possuiBatidas) {
            return ($ehDomingo || $ehFeriado) ? '' : 'Falta';
        }

        $intervaloIncompleto = ($registro->intervalo_inicio_em && !$registro->intervalo_fim_em)
            || (!$registro->intervalo_inicio_em && $registro->intervalo_fim_em);

        if (!$registro->entrada_em || !$registro->saida_em || $intervaloIncompleto) {
            return 'Incompleto';
        }

        if ($ehDomingo || $ehFeriado) {
            return $ehFeriado ? 'Extra feriado' : 'Extra';
        }

        // 29400 segundos = 8 horas e 10 minutos (tolerância para hora extra)
        return $segundosTrabalhados > 29400 ? 'Extra' : 'Normal';
    }

    /**
     * Calcula o saldo de banco de horas por mês
     */
    protected function calcularSaldoBancoHorasMeses(int $funcionarioId, array $mesesReferencia): array
    {
        $saldoPorMes = [];

        foreach ($mesesReferencia as $mesAno) {
            [$mes, $ano] = explode('/', $mesAno);
            $inicioMes = Carbon::createFromDate((int) $ano, (int) $mes, 1)->startOfMonth();
            $fimMes = $inicioMes->copy()->endOfMonth();

            $registrosMes = RegistroPontoPortal::query()
                ->where('funcionario_id', $funcionarioId)
                ->whereBetween('data_referencia', [$inicioMes->toDateString(), $fimMes->toDateString()])
                ->orderBy('data_referencia')
                ->get();

            $totaisSemanais = [];
            foreach ($registrosMes as $registro) {
                $dia = Carbon::parse($registro->data_referencia)->startOfDay();
                $chaveSemana = sprintf('%s-W%s', $dia->format('o'), $dia->format('W'));
                $totaisSemanais[$chaveSemana] = ($totaisSemanais[$chaveSemana] ?? 0) 
                    + $this->calcularSegundosTrabalhados($registro);
            }

            $saldoSegundos = collect($totaisSemanais)
                ->reduce(fn (int $saldo, int $segundosSemana) 
                    => $saldo + ($segundosSemana - self::SEGUNDOS_META_SEMANAL), 0
                );

            $saldoPorMes[$mesAno] = [
                'segundos' => $saldoSegundos,
                'formatado' => $this->formatarSaldoBancoHoras($saldoSegundos),
                'positivo' => $saldoSegundos >= 0,
            ];
        }

        return $saldoPorMes;
    }

    /**
     * Calcula o saldo de banco de horas para um único mês
     */
    protected function calcularSaldoBancoHorasMesUnico(int $funcionarioId, string $mesAno): array
    {
        $resultado = $this->calcularSaldoBancoHorasMeses($funcionarioId, [$mesAno]);
        return $resultado[$mesAno] ?? [
            'segundos' => 0,
            'formatado' => '+00:00',
            'positivo' => true,
        ];
    }
}
