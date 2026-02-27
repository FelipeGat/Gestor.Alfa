<?php

namespace App\Services\Relatorio;

use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Orcamento;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\DB;

class RelatorioComercialService
{
    public function gerar(array $filtros): array
    {
        $pipeline = $this->pipelineComercial($filtros);

        return [
            'pipeline' => $pipeline,
            'enviados_fechados' => $this->enviadosFechados($filtros),
            'follow_up' => $this->followUp($filtros),
            'receita_prevista_real' => $this->receitaPrevistaReal($filtros),
            'origem_leads' => $this->origemLeads($filtros),
            'ticket_por_tipo' => $this->ticketMedioPorTipo($filtros),
            'performance_vendedor' => $this->performancePorVendedor($filtros),
            'clientes_ativos_inativos' => $this->clientesAtivosInativos($filtros),
            'lucratividade_servico' => $this->lucratividadePorServico($filtros),
            'kpis' => [
                'taxa_conversao' => $pipeline['taxa_conversao'],
                'ticket_medio' => $pipeline['ticket_medio'],
                'tempo_medio_fechamento_dias' => $pipeline['tempo_medio_fechamento_dias'],
            ],
        ];
    }

    public function pipelineComercial(array $filtros): array
    {
        $query = $this->baseOrcamentos($filtros);

        $porStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as quantidade'), DB::raw('SUM(valor_total) as valor_total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $enviados = (clone $query)
            ->whereNotNull('data_envio')
            ->count();

        $fechados = (clone $query)
            ->whereIn('status', $this->statusFechados())
            ->count();

        $ticketMedio = (clone $query)
            ->whereIn('status', $this->statusFechados())
            ->avg('valor_total') ?? 0;

        $tempoMedio = (clone $query)
            ->whereNotNull('data_aprovacao')
            ->selectRaw($this->avgDiffDaysExpression('created_at', 'data_aprovacao').' as tempo_medio')
            ->value('tempo_medio');

        return [
            'por_status' => $porStatus,
            'taxa_conversao' => $enviados > 0 ? round(($fechados / $enviados) * 100, 2) : 0,
            'ticket_medio' => (float) $ticketMedio,
            'tempo_medio_fechamento_dias' => round((float) ($tempoMedio ?? 0), 2),
        ];
    }

    public function enviadosFechados(array $filtros): array
    {
        $query = $this->baseOrcamentos($filtros);

        $enviados = (clone $query)->whereNotNull('data_envio');
        $fechados = (clone $query)->whereIn('status', $this->statusFechados());

        $qtdEnviados = (clone $enviados)->count();
        $qtdFechados = (clone $fechados)->count();

        return [
            'quantidade_enviada' => $qtdEnviados,
            'quantidade_fechada' => $qtdFechados,
            'percentual_conversao' => $qtdEnviados > 0 ? round(($qtdFechados / $qtdEnviados) * 100, 2) : 0,
            'valor_total_enviado' => (float) ((clone $enviados)->sum('valor_total')),
            'valor_total_fechado' => (float) ((clone $fechados)->sum('valor_total')),
        ];
    }

    public function followUp(array $filtros): array
    {
        $base = $this->baseOrcamentos($filtros)
            ->whereIn('status', ['em_elaboracao', 'aguardando_aprovacao'])
            ->where(function (Builder $query): void {
                $query->whereNull('data_aprovacao')
                    ->whereNull('data_envio')
                    ->orWhereNotNull('data_envio');
            });

        $agora = now();

        $mais3 = (clone $base)->where('updated_at', '<', $agora->copy()->subDays(3))->count();
        $mais7 = (clone $base)->where('updated_at', '<', $agora->copy()->subDays(7))->count();
        $mais15 = (clone $base)->where('updated_at', '<', $agora->copy()->subDays(15))->count();

        $lista = (clone $base)
            ->with(['cliente:id,nome,nome_fantasia,razao_social', 'preCliente:id,nome_fantasia,razao_social', 'empresa:id,nome_fantasia', 'vendedor:id,name'])
            ->orderBy('updated_at')
            ->paginate(15)
            ->withQueryString();

        return [
            'mais_3_dias' => $mais3,
            'mais_7_dias' => $mais7,
            'mais_15_dias' => $mais15,
            'lista' => $lista,
        ];
    }

