<?php

namespace App\Services\Financeiro;

use App\Models\ContaFinanceira;
use App\Models\ContaPagar;
use App\Models\MovimentacaoFinanceira;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContaPagarService
{
    public function listar(array $filtros): LengthAwarePaginator
    {
        $vencimentoInicio = $filtros['vencimento_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $vencimentoFim = $filtros['vencimento_fim'] ?? now()->endOfMonth()->format('Y-m-d');

        $query = ContaPagar::with(['fornecedor', 'centroCusto', 'conta']);

        if (! empty($filtros['search'])) {
            $searchTerm = '%'.$filtros['search'].'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('descricao', 'like', $searchTerm)
                    ->orWhereHas('fornecedor', function ($sq) use ($searchTerm) {
                        $sq->where('razao_social', 'like', $searchTerm)
                            ->orWhere('nome_fantasia', 'like', $searchTerm);
                    })
                    ->orWhereHas('centroCusto', function ($sq) use ($searchTerm) {
                        $sq->where('nome', 'like', $searchTerm);
                    });
            });
        }

        if (! empty($filtros['centro_custo_id'])) {
            $query->where('centro_custo_id', $filtros['centro_custo_id']);
        }

        if (! empty($filtros['categoria_id'])) {
            $query->whereHas('conta', function ($q) use ($filtros) {
                $q->where('categoria_id', $filtros['categoria_id']);
            });
        }

        if (! empty($filtros['subcategoria_id'])) {
            $query->whereHas('conta', function ($q) use ($filtros) {
                $q->where('subcategoria_id', $filtros['subcategoria_id']);
            });
        }

        if (! empty($filtros['conta_id'])) {
            $query->where('conta_id', $filtros['conta_id']);
        }

        if (! empty($filtros['status'])) {
            $status = $filtros['status'];
            if (is_array($status)) {
                $query->where(function ($q) use ($status) {
                    foreach ($status as $s) {
                        if ($s === 'vencido') {
                            $q->orWhere(function ($sq) {
                                $sq->where('status', '!=', 'pago')
                                    ->whereDate('data_vencimento', '<', now()->toDateString());
                            });
                        } else {
                            $q->orWhere('status', $s);
                        }
                    }
                });
            } else {
                $query->where('status', $status);
            }
        }

        $query->whereDate('data_vencimento', '>=', $vencimentoInicio)
            ->whereDate('data_vencimento', '<=', $vencimentoFim);

        if (empty($filtros['status'])) {
            $query->where('status', '!=', 'pago');
        }

        return $query->orderBy('data_vencimento', 'asc')
            ->paginate(15)
            ->appends($filtros);
    }

    public function calcularKPIs(array $filtros = []): array
    {
        $periodoCustomizado = ! empty($filtros['vencimento_inicio']) || ! empty($filtros['vencimento_fim']);

        $vencimentoInicio = $filtros['vencimento_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $vencimentoFim = $filtros['vencimento_fim'] ?? now()->endOfMonth()->format('Y-m-d');

        $dataVenceHoje = $periodoCustomizado ? $vencimentoInicio : now()->toDateString();

        $buildQuery = function ($status, $dataVencimento = null) use ($filtros, $vencimentoInicio, $vencimentoFim, $dataVenceHoje) {
            $q = ContaPagar::query();

            if (! empty($filtros['centro_custo_id'])) {
                $q->where('centro_custo_id', $filtros['centro_custo_id']);
            }

            if (! empty($filtros['categoria_id'])) {
                $q->whereHas('conta', function ($sq) use ($filtros) {
                    $sq->where('categoria_id', $filtros['categoria_id']);
                });
            }

            if (! empty($filtros['subcategoria_id'])) {
                $q->whereHas('conta', function ($sq) use ($filtros) {
                    $sq->where('subcategoria_id', $filtros['subcategoria_id']);
                });
            }

            if (! empty($filtros['conta_id'])) {
                $q->where('conta_id', $filtros['conta_id']);
            }

            if ($status === 'a_pagar') {
                $q->where('status', 'em_aberto');
            } elseif ($status === 'pago') {
                $q->where('status', 'pago');
            } elseif ($status === 'vencido') {
                $q->where('status', '!=', 'pago')
                    ->whereDate('data_vencimento', '<', now()->toDateString());
            } elseif ($status === 'vence_hoje') {
                $q->where('status', 'em_aberto')
                    ->whereDate('data_vencimento', $dataVenceHoje);
            }

            if ($dataVencimento === 'periodo') {
                $q->whereDate('data_vencimento', '>=', $vencimentoInicio)
                    ->whereDate('data_vencimento', '<=', $vencimentoFim);
            }

            return $q->sum('valor');
        };

        return [
            'a_pagar' => $buildQuery('a_pagar', 'periodo'),
            'pago' => $buildQuery('pago', 'periodo'),
            'vencido' => $buildQuery('vencido', null),
            'vence_hoje' => $buildQuery('vence_hoje', 'periodo'),
        ];
    }

    public function contarPorStatus(array $filtros = []): array
    {
        $buildQuery = function ($status) use ($filtros) {
            $q = ContaPagar::query();

            if (! empty($filtros['centro_custo_id'])) {
                $q->where('centro_custo_id', $filtros['centro_custo_id']);
            }

            if (! empty($filtros['categoria_id'])) {
                $q->whereHas('conta', function ($sq) use ($filtros) {
                    $sq->where('categoria_id', $filtros['categoria_id']);
                });
            }

            if (! empty($filtros['subcategoria_id'])) {
                $q->whereHas('conta', function ($sq) use ($filtros) {
                    $sq->where('subcategoria_id', $filtros['subcategoria_id']);
                });
            }

            if (! empty($filtros['conta_id'])) {
                $q->where('conta_id', $filtros['conta_id']);
            }

            if ($status === 'em_aberto') {
                $q->where('status', 'em_aberto');
            } elseif ($status === 'vencido') {
                $q->where('status', '!=', 'pago')
                    ->whereDate('data_vencimento', '<', now()->toDateString());
            } elseif ($status === 'pago') {
                $q->where('status', 'pago');
            }

            return $q->count();
        };

        return [
            'em_aberto' => $buildQuery('em_aberto'),
            'vencido' => $buildQuery('vencido'),
            'pago' => $buildQuery('pago'),
        ];
    }

    public function criar(array $data): ContaPagar
    {
        $conta = ContaPagar::create($data);
        $this->limparCache();

        return $conta;
    }

    public function atualizar(int $id, array $data): ?ContaPagar
    {
        $conta = ContaPagar::find($id);
        if ($conta) {
            $conta->update($data);
            $this->limparCache();
        }

        return $conta;
    }

    public function excluir(int $id): bool
    {
        $conta = ContaPagar::find($id);
        if ($conta && $conta->delete()) {
            $this->limparCache();

            return true;
        }

        return false;
    }

    public function buscarPorId(int $id): ?ContaPagar
    {
        return ContaPagar::with(['fornecedor', 'centroCusto', 'conta', 'contaFinanceira', 'anexos'])->find($id);
    }

    public function pagar(array $contaIds, array $dadosPagamento): void
    {
        $pagamentosRealizados = false;

        DB::transaction(function () use ($contaIds, $dadosPagamento, &$pagamentosRealizados) {
            foreach ($contaIds as $id) {
                $conta = ContaPagar::find($id);
                if (! $conta || $conta->status === 'pago') {
                    continue;
                }

                $conta->update([
                    'status' => 'pago',
                    'pago_em' => $dadosPagamento['data_pagamento'],
                    'data_pagamento' => $dadosPagamento['data_pagamento'],
                    'conta_financeira_id' => $dadosPagamento['conta_financeira_id'],
                    'forma_pagamento' => $dadosPagamento['forma_pagamento'],
                    'user_id' => Auth::id(),
                ]);

                $pagamentosRealizados = true;

                if ($dadosPagamento['conta_financeira_id']) {
                    $contaFinanceira = ContaFinanceira::find($dadosPagamento['conta_financeira_id']);
                    if ($contaFinanceira) {
                        $contaFinanceira->decrement('saldo', $conta->valor);

                        MovimentacaoFinanceira::create([
                            'conta_origem_id' => $dadosPagamento['conta_financeira_id'],
                            'conta_destino_id' => null,
                            'tipo' => 'saida',
                            'valor' => $conta->valor,
                            'saldo_resultante' => $contaFinanceira->saldo,
                            'observacao' => 'Pagamento de conta ID '.$conta->id,
                            'user_id' => Auth::id(),
                            'data_movimentacao' => $dadosPagamento['data_pagamento'],
                        ]);
                    }
                }
            }
        });

        if ($pagamentosRealizados) {
            $this->limparCache();
        }
    }

    public function marcarComoPago(ContaPagar $conta, array $dados): void
    {
        DB::transaction(function () use ($conta, $dados) {
            $conta->update([
                'status' => 'pago',
                'pago_em' => $dados['data_pagamento'],
                'data_pagamento' => $dados['data_pagamento'],
                'valor' => $dados['valor_total'],
                'juros_multa' => $dados['juros_multa'] ?? 0,
                'forma_pagamento' => $dados['forma_pagamento'],
                'conta_financeira_id' => $dados['conta_financeira_id'],
                'user_id' => Auth::id(),
            ]);

            if ($dados['conta_financeira_id']) {
                $contaFinanceira = ContaFinanceira::find($dados['conta_financeira_id']);
                if ($contaFinanceira) {
                    $contaFinanceira->decrement('saldo', $dados['valor_pago']);

                    MovimentacaoFinanceira::create([
                        'conta_origem_id' => $dados['conta_financeira_id'],
                        'conta_destino_id' => null,
                        'tipo' => 'saida',
                        'valor' => $dados['valor_pago'],
                        'saldo_resultante' => $contaFinanceira->saldo,
                        'observacao' => 'Pagamento de conta: '.$conta->descricao,
                        'user_id' => Auth::id(),
                        'data_movimentacao' => $dados['data_pagamento'],
                    ]);
                }
            }
        });
    }

    public function podeSerPaga(ContaPagar $conta): bool
    {
        if ($conta->conta_fixa_pagar_id) {
            $parcelaAnteriorNaoPaga = ContaPagar::where('conta_fixa_pagar_id', $conta->conta_fixa_pagar_id)
                ->where('status', '!=', 'pago')
                ->where('data_vencimento', '<', $conta->data_vencimento)
                ->exists();

            if ($parcelaAnteriorNaoPaga) {
                return false;
            }
        }

        return true;
    }

    private function limparCache(): void
    {
        $hoje = date('Y-m-d');
        Cache::forget('contas_pagar_kpis_'.$hoje);
        Cache::forget('contas_pagar_contadores_'.$hoje);
    }
}
