<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\ContaPagar;
use App\Models\Cobranca;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class RelatorioCustosOrcamentosController extends Controller
{
    public function index(Request $request)
    {
        // Filtros
        $clientes = Cliente::where('ativo', true)->orderBy('nome_fantasia')->get();
        $orcamentos = collect();
        $orcamentoSelecionado = null;
        $dadosOrcamento = null;
        $custos = collect();
        $custosPorTipo = [];
        $custosPorCategoria = collect();
        $custosEvolucao = collect();
        $kpis = [];
        $tabelaCustos = collect();
        $receitaRecebida = 0;
        $receitaPrevista = 0;
        $percentualConsumido = 0;
        $qtdLancamentos = 0;
        $orcamentoId = $request->input('orcamento_id');
        $clienteId = $request->input('cliente_id');

        // Carregar orçamentos filtrados por cliente e status
        if ($clienteId) {
            $orcamentos = Orcamento::where('cliente_id', $clienteId)
                ->whereIn('status', ['APROVADO', 'EM_ANDAMENTO'])
                ->orderByDesc('created_at')
                ->get();
        } else {
            $orcamentos = Orcamento::whereIn('status', ['APROVADO', 'EM_ANDAMENTO'])
                ->orderByDesc('created_at')
                ->get();
        }

        if ($orcamentoId) {
            $orcamentoSelecionado = Orcamento::with('cliente')->find($orcamentoId);
        }

        if ($orcamentoSelecionado) {
            // 1) Dados do Orçamento
            $dadosOrcamento = [
                'cliente' => $orcamentoSelecionado->cliente?->nome_fantasia ?? $orcamentoSelecionado->cliente?->razao_social ?? '—',
                'numero' => $orcamentoSelecionado->numero_orcamento,
                'status' => $orcamentoSelecionado->status,
                'valor_total' => $orcamentoSelecionado->valor_total,
                'data_inicio' => $orcamentoSelecionado->created_at?->format('d/m/Y'),
                'data_fim' => $orcamentoSelecionado->validade ? Carbon::parse($orcamentoSelecionado->validade)->format('d/m/Y') : null,
            ];

            // 2) Custos Operacionais
            $custosQuery = ContaPagar::with(['fornecedor', 'centroCusto'])
                ->where('orcamento_id', $orcamentoId);
            $custos = $custosQuery->get();
            $totalCustos = $custos->sum('valor');
            $custosPorTipo = [
                'FIXO' => $custos->where('tipo', 'FIXO')->sum('valor'),
                'VARIAVEL' => $custos->where('tipo', 'VARIAVEL')->sum('valor'),
            ];
            $custosPorCategoria = $custos->groupBy('centroCusto.categoria_id')->map(function ($items, $catId) {
                return [
                    'categoria_id' => $catId,
                    'categoria_nome' => optional($items->first()->centroCusto?->categoria)->nome ?? '—',
                    'valor' => $items->sum('valor'),
                ];
            })->values();
            $qtdLancamentos = $custos->count();

            // Evolução de custos no tempo (por mês)
            $custosEvolucao = $custos->groupBy(function ($item) {
                return $item->data_vencimento ? Carbon::parse($item->data_vencimento)->format('Y-m') : 'Sem Data';
            })->map(function ($items, $mes) {
                return [
                    'mes' => $mes,
                    'valor' => $items->sum('valor'),
                ];
            })->sortKeys()->values();

            // 3) Receita do Orçamento
            $cobrancas = Cobranca::where('orcamento_id', $orcamentoId)->get();
            $receitaRecebida = $cobrancas->where('status', 'pago')->sum('valor');
            $receitaPrevista = $cobrancas->sum('valor');

            // 4) Resultado Financeiro
            $lucroBruto = $receitaRecebida - $totalCustos;
            $margem = $orcamentoSelecionado->valor_total > 0 ? ($lucroBruto / $orcamentoSelecionado->valor_total) * 100 : 0;
            $percentualConsumido = $orcamentoSelecionado->valor_total > 0 ? ($totalCustos / $orcamentoSelecionado->valor_total) * 100 : 0;

            // KPIs
            $kpis = [
                'valor_orcado' => $orcamentoSelecionado->valor_total,
                'custo_total' => $totalCustos,
                'receita_recebida' => $receitaRecebida,
                'lucro' => $lucroBruto,
                'margem' => $margem,
            ];

            // Tabela detalhada de custos
            $tabelaCustos = $custos->map(function ($item) {
                return [
                    'data' => $item->data_vencimento?->format('d/m/Y'),
                    'fornecedor' => $item->fornecedor?->nome_fantasia ?? $item->fornecedor?->razao_social ?? '—',
                    'categoria' => $item->centroCusto?->categoria?->nome ?? '—',
                    'descricao' => $item->descricao,
                    'tipo' => $item->tipo,
                    'valor' => $item->valor,
                ];
            });
        }

        return view('relatorios.custos-orcamentos.index', compact(
            'clientes',
            'orcamentos',
            'orcamentoSelecionado',
            'dadosOrcamento',
            'custos',
            'custosPorTipo',
            'custosPorCategoria',
            'custosEvolucao',
            'receitaRecebida',
            'receitaPrevista',
            'kpis',
            'tabelaCustos',
            'percentualConsumido',
            'qtdLancamentos'
        ));
    }
}
