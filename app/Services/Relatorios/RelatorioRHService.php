<?php

namespace App\Services\Relatorios;

use Illuminate\Support\Facades\DB;

class RelatorioRHService extends BaseRelatorioService
{
    public function gerar(array $filtros): array
    {
        [$inicio, $fim] = $this->periodo($filtros);
        $empresaId = isset($filtros['empresa_id']) && $filtros['empresa_id'] !== null
            ? (int) $filtros['empresa_id']
            : null;

        $funcionarios = DB::table('funcionarios as f')
            ->join('empresa_funcionario as ef', 'ef.funcionario_id', '=', 'f.id')
            ->when($empresaId, fn ($q) => $q->where('ef.empresa_id', $empresaId))
            ->select('f.id', 'f.nome')
            ->distinct()
            ->get();

        $funcionarioIds = $funcionarios->pluck('id')->all();

        if (empty($funcionarioIds)) {
            return [
                'periodo' => [
                    'data_inicio' => $inicio->toDateString(),
                    'data_fim' => $fim->toDateString(),
                ],
                'total_atrasos' => 0,
                'total_faltas' => 0,
                'total_atestados' => 0,
                'horas_extras' => 0.0,
                'saldo_banco_horas_consolidado' => 0.0,
                'indice_absenteismo' => 0.0,
                'ranking_por_colaborador' => [],
                'insights_automaticos' => ['Nenhum colaborador vinculado à empresa no período.'],
            ];
        }

        $atrasosPorColaborador = DB::table('registro_pontos_portal as rp')
            ->join('funcionario_jornadas as fj', function ($join): void {
                $join->on('fj.funcionario_id', '=', 'rp.funcionario_id')
                    ->whereRaw('fj.data_inicio <= rp.data_referencia')
                    ->whereRaw('(fj.data_fim IS NULL OR fj.data_fim >= rp.data_referencia)');
            })
            ->join('jornadas as j', 'j.id', '=', 'fj.jornada_id')
            ->whereIn('rp.funcionario_id', $funcionarioIds)
            ->whereBetween('rp.data_referencia', [$inicio->toDateString(), $fim->toDateString()])
            ->whereNotNull('rp.entrada_em')
            ->whereRaw('TIME(rp.entrada_em) > ADDTIME(COALESCE(j.hora_entrada_padrao, j.hora_inicio), SEC_TO_TIME(COALESCE(j.tolerancia_entrada_min, 0) * 60))')
            ->groupBy('rp.funcionario_id')
            ->pluck(DB::raw('COUNT(DISTINCT rp.id) as total_atrasos'), 'rp.funcionario_id');

        $afastamentosPorColaborador = DB::table('afastamentos as af')
            ->whereIn('af.funcionario_id', $funcionarioIds)
            ->whereBetween('af.data_inicio', [$inicio->toDateString(), $fim->toDateString()])
            ->groupBy('af.funcionario_id')
            ->get([
                'af.funcionario_id',
                DB::raw("SUM(CASE WHEN LOWER(af.tipo) LIKE '%falta%' THEN 1 ELSE 0 END) as total_faltas"),
                DB::raw("SUM(CASE WHEN LOWER(af.tipo) LIKE '%atest%' THEN 1 ELSE 0 END) as total_atestados"),
            ])
            ->keyBy('funcionario_id');

        $ajustesPorColaborador = DB::table('rh_ajustes_ponto as rap')
            ->whereIn('rap.funcionario_id', $funcionarioIds)
            ->whereBetween(DB::raw('DATE(rap.ajustado_em)'), [$inicio->toDateString(), $fim->toDateString()])
            ->groupBy('rap.funcionario_id')
            ->get([
                'rap.funcionario_id',
                DB::raw('SUM(CASE WHEN rap.minutos_ajuste > 0 THEN rap.minutos_ajuste ELSE 0 END) as minutos_extras'),
                DB::raw('SUM(rap.minutos_ajuste) as saldo_minutos'),
            ])
            ->keyBy('funcionario_id');

        $ranking = $funcionarios->map(function ($funcionario) use ($atrasosPorColaborador, $afastamentosPorColaborador, $ajustesPorColaborador) {
            $atrasos = (int) ($atrasosPorColaborador[$funcionario->id] ?? 0);
            $faltas = (int) optional($afastamentosPorColaborador->get($funcionario->id))->total_faltas;
            $atestados = (int) optional($afastamentosPorColaborador->get($funcionario->id))->total_atestados;
            $minutosExtras = (int) optional($ajustesPorColaborador->get($funcionario->id))->minutos_extras;
            $saldoMinutos = (int) optional($ajustesPorColaborador->get($funcionario->id))->saldo_minutos;

            return [
                'funcionario_id' => $funcionario->id,
                'colaborador_nome' => $funcionario->nome,
                'atrasos' => $atrasos,
                'faltas' => $faltas,
                'atestados' => $atestados,
                'horas_extras' => $this->f($minutosExtras / 60),
                'saldo_banco_horas' => $this->f($saldoMinutos / 60),
            ];
        })->sortByDesc(fn ($item) => ($item['atrasos'] * 3) + ($item['faltas'] * 2) + $item['atestados'])
            ->values()
            ->all();

        $totalAtrasos = (int) collect($ranking)->sum('atrasos');
        $totalFaltas = (int) collect($ranking)->sum('faltas');
        $totalAtestados = (int) collect($ranking)->sum('atestados');
        $horasExtras = $this->f(collect($ranking)->sum('horas_extras'));
        $saldoBancoHoras = $this->f(collect($ranking)->sum('saldo_banco_horas'));
        $indiceAbsenteismo = $this->f($this->percentual($totalFaltas + $totalAtestados, max(1, count($funcionarioIds))));

        $insights = [];

        $comMaisAtrasos = collect($ranking)->first(fn ($item) => $item['atrasos'] > 3);
        if ($comMaisAtrasos) {
            $insights[] = 'Colaborador '.$comMaisAtrasos['colaborador_nome'].' com mais de 3 atrasos no período.';
        }

        $diasNegativos = DB::table('rh_ajustes_ponto as rap')
            ->whereIn('rap.funcionario_id', $funcionarioIds)
            ->whereBetween(DB::raw('DATE(rap.ajustado_em)'), [$inicio->toDateString(), $fim->toDateString()])
            ->where('rap.minutos_ajuste', '<', 0)
            ->count();

        if ($saldoBancoHoras < 0 && $diasNegativos >= 3) {
            $insights[] = 'Banco de horas negativo recorrente identificado.';
        }

        if ($indiceAbsenteismo > 10) {
            $insights[] = 'Alto índice de absenteísmo no período.';
        }

        return [
            'periodo' => [
                'data_inicio' => $inicio->toDateString(),
                'data_fim' => $fim->toDateString(),
            ],
            'total_atrasos' => $totalAtrasos,
            'total_faltas' => $totalFaltas,
            'total_atestados' => $totalAtestados,
            'horas_extras' => $horasExtras,
            'saldo_banco_horas_consolidado' => $saldoBancoHoras,
            'indice_absenteismo' => $indiceAbsenteismo,
            'ranking_por_colaborador' => $ranking,
            'insights_automaticos' => $insights,
        ];
    }
}