    public function receitaPrevistaReal(array $filtros): array
    {
        $query = $this->baseOrcamentos($filtros);

        $contratosAprovados = (clone $query)
            ->whereIn('status', ['aprovado', 'financeiro', 'aguardando_pagamento', 'em_andamento', 'concluido', 'garantia'])
            ->sum('valor_total');

        $negociacaoPonderada = (clone $query)
            ->whereIn('status', ['em_elaboracao', 'aguardando_aprovacao'])
            ->selectRaw('SUM(valor_total * (probabilidade_fechamento / 100)) as total')
            ->value('total') ?? 0;

        $receitaRealQuery = Cobranca::query()
            ->where('status', 'pago')
            ->whereBetween('pago_em', [$this->inicio($filtros)->copy()->startOfDay(), $this->fim($filtros)->copy()->endOfDay()]);

        if (! empty($filtros['empresa_id'])) {
            $receitaRealQuery->whereHas('orcamento', function (Builder $query) use ($filtros): void {
                $query->where('empresa_id', $filtros['empresa_id']);
            });
        }

        $receitaReal = (float) $receitaRealQuery->sum('valor');
        $prevista = (float) $contratosAprovados + (float) $negociacaoPonderada;

        return [
            'contratos_aprovados' => (float) $contratosAprovados,
            'negociacao_ponderada' => (float) $negociacaoPonderada,
            'receita_prevista' => $prevista,
            'receita_real' => $receitaReal,
            'diferenca_percentual' => $prevista > 0 ? round((($receitaReal - $prevista) / $prevista) * 100, 2) : 0,
        ];
    }

    public function origemLeads(array $filtros)
    {
        return $this->baseOrcamentos($filtros)
            ->selectRaw("COALESCE(NULLIF(origem_lead, ''), 'Nao informado') as origem")
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('SUM(valor_total) as valor_gerado')
            ->selectRaw("SUM(CASE WHEN status IN ('aprovado','financeiro','aguardando_pagamento','em_andamento','concluido','garantia') THEN 1 ELSE 0 END) as fechados")
            ->groupBy('origem')
            ->orderByDesc('quantidade')
            ->get()
            ->map(function ($item) {
                $item->taxa_conversao = $item->quantidade > 0
                    ? round(($item->fechados / $item->quantidade) * 100, 2)
                    : 0;

                return $item;
            });
    }

    public function ticketMedioPorTipo(array $filtros)
    {
        $query = DB::table('orcamento_itens as oi')
            ->join('orcamentos as o', 'o.id', '=', 'oi.orcamento_id')
            ->select('oi.tipo')
            ->selectRaw('AVG(oi.subtotal) as ticket_medio')
            ->selectRaw('COUNT(DISTINCT o.id) as quantidade_vendas')
            ->selectRaw('SUM(oi.subtotal) as receita_total')
            ->whereBetween('o.created_at', [$this->inicio($filtros)->copy()->startOfDay(), $this->fim($filtros)->copy()->endOfDay()]);

        $this->aplicarFiltrosSql($query, $filtros);

        return $query->groupBy('oi.tipo')
            ->orderBy('oi.tipo')
            ->get();
    }

    public function performancePorVendedor(array $filtros)
    {
        $query = $this->baseOrcamentos($filtros)
            ->leftJoin('users', 'users.id', '=', 'orcamentos.vendedor_id')
            ->selectRaw("COALESCE(users.name, 'Sem vendedor') as vendedor")
            ->selectRaw('COUNT(*) as total_orcamentos')
            ->selectRaw("SUM(CASE WHEN orcamentos.status IN ('aprovado','financeiro','aguardando_pagamento','em_andamento','concluido','garantia') THEN 1 ELSE 0 END) as fechados")
            ->selectRaw("SUM(CASE WHEN orcamentos.status IN ('aprovado','financeiro','aguardando_pagamento','em_andamento','concluido','garantia') THEN orcamentos.valor_total ELSE 0 END) as valor_vendido")
            ->selectRaw("AVG(CASE WHEN orcamentos.status IN ('aprovado','financeiro','aguardando_pagamento','em_andamento','concluido','garantia') THEN orcamentos.valor_total ELSE NULL END) as ticket_medio")
            ->selectRaw('AVG(CASE WHEN orcamentos.data_aprovacao IS NOT NULL THEN '.$this->diffDaysExpression('orcamentos.created_at', 'orcamentos.data_aprovacao').' ELSE NULL END) as tempo_medio_fechamento')
            ->groupBy('users.name')
            ->orderByDesc('valor_vendido')
            ->get();

        return $query->map(function ($item) {
            $item->conversao = $item->total_orcamentos > 0
                ? round(($item->fechados / $item->total_orcamentos) * 100, 2)
                : 0;

            return $item;
        });
    }

