<?php

namespace App\Services\Relatorios;

use Illuminate\Support\Facades\DB;

class RelatorioComercialService extends BaseRelatorioService
{
    public function gerar(array $filtros): array
    {
        [$inicio, $fim] = $this->periodo($filtros);
        [$inicioAnterior, $fimAnterior] = $this->periodoAnterior($inicio, $fim);

        $empresaId = (int) $filtros['empresa_id'];
        $centroCustoId = $filtros['centro_custo_id'] ?? null;

        $statusFechados = ['aprovado', 'financeiro', 'aguardando_pagamento', 'em_andamento', 'concluido', 'garantia'];
        $statusPerdidos = ['reprovado', 'perdido', 'cancelado'];

        $base = DB::table('orcamentos as o')
            ->where('o.empresa_id', $empresaId)
            ->when($centroCustoId, fn ($q) => $q->where('o.centro_custo_id', $centroCustoId))
            ->whereBetween(DB::raw('DATE(o.created_at)'), [$inicio->toDateString(), $fim->toDateString()]);

        $totalOrcamentos = (int) (clone $base)->count();

        $fechados = (int) (clone $base)
            ->whereIn(DB::raw('LOWER(o.status)'), $statusFechados)
            ->count();

        $perdidos = (int) (clone $base)
            ->whereIn(DB::raw('LOWER(o.status)'), $statusPerdidos)
            ->count();

        $emAberto = max(0, $totalOrcamentos - $fechados - $perdidos);

        $receitaFechada = $this->f(
            (clone $base)
                ->whereIn(DB::raw('LOWER(o.status)'), $statusFechados)
                ->sum('o.valor_total')
        );

        $taxaConversao = $this->percentual($fechados, max(1, $totalOrcamentos));
        $ticketMedio = $this->f($fechados > 0 ? ($receitaFechada / $fechados) : 0);

        $tempoMedioFechamento = $this->f(
            (clone $base)
                ->whereIn(DB::raw('LOWER(o.status)'), $statusFechados)
                ->whereNotNull('o.data_aprovacao')
                ->selectRaw('AVG(TIMESTAMPDIFF(DAY, o.created_at, o.data_aprovacao)) as media_dias')
                ->value('media_dias')
        );

        $ticketMedioAnterior = $this->f(
            DB::table('orcamentos as o')
                ->where('o.empresa_id', $empresaId)
                ->when($centroCustoId, fn ($q) => $q->where('o.centro_custo_id', $centroCustoId))
                ->whereIn(DB::raw('LOWER(o.status)'), $statusFechados)
                ->whereBetween(DB::raw('DATE(o.created_at)'), [$inicioAnterior->toDateString(), $fimAnterior->toDateString()])
                ->avg('o.valor_total')
        );

        $performanceVendedores = (clone $base)
            ->leftJoin('users as u', 'u.id', '=', 'o.created_by')
            ->groupBy('o.created_by', 'u.name')
            ->orderByDesc(DB::raw('SUM(CASE WHEN LOWER(o.status) IN (\'aprovado\',\'financeiro\',\'aguardando_pagamento\',\'em_andamento\',\'concluido\',\'garantia\') THEN o.valor_total ELSE 0 END)'))
            ->get([
                'o.created_by as vendedor_id',
                DB::raw("COALESCE(u.name, 'Sem vendedor') as vendedor_nome"),
                DB::raw('COUNT(*) as total_orcamentos'),
                DB::raw("SUM(CASE WHEN LOWER(o.status) IN ('aprovado','financeiro','aguardando_pagamento','em_andamento','concluido','garantia') THEN 1 ELSE 0 END) as fechados"),
                DB::raw("SUM(CASE WHEN LOWER(o.status) IN ('aprovado','financeiro','aguardando_pagamento','em_andamento','concluido','garantia') THEN o.valor_total ELSE 0 END) as receita_fechada"),
            ])
            ->map(function ($item) {
                $item->taxa_conversao = $this->percentual((int) $item->fechados, max(1, (int) $item->total_orcamentos));
                $item->receita_fechada = $this->f($item->receita_fechada);

                return $item;
            })
            ->all();

        $insights = [];

        if ($taxaConversao < 30) {
            $insights[] = 'Taxa de conversão abaixo de 30%.';
        }

        if ($ticketMedioAnterior > 0 && $ticketMedio < $ticketMedioAnterior) {
            $insights[] = 'Ticket médio caiu em relação ao período anterior equivalente.';
        }

        $mediaReceitaVendedores = collect($performanceVendedores)->avg('receita_fechada') ?: 0;
        $vendedorBaixaPerformance = collect($performanceVendedores)
            ->first(fn ($item) => $mediaReceitaVendedores > 0 && (float) $item->receita_fechada < ($mediaReceitaVendedores * 0.7));

        if ($vendedorBaixaPerformance) {
            $insights[] = 'Vendedor '.$vendedorBaixaPerformance->vendedor_nome.' com baixa performance.';
        }

        return [
            'periodo' => [
                'data_inicio' => $inicio->toDateString(),
                'data_fim' => $fim->toDateString(),
            ],
            'total_orcamentos_criados' => $totalOrcamentos,
            'fechados' => $fechados,
            'perdidos' => $perdidos,
            'em_aberto' => $emAberto,
            'taxa_conversao' => $this->f($taxaConversao),
            'receita_fechada' => $receitaFechada,
            'ticket_medio' => $ticketMedio,
            'tempo_medio_ate_fechamento_dias' => $tempoMedioFechamento,
            'performance_vendedores' => $performanceVendedores,
            'insights_automaticos' => $insights,
        ];
    }
}
