<?php

namespace App\Services\Relatorios;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RelatorioTecnicoService extends BaseRelatorioService
{
    public function gerar(array $filtros): array
    {
        [$inicio, $fim] = $this->periodo($filtros);

        $empresaId = (int) $filtros['empresa_id'];
        $centroCustoId = $filtros['centro_custo_id'] ?? null;
        $statusFinalizados = ['concluido', 'finalizado'];

        $baseAtendimentos = DB::table('atendimentos as a')
            ->where('a.empresa_id', $empresaId)
            ->whereBetween(DB::raw('DATE(COALESCE(a.data_atendimento, a.data_inicio_agendamento, a.created_at))'), [$inicio->toDateString(), $fim->toDateString()]);

        if ($centroCustoId) {
            $baseAtendimentos->whereExists(function ($query) use ($centroCustoId): void {
                $query->selectRaw('1')
                    ->from('orcamentos as o')
                    ->whereColumn('o.atendimento_id', 'a.id')
                    ->where('o.centro_custo_id', $centroCustoId);
            });
        }

        $totalChamados = (int) (clone $baseAtendimentos)->count();

        $finalizados = (int) (clone $baseAtendimentos)
            ->whereIn(DB::raw('LOWER(a.status_atual)'), $statusFinalizados)
            ->count();

        $cancelados = (int) (clone $baseAtendimentos)
            ->whereRaw('LOWER(a.status_atual) LIKE ?', ['cancel%'])
            ->count();

        $abertos = max(0, $totalChamados - $finalizados - $cancelados);

        $tempoMedioAtendimentoMin = $this->f(
            (clone $baseAtendimentos)
                ->whereIn(DB::raw('LOWER(a.status_atual)'), $statusFinalizados)
                ->selectRaw('AVG(COALESCE(a.tempo_execucao_segundos, TIMESTAMPDIFF(SECOND, a.iniciado_em, a.finalizado_em), 0)) / 60 as media_min')
                ->value('media_min')
        );

        $quantidadePorTecnico = (clone $baseAtendimentos)
            ->leftJoin('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
            ->groupBy('a.funcionario_id', 'f.nome')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                'a.funcionario_id as tecnico_id',
                DB::raw("COALESCE(f.nome, 'Não atribuído') as tecnico_nome"),
                DB::raw('COUNT(*) as quantidade'),
            ])
            ->all();

        $receitaPorTecnico = DB::table('orcamentos as o')
            ->join('atendimentos as a', 'a.id', '=', 'o.atendimento_id')
            ->leftJoin('funcionarios as f', 'f.id', '=', 'a.funcionario_id')
            ->where('a.empresa_id', $empresaId)
            ->when($centroCustoId, fn ($q) => $q->where('o.centro_custo_id', $centroCustoId))
            ->whereIn(DB::raw('LOWER(o.status)'), ['aprovado', 'financeiro', 'aguardando_pagamento', 'em_andamento', 'concluido', 'garantia'])
            ->whereBetween(DB::raw('DATE(COALESCE(o.data_aprovacao, o.created_at))'), [$inicio->toDateString(), $fim->toDateString()])
            ->groupBy('a.funcionario_id', 'f.nome')
            ->orderByDesc(DB::raw('SUM(o.valor_total)'))
            ->get([
                'a.funcionario_id as tecnico_id',
                DB::raw("COALESCE(f.nome, 'Não atribuído') as tecnico_nome"),
                DB::raw('SUM(o.valor_total) as receita'),
            ])
            ->map(function ($item) {
                $item->receita = $this->f($item->receita);

                return $item;
            })
            ->all();

        $chamadosVencidos = (int) DB::table('atendimentos as a')
            ->where('a.empresa_id', $empresaId)
            ->when($centroCustoId, function ($q) use ($centroCustoId): void {
                $q->whereExists(function ($sub) use ($centroCustoId): void {
                    $sub->selectRaw('1')
                        ->from('orcamentos as o')
                        ->whereColumn('o.atendimento_id', 'a.id')
                        ->where('o.centro_custo_id', $centroCustoId);
                });
            })
            ->whereRaw('DATE(COALESCE(a.data_fim_agendamento, a.data_inicio_agendamento, a.data_atendimento, a.created_at)) < CURDATE()')
            ->whereNotIn(DB::raw('LOWER(a.status_atual)'), $statusFinalizados)
            ->whereRaw('LOWER(a.status_atual) NOT LIKE ?', ['cancel%'])
            ->count();

        $insights = [];

        $mediaProdutividade = collect($quantidadePorTecnico)->avg('quantidade') ?: 0;
        $tecnicoBaixaProdutividade = collect($quantidadePorTecnico)
            ->first(fn ($item) => $mediaProdutividade > 0 && (int) $item->quantidade < ($mediaProdutividade * 0.7));

        if ($tecnicoBaixaProdutividade) {
            $insights[] = 'Técnico '.$tecnicoBaixaProdutividade->tecnico_nome.' com produtividade 30% abaixo da média.';
        }

        if ($totalChamados > 0 && ($abertos / $totalChamados) > 0.4) {
            $insights[] = 'Alto número de chamados em aberto no período.';
        }

        $tempoIdeal = 120.0;
        if (Schema::hasTable('settings')) {
            $tempoIdeal = (float) (DB::table('settings')
                ->whereIn('key', ['tecnico_tempo_medio_ideal_minutos', 'tempo_medio_ideal_atendimento_minutos'])
                ->value('value') ?? 120);
        }

        if ($tempoMedioAtendimentoMin > $tempoIdeal) {
            $insights[] = 'Tempo médio de atendimento acima do ideal definido no sistema.';
        }

        return [
            'periodo' => [
                'data_inicio' => $inicio->toDateString(),
                'data_fim' => $fim->toDateString(),
            ],
            'total_chamados' => $totalChamados,
            'finalizados' => $finalizados,
            'abertos' => $abertos,
            'cancelados' => $cancelados,
            'tempo_medio_atendimento_minutos' => $tempoMedioAtendimentoMin,
            'quantidade_por_tecnico' => $quantidadePorTecnico,
            'receita_por_tecnico' => $receitaPorTecnico,
            'chamados_vencidos' => $chamadosVencidos,
            'insights_automaticos' => $insights,
        ];
    }
}
