<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\ContaPagar;
use App\Models\Categoria;
use App\Models\Fornecedor;
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
        $orcamentos = Orcamento::with('cliente')
            ->whereIn('status', [
                'APROVADO',
                'EM_ANDAMENTO',
                'FINANCEIRO',
                'CONCLUIDO',
                'AGUARDANDO PAGAMENTO',
                'AGUARDANDO_PAGAMENTO'
            ])
            ->orderBy('id', 'desc')
            ->get();

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

        // ...existing code...
        $valorOrcado = $orcamento->valor_total;
        $custoTotal = $custos->sum('valor');
        $lucro = $receitaRecebida - $custoTotal;
        $margem = $valorOrcado > 0 ? ($lucro / $valorOrcado) * 100 : 0;

        // Curva de Saúde
        $margemMinima = 0.3;
        $custoMaximo = $valorOrcado * (1 - $margemMinima);
        $custoAcumulado = $custos->sortBy('data')->groupBy('data')->map(function ($dia) {
            return $dia->sum('valor');
        });
        $custoAcumuladoLinha = [];
        $acumulado = 0;
        foreach ($custoAcumulado as $data => $valor) {
            $acumulado += $valor;
            $custoAcumuladoLinha[$data] = $acumulado;
        }

        // IEO
        $hoje = Carbon::now();
        $diasExecutados = $inicio->diffInDays($hoje) + 1;
        $custoRealAteHoje = $custos->where('data', '<=', $hoje)->sum('valor');
        $custoPlanejadoAteHoje = ($valorOrcado / $dias) * $diasExecutados;
        $ieo = $custoPlanejadoAteHoje > 0 ? ($custoRealAteHoje / $custoPlanejadoAteHoje) * 100 : 0;
        if ($ieo <= 100) $ieoStatus = 'Saudável';
        elseif ($ieo <= 110) $ieoStatus = 'Atenção';
        else $ieoStatus = 'Alerta';

        // Burn Rate
        $burnRate = $diasExecutados > 0 ? $custoTotal / $diasExecutados : 0;
        $burnRatePlanejado = $dias > 0 ? $valorOrcado / $dias : 0;

        // Custos por categoria
        $categorias = Categoria::all();
        $custosPorCategoria = $categorias->mapWithKeys(function ($cat) use ($custos) {
            $valorReal = $custos->where('categoria_id', $cat->id)->sum('valor');
            return [$cat->nome => $valorReal];
        });

        // Orçado x Realizado
        $orcadoXRealizado = [
            'Valor Orçado' => $valorOrcado,
            'Custo Total' => $custoTotal,
            'Receita Recebida' => $receitaRecebida,
        ];

        // Desvio por categoria
        $desvios = [];
        foreach ($categorias as $cat) {
            $planejado = $orcamento->planejados()->where('categoria_id', $cat->id)->sum('valor');
            $real = $custos->where('categoria_id', $cat->id)->sum('valor');
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
            return [
                'fornecedor' => $c->fornecedor->nome ?? '',
                'tipo' => $c->tipo,
                'valor' => $c->valor,
            ];
        });

        // Alertas automáticos
        $alertas = [];
        if ($custoTotal > ($custoMaximo * 0.7)) {
            $alertas[] = '⚠️ Este orçamento já consumiu mais de 70% do custo permitido.';
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

        // Tabela detalhada
        $tabela = $custos->map(function ($c) {
            return [
                'data' => $c->data,
                'fornecedor' => $c->fornecedor->nome ?? '',
                'categoria' => $c->categoria->nome ?? '',
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
            'custosPorCategoria' => $custosPorCategoria,
            'orcadoXRealizado' => $orcadoXRealizado,
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
        ]);
    }
}
