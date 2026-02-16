<?php

namespace App\Services\Relatorio;

use App\Models\Cobranca;
use App\Models\ContaPagar;
use Illuminate\Support\Facades\DB;

class RelatorioFinanceiroService
{
    public function getResumoFinanceiro(array $filtros): array
    {
        $dataInicio = $filtros['data_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $dataFim = $filtros['data_fim'] ?? now()->endOfMonth()->format('Y-m-d');
        $empresaId = $filtros['empresa_id'] ?? null;

        $queryCobranca = Cobranca::query();
        $queryContaPagar = ContaPagar::query();

        if ($empresaId) {
            $queryCobranca->whereHas('orcamento', fn ($q) => $q->where('empresa_id', $empresaId));
            $queryContaPagar->where('empresa_id', $empresaId);
        }

        $receitas = (clone $queryCobranca)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$dataInicio, $dataFim])
            ->sum('valor_pago');

        $receitasPendentes = (clone $queryCobranca)
            ->where('status', 'pendente')
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->sum('valor');

        $despesas = (clone $queryContaPagar)
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$dataInicio, $dataFim])
            ->sum('valor_pago');

        $despesasPendentes = (clone $queryContaPagar)
            ->where('status', 'pendente')
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim])
            ->sum('valor');

        return [
            'periodo' => [
                'inicio' => $dataInicio,
                'fim' => $dataFim,
            ],
            'receitas' => [
                'recebidas' => $receitas,
                'pendentes' => $receitasPendentes,
                'total_esperado' => $receitas + $receitasPendentes,
            ],
            'despesas' => [
                'pagas' => $despesas,
                'pendentes' => $despesasPendentes,
                'total_esperado' => $despesas + $despesasPendentes,
            ],
            'saldo' => [
                'atual' => $receitas - $despesas,
                'esperado' => ($receitas + $receitasPendentes) - ($despesas + $despesasPendentes),
            ],
        ];
    }

    public function getReceitasPorCategoria(array $filtros): array
    {
        $dataInicio = $filtros['data_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $dataFim = $filtros['data_fim'] ?? now()->endOfMonth()->format('Y-m-d');
        $empresaId = $filtros['empresa_id'] ?? null;

        $query = DB::table('movimentacoes_financeiras')
            ->select('contas.categoria_id', 'categorias.nome as categoria_nome', DB::raw('SUM(valor) as total'))
            ->leftJoin('contas', 'movimentacoes_financeiras.conta_id', '=', 'contas.id')
            ->leftJoin('categorias', 'contas.categoria_id', '=', 'categorias.id')
            ->where('movimentacoes_financeiras.tipo', 'entrada')
            ->whereBetween('movimentacoes_financeiras.data_movimentacao', [$dataInicio, $dataFim]);

        if ($empresaId) {
            $query->where('movimentacoes_financeiras.empresa_id', $empresaId);
        }

        return $query->groupBy('contas.categoria_id', 'categorias.nome')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    public function getDespesasPorCentroCusto(array $filtros): array
    {
        $dataInicio = $filtros['data_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $dataFim = $filtros['data_fim'] ?? now()->endOfMonth()->format('Y-m-d');
        $empresaId = $filtros['empresa_id'] ?? null;

        $query = ContaPagar::select(
            'centro_custos.id',
            'centro_custos.nome',
            DB::raw('SUM(valor) as total'),
            DB::raw('COUNT(*) as quantidade')
        )
            ->leftJoin('centro_custos', 'contas_pagar.centro_custo_id', '=', 'centro_custos.id')
            ->whereBetween('contas_pagar.data_vencimento', [$dataInicio, $dataFim]);

        if ($empresaId) {
            $query->where('contas_pagar.empresa_id', $empresaId);
        }

        return $query->groupBy('centro_custos.id', 'centro_custos.nome')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    public function getFluxoCaixaDiario(array $filtros): array
    {
        $dataInicio = $filtros['data_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $dataFim = $filtros['data_fim'] ?? now()->endOfMonth()->format('Y-m-d');
        $empresaId = $filtros['empresa_id'] ?? null;

        $entradas = DB::table('movimentacoes_financeiras')
            ->select(DB::raw('DATE(data_movimentacao) as data'), DB::raw('SUM(valor) as total'))
            ->where('tipo', 'entrada')
            ->whereBetween('data_movimentacao', [$dataInicio, $dataFim]);

        if ($empresaId) {
            $entradas->where('empresa_id', $empresaId);
        }

        $entradas = $entradas->groupBy(DB::raw('DATE(data_movimentacao)'))
            ->pluck('total', 'data')
            ->toArray();

        $saidas = DB::table('movimentacoes_financeiras')
            ->select(DB::raw('DATE(data_movimentacao) as data'), DB::raw('SUM(valor) as total'))
            ->where('tipo', 'saida')
            ->whereBetween('data_movimentacao', [$dataInicio, $dataFim]);

        if ($empresaId) {
            $saidas->where('empresa_id', $empresaId);
        }

        $saidas = $saidas->groupBy(DB::raw('DATE(data_movimentacao)'))
            ->pluck('total', 'data')
            ->toArray();

        $datas = [];
        $periodo = new \DatePeriod(
            new \DateTime($dataInicio),
            new \DateInterval('P1D'),
            (new \DateTime($dataFim))->modify('+1 day')
        );

        foreach ($periodo as $dt) {
            $dataStr = $dt->format('Y-m-d');
            $datas[] = [
                'data' => $dataStr,
                'entradas' => $entradas[$dataStr] ?? 0,
                'saidas' => $saidas[$dataStr] ?? 0,
                'saldo' => ($entradas[$dataStr] ?? 0) - ($saidas[$dataStr] ?? 0),
            ];
        }

        return $datas;
    }
}