    public function clientesAtivosInativos(array $filtros): array
    {
        $inicio12M = now()->subMonths(12)->startOfDay();

        $ativosIds = Orcamento::query()
            ->whereNotNull('cliente_id')
            ->whereIn('status', $this->statusFechados())
            ->whereBetween('created_at', [$inicio12M, now()->endOfDay()])
            ->when(! empty($filtros['empresa_id']), fn (Builder $query) => $query->where('empresa_id', $filtros['empresa_id']))
            ->distinct()
            ->pluck('cliente_id');

        $ativos = Cliente::query()
            ->whereIn('id', $ativosIds)
            ->count();

        $inativos = Cliente::query()
            ->when($ativosIds->isNotEmpty(), fn (Builder $query) => $query->whereNotIn('id', $ativosIds))
            ->count();

        $receitaAnualPorCliente = Orcamento::query()
            ->leftJoin('clientes', 'clientes.id', '=', 'orcamentos.cliente_id')
            ->whereNotNull('orcamentos.cliente_id')
            ->whereIn('orcamentos.status', $this->statusFechados())
            ->whereBetween('orcamentos.created_at', [$inicio12M, now()->endOfDay()])
            ->when(! empty($filtros['empresa_id']), fn ($query) => $query->where('orcamentos.empresa_id', $filtros['empresa_id']))
            ->selectRaw("COALESCE(clientes.nome_fantasia, clientes.razao_social, clientes.nome, 'Sem nome') as cliente")
            ->selectRaw('SUM(orcamentos.valor_total) as receita_anual')
            ->groupBy('cliente')
            ->orderByDesc('receita_anual')
            ->limit(30)
            ->get();

        return [
            'ativos' => $ativos,
            'inativos' => $inativos,
            'receita_anual_por_cliente' => $receitaAnualPorCliente,
        ];
    }

    public function lucratividadePorServico(array $filtros)
    {
        $query = DB::table('orcamento_itens as oi')
            ->join('orcamentos as o', 'o.id', '=', 'oi.orcamento_id')
            ->leftJoin('itens_comerciais as ic', 'ic.id', '=', 'oi.item_comercial_id')
            ->selectRaw("COALESCE(oi.nome, ic.nome, 'Sem nome') as servico")
            ->selectRaw('SUM(oi.subtotal) as valor_vendido')
            ->selectRaw('SUM(COALESCE(ic.preco_custo, 0) * oi.quantidade) as custo_estimado')
            ->whereBetween('o.created_at', [$this->inicio($filtros)->copy()->startOfDay(), $this->fim($filtros)->copy()->endOfDay()]);

        $this->aplicarFiltrosSql($query, $filtros);

        return $query->groupBy('servico')
            ->orderByDesc('valor_vendido')
            ->get()
            ->map(function ($item) {
                $item->margem_bruta = (float) $item->valor_vendido - (float) $item->custo_estimado;
                $item->margem_percentual = (float) $item->valor_vendido > 0
                    ? round(($item->margem_bruta / $item->valor_vendido) * 100, 2)
                    : 0;

                return $item;
            });
    }

    private function baseOrcamentos(array $filtros): Builder
    {
        $query = Orcamento::query()
            ->whereBetween('orcamentos.created_at', [$this->inicio($filtros)->copy()->startOfDay(), $this->fim($filtros)->copy()->endOfDay()]);

        if (! empty($filtros['empresa_id'])) {
            $query->where('orcamentos.empresa_id', $filtros['empresa_id']);
        }

        if (! empty($filtros['vendedor_id'])) {
            $query->where('orcamentos.vendedor_id', $filtros['vendedor_id']);
        }

        if (! empty($filtros['tipo_servico'])) {
            $tipo = $filtros['tipo_servico'];
            $query->whereHas('itens', function (Builder $itens) use ($tipo): void {
                $itens->where('tipo', $tipo);
            });
        }

        return $query;
    }

    private function aplicarFiltrosSql($query, array $filtros): void
    {
        if (! empty($filtros['empresa_id'])) {
            $query->where('o.empresa_id', $filtros['empresa_id']);
        }

        if (! empty($filtros['vendedor_id'])) {
            $query->where('o.vendedor_id', $filtros['vendedor_id']);
        }

        if (! empty($filtros['tipo_servico'])) {
            $query->where('oi.tipo', $filtros['tipo_servico']);
        }
    }

    private function inicio(array $filtros): Carbon
    {
        if (! empty($filtros['data_inicio'])) {
            return Carbon::parse($filtros['data_inicio']);
        }

        return now()->startOfMonth();
    }

    private function fim(array $filtros): Carbon
    {
        if (! empty($filtros['data_fim'])) {
            return Carbon::parse($filtros['data_fim']);
        }

        return now()->endOfMonth();
    }

    private function statusFechados(): array
    {
        return ['aprovado', 'financeiro', 'aguardando_pagamento', 'em_andamento', 'concluido', 'garantia'];
    }

    private function avgDiffDaysExpression(string $inicio, string $fim): string
    {
        return 'AVG('.$this->diffDaysExpression($inicio, $fim).')';
    }

    private function diffDaysExpression(string $inicio, string $fim): string
    {
        $driver = FacadesDB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => "(julianday({$fim}) - julianday({$inicio}))",
            'pgsql' => "DATE_PART('day', {$fim} - {$inicio})",
            default => "TIMESTAMPDIFF(DAY, {$inicio}, {$fim})",
        };
    }
}
