<?php

namespace App\Services\Relatorios;

use Illuminate\Support\Facades\DB;

class RelatorioFinanceiroService extends BaseRelatorioService
{
    public function gerar(array $filtros): array
    {
        [$inicio, $fim] = $this->periodo($filtros);
        [$inicioAnterior, $fimAnterior] = $this->periodoAnterior($inicio, $fim);

        $empresaId = (int) $filtros['empresa_id'];
        $centroCustoId = $filtros['centro_custo_id'] ?? null;

        $receitaTotal = $this->receitaNoPeriodo($empresaId, $inicio->toDateString(), $fim->toDateString(), $centroCustoId);
        $despesaTotal = $this->despesaNoPeriodo($empresaId, $inicio->toDateString(), $fim->toDateString(), $centroCustoId);

        $lucroLiquido = $receitaTotal - $despesaTotal;
        $margem = $this->percentual($lucroLiquido, $receitaTotal);

        $receitaAnterior = $this->receitaNoPeriodo($empresaId, $inicioAnterior->toDateString(), $fimAnterior->toDateString(), $centroCustoId);
        $despesaAnterior = $this->despesaNoPeriodo($empresaId, $inicioAnterior->toDateString(), $fimAnterior->toDateString(), $centroCustoId);

        $receitaPorCentro = $this->receitaPorCentroCusto($empresaId, $inicio->toDateString(), $fim->toDateString(), $centroCustoId);
        $despesaPorCentro = $this->despesaPorCentroCusto($empresaId, $inicio->toDateString(), $fim->toDateString(), $centroCustoId);

        $saldoBancario = $this->f(
            DB::table('contas_financeiras')
                ->where('empresa_id', $empresaId)
                ->sum('saldo')
        );

        $contasReceberEmAberto = $this->f(
            DB::table('cobrancas as c')
                ->leftJoin('orcamentos as o', 'o.id', '=', 'c.orcamento_id')
                ->leftJoin('contas_fixas as cf', 'cf.id', '=', 'c.conta_fixa_id')
                ->where(function ($query) use ($empresaId): void {
                    $query->where('o.empresa_id', $empresaId)
                        ->orWhere('cf.empresa_id', $empresaId);
                })
                ->whereRaw("LOWER(COALESCE(c.status, '')) <> ?", ['pago'])
                ->when($centroCustoId, fn ($q) => $q->where('o.centro_custo_id', $centroCustoId))
                ->whereBetween('c.data_vencimento', [$inicio->toDateString(), $fim->toDateString()])
                ->sum('c.valor')
        );

        $contasPagarEmAberto = $this->f(
            DB::table('contas_pagar as cp')
                ->leftJoin('orcamentos as o', 'o.id', '=', 'cp.orcamento_id')
                ->leftJoin('contas_financeiras as cf', 'cf.id', '=', 'cp.conta_financeira_id')
                ->leftJoin('centros_custo as cc', 'cc.id', '=', 'cp.centro_custo_id')
                ->where(function ($query) use ($empresaId): void {
                    $query->where('o.empresa_id', $empresaId)
                        ->orWhere('cf.empresa_id', $empresaId)
                        ->orWhere('cc.empresa_id', $empresaId);
                })
                ->whereRaw("LOWER(COALESCE(cp.status, '')) <> ?", ['pago'])
                ->when($centroCustoId, fn ($q) => $q->where('cp.centro_custo_id', $centroCustoId))
                ->whereBetween('cp.data_vencimento', [$inicio->toDateString(), $fim->toDateString()])
                ->sum('cp.valor')
        );

        $insights = [];

        if ($margem < 20) {
            $insights[] = 'Margem abaixo de 20% no período.';
        }

        $crescimentoDespesa = $this->percentual($despesaTotal - $despesaAnterior, max(0.01, $despesaAnterior));
        if ($despesaAnterior > 0 && $crescimentoDespesa > 15) {
            $insights[] = 'Despesas cresceram mais de 15% versus período anterior equivalente.';
        }

        $mapaReceita = collect($receitaPorCentro)->keyBy('centro_custo_id');
        $mapaDespesa = collect($despesaPorCentro)->keyBy('centro_custo_id');
        $centrosComPrejuizo = $mapaDespesa
            ->filter(function ($despesaItem, $centroId) use ($mapaReceita) {
                $receitaCentro = (float) optional($mapaReceita->get($centroId))->total;
                $despesaCentro = (float) $despesaItem->total;

                return $despesaCentro > $receitaCentro;
            })
            ->values()
            ->map(fn ($item) => $item->centro_custo_nome)
            ->all();

        if (! empty($centrosComPrejuizo)) {
            $insights[] = 'Centro(s) de custo com prejuízo: '.implode(', ', $centrosComPrejuizo).'.';
        }

        $lucroAnterior = $receitaAnterior - $despesaAnterior;

        return [
            'periodo' => [
                'data_inicio' => $inicio->toDateString(),
                'data_fim' => $fim->toDateString(),
            ],
            'periodo_anterior' => [
                'data_inicio' => $inicioAnterior->toDateString(),
                'data_fim' => $fimAnterior->toDateString(),
            ],
            'receita_total' => $this->f($receitaTotal),
            'despesa_total' => $this->f($despesaTotal),
            'lucro_liquido' => $this->f($lucroLiquido),
            'margem_percentual' => $this->f($margem),
            'receita_total_anterior' => $this->f($receitaAnterior),
            'despesa_total_anterior' => $this->f($despesaAnterior),
            'lucro_liquido_anterior' => $this->f($lucroAnterior),
            'receita_por_centro_custo' => $receitaPorCentro,
            'despesa_por_centro_custo' => $despesaPorCentro,
            'saldo_bancario_total' => $saldoBancario,
            'contas_receber_em_aberto' => $contasReceberEmAberto,
            'contas_pagar_em_aberto' => $contasPagarEmAberto,
            'insights_automaticos' => $insights,
        ];
    }

