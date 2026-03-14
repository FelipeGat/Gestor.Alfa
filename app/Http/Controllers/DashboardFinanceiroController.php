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
            case 'mes_anterior':
                $inicio = now()->subMonth()->startOfMonth();
                $fim = now()->subMonth()->endOfMonth();
                break;
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

        // Status de orçamentos considerados "ativos" para fins de previsão
        $statusOrcamentoAtivo = ['aprovado', 'em_andamento', 'aguardando_pagamento', 'agendado', 'financeiro', 'concluido'];

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
        // Previsto - A Receber: contratos ativos no período + orçamentos com status ativo
        $lancamentosPrevistoReceber = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
            ->where(function ($q) use ($statusOrcamentoAtivo) {
                $q->where('tipo', 'contrato')
                  ->orWhere(function ($sq) use ($statusOrcamentoAtivo) {
                      $sq->where('tipo', 'orcamento')
                         ->whereHas('orcamento', fn($oq) => $oq->whereIn('status', $statusOrcamentoAtivo));
                  });
            })
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

        // Lançamentos Situação - Despesas Atrasadas: sem filtro de período nem empresa (total global)
        $lancamentosAtrasado = ContaPagar::whereIn('status', ['em_aberto', 'atrasado'])
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->with(['fornecedor', 'centroCusto'])
            ->get()
            ->map(function ($item) {
                return [
                    'data' => optional($item->data_vencimento)->format('d/m/Y'),
                    'centro_custo' => optional($item->centroCusto)->nome,
                    'cliente' => optional($item->fornecedor)->nome_fantasia ?? optional($item->fornecedor)->razao_social ?? '-',
                    'descricao' => $item->descricao,
                    'tipo' => 'Despesa',
                    'valor' => $item->valor,
                ];
            })->toArray();

        // Lançamentos Situação - Receitas Atrasadas: cobranças vencidas e não pagas (total global, sem filtro)
        $lancamentosReceitasAtrasadas = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '<', now()->toDateString())
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
        // PREVISTO A RECEBER: contratos mensais ativos no período + orçamentos com status ativo (não pagos)
        $aReceber = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
            ->where(function ($q) use ($statusOrcamentoAtivo) {
                $q->where('tipo', 'contrato')
                  ->orWhere(function ($sq) use ($statusOrcamentoAtivo) {
                      $sq->where('tipo', 'orcamento')
                         ->whereHas('orcamento', fn($oq) => $oq->whereIn('status', $statusOrcamentoAtivo));
                  });
            })
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
         * SITUAÇÃO — total global de atrasos (sem filtro de período nem de empresa)
         */
        // Receitas Atrasadas: cobranças não pagas com vencimento antes de hoje
        $receitasAtrasadas = Cobranca::where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->sum('valor');

        // Despesas Atrasadas: contas a pagar em aberto/atrasadas com vencimento antes de hoje
        $despesasAtrasadas = ContaPagar::whereIn('status', ['em_aberto', 'atrasado'])
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->sum('valor');

        $saldoSituacao = $receitasAtrasadas - $despesasAtrasadas;

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

        // Ticket médio por tipo (fixas x variáveis)
        $fixasPagas = (clone $despesasPagas)->where('tipo', 'FIXA');
        $totalFixasTM = $fixasPagas->sum('valor');
        $qtdFixasTM = (clone $fixasPagas)->count();
        $ticketMedioDespesasFixas = $qtdFixasTM > 0 ? round($totalFixasTM / $qtdFixasTM, 2) : 0;

        $variaveisPagas = (clone $despesasPagas)->where('tipo', '!=', 'FIXA');
        $totalVariaveisTM = $variaveisPagas->sum('valor');
        $qtdVariaveisTM = (clone $variaveisPagas)->count();
        $ticketMedioDespesasVariaveis = $qtdVariaveisTM > 0 ? round($totalVariaveisTM / $qtdVariaveisTM, 2) : 0;

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
        // Baseados em dados reais do período selecionado — sem dependência de nomes de categoria fixos.
        $alertasFinanceiros = [];

        // ── 1) Saldo do período: despesas superam receitas ──────────────────────────────
        if ($despesaRealizada > 0 && $despesaRealizada > $receitaRealizada) {
            $deficit = $despesaRealizada - $receitaRealizada;
            $alertasFinanceiros[] = [
                'tipo'     => 'alerta',
                'mensagem' => 'As despesas realizadas (R$ ' . number_format($despesaRealizada, 2, ',', '.') . ') superaram as receitas (R$ ' . number_format($receitaRealizada, 2, ',', '.') . ') em R$ ' . number_format($deficit, 2, ',', '.') . ' no período.',
            ];
        }

        // ── 2) Cobranças vencidas representam risco alto de inadimplência ───────────────
        if ($receitasAtrasadas > 0) {
            $totalReceberGlobal = Cobranca::where('status', '!=', 'pago')->sum('valor') ?: 1;
            $percInadimplencia = round(($receitasAtrasadas / $totalReceberGlobal) * 100, 1);
            if ($percInadimplencia >= 30) {
                $alertasFinanceiros[] = [
                    'tipo'     => 'alerta',
                    'mensagem' => "Inadimplência crítica: {$percInadimplencia}% das cobranças em aberto estão vencidas (R$ " . number_format($receitasAtrasadas, 2, ',', '.') . ').',
                ];
            } elseif ($percInadimplencia >= 10) {
                $alertasFinanceiros[] = [
                    'tipo'     => 'info',
                    'mensagem' => "Atenção: {$percInadimplencia}% das cobranças em aberto estão vencidas (R$ " . number_format($receitasAtrasadas, 2, ',', '.') . '). Considere acionar os clientes.',
                ];
            }
        }

        // ── 3) Despesas atrasadas representam risco para o fluxo de caixa ───────────────
        if ($despesasAtrasadas > 0) {
            $alertasFinanceiros[] = [
                'tipo'     => 'alerta',
                'mensagem' => 'Existem R$ ' . number_format($despesasAtrasadas, 2, ',', '.') . ' em despesas vencidas e não pagas. Regularize para evitar juros e multas.',
            ];
        }

        // ── 4) Concentração de receita em uma única empresa ─────────────────────────────
        if ($receitaRealizada > 0 && !$empresaId) {
            $receitaTopEmpresa = Cobranca::where('status', 'pago')
                ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
                ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
                ->with('orcamento:id,empresa_id', 'orcamento.empresa:id,nome_fantasia,razao_social',
                        'contaFixa:id,empresa_id', 'contaFixa.empresa:id,nome_fantasia,razao_social')
                ->get()
                ->groupBy(function ($c) {
                    if ($c->orcamento && $c->orcamento->empresa_id) return $c->orcamento->empresa_id;
                    if ($c->contaFixa && $c->contaFixa->empresa_id) return $c->contaFixa->empresa_id;
                    return 0;
                })
                ->map(function ($group) {
                    $first = $group->first();
                    $nome = $first->orcamento?->empresa?->nome_fantasia
                         ?? $first->orcamento?->empresa?->razao_social
                         ?? $first->contaFixa?->empresa?->nome_fantasia
                         ?? 'Sem empresa';
                    return ['total' => $group->sum('valor'), 'nome' => $nome];
                })
                ->sortByDesc('total')
                ->first();
            if ($receitaTopEmpresa) {
                $percConcentracao = round(($receitaTopEmpresa['total'] / $receitaRealizada) * 100, 1);
                if ($percConcentracao >= 70) {
                    $alertasFinanceiros[] = [
                        'tipo'     => 'info',
                        'mensagem' => "Atenção à concentração: {$percConcentracao}% das receitas do período vêm apenas de <b>{$receitaTopEmpresa['nome']}</b>. Diversificar reduz o risco.",
                    ];
                }
            }
        }

        // ── 5) Período com saldo positivo e sem inadimplência ───────────────────────────
        if ($receitaRealizada > 0 && $receitaRealizada > $despesaRealizada && $receitasAtrasadas == 0 && $despesasAtrasadas == 0) {
            $alertasFinanceiros[] = [
                'tipo'     => 'sucesso',
                'mensagem' => 'Excelente! O período está com saldo positivo de R$ ' . number_format($receitaRealizada - $despesaRealizada, 2, ',', '.') . ' e sem inadimplências.',
            ];
        }

        // ── 6) Previsto a receber alto vs realizado (oportunidade de cobrança) ───────────
        if ($aReceber > 0 && $receitaRealizada > 0) {
            $percRealizado = round(($receitaRealizada / ($receitaRealizada + $aReceber)) * 100, 1);
            if ($percRealizado < 50) {
                $alertasFinanceiros[] = [
                    'tipo'     => 'info',
                    'mensagem' => "Apenas {$percRealizado}% do previsto foi realizado no período. Há R$ " . number_format($aReceber, 2, ',', '.') . ' ainda a receber.',
                ];
            }
        }

        // ── 7) Sem movimentação no período ──────────────────────────────────────────────
        if ($receitaRealizada == 0 && $despesaRealizada == 0) {
            $alertasFinanceiros[] = [
                'tipo'     => 'info',
                'mensagem' => 'Nenhuma movimentação financeira realizada foi registrada no período selecionado.',
            ];
        }

        // ================= RECEITAS POR EMPRESA (flip card) =================
        $todasEmpresasAtivas = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();

        $receitasPorEmpresa = $todasEmpresasAtivas->map(function ($empresa) use ($inicio, $fim, $statusOrcamentoAtivo) {
            // -- Receita realizada por tipo (orçamento vs contrato) --
            $realizadaRaw = Cobranca::where('status', 'pago')
                ->whereDate('data_pagamento', '>=', $inicio->format('Y-m-d'))
                ->whereDate('data_pagamento', '<=', $fim->format('Y-m-d'))
                ->where(function ($q) use ($empresa) {
                    $q->whereHas('orcamento', fn ($oq) => $oq->where('empresa_id', $empresa->id))
                      ->orWhereHas('contaFixa', fn ($cq) => $cq->where('empresa_id', $empresa->id));
                })
                ->selectRaw('tipo, SUM(valor) as total, COUNT(*) as qtd')
                ->groupBy('tipo')
                ->get()
                ->keyBy('tipo');

            $realizadaOrcamento = (float) ($realizadaRaw->get('orcamento')->total ?? 0);
            $realizadaContrato  = (float) ($realizadaRaw->get('contrato')->total ?? 0);
            $realizadaTotal     = $realizadaOrcamento + $realizadaContrato;
            $qtdCobrancas       = (int)   $realizadaRaw->sum('qtd');

            // -- A receber no período (previsto, não pago) --
            $aReceberPeriodo = Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', '>=', $inicio->format('Y-m-d'))
                ->whereDate('data_vencimento', '<=', $fim->format('Y-m-d'))
                ->where(function ($q) use ($statusOrcamentoAtivo) {
                    $q->where('tipo', 'contrato')
                      ->orWhere(function ($sq) use ($statusOrcamentoAtivo) {
                          $sq->where('tipo', 'orcamento')
                             ->whereHas('orcamento', fn ($oq) => $oq->whereIn('status', $statusOrcamentoAtivo));
                      });
                })
                ->where(function ($q) use ($empresa) {
                    $q->whereHas('orcamento', fn ($oq) => $oq->where('empresa_id', $empresa->id))
                      ->orWhereHas('contaFixa', fn ($cq) => $cq->where('empresa_id', $empresa->id));
                })
                ->sum('valor');

            // -- Atrasado (global — independente de período) --
            $atrasado = Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', '<', now()->toDateString())
                ->where(function ($q) use ($empresa) {
                    $q->whereHas('orcamento', fn ($oq) => $oq->where('empresa_id', $empresa->id))
                      ->orWhereHas('contaFixa', fn ($cq) => $cq->where('empresa_id', $empresa->id));
                })
                ->sum('valor');

            // -- Receita total anual (para % participação) --
            $receitaAnual = Cobranca::where('status', 'pago')
                ->whereYear('data_pagamento', now()->year)
                ->where(function ($q) use ($empresa) {
                    $q->whereHas('orcamento', fn ($oq) => $oq->where('empresa_id', $empresa->id))
                      ->orWhereHas('contaFixa', fn ($cq) => $cq->where('empresa_id', $empresa->id));
                })
                ->sum('valor');

            $ticketMedio = $qtdCobrancas > 0 ? round($realizadaTotal / $qtdCobrancas, 2) : 0;

            return (object) [
                'id'                  => $empresa->id,
                'nome'                => $empresa->nome_fantasia ?? $empresa->razao_social,
                'realizada'           => $realizadaTotal,
                'realizada_orcamento' => $realizadaOrcamento,
                'realizada_contrato'  => $realizadaContrato,
                'a_receber'           => (float) $aReceberPeriodo,
                'atrasado'            => (float) $atrasado,
                'receita_anual'       => (float) $receitaAnual,
                'ticket_medio'        => $ticketMedio,
                'qtd_cobrancas'       => $qtdCobrancas,
            ];
        })->filter(fn ($e) => $e->realizada > 0 || $e->a_receber > 0 || $e->atrasado > 0)->values();

        // Calcular % participação de cada empresa no total realizado do período
        $totalRealizadoTodasEmpresas = $receitasPorEmpresa->sum('realizada') ?: 1;
        $receitasPorEmpresa = $receitasPorEmpresa->map(function ($emp) use ($totalRealizadoTodasEmpresas) {
            $emp->percentual = $emp->realizada > 0
                ? round(($emp->realizada / $totalRealizadoTodasEmpresas) * 100, 1)
                : 0;
            return $emp;
        });

        // ================= CUSTO FIXO PRÓXIMO MÊS =================
        $proximoMesRef    = now()->addMonth();
        $inicioProxMes    = $proximoMesRef->copy()->startOfMonth();
        $fimProxMes       = $proximoMesRef->copy()->endOfMonth();
        $nomeProximoMes   = ucfirst($proximoMesRef->translatedFormat('F'));

        // Dias úteis (seg–sex) do próximo mês
        $diasUteisProximoMes = 0;
        $diaIter = $inicioProxMes->copy();
        while ($diaIter->lte($fimProxMes)) {
            if ($diaIter->isWeekday()) {
                $diasUteisProximoMes++;
            }
            $diaIter->addDay();
        }

        // Total global: tudo lançado como a pagar no próximo mês
        $custoFixoProximoMes = ContaPagar::where('status', 'em_aberto')
            ->whereDate('data_vencimento', '>=', $inicioProxMes->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fimProxMes->format('Y-m-d'))
            ->sum('valor');

        $ticketMedioCustoFixoGlobal = $diasUteisProximoMes > 0
            ? round((float) $custoFixoProximoMes / $diasUteisProximoMes, 2)
            : 0;

        // Por empresa (via centro de custo → empresa_id)
        $custoFixoProximoMesPorEmpresa = ContaPagar::where('status', 'em_aberto')
            ->whereDate('data_vencimento', '>=', $inicioProxMes->format('Y-m-d'))
            ->whereDate('data_vencimento', '<=', $fimProxMes->format('Y-m-d'))
            ->whereHas('centroCusto')
            ->with('centroCusto:id,nome,empresa_id')
            ->get(['id', 'centro_custo_id', 'valor'])
            ->groupBy(fn ($cp) => $cp->centroCusto?->empresa_id)
            ->filter(fn ($group, $empresaId) => $empresaId)
            ->map(function ($group, $empresaId) use ($diasUteisProximoMes, $todasEmpresasAtivas) {
                $total   = (float) $group->sum('valor');
                $empresa = $todasEmpresasAtivas->firstWhere('id', $empresaId);
                return (object) [
                    'nome'             => $empresa?->nome_fantasia ?? $empresa?->razao_social ?? '—',
                    'custo_fixo'       => $total,
                    'ticket_medio_dia' => $diasUteisProximoMes > 0 ? round($total / $diasUteisProximoMes, 2) : 0,
                ];
            })
            ->sortByDesc('custo_fixo')
            ->values();

        // Cartões de crédito para o flip card do Saldo em Bancos
        $cartoesCredito = ContaFinanceira::where('tipo', 'credito')
            ->where('ativo', true)
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->with('empresa')
            ->orderBy('empresa_id')
            ->orderBy('nome')
            ->get()
            ->map(function ($cartao) {
                $parcelasAberto = ContaPagar::where('cartao_credito_id', $cartao->id)
                    ->where('status', '!=', 'pago')
                    ->count();

                return (object) [
                    'id'                  => $cartao->id,
                    'nome'                => $cartao->nome,
                    'empresa'             => $cartao->empresa
                        ? ($cartao->empresa->nome_fantasia ?? $cartao->empresa->razao_social)
                        : '—',
                    'bandeira'            => $cartao->bandeira,
                    'limite_total'        => (float) $cartao->limite_credito,
                    'limite_utilizado'    => (float) $cartao->limite_credito_utilizado,
                    'limite_disponivel'   => $cartao->limite_disponivel,
                    'parcelas_em_aberto'  => $parcelasAberto,
                    'melhor_dia_compra'   => $cartao->melhor_dia_compra,
                    'dia_vencimento_fatura' => $cartao->dia_vencimento_fatura,
                ];
            });

        // Flip card: resumo de hoje por empresa (a receber e a pagar no dia)
        $hojeResumoPorEmpresa = Empresa::where('ativo', true)
            ->orderBy('nome_fantasia')
            ->get()
            ->map(function ($empresa) {
                $aReceberHoje = Cobranca::where('status', '!=', 'pago')
                    ->whereDate('data_vencimento', now()->toDateString())
                    ->where(function ($q) use ($empresa) {
                        $q->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresa->id))
                          ->orWhereHas('contaFixa', fn($cq) => $cq->where('empresa_id', $empresa->id));
                    })
                    ->sum('valor');

                $aPagarHoje = ContaPagar::whereIn('status', ['em_aberto', 'atrasado', 'pendente'])
                    ->whereDate('data_vencimento', now()->toDateString())
                    ->whereHas('orcamento', fn($oq) => $oq->where('empresa_id', $empresa->id))
                    ->sum('valor');

                return [
                    'id'        => $empresa->id,
                    'nome'      => $empresa->nome_fantasia ?? $empresa->razao_social,
                    'a_receber' => (float) $aReceberHoje,
                    'a_pagar'   => (float) $aPagarHoje,
                    'saldo'     => (float) ($aReceberHoje - $aPagarHoje),
                ];
            });

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
            'filtroRapido' => $filtroRapido,
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
            'receitasAtrasadas' => $receitasAtrasadas,
            'despesasAtrasadas' => $despesasAtrasadas,
            'saldoSituacao' => $saldoSituacao,
            // Novos indicadores
            'percentualRendaComprometida' => $percentualRendaComprometida,
            'ticketMedioDespesas' => $ticketMedioDespesas,
            'ticketMedioDespesasFixas' => $ticketMedioDespesasFixas,
            'ticketMedioDespesasVariaveis' => $ticketMedioDespesasVariaveis,
            'custoFixo' => $custoFixo,
            'custoVariavel' => $custoVariavel,
            'percentualFixo' => $percentualFixo,
            'percentualVariavel' => $percentualVariavel,
            'orcado' => $orcado,
            'realizadoMeta' => $realizado,
            'diferencaMeta' => $diferencaMeta,
            'percentualMeta' => $percentualMeta,
            'alertasFinanceiros' => $alertasFinanceiros,
            // Custo fixo próximo mês
            'custoFixoProximoMes' => $custoFixoProximoMes,
            'ticketMedioCustoFixoGlobal' => $ticketMedioCustoFixoGlobal,
            'custoFixoProximoMesPorEmpresa' => $custoFixoProximoMesPorEmpresa,
            'diasUteisProximoMes' => $diasUteisProximoMes,
            'nomeProximoMes' => $nomeProximoMes,
            // Lançamentos para modal
            'lancamentosReceita' => $lancamentosReceita,
            'lancamentosDespesa' => $lancamentosDespesa,
            'lancamentosPrevistoReceber' => $lancamentosPrevistoReceber,
            'lancamentosPrevistoPagar' => $lancamentosPrevistoPagar,
            'lancamentosAtrasado' => $lancamentosAtrasado,
            'lancamentosReceitasAtrasadas' => $lancamentosReceitasAtrasadas,
            'hojeResumoPorEmpresa' => $hojeResumoPorEmpresa,
            'cartoesCredito' => $cartoesCredito,
            'receitasPorEmpresa' => $receitasPorEmpresa,
        ]);
    }
}
