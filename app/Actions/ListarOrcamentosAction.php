<?php

namespace App\Actions;

use App\Models\Orcamento;
use Illuminate\Pagination\LengthAwarePaginator;

class ListarOrcamentosAction
{
    public function execute(array $filtros): LengthAwarePaginator
    {
        $query = Orcamento::with(['empresa', 'cliente', 'preCliente', 'centroCusto']);

        if (! empty($filtros['search'])) {
            $search = '%'.$filtros['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_orcamento', 'like', $search)
                    ->orWhere('descricao', 'like', $search)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nome_fantasia', 'like', $search))
                    ->orWhereHas('preCliente', fn ($p) => $p->where('nome_fantasia', 'like', $search));
            });
        }

        if (! empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (! empty($filtros['empresa_id'])) {
            $query->where('empresa_id', $filtros['empresa_id']);
        }

        if (! empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        if (! empty($filtros['centro_custo_id'])) {
            $query->where('centro_custo_id', $filtros['centro_custo_id']);
        }

        if (! empty($filtros['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['data_inicio']);
        }

        if (! empty($filtros['data_fim'])) {
            $query->whereDate('created_at', '<=', $filtros['data_fim']);
        }

        if (! empty($filtros['valor_min'])) {
            $query->where('valor_total', '>=', $filtros['valor_min']);
        }

        if (! empty($filtros['valor_max'])) {
            $query->where('valor_total', '<=', $filtros['valor_max']);
        }

        $ordenacao = $filtros['ordenacao'] ?? 'created_at';
        $direcao = $filtros['direcao'] ?? 'desc';

        $allowedColumns = ['id', 'numero_orcamento', 'valor_total', 'status', 'created_at', 'updated_at'];

        if (! in_array($ordenacao, $allowedColumns)) {
            $ordenacao = 'created_at';
        }

        return $query->orderBy($ordenacao, $direcao)->paginate($filtros['por_pagina'] ?? 15);
    }
}