    private function receitaNoPeriodo(int $empresaId, string $dataInicio, string $dataFim, ?int $centroCustoId = null): float
    {
        return $this->f(
            DB::table('cobrancas as c')
                ->leftJoin('orcamentos as o', 'o.id', '=', 'c.orcamento_id')
                ->leftJoin('contas_fixas as cf', 'cf.id', '=', 'c.conta_fixa_id')
                ->where(function ($query) use ($empresaId): void {
                    $query->where('o.empresa_id', $empresaId)
                        ->orWhere('cf.empresa_id', $empresaId);
                })
                ->whereRaw("LOWER(COALESCE(c.status, '')) = ?", ['pago'])
                ->when($centroCustoId, fn ($q) => $q->where('o.centro_custo_id', $centroCustoId))
                ->whereBetween(DB::raw('DATE(COALESCE(c.data_pagamento, c.pago_em))'), [$dataInicio, $dataFim])
                ->sum('c.valor')
        );
    }

    private function despesaNoPeriodo(int $empresaId, string $dataInicio, string $dataFim, ?int $centroCustoId = null): float
    {
        return $this->f(
            DB::table('contas_pagar as cp')
                ->leftJoin('orcamentos as o', 'o.id', '=', 'cp.orcamento_id')
                ->leftJoin('contas_financeiras as cf', 'cf.id', '=', 'cp.conta_financeira_id')
                ->leftJoin('centros_custo as cc', 'cc.id', '=', 'cp.centro_custo_id')
                ->where(function ($query) use ($empresaId): void {
                    $query->where('o.empresa_id', $empresaId)
                        ->orWhere('cf.empresa_id', $empresaId)
                        ->orWhere('cc.empresa_id', $empresaId);
                })
                ->whereRaw("LOWER(COALESCE(cp.status, '')) = ?", ['pago'])
                ->when($centroCustoId, fn ($q) => $q->where('cp.centro_custo_id', $centroCustoId))
                ->whereBetween(DB::raw('DATE(COALESCE(cp.data_pagamento, cp.pago_em))'), [$dataInicio, $dataFim])
                ->sum('cp.valor')
        );
    }

    private function receitaPorCentroCusto(int $empresaId, string $dataInicio, string $dataFim, ?int $centroCustoId = null): array
    {
        return DB::table('cobrancas as c')
            ->join('orcamentos as o', 'o.id', '=', 'c.orcamento_id')
            ->leftJoin('centros_custo as cc', 'cc.id', '=', 'o.centro_custo_id')
            ->where('o.empresa_id', $empresaId)
            ->whereRaw("LOWER(COALESCE(c.status, '')) = ?", ['pago'])
            ->when($centroCustoId, fn ($q) => $q->where('o.centro_custo_id', $centroCustoId))
            ->whereBetween(DB::raw('DATE(COALESCE(c.data_pagamento, c.pago_em))'), [$dataInicio, $dataFim])
            ->groupBy('o.centro_custo_id', 'cc.nome')
            ->orderByDesc(DB::raw('SUM(c.valor)'))
            ->get([
                'o.centro_custo_id',
                DB::raw("COALESCE(cc.nome, 'Sem centro de custo') as centro_custo_nome"),
                DB::raw('SUM(c.valor) as total'),
            ])
            ->map(function ($item) {
                $item->total = $this->f($item->total);

                return $item;
            })
            ->all();
    }

    private function despesaPorCentroCusto(int $empresaId, string $dataInicio, string $dataFim, ?int $centroCustoId = null): array
    {
        return DB::table('contas_pagar as cp')
            ->leftJoin('centros_custo as cc', 'cc.id', '=', 'cp.centro_custo_id')
            ->leftJoin('orcamentos as o', 'o.id', '=', 'cp.orcamento_id')
            ->leftJoin('contas_financeiras as cf', 'cf.id', '=', 'cp.conta_financeira_id')
            ->where(function ($query) use ($empresaId): void {
                $query->where('o.empresa_id', $empresaId)
                    ->orWhere('cf.empresa_id', $empresaId)
                    ->orWhere('cc.empresa_id', $empresaId);
            })
            ->whereRaw("LOWER(COALESCE(cp.status, '')) = ?", ['pago'])
            ->when($centroCustoId, fn ($q) => $q->where('cp.centro_custo_id', $centroCustoId))
            ->whereBetween(DB::raw('DATE(COALESCE(cp.data_pagamento, cp.pago_em))'), [$dataInicio, $dataFim])
            ->groupBy('cp.centro_custo_id', 'cc.nome')
            ->orderByDesc(DB::raw('SUM(cp.valor)'))
            ->get([
                'cp.centro_custo_id',
                DB::raw("COALESCE(cc.nome, 'Sem centro de custo') as centro_custo_nome"),
                DB::raw('SUM(cp.valor) as total'),
            ])
            ->map(function ($item) {
                $item->total = $this->f($item->total);

                return $item;
            })
            ->all();
    }
}
