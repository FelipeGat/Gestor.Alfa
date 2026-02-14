<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Cobranca;
use App\Models\ContaPagar;
use App\Models\Empresa;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\Fornecedor;

class RelatorioFinanceiroController extends Controller
{
    public function contasReceberPagar(Request $request)
    {
        $empresaId = $request->empresa_id;
        $centroCustoId = $request->centro_custo_id;
        $clienteId = $request->cliente_id;
        $fornecedorId = $request->fornecedor_id;
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;
        $status = $request->status;
        $hoje = Carbon::today();

        if (!$dataInicio && !$dataFim) {
            $dataInicio = Carbon::now()->startOfMonth()->toDateString();
            $dataFim = Carbon::now()->endOfMonth()->toDateString();
        }

        $applyStatusReceber = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $applyStatusPagar = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $queryReceber = Cobranca::with([
            'cliente',
            'orcamento.empresa',
            'orcamento.centroCusto',
            'contaFixa.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFixa', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, function ($q) use ($centroCustoId) {
                $q->whereHas('orcamento', function ($oq) use ($centroCustoId) {
                    $oq->where('centro_custo_id', $centroCustoId);
                });
            })
            ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
            ->when($status, function ($q) use ($applyStatusReceber) {
                $applyStatusReceber($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $dataFim));

        $totalReceber = (clone $queryReceber)->sum('valor');

        if ($request->get('per_page') === 'all') {
            $contasReceber = $queryReceber
                ->orderBy('data_vencimento', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $contasReceber = $queryReceber
                ->orderBy('data_vencimento', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(15, ['*'], 'receber_page')
                ->withQueryString();
        }

        $queryPagar = ContaPagar::with([
            'fornecedor',
            'centroCusto',
            'orcamento.empresa',
            'contaFinanceira.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFinanceira', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, fn($q) => $q->where('centro_custo_id', $centroCustoId))
            ->when($fornecedorId, fn($q) => $q->where('fornecedor_id', $fornecedorId))
            ->when($status, function ($q) use ($applyStatusPagar) {
                $applyStatusPagar($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $dataFim));

        $totalPagar = (clone $queryPagar)->sum('valor');

        if ($request->get('per_page') === 'all') {
            $contasPagar = $queryPagar
                ->orderBy('data_vencimento', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $contasPagar = $queryPagar
                ->orderBy('data_vencimento', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(15, ['*'], 'pagar_page')
                ->withQueryString();
        }

        $resultado = $totalReceber - $totalPagar;
        $impressao = $request->get('impressao') == '1';

        $empresas = Empresa::orderBy('nome_fantasia')->orderBy('razao_social')->get();
        $centrosCusto = CentroCusto::orderBy('nome')->get();
        $clientes = Cliente::orderBy('nome_fantasia')->orderBy('razao_social')->orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->orderBy('razao_social')->get();

        return view('relatorios.relatorio-contas-receber-pagar', compact(
            'contasReceber',
            'contasPagar',
            'totalReceber',
            'totalPagar',
            'resultado',
            'dataInicio',
            'dataFim',
            'empresas',
            'centrosCusto',
            'clientes',
            'fornecedores',
            'impressao'
        ));
    }

    /**
     * Retorna os dados do relatório em formato JSON para impressão completa
     */
    public function contasReceberPagarJson(Request $request)
    {
        $empresaId = $request->empresa_id;
        $centroCustoId = $request->centro_custo_id;
        $clienteId = $request->cliente_id;
        $fornecedorId = $request->fornecedor_id;
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;
        $status = $request->status;
        $hoje = Carbon::today();

        if (!$dataInicio && !$dataFim) {
            $dataInicio = Carbon::now()->startOfMonth()->toDateString();
            $dataFim = Carbon::now()->endOfMonth()->toDateString();
        }

        $applyStatusReceber = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $applyStatusPagar = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $queryReceber = Cobranca::with([
            'cliente',
            'orcamento.empresa',
            'orcamento.centroCusto',
            'contaFixa.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFixa', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, function ($q) use ($centroCustoId) {
                $q->whereHas('orcamento', function ($oq) use ($centroCustoId) {
                    $oq->where('centro_custo_id', $centroCustoId);
                });
            })
            ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
            ->when($status, function ($q) use ($applyStatusReceber) {
                $applyStatusReceber($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $dataFim));

        $totalReceber = (clone $queryReceber)->sum('valor');

        $contasReceber = $queryReceber
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $queryPagar = ContaPagar::with([
            'fornecedor',
            'centroCusto',
            'orcamento.empresa',
            'contaFinanceira.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFinanceira', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, fn($q) => $q->where('centro_custo_id', $centroCustoId))
            ->when($fornecedorId, fn($q) => $q->where('fornecedor_id', $fornecedorId))
            ->when($status, function ($q) use ($applyStatusPagar) {
                $applyStatusPagar($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $dataFim));

        $totalPagar = (clone $queryPagar)->sum('valor');

        $contasPagar = $queryPagar
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $resultado = $totalReceber - $totalPagar;

        // Formatar os dados para JSON
        $contasReceberFormatadas = $contasReceber->map(function ($conta) {
            return [
                'empresa' => $conta->empresaRelacionada?->nome_fantasia ?? $conta->empresaRelacionada?->razao_social ?? '-',
                'cliente' => $conta->cliente?->nome_fantasia ?? $conta->cliente?->razao_social ?? $conta->cliente?->nome ?? '-',
                'data_vencimento' => $conta->data_vencimento?->format('d/m/Y') ?? '-',
                'valor' => $conta->valor,
                'valor_formatado' => 'R$ ' . number_format($conta->valor, 2, ',', '.'),
                'status' => $conta->status ?? '-',
            ];
        });

        $contasPagarFormatadas = $contasPagar->map(function ($conta) {
            return [
                'empresa' => $conta->empresaRelacionada?->nome_fantasia ?? $conta->empresaRelacionada?->razao_social ?? '-',
                'centro_custo' => $conta->centroCusto?->nome ?? '-',
                'fornecedor' => $conta->fornecedor?->nome_fantasia ?? $conta->fornecedor?->razao_social ?? '-',
                'data_vencimento' => $conta->data_vencimento?->format('d/m/Y') ?? '-',
                'valor' => $conta->valor,
                'valor_formatado' => 'R$ ' . number_format($conta->valor, 2, ',', '.'),
                'status' => $conta->status ?? '-',
            ];
        });

        return response()->json([
            'contas_receber' => $contasReceberFormatadas,
            'contas_pagar' => $contasPagarFormatadas,
            'total_receber' => $totalReceber,
            'total_receber_formatado' => 'R$ ' . number_format($totalReceber, 2, ',', '.'),
            'total_pagar' => $totalPagar,
            'total_pagar_formatado' => 'R$ ' . number_format($totalPagar, 2, ',', '.'),
            'resultado' => $resultado,
            'resultado_formatado' => 'R$ ' . number_format($resultado, 2, ',', '.'),
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'data_inicio_formatada' => \Carbon\Carbon::parse($dataInicio)->format('d/m/Y'),
            'data_fim_formatada' => \Carbon\Carbon::parse($dataFim)->format('d/m/Y'),
        ]);
    }

    /**
     * Relatório de Contas a Receber (separado)
     */
    public function contasReceber(Request $request)
    {
        $empresaId = $request->empresa_id;
        $centroCustoId = $request->centro_custo_id;
        $clienteId = $request->cliente_id;
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;
        $status = $request->status;
        $hoje = Carbon::today();

        if (!$dataInicio && !$dataFim) {
            $dataInicio = Carbon::now()->startOfMonth()->toDateString();
            $dataFim = Carbon::now()->endOfMonth()->toDateString();
        }

        $applyStatusReceber = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $queryReceber = Cobranca::with([
            'cliente',
            'orcamento.empresa',
            'orcamento.centroCusto',
            'contaFixa.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFixa', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, function ($q) use ($centroCustoId) {
                $q->whereHas('orcamento', function ($oq) use ($centroCustoId) {
                    $oq->where('centro_custo_id', $centroCustoId);
                });
            })
            ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
            ->when($status, function ($q) use ($applyStatusReceber) {
                $applyStatusReceber($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $dataFim));

        $totalReceber = (clone $queryReceber)->sum('valor');

        $totalizadoresPorEmpresa = (clone $queryReceber)
            ->get()
            ->groupBy(function ($conta) {
                $empresa = $conta->empresaRelacionada;
                return $empresa ? ($empresa->nome_fantasia ?? $empresa->razao_social ?? 'Sem Empresa') : 'Sem Empresa';
            })
            ->map(function ($grouped, $nomeEmpresa) {
                return [
                    'nome' => $nomeEmpresa,
                    'total' => $grouped->sum('valor'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        if ($request->get('per_page') === 'all') {
            $contasReceber = $queryReceber
                ->orderBy('data_vencimento', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $contasReceber = $queryReceber
                ->orderBy('data_vencimento', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString();
        }

        $impressao = $request->get('impressao') == '1';

        $empresas = Empresa::orderBy('nome_fantasia')->orderBy('razao_social')->get();
        $centrosCusto = CentroCusto::orderBy('nome')->get();
        $clientes = Cliente::orderBy('nome_fantasia')->orderBy('razao_social')->orderBy('nome')->get();

        return view('relatorios.relatorio-contas-receber', compact(
            'contasReceber',
            'totalReceber',
            'totalizadoresPorEmpresa',
            'dataInicio',
            'dataFim',
            'empresas',
            'centrosCusto',
            'clientes',
            'impressao'
        ));
    }

    /**
     * Retorna os dados do relatório de Contas a Receber em formato JSON
     */
    public function contasReceberJson(Request $request)
    {
        $empresaId = $request->empresa_id;
        $centroCustoId = $request->centro_custo_id;
        $clienteId = $request->cliente_id;
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;
        $status = $request->status;
        $hoje = Carbon::today();

        if (!$dataInicio && !$dataFim) {
            $dataInicio = Carbon::now()->startOfMonth()->toDateString();
            $dataFim = Carbon::now()->endOfMonth()->toDateString();
        }

        $applyStatusReceber = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $queryReceber = Cobranca::with([
            'cliente',
            'orcamento.empresa',
            'orcamento.centroCusto',
            'contaFixa.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFixa', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, function ($q) use ($centroCustoId) {
                $q->whereHas('orcamento', function ($oq) use ($centroCustoId) {
                    $oq->where('centro_custo_id', $centroCustoId);
                });
            })
            ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
            ->when($status, function ($q) use ($applyStatusReceber) {
                $applyStatusReceber($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $dataFim));

        $totalReceber = (clone $queryReceber)->sum('valor');

        $totalizadoresPorEmpresa = (clone $queryReceber)
            ->get()
            ->groupBy(function ($conta) {
                $empresa = $conta->empresaRelacionada;
                return $empresa ? ($empresa->nome_fantasia ?? $empresa->razao_social ?? 'Sem Empresa') : 'Sem Empresa';
            })
            ->map(function ($grouped, $nomeEmpresa) {
                return [
                    'nome' => $nomeEmpresa,
                    'total' => $grouped->sum('valor'),
                    'total_formatado' => 'R$ ' . number_format($grouped->sum('valor'), 2, ',', '.'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $contasReceber = $queryReceber
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Formatar os dados para JSON
        $contasReceberFormatadas = $contasReceber->map(function ($conta) {
            return [
                'empresa' => $conta->empresaRelacionada?->nome_fantasia ?? $conta->empresaRelacionada?->razao_social ?? '-',
                'cliente' => $conta->cliente?->nome_fantasia ?? $conta->cliente?->razao_social ?? $conta->cliente?->nome ?? '-',
                'data_vencimento' => $conta->data_vencimento?->format('d/m/Y') ?? '-',
                'valor' => $conta->valor,
                'valor_formatado' => 'R$ ' . number_format($conta->valor, 2, ',', '.'),
                'status' => $conta->status ?? '-',
            ];
        });

        return response()->json([
            'contas_receber' => $contasReceberFormatadas,
            'total_receber' => $totalReceber,
            'total_receber_formatado' => 'R$ ' . number_format($totalReceber, 2, ',', '.'),
            'totalizadores_por_empresa' => $totalizadoresPorEmpresa,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'data_inicio_formatada' => \Carbon\Carbon::parse($dataInicio)->format('d/m/Y'),
            'data_fim_formatada' => \Carbon\Carbon::parse($dataFim)->format('d/m/Y'),
        ]);
    }

    /**
     * Relatório de Contas a Pagar (separado)
     */
    public function contasPagar(Request $request)
    {
        $empresaId = $request->empresa_id;
        $centroCustoId = $request->centro_custo_id;
        $fornecedorId = $request->fornecedor_id;
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;
        $status = $request->status;
        $hoje = Carbon::today();

        if (!$dataInicio && !$dataFim) {
            $dataInicio = Carbon::now()->startOfMonth()->toDateString();
            $dataFim = Carbon::now()->endOfMonth()->toDateString();
        }

        $applyStatusPagar = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $queryPagar = ContaPagar::with([
            'fornecedor',
            'centroCusto',
            'orcamento.empresa',
            'contaFinanceira.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFinanceira', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    })->orWhereHas('centroCusto', function ($ccq) use ($empresaId) {
                        $ccq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, fn($q) => $q->where('centro_custo_id', $centroCustoId))
            ->when($fornecedorId, fn($q) => $q->where('fornecedor_id', $fornecedorId))
            ->when($status, function ($q) use ($applyStatusPagar) {
                $applyStatusPagar($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate($status === 'pago' ? 'data_pagamento' : 'data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate($status === 'pago' ? 'data_pagamento' : 'data_vencimento', '<=', $dataFim));

        $totalPagar = (clone $queryPagar)->sum('valor');

        $totalizadoresPorCentro = (clone $queryPagar)
            ->select('centro_custo_id')
            ->selectRaw('SUM(valor) as total')
            ->with('centroCusto')
            ->groupBy('centro_custo_id')
            ->get()
            ->map(function ($item) {
                return [
                    'centro_custo_id' => $item->centro_custo_id,
                    'nome' => $item->centroCusto?->nome ?? 'Sem Centro',
                    'total' => $item->total,
                ];
            })
            ->sortByDesc('total')
            ->values();

        if ($request->get('per_page') === 'all') {
            $contasPagar = $queryPagar
                ->orderBy('data_vencimento', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $contasPagar = $queryPagar
                ->orderBy('data_vencimento', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString();
        }

        $impressao = $request->get('impressao') == '1';

        $empresas = Empresa::orderBy('nome_fantasia')->orderBy('razao_social')->get();
        $centrosCusto = CentroCusto::orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->orderBy('razao_social')->get();

        return view('relatorios.relatorio-contas-pagar', compact(
            'contasPagar',
            'totalPagar',
            'totalizadoresPorCentro',
            'dataInicio',
            'dataFim',
            'empresas',
            'centrosCusto',
            'fornecedores',
            'impressao'
        ));
    }

    /**
     * Retorna os dados do relatório de Contas a Pagar em formato JSON
     */
    public function contasPagarJson(Request $request)
    {
        $empresaId = $request->empresa_id;
        $centroCustoId = $request->centro_custo_id;
        $fornecedorId = $request->fornecedor_id;
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;
        $status = $request->status;
        $hoje = Carbon::today();

        if (!$dataInicio && !$dataFim) {
            $dataInicio = Carbon::now()->startOfMonth()->toDateString();
            $dataFim = Carbon::now()->endOfMonth()->toDateString();
        }

        $applyStatusPagar = function ($q) use ($status, $hoje) {
            switch ($status) {
                case 'pago':
                    $q->where('status', 'pago');
                    break;
                case 'vence_hoje':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', $hoje);
                    break;
                case 'a_vencer':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>', $hoje);
                    break;
                case 'vencido':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '<', $hoje);
                    break;
                case 'em_aberto':
                    $q->where('status', '!=', 'pago')
                        ->whereDate('data_vencimento', '>=', $hoje);
                    break;
            }
        };

        $queryPagar = ContaPagar::with([
            'fornecedor',
            'centroCusto',
            'orcamento.empresa',
            'contaFinanceira.empresa',
        ])
            ->when($empresaId, function ($q) use ($empresaId) {
                $q->where(function ($sq) use ($empresaId) {
                    $sq->whereHas('orcamento', function ($oq) use ($empresaId) {
                        $oq->where('empresa_id', $empresaId);
                    })->orWhereHas('contaFinanceira', function ($cq) use ($empresaId) {
                        $cq->where('empresa_id', $empresaId);
                    })->orWhereHas('centroCusto', function ($ccq) use ($empresaId) {
                        $ccq->where('empresa_id', $empresaId);
                    });
                });
            })
            ->when($centroCustoId, fn($q) => $q->where('centro_custo_id', $centroCustoId))
            ->when($fornecedorId, fn($q) => $q->where('fornecedor_id', $fornecedorId))
            ->when($status, function ($q) use ($applyStatusPagar) {
                $applyStatusPagar($q);
            }, function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->when($dataInicio, fn($q) => $q->whereDate($status === 'pago' ? 'data_pagamento' : 'data_vencimento', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->whereDate($status === 'pago' ? 'data_pagamento' : 'data_vencimento', '<=', $dataFim));

        $totalPagar = (clone $queryPagar)->sum('valor');

        $totalizadoresPorCentro = (clone $queryPagar)
            ->select('centro_custo_id')
            ->selectRaw('SUM(valor) as total')
            ->with('centroCusto')
            ->groupBy('centro_custo_id')
            ->get()
            ->map(function ($item) {
                return [
                    'centro_custo_id' => $item->centro_custo_id,
                    'nome' => $item->centroCusto?->nome ?? 'Sem Centro',
                    'total' => $item->total,
                    'total_formatado' => 'R$ ' . number_format($item->total, 2, ',', '.'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $contasPagar = $queryPagar
            ->orderBy('data_vencimento', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Formatar os dados para JSON
        $contasPagarFormatadas = $contasPagar->map(function ($conta) {
            return [
                'empresa' => $conta->empresaRelacionada?->nome_fantasia ?? $conta->empresaRelacionada?->razao_social ?? '-',
                'centro_custo' => $conta->centroCusto?->nome ?? '-',
                'fornecedor' => $conta->fornecedor?->nome_fantasia ?? $conta->fornecedor?->razao_social ?? '-',
                'data_vencimento' => $conta->data_vencimento?->format('d/m/Y') ?? '-',
                'valor' => $conta->valor,
                'valor_formatado' => 'R$ ' . number_format($conta->valor, 2, ',', '.'),
                'status' => $conta->status ?? '-',
            ];
        });

        return response()->json([
            'contas_pagar' => $contasPagarFormatadas,
            'total_pagar' => $totalPagar,
            'total_pagar_formatado' => 'R$ ' . number_format($totalPagar, 2, ',', '.'),
            'totalizadores_por_centro' => $totalizadoresPorCentro,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'data_inicio_formatada' => \Carbon\Carbon::parse($dataInicio)->format('d/m/Y'),
            'data_fim_formatada' => \Carbon\Carbon::parse($dataFim)->format('d/m/Y'),
        ]);
    }
}
