<?php

namespace App\Repositories\Eloquent;

use App\Models\Cobranca;
use App\Repositories\Interfaces\CobrancaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CobrancaRepository implements CobrancaRepositoryInterface
{
    private Cobranca $model;

    public function __construct(Cobranca $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?Cobranca
    {
        return $this->model->with(['orcamento', 'orcamento.cliente'])->find($id);
    }

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = $this->model->with(['orcamento', 'orcamento.cliente']);

        $query = $this->aplicarFiltros($query, $filtros);

        $ordenacao = $filtros['ordenacao'] ?? 'data_vencimento';
        $direcao = $filtros['direcao'] ?? 'asc';

        return $query->orderBy($ordenacao, $direcao)->paginate($filtros['por_pagina'] ?? 15);
    }

    public function listarPorOrcamento(int $orcamentoId): LengthAwarePaginator
    {
        return $this->model->where('orcamento_id', $orcamentoId)
            ->orderByDesc('data_vencimento')
            ->paginate(15);
    }

    public function listarPorStatus(string $status): LengthAwarePaginator
    {
        return $this->model->with(['orcamento', 'orcamento.cliente'])
            ->where('status', $status)
            ->orderBy('data_vencimento')
            ->paginate(15);
    }

    public function listarVencidas(): LengthAwarePaginator
    {
        return $this->model->with(['orcamento', 'orcamento.cliente'])
            ->where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->orderBy('data_vencimento')
            ->paginate(15);
    }

    public function listarVenceHoje(): LengthAwarePaginator
    {
        return $this->model->with(['orcamento', 'orcamento.cliente'])
            ->where('status', '!=', 'pago')
            ->whereDate('data_vencimento', now()->toDateString())
            ->orderBy('data_vencimento')
            ->paginate(15);
    }

    public function listarPendentes(): LengthAwarePaginator
    {
        return $this->model->with(['orcamento', 'orcamento.cliente'])
            ->where('status', 'pendente')
            ->orderBy('data_vencimento')
            ->paginate(15);
    }

    private function aplicarFiltros($query, array $filtros): \Illuminate\Database\Eloquent\Builder
    {
        if (! empty($filtros['search'])) {
            $search = '%'.$filtros['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('descricao', 'like', $search)
                    ->orWhereHas('orcamento', fn ($o) => $o->where('numero_orcamento', 'like', $search))
                    ->orWhereHas('orcamento.cliente', fn ($c) => $c->where('nome_fantasia', 'like', $search));
            });
        }

        if (! empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (! empty($filtros['data_vencimento_inicio'])) {
            $query->whereDate('data_vencimento', '>=', $filtros['data_vencimento_inicio']);
        }

        if (! empty($filtros['data_vencimento_fim'])) {
            $query->whereDate('data_vencimento', '<=', $filtros['data_vencimento_fim']);
        }

        if (! empty($filtros['valor_min'])) {
            $query->where('valor', '>=', $filtros['valor_min']);
        }

        if (! empty($filtros['valor_max'])) {
            $query->where('valor', '<=', $filtros['valor_max']);
        }

        return $query;
    }
}
