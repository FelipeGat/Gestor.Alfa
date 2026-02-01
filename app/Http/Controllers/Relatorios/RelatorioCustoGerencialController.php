<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\ContaPagar;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\Cobranca;
use Illuminate\Support\Carbon;

class RelatorioCustoGerencialController extends Controller
{
    public function index(Request $request)
    {
        // Filtros recebidos
        $clienteId = $request->input('cliente_id');
        $orcamentoId = $request->input('orcamento_id');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        // Listar clientes e orçamentos para os filtros
        $clientes = Cliente::orderBy('nome')->get();
        $orcamentos = Orcamento::with('cliente')->orderBy('id', 'desc')->get();

        // Se não selecionou orçamento, retorna apenas os filtros e mensagem
        if (!$orcamentoId) {
            return view('relatorios.custos-gerencial', [
                'clientes' => $clientes,
                'orcamentos' => $orcamentos,
                'mensagem' => 'Selecione um orçamento para visualizar o relatório.',
                'dias' => null,
                'valorOrcado' => null,
                'custoTotal' => null,
                'receitaRecebida' => null,
                'lucro' => null,
                'margem' => null,
                'custoMaximo' => null,
                'custoAcumuladoLinha' => [],
                'margemMinima' => 0.3,
                'ieo' => null,
                'ieoStatus' => null,
                'burnRate' => null,
                'burnRatePlanejado' => null,
                'custosPorCategoria' => collect(),
                'orcadoXRealizado' => [],
                'desvios' => [],
                'topCustos' => [],
                'alertas' => [],
                'tabela' => [],
                'totalTabela' => null,
                'quantidadeLancamentos' => null,
                'inicio' => null,
                'fim' => null,
                'clienteId' => $clienteId,
                'orcamentoId' => $orcamentoId,
            ]);
        }

        // Busca orçamento
        $orcamento = Orcamento::with('cliente')->findOrFail($orcamentoId);

        // Buscar custos vinculados ao orçamento e período
        $inicio = $dataInicio ? Carbon::parse($dataInicio) : Carbon::parse($orcamento->data_inicio);
        $fim = $dataFim ? Carbon::parse($dataFim) : Carbon::parse($orcamento->data_fim);
        // Correção 1: Duração correta (mínimo 1 dia)
        $dias = max(1, $inicio->diffInDays($fim) + 1);

        // CORREÇÃO: Trazer todos os custos vinculados ao orçamento, já carregando fornecedor e categoria
        // Isso garante que os gráficos, desvios e tabelas tenham acesso aos dados relacionados
        // Carregar fornecedor e relação indireta de categoria
        $custos = ContaPagar::with(['fornecedor', 'conta.subcategoria.categoria'])
            ->where('orcamento_id', $orcamentoId)
            ->get();

        // Correção 3: Receita recebida deve considerar TODAS as cobranças pagas do orçamento (sem filtro de data)
        $receitaRecebida = Cobranca::where('orcamento_id', $orcamentoId)
            ->whereNotNull('data_pagamento')
            ->where('status', 'pago')
            ->sum('valor');

        $valorOrcado = $orcamento->valor_total;
        $custoTotal = $custos->sum('valor');
        $lucro = $receitaRecebida - $custoTotal;
        $margem = $valorOrcado > 0 ? ($lucro / $valorOrcado) * 100 : 0;

        // Curva de Saúde (usar data_pagamento padronizado)
        $margemMinima = 0.3;
        $custoMaximo = $valorOrcado * (1 - $margemMinima);
        // Gráficos: usar data_pagamento, mas se nulo usar created_at
        $custoAcumulado = $custos->map(function ($c) {
            $data = $c->data_pagamento ?? $c->created_at;
            return [
                'data' => $data ? $data->format('Y-m-d') : null,
                'valor' => $c->valor
            ];
        })->filter(fn($c) => $c['data'])->groupBy('data')->map(function ($dia) {
            return collect($dia)->sum('valor');
        });
        $custoAcumuladoLinha = [];
        $acumulado = 0;
        foreach ($custoAcumulado as $data => $valor) {
            $acumulado += $valor;
            $custoAcumuladoLinha[$data] = $acumulado;
        }
        // Garantir pelo menos um ponto no gráfico
        if (empty($custoAcumuladoLinha)) {
            $custoAcumuladoLinha[date('Y-m-d')] = 0;
        }

        // Correção 5: IEO deve usar custo previsto, não valor de venda
        $hoje = Carbon::now();
        $diasExecutados = max(1, $inicio->diffInDays($hoje) + 1);
        // Buscar custo previsto total dos itens do orçamento
        $itensOrcamento = $orcamento->itens()->with('item')->get();
        $custoPrevistoTotal = $itensOrcamento->sum('subtotal');
        // Custo real acumulado até hoje (usar data_pagamento)
        $custoRealAteHoje = $custos->where('data_pagamento', '<=', $hoje)->sum('valor');
        // Custo planejado até hoje
        $custoPlanejadoAteHoje = $dias > 0 ? ($custoPrevistoTotal / $dias) * $diasExecutados : 0;
        $ieo = ($custoPlanejadoAteHoje > 0) ? ($custoRealAteHoje / $custoPlanejadoAteHoje) * 100 : null;
        if (is_null($ieo)) {
            $ieoStatus = 'Indisponível';
        } elseif ($ieo <= 100) {
            $ieoStatus = 'Saudável';
        } elseif ($ieo <= 110) {
            $ieoStatus = 'Atenção';
        } else {
            $ieoStatus = 'Alerta';
        }

        // Burn Rate real (custo total / dias executados)
        $burnRate = $diasExecutados > 0 ? $custoTotal / $diasExecutados : null;
        // Correção 2: Burn Rate Planejado deve usar custo previsto
        if ($custoPrevistoTotal > 0 && $dias > 0) {
            $burnRatePlanejado = $custoPrevistoTotal / $dias;
        } else {
            $burnRatePlanejado = null; // Indisponível
        }

        // Custos por categoria (usar data_pagamento padronizado)
        // Carregar subcategorias e contas para cada categoria
        $categorias = Categoria::with(['subcategorias.contas'])->get();
        // Montar custos por categoria usando relação indireta
        $custosPorCategoria = $categorias->mapWithKeys(function ($cat) use ($custos) {
            $valorReal = $custos->filter(function ($c) use ($cat) {
                return optional(optional(optional($c->conta)->subcategoria)->categoria)->id === $cat->id;
            })->sum('valor');
            return [$cat->nome => $valorReal];
        });
        // Garantir pelo menos uma categoria no gráfico
        if ($custosPorCategoria->isEmpty()) {
            $custosPorCategoria = collect(['Sem Categoria' => 0]);
        }

        // Orçado x Realizado
        $orcadoXRealizado = [
            'Valor Orçado' => $valorOrcado,
            'Custo Total' => $custoTotal,
            'Receita Recebida' => $receitaRecebida,
        ];
        // Garantir pelo menos um valor para gráfico de barras
        if (empty($orcadoXRealizado)) {
            $orcadoXRealizado = ['Sem Dados' => 0];
        }

        // Desvio por categoria (usar data_pagamento padronizado)
        $desvios = [];
        foreach ($categorias as $cat) {
            $planejado = $itensOrcamento->filter(function ($item) use ($cat) {
                return $item->item && $item->item->categoria_id == $cat->id;
            })->sum('subtotal');
            $real = $custos->filter(function ($c) use ($cat) {
                return optional(optional(optional($c->conta)->subcategoria)->categoria)->id === $cat->id;
            })->sum('valor');
            $percentual = $planejado > 0 ? (($real - $planejado) / $planejado) * 100 : 0;
            $desvios[] = [
                'categoria' => $cat->nome,
                'planejado' => $planejado,
                'real' => $real,
                'percentual' => $percentual,
                'alerta' => abs($percentual) > 20,
            ];
        }

        // Ranking de custos
        $topCustos = $custos->sortByDesc('valor')->take(5)->map(function ($c) {
            $fornecedor = '';
            if ($c->fornecedor) {
                $fornecedor = $c->fornecedor->nome_fantasia ?? $c->fornecedor->razao_social ?? $c->fornecedor->nome ?? '';
            }
            return [
                'fornecedor' => $fornecedor,
                'tipo' => $c->tipo,
                'valor' => $c->valor,
            ];
        });
        if ($topCustos->isEmpty()) {
            $topCustos = collect([
                [
                    'fornecedor' => 'Sem Fornecedor',
                    'tipo' => '',
                    'valor' => 0,
                ]
            ]);
        }

        // Alertas automáticos
        $alertas = [];
        // Alerta de consumo de custo só aparece se:
        // 1. Orçamento não está concluído
        // 2. Custo total > 70% do custo máximo permitido
        // 3. IEO existe e é maior que 100
        if (
            $orcamento->status !== 'concluido'
            && $custoTotal > ($custoMaximo * 0.7)
            && $ieo !== null
            && $ieo > 100
        ) {
            $alertas[] = '⚠️ Consumo de custo acima do esperado para o estágio atual do serviço.';
        }
        if ($burnRate > $burnRatePlanejado) {
            $alertas[] = '🔴 Custo diário atual está acima do planejado.';
        }
        if ($margem >= 30) {
            $alertas[] = '🟢 Serviço dentro da margem esperada.';
        }
        foreach ($desvios as $d) {
            if ($d['alerta'] && $d['categoria'] == 'Mão de Obra') {
                $alertas[] = '⚠️ Categoria Mão de Obra acima do previsto.';
            }
        }

        // Tabela detalhada (usar data_pagamento padronizado)
        // Exibir CONTA ao invés de categoria na tabela detalhada
        $tabela = $custos->map(function ($c) {
            return [
                'data' => $c->data_pagamento,
                'fornecedor' => $c->fornecedor ? ($c->fornecedor->nome_fantasia ?? $c->fornecedor->razao_social ?? '') : '',
                'conta' => $c->conta ? $c->conta->nome : '',
                'descricao' => $c->descricao,
                'tipo' => $c->tipo,
                'valor' => $c->valor,
            ];
        });
        $totalTabela = $tabela->sum('valor');
        $quantidadeLancamentos = $tabela->count();

        return view('relatorios.custos-gerencial', [
            'orcamento' => $orcamento,
            'dias' => $dias,
            'valorOrcado' => $valorOrcado,
            'custoTotal' => $custoTotal,
            'receitaRecebida' => $receitaRecebida,
            'lucro' => $lucro,
            'margem' => $margem,
            'custoMaximo' => $custoMaximo,
            'custoAcumuladoLinha' => $custoAcumuladoLinha,
            'margemMinima' => $margemMinima,
            'ieo' => $ieo,
            'ieoStatus' => $ieoStatus,
            'burnRate' => $burnRate,
            'burnRatePlanejado' => $burnRatePlanejado,
            'custos' => $custos, // garantir que $custos está disponível na view
            'categorias' => $categorias,
            'custosPorCategoria' => $custosPorCategoria,
            'orcadoXRealizado' => [
                'Valor Orçado' => $valorOrcado,
                'Custo Total' => $custoTotal,
                'Receita Recebida' => $receitaRecebida,
            ],
            'desvios' => $desvios,
            'topCustos' => $topCustos,
            'alertas' => $alertas,
            'tabela' => $tabela,
            'totalTabela' => $totalTabela,
            'quantidadeLancamentos' => $quantidadeLancamentos,
            'inicio' => $inicio,
            'fim' => $fim,
            'clienteId' => $clienteId,
            'orcamentoId' => $orcamentoId,
            // Passar custoPrevistoTotal para a view para exibir avisos
            'custoPrevistoTotal' => $custoPrevistoTotal,
        ]);
    }
}
