<?php


namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\Empresa;
use App\Models\ContaFinanceira;
use App\Models\ContaPagar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardFinanceiroController extends Controller
{
    /**
     * Exibe o dashboard financeiro com KPIs e gráficos.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $empresaId = $request->get('empresa_id');
        $ano = $request->get('ano', now()->year);

        // Processar filtro rápido (período) — calculado uma única vez e reutilizado em todo o controller
        $filtroRapido = $request->get('filtro_rapido', 'mes');
        switch ($filtroRapido) {
            case 'dia':
                $inicio = now()->startOfDay();
                $fim = now()->endOfDay();
                break;
            case 'semana':
                $inicio = now()->startOfWeek();
                $fim = now()->endOfWeek();
                break;
            case 'mes':
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
                break;
            case 'proximo_mes':
                $proximoMes = now()->addMonth();
                $inicio = $proximoMes->copy()->startOfMonth();
                $fim = $proximoMes->copy()->endOfMonth();
                break;
            case 'ano':
                $inicio = now()->startOfYear();
                $fim = now()->endOfYear();
                break;
            case 'custom':
                $inicio = $request->get('inicio')
                    ? Carbon::parse($request->inicio)->startOfDay()
                    : now()->startOfMonth()->startOfDay();
                $fim = $request->get('fim')
                    ? Carbon::parse($request->fim)->endOfDay()
                    : now()->endOfMonth()->endOfDay();
                break;
            default:
                $inicio = now()->startOfMonth();
                $fim = now()->endOfMonth();
        }

        // Lançamentos de Receita Realizada
        $lancamentosReceita = Cobranca::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresaId))
                       ->orWhereHas('contaFixa', fn($cq) => $cq->where('empresa_id', $empresaId));
                });
            })
            ->with(['orcamento.empresa', 'cliente', 'contaFixa.empresa'])
            ->get()
            ->map(function ($item) {
                // Se for contrato, tenta pegar empresa da conta fixa
                $empresa = null;
                if ($item->tipo === 'contrato' && $item->contaFixa && $item->contaFixa->empresa) {
                    $empresa = $item->contaFixa->empresa->nome_fantasia ?? $item->contaFixa->empresa->razao_social;
                } elseif ($item->orcamento && $item->orcamento->empresa) {
                    $empresa = $item->orcamento->empresa->nome_fantasia ?? $item->orcamento->empresa->razao_social;
                }
                $cliente = $item->cliente ? $item->cliente->nome_fantasia ?? $item->cliente->razao_social ?? $item->cliente->nome : null;
                $cnpjcpf = $item->cliente ? $item->cliente->cpf_cnpj_formatado : null;
                return [
                    'data' => optional($item->data_pagamento)->format('d/m/Y'),
                    'empresa' => $empresa,
                    'cliente' => $cliente,
                    'cnpjcpf' => $cnpjcpf,
                    'descricao' => $item->descricao,
                    'tipo' => $item->tipo,
                    'valor' => $item->valor,
                ];
            })->toArray();

        // Buscar todos os lançamentos pagos no período, agrupando por centro_custo_id
        $contasPagarAll = \App\Models\ContaPagar::query()
            ->where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->with(['conta.subcategoria.categoria'])
            ->get();

        // Lançamentos de Despesa Realizada
        $lancamentosDespesa = ContaPagar::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->with(['orcamento.centroCusto', 'fornecedor', 'centroCusto'])
            ->get()
            ->map(function ($item) {
                $centroCusto = null;
                if ($item->orcamento && $item->orcamento->centroCusto) {
                    $centroCusto = $item->orcamento->centroCusto->nome;
                } elseif ($item->centroCusto) {
                    $centroCusto = $item->centroCusto->nome;
                }
                $cliente = $item->fornecedor ? $item->fornecedor->nome_fantasia ?? $item->fornecedor->razao_social : null;
                $cnpjcpf = $item->fornecedor ? $item->fornecedor->cpf_cnpj : null;
                return [
                    'data' => optional($item->data_pagamento)->format('d/m/Y'),
                    'centro_custo' => $centroCusto,
                    'cliente' => $cliente,
                    'cnpjcpf' => $cnpjcpf,
                    'descricao' => $item->descricao,
                    'tipo' => $item->tipo,
                    'valor' => $item->valor,
                ];
            })->toArray();

        // Lançamentos Previsto - A Receber
        // Lançamentos Previsto - A Pagar
        $lancamentosPrevistoPagar = ContaPagar::where('status', 'em_aberto')
            ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->whereHas('orcamento', function ($oq) use ($empresaId) {
                    $oq->where('empresa_id', $empresaId);
                });
            })
            ->with(['orcamento.centroCusto', 'fornecedor', 'centroCusto'])
            ->get()
            ->map(function ($item) {
                $centroCusto = null;
                if ($item->orcamento && $item->orcamento->centroCusto) {
                    $centroCusto = $item->orcamento->centroCusto->nome;
                } elseif ($item->centroCusto) {
                    $centroCusto = $item->centroCusto->nome;
                }
                $cliente = $item->fornecedor ? $item->fornecedor->nome_fantasia ?? $item->fornecedor->razao_social : null;
                $cnpjcpf = $item->fornecedor ? $item->fornecedor->cpf_cnpj : null;
                return [
                    'data' => optional($item->data_vencimento)->format('d/m/Y'),
                    'centro_custo' => $centroCusto,
                    'cliente' => $cliente,
                    'cnpjcpf' => $cnpjcpf,
                    'descricao' => $item->descricao,
                    'tipo' => $item->tipo,
                    'valor' => $item->valor,
                ];
            })->toArray();
        // Previsto - A Receber: todos status exceto pago, no período (igual ao card)
        $lancamentosPrevistoReceber = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresaId))
                       ->orWhereHas('contaFixa', fn($cq) => $cq->where('empresa_id', $empresaId));
                });
            })
            ->with(['orcamento.empresa', 'cliente', 'contaFixa.empresa'])
            ->get()
            ->map(function ($item) {
                $empresa = null;
                if ($item->tipo === 'contrato' && $item->contaFixa && $item->contaFixa->empresa) {
                    $empresa = $item->contaFixa->empresa->nome_fantasia ?? $item->contaFixa->empresa->razao_social;
                } elseif ($item->orcamento && $item->orcamento->empresa) {
                    $empresa = $item->orcamento->empresa->nome_fantasia ?? $item->orcamento->empresa->razao_social;
                }
                $cliente = $item->cliente ? $item->cliente->nome_fantasia ?? $item->cliente->razao_social ?? $item->cliente->nome : null;
                $cnpjcpf = $item->cliente ? $item->cliente->cpf_cnpj_formatado : null;
                return [
                    'data' => optional($item->data_vencimento)->format('d/m/Y'),
                    'empresa' => $empresa,
                    'cliente' => $cliente,
                    'cnpjcpf' => $cnpjcpf,
                    'descricao' => $item->descricao,
                    'tipo' => $item->tipo,
                    'valor' => $item->valor,
                ];
            })->toArray();

        // Lançamentos Situação - Atrasado
        // Situação - Despesas Atrasadas: contas vencidas e não pagas, igual ao card
        $baseSituacao = ContaPagar::query()
            ->whereIn('status', ['em_aberto', 'atrasado']);

        $lancamentosAtrasado = (clone $baseSituacao)
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->with(['fornecedor', 'centroCusto'])
            ->get()
            ->map(function ($item) {
                return [
                    'data' => optional($item->data_vencimento)->format('d/m/Y'),
                    'centro_custo' => optional($item->centroCusto)->nome,
                    'cliente' => optional($item->fornecedor)->nome_fantasia ?? $item->fornecedor->razao_social ?? '-',
                    'descricao' => $item->descricao,
                    'tipo' => 'Despesa',
                    'valor' => $item->valor,
                ];
            })->toArray();

        // Lançamentos Situação - Pago
        $lancamentosPago = Cobranca::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresaId))
                       ->orWhereHas('contaFixa', fn($cq) => $cq->where('empresa_id', $empresaId));
                });
            })
            ->with(['orcamento.empresa', 'cliente', 'contaFixa.empresa'])
            ->get()
            ->map(function ($item) {
                $empresa = null;
                if ($item->tipo === 'contrato' && $item->contaFixa && $item->contaFixa->empresa) {
                    $empresa = $item->contaFixa->empresa->nome_fantasia ?? $item->contaFixa->empresa->razao_social;
                } elseif ($item->orcamento && $item->orcamento->empresa) {
                    $empresa = $item->orcamento->empresa->nome_fantasia ?? $item->orcamento->empresa->razao_social;
                }
                $cliente = $item->cliente ? $item->cliente->nome_fantasia ?? $item->cliente->razao_social ?? $item->cliente->nome : null;
                $cnpjcpf = $item->cliente ? $item->cliente->cpf_cnpj_formatado : null;
                return [
                    'data' => optional($item->data_pagamento)->format('d/m/Y'),
                    'empresa' => $empresa,
                    'cliente' => $cliente,
                    'cnpjcpf' => $cnpjcpf,
                    'descricao' => $item->descricao,
                    'tipo' => $item->tipo,
                    'valor' => $item->valor,
                ];
            })->toArray();

        // Lançamentos Situação - Diferença (pode ser customizado conforme regra de negócio)
        $lancamentosDiferenca = []; // Por padrão, vazio. Pode ser preenchido conforme necessidade.

        // Base para consultas de cobranças (gráfico anual)
        $baseQuery = Cobranca::query()
            ->whereYear('data_vencimento', $ano);

        if ($empresaId) {
            $baseQuery->where(function ($q) use ($empresaId) {
                $q->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresaId))
                  ->orWhereHas('contaFixa', fn($cq) => $cq->where('empresa_id', $empresaId));
            });
        }

        // DADOS AGRUPADOS DO BANCO
        // Receita recebida: agrupa por mês da data de pagamento (competência de recebimento)
        $recebidoPorMes = (clone $baseQuery)
            ->where('status', 'pago')
            ->whereNotNull('data_pagamento')
            ->selectRaw('MONTH(data_pagamento) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $previstoPorMes = (clone $baseQuery)
            ->where('status', '!=', 'pago')
            ->selectRaw('MONTH(data_vencimento) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        // DESPESAS POR MÊS — agrupa por mês da data de pagamento (competência)
        $despesaPagaPorMes = ContaPagar::whereYear('data_pagamento', $ano)
            ->where('status', 'pago')
            ->whereNotNull('data_pagamento')
            ->selectRaw('MONTH(data_pagamento) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        $despesaPrevistaPorMes = ContaPagar::whereYear('data_vencimento', $ano)
            ->where('status', 'em_aberto')
            ->selectRaw('MONTH(data_vencimento) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        /**
         * MONTA JANEIRO → DEZEMBRO FIXO
         */
        $labels = [];
        $recebido = [];
        $previsto = [];
        $despesaPaga = [];
        $despesaPrevista = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            $labels[]   = Carbon::create()->month($mes)->translatedFormat('M');
            $recebido[] = $recebidoPorMes[$mes] ?? 0;
            $previsto[] = $previstoPorMes[$mes] ?? 0;
            $despesaPaga[] = $despesaPagaPorMes[$mes] ?? 0;
            $despesaPrevista[] = $despesaPrevistaPorMes[$mes] ?? 0;
        }

        // Criar collection para o gráfico mensal
        $financeiroMensal = collect();
        for ($mes = 1; $mes <= 12; $mes++) {
            $financeiroMensal->push([
                'mes' => Carbon::create()->month($mes)->translatedFormat('M'),
                'recebido' => $recebidoPorMes[$mes] ?? 0,
                'previsto' => $previstoPorMes[$mes] ?? 0,
                'despesaPaga' => $despesaPagaPorMes[$mes] ?? 0,
                'despesaPrevista' => $despesaPrevistaPorMes[$mes] ?? 0,
            ]);
        }

        // KPIs
        $totalRecebido = (clone $baseQuery)->where('status', 'pago')->sum('valor');
        $totalPrevisto = (clone $baseQuery)->where('status', '!=', 'pago')->sum('valor');

        $empresas = Empresa::orderBy('nome_fantasia')->get();

        // SALDOS DAS CONTAS BANCÁRIAS
        $contasFinanceiras = ContaFinanceira::query()
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        // Agrupar por tipo
        $contasAgrupadasPorTipo = $contasFinanceiras->groupBy('tipo');

        // Saldo total apenas de contas correntes
        $saldoTotalBancos = $contasFinanceiras
            ->where('tipo', 'corrente')
            ->sum('saldo');

        // O período ($inicio/$fim) já foi calculado no início do método.

        // RECEITA / DESPESA REALIZADA (usa data_pagamento)
        $receitaRealizada = Cobranca::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresaId))
                       ->orWhereHas('contaFixa', fn($cq) => $cq->where('empresa_id', $empresaId));
                });
            })
            ->sum('valor');

        $despesaRealizada = ContaPagar::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->whereHas('orcamento', function ($oq) use ($empresaId) {
                    $oq->where('empresa_id', $empresaId);
                });
            })
            ->sum('valor');

        $saldoRealizado = $receitaRealizada - $despesaRealizada;

        /**
         * PREVISTO
         */
        $aReceber = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresaId))
                       ->orWhereHas('contaFixa', fn($cq) => $cq->where('empresa_id', $empresaId));
                });
            })
            ->sum('valor');

        $aPagar = ContaPagar::where('status', 'em_aberto')
            ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->whereHas('orcamento', function ($oq) use ($empresaId) {
                    $oq->where('empresa_id', $empresaId);
                });
            })
            ->sum('valor');

        $saldoPrevisto = $aReceber - $aPagar;

        /**
         * SITUAÇÃO (dentro do período filtrado)
         */
        // Atrasados = contas vencidas antes do período que ainda não foram pagas
        $atrasado = ContaPagar::query()
            ->whereIn('status', ['em_aberto', 'atrasado'])
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->whereHas('orcamento', function ($oq) use ($empresaId) {
                    $oq->where('empresa_id', $empresaId);
                });
            })
            ->sum('valor');

        // Pago = cobranças pagas dentro do período filtrado
        $pago = Cobranca::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresaId))
                       ->orWhereHas('contaFixa', fn($cq) => $cq->where('empresa_id', $empresaId));
                });
            })
            ->sum('valor');

        $saldoSituacao = $pago - $atrasado;

        // Gráficos de Gastos por Categoria (fiel aos lançamentos reais)
        // Buscar todos os centros de custo existentes
        $centrosCusto = \App\Models\CentroCusto::where('nome', '!=', 'Alfa')->orderBy('id')->get();
        $dadosCentros = [];
        // Buscar todos os lançamentos pagos no período, agrupando por centro_custo_id
        $contasPagarAll = \App\Models\ContaPagar::query()
            ->where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->with(['conta.subcategoria.categoria'])
            ->get();

        foreach ($centrosCusto as $centro) {
            $contasPagar = $contasPagarAll->where('centro_custo_id', $centro->id);

            $categoriasArr = [];
            $subcategoriasArr = [];
            $contasArr = [];

            foreach ($contasPagar as $item) {
                $conta = $item->conta;
                $sub = $conta ? $conta->subcategoria : null;
                $categoria = $sub ? $sub->categoria : null;

                // Categoria
                if ($categoria && $categoria->id) {
                    if (!isset($categoriasArr[$categoria->id])) {
                        $categoriasArr[$categoria->id] = [
                            'id' => $categoria->id,
                            'nome' => $categoria->nome,
                            'total' => 0,
                        ];
                    }
                    $categoriasArr[$categoria->id]['total'] += (float)$item->valor;
                }
                // Subcategoria
                if ($sub && $sub->id) {
                    if (!isset($subcategoriasArr[$sub->id])) {
                        $subcategoriasArr[$sub->id] = [
                            'id' => $sub->id,
                            'nome' => $sub->nome,
                            'total' => 0,
                        ];
                    }
                    $subcategoriasArr[$sub->id]['total'] += (float)$item->valor;
                }
                // Conta
                if ($conta && $conta->id) {
                    if (!isset($contasArr[$conta->id])) {
                        $contasArr[$conta->id] = [
                            'id' => $conta->id,
                            'nome' => $conta->nome,
                            'total' => 0,
                        ];
                    }
                    $contasArr[$conta->id]['total'] += (float)$item->valor;
                }
            }

            // Reindexar para arrays simples
            $categoriasArr = array_values($categoriasArr);
            $subcategoriasArr = array_values($subcategoriasArr);
            $contasArr = array_values($contasArr);

            // Exibir nome do centro de custo no gráfico
            $nomeCentro = $centro->nome;
            if ($centro->id == 1) $nomeCentro = 'DESPESAS GERAIS';
            elseif ($centro->id == 2) $nomeCentro = 'INVEST';
            elseif ($centro->id == 3) $nomeCentro = 'DELTA';
            elseif ($centro->id == 4) $nomeCentro = 'GW';

            $dadosCentros[$nomeCentro] = [
                'categorias' => $categoriasArr,
                'subcategorias' => $subcategoriasArr,
                'contas' => $contasArr,
            ];
        }

        // ================= INDICADORES INTELIGENTES =================
        // 1) % da renda comprometida
        $percentualRendaComprometida = $receitaRealizada > 0 ? round(($despesaRealizada / $receitaRealizada) * 100, 1) : 0;

        // 2) Ticket médio de despesas
        $despesasPagas = ContaPagar::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'));
        $totalDespesasRealizadas = $despesasPagas->sum('valor');
        $quantidadeDespesas = (clone $despesasPagas)->count();
        $ticketMedioDespesas = $quantidadeDespesas > 0 ? round($totalDespesasRealizadas / $quantidadeDespesas, 2) : 0;

        // 3) Custo fixo x variável (corrigido: usar categoria, não tipo)
        $despesasPagas = ContaPagar::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->with('conta.subcategoria.categoria')
            ->get();

        $custoFixo = $despesasPagas->filter(function ($item) {
            return $item->conta
                && $item->conta->subcategoria
                && $item->conta->subcategoria->categoria
                && str_contains(
                    strtolower($item->conta->subcategoria->categoria->nome),
                    'fix'
                );
        })->sum('valor');

        $custoVariavel = $despesasPagas->filter(function ($item) {
            return $item->conta
                && $item->conta->subcategoria
                && $item->conta->subcategoria->categoria
                && str_contains(
                    strtolower($item->conta->subcategoria->categoria->nome),
                    'vari'
                );
        })->sum('valor');

        $totalCustos = $custoFixo + $custoVariavel;
        $percentualFixo = $totalCustos > 0 ? round(($custoFixo / $totalCustos) * 100, 1) : 0;
        $percentualVariavel = $totalCustos > 0 ? round(($custoVariavel / $totalCustos) * 100, 1) : 0;

        // 4) Meta mensal (Orçado x Realizado)
        $orcado = \App\Models\ContaFixaPagar::where(function ($q) use ($inicio, $fim) {
            $q->whereNull('data_fim')
                ->orWhere('data_fim', '>=', $inicio->format('Y-m-d'));
        })
            ->where('data_inicial', '<=', $fim->format('Y-m-d'))
            ->where('ativo', true)
            ->sum('valor');
        $realizado = ContaPagar::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
            ->where('tipo', 'FIXA')
            ->sum('valor');
        $diferencaMeta = $orcado - $realizado;
        $percentualMeta = $orcado > 0 ? round(($realizado / $orcado) * 100, 1) : 0;

        // ================= ALERTAS E INSIGHTS AUTOMÁTICOS =================
        $alertasFinanceiros = [];
        // 1) Gastos acima do normal em Alimentação
        $categoriaAlimentacao = \App\Models\Categoria::where('nome', 'Alimentação')->first();
        if ($categoriaAlimentacao) {
            // Buscar IDs das subcategorias de Alimentação
            $subcategoriasIds = $categoriaAlimentacao->subcategorias->pluck('id');
            // Buscar IDs das contas dessas subcategorias
            $contasIds = \App\Models\Conta::whereIn('subcategoria_id', $subcategoriasIds)->pluck('id');
            // Média dos últimos 3 meses
            $mediaUltimos3 = ContaPagar::where('status', 'pago')
                ->whereIn('conta_id', $contasIds)
                ->whereBetween('data_pagamento', [
                    Carbon::parse($inicio)->copy()->subMonths(3)->startOfMonth(),
                    Carbon::parse($inicio)->copy()->subMonths(1)->endOfMonth()
                ])
                ->sum('valor') / 3;
            // Gasto atual
            $gastoAtual = ContaPagar::where('status', 'pago')
                ->whereIn('conta_id', $contasIds)
                ->whereBetween('data_pagamento', [$inicio, $fim])
                ->sum('valor');
            if ($mediaUltimos3 > 0 && $gastoAtual > $mediaUltimos3 * 1.2) {
                $percentualAcima = round((($gastoAtual - $mediaUltimos3) / $mediaUltimos3) * 100, 1);
                $alertasFinanceiros[] = [
                    'tipo' => 'alerta',
                    'mensagem' => "Gastos acima do normal em Alimentação: {$percentualAcima}% acima da média dos últimos 3 meses.",
                ];
            }
        }

        // 2) Meta de economia atingida
        $categoriaEconomia = \App\Models\Categoria::where('nome', 'Economia')->first();
        if ($categoriaEconomia) {
            $subcategoriasIds = $categoriaEconomia->subcategorias->pluck('id');
            $contasIds = \App\Models\Conta::whereIn('subcategoria_id', $subcategoriasIds)->pluck('id');
            // Orçado: soma de contas fixas dessa categoria
            $orcadoEconomia = \App\Models\ContaFixaPagar::whereIn('conta_id', $contasIds)
                ->where(function ($q) use ($inicio, $fim) {
                    $q->whereNull('data_fim')
                        ->orWhere('data_fim', '>=', $inicio->format('Y-m-d'));
                })
                ->where('data_inicial', '<=', $fim->format('Y-m-d'))
                ->where('ativo', true)
                ->sum('valor');
            // Realizado: soma de contas pagas dessa categoria
            $realizadoEconomia = ContaPagar::where('status', 'pago')
                ->whereIn('conta_id', $contasIds)
                ->whereBetween('data_pagamento', [$inicio, $fim])
                ->sum('valor');
            if ($orcadoEconomia > 0 && $realizadoEconomia >= $orcadoEconomia) {
                $alertasFinanceiros[] = [
                    'tipo' => 'sucesso',
                    'mensagem' => 'Meta de economia atingida! 🎉',
                ];
            } elseif ($orcadoEconomia > 0) {
                $falta = $orcadoEconomia - $realizadoEconomia;
                $alertasFinanceiros[] = [
                    'tipo' => 'info',
                    'mensagem' => 'Faltam R$ ' . number_format($falta, 2, ',', '.') . ' para atingir a meta de economia.',
                ];
            }
        }

        return view('dashboard-financeiro.index', [
            // ...existente...
            'dadosCentros' => $dadosCentros,
            'labels' => $labels,
            'recebido' => $recebido,
            'previsto' => $previsto,
            'despesaPaga' => $despesaPaga,
            'despesaPrevista' => $despesaPrevista,
            'financeiroMensal' => $financeiroMensal,
            'totalRecebido' => $totalRecebido,
            'totalPrevisto' => $totalPrevisto,
            'empresas' => $empresas,
            'empresaId' => $empresaId,
            'ano' => $ano,
            'contasFinanceiras' => $contasFinanceiras,
            'contasAgrupadasPorTipo' => $contasAgrupadasPorTipo,
            'saldoTotalBancos' => $saldoTotalBancos,
            'inicio' => $inicio,
            'fim' => $fim,
            'receitaRealizada' => $receitaRealizada,
            'despesaRealizada' => $despesaRealizada,
            'saldoRealizado' => $saldoRealizado,
            'aReceber' => $aReceber,
            'aPagar' => $aPagar,
            'saldoPrevisto' => $saldoPrevisto,
            'atrasado' => $atrasado,
            'pago' => $pago,
            'saldoSituacao' => $saldoSituacao,
            // Novos indicadores
            'percentualRendaComprometida' => $percentualRendaComprometida,
            'ticketMedioDespesas' => $ticketMedioDespesas,
            'custoFixo' => $custoFixo,
            'custoVariavel' => $custoVariavel,
            'percentualFixo' => $percentualFixo,
            'percentualVariavel' => $percentualVariavel,
            'orcado' => $orcado,
            'realizadoMeta' => $realizado,
            'diferencaMeta' => $diferencaMeta,
            'percentualMeta' => $percentualMeta,
            'alertasFinanceiros' => $alertasFinanceiros,
            // Lançamentos para modal
            'lancamentosReceita' => $lancamentosReceita,
            'lancamentosDespesa' => $lancamentosDespesa,
            'lancamentosPrevistoReceber' => $lancamentosPrevistoReceber,
            'lancamentosPrevistoPagar' => $lancamentosPrevistoPagar,
            'lancamentosAtrasado' => $lancamentosAtrasado,
            'lancamentosPago' => $lancamentosPago,
            'lancamentosDiferenca' => $lancamentosDiferenca,
        ]);
    }
}
