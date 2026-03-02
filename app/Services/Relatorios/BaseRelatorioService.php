<?php

namespace App\Services\Relatorios;

use Carbon\Carbon;

abstract class BaseRelatorioService
{
    protected function periodo(array $filtros): array
    {
        $inicio = Carbon::parse($filtros['data_inicio'])->startOfDay();
        $fim = Carbon::parse($filtros['data_fim'])->endOfDay();

        if ($fim->lt($inicio)) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        return [$inicio, $fim];
    }

    protected function periodoAnterior(Carbon $inicio, Carbon $fim): array
    {
        $dias = max(1, $inicio->copy()->startOfDay()->diffInDays($fim->copy()->startOfDay()) + 1);
        $fimAnterior = $inicio->copy()->subDay()->endOfDay();
        $inicioAnterior = $fimAnterior->copy()->subDays($dias - 1)->startOfDay();

        return [$inicioAnterior, $fimAnterior, $dias];
    }

    protected function percentual(float|int $numerador, float|int $denominador): float
    {
        if ((float) $denominador === 0.0) {
            return 0.0;
        }

        return round(((float) $numerador / (float) $denominador) * 100, 2);
    }

    protected function f(float|int|null $valor): float
    {
        return round((float) ($valor ?? 0), 2);
    }
}
