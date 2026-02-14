<?php

namespace App\Services\Financeiro;

use App\Models\Cobranca;
use App\Models\ContaFinanceira;
use App\Models\MovimentacaoFinanceira;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContaReceberService
{
    public function listar(array $filtros): LengthAwarePaginator
    {
        $vencimentoInicio = $filtros['vencimento_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
        $vencimentoFim = $filtros['vencimento_fim'] ?? now()->endOfMonth()->format('Y-m-d');

        $query = Cobranca::with(['cliente', 'orcamento', 'contaFinanceira']);

        if (!empty($filtros['search'])) {
            $searchTerm = '%' . $filtros['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('descricao', 'like', $searchTerm)
                    ->orWhereHas('cliente', function ($sq) use ($searchTerm) {
                        $sq->where('nome', 'like', $searchTerm)
                            ->orWhere('cpf_cnpj', 'like', $searchTerm);
                    });
            });
        }

        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        if (!empty($filtros['empresa_id'])) {
            $query->whereHas('orcamento', function ($q) use ($filtros) {
                $q->where('empresa_id', $filtros['empresa_id']);
            });
        }

        if (!empty($filtros['conta_financeira_id'])) {
            $query->where('conta_financeira_id', $filtros['conta_financeira_id']);
        }

        if (!empty($filtros['status'])) {
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
        } else {
            $query->where('status', '!=', 'pago');
        }

        $query->whereDate('data_vencimento', '>=', $vencimentoInicio)
            ->whereDate('data_vencimento', '<=', $vencimentoFim);

        return $query->orderBy('data_vencimento', 'asc')
            ->paginate(15)
            ->appends($filtros);
    }

    public function calcularKPIs(array $filtros = []): array
    {
        return [
            'a_receber' => Cobranca::where('status', 'em_aberto')
                ->whereDate('data_vencimento', '>=', now()->startOfMonth())
                ->sum('valor'),
            'recebido' => Cobranca::where('status', 'pago')
                ->whereDate('data_vencimento', '>=', now()->startOfMonth())
                ->sum('valor'),
            'vencido' => Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', '<', now()->toDateString())
                ->sum('valor'),
            'recebe_hoje' => Cobranca::where('status', 'em_aberto')
                ->whereDate('data_vencimento', now()->toDateString())
                ->sum('valor'),
        ];
    }

    public function contarPorStatus(): array
    {
        return [
            'em_aberto' => Cobranca::where('status', 'em_aberto')->count(),
            'vencido' => Cobranca::where('status', '!=', 'pago')
                ->where('data_vencimento', '<', now()->toDateString())->count(),
            'pago' => Cobranca::where('status', 'pago')->count(),
        ];
    }

    public function receber(array $cobrancaIds, array $dadosRecebimento): void
    {
        DB::transaction(function () use ($cobrancaIds, $dadosRecebimento) {
            foreach ($cobrancaIds as $id) {
                $cobranca = Cobranca::find($id);
                if (!$cobranca || $cobranca->status === 'pago') {
                    continue;
                }

                $cobranca->update([
                    'status' => 'pago',
                    'data_pagamento' => $dadosRecebimento['data_pagamento'],
                    'conta_financeira_id' => $dadosRecebimento['conta_financeira_id'],
                    'forma_pagamento' => $dadosRecebimento['forma_pagamento'],
                    'user_id' => Auth::id(),
                ]);

                if ($dadosRecebimento['conta_financeira_id']) {
                    $contaFinanceira = ContaFinanceira::find($dadosRecebimento['conta_financeira_id']);
                    if ($contaFinanceira) {
                        $contaFinanceira->increment('saldo', $cobranca->valor);

                        MovimentacaoFinanceira::create([
                            'conta_origem_id' => null,
                            'conta_destino_id' => $dadosRecebimento['conta_financeira_id'],
                            'tipo' => 'entrada',
                            'valor' => $cobranca->valor,
                            'saldo_resultante' => $contaFinanceira->saldo,
                            'observacao' => 'Recebimento de cobrança ID ' . $cobranca->id,
                            'user_id' => Auth::id(),
                            'data_movimentacao' => $dadosRecebimento['data_pagamento'],
                        ]);
                    }
                }
            }
        });
    }

    public function receberUma(Cobranca $cobranca, array $dados): void
    {
        DB::transaction(function () use ($cobranca, $dados) {
            $cobranca->update([
                'status' => 'pago',
                'data_pagamento' => $dados['data_pagamento'],
                'conta_financeira_id' => $dados['conta_financeira_id'],
                'forma_pagamento' => $dados['forma_pagamento'],
                'user_id' => Auth::id(),
            ]);

            if ($dados['conta_financeira_id']) {
                $contaFinanceira = ContaFinanceira::find($dados['conta_financeira_id']);
                if ($contaFinanceira) {
                    $contaFinanceira->increment('saldo', $cobranca->valor);

                    MovimentacaoFinanceira::create([
                        'conta_origem_id' => null,
                        'conta_destino_id' => $dados['conta_financeira_id'],
                        'tipo' => 'entrada',
                        'valor' => $cobranca->valor,
                        'saldo_resultante' => $contaFinanceira->saldo,
                        'observacao' => 'Recebimento: ' . $cobranca->descricao,
                        'user_id' => Auth::id(),
                        'data_movimentacao' => $dados['data_pagamento'],
                    ]);
                }
            }
        });
    }

    public function estornar(Cobranca $cobranca): void
    {
        DB::transaction(function () use ($cobranca) {
            $contaFinanceiraId = $cobranca->conta_financeira_id;
            $valor = $cobranca->valor;

            $cobranca->update([
                'status' => 'em_aberto',
                'data_pagamento' => null,
                'conta_financeira_id' => null,
                'forma_pagamento' => null,
            ]);

            if ($contaFinanceiraId) {
                $contaFinanceira = ContaFinanceira::find($contaFinanceiraId);
                if ($contaFinanceira) {
                    $contaFinanceira->decrement('saldo', $valor);

                    MovimentacaoFinanceira::create([
                        'conta_origem_id' => $contaFinanceiraId,
                        'conta_destino_id' => null,
                        'tipo' => 'saida',
                        'valor' => $valor,
                        'saldo_resultante' => $contaFinanceira->saldo,
                        'observacao' => 'Estorno de cobrança ID ' . $cobranca->id,
                        'user_id' => Auth::id(),
                        'data_movimentacao' => now(),
                    ]);
                }
            }
        });
    }

    public function excluir(int $id): bool
    {
        $cobranca = Cobranca::find($id);
        return $cobranca ? $cobranca->delete() : false;
    }

    public function buscarPorId(int $id): ?Cobranca
    {
        return Cobranca::with(['cliente', 'orcamento', 'contaFinanceira', 'anexos'])->find($id);
    }

    public function criar(array $data): Cobranca
    {
        return Cobranca::create($data);
    }

    public function atualizar(int $id, array $data): ?Cobranca
    {
        $cobranca = Cobranca::find($id);
        if ($cobranca) {
            $cobranca->update($data);
        }
        return $cobranca;
    }
}
