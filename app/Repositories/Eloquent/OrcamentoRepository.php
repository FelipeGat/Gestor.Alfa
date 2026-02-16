<?php

namespace App\Repositories\Eloquent;

use App\Models\Orcamento;
use App\Repositories\Interfaces\OrcamentoRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class OrcamentoRepository implements OrcamentoRepositoryInterface
{
    private Orcamento $model;

    public function __construct(Orcamento $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?Orcamento
    {
        return $this->model->with(['empresa', 'cliente', 'preCliente', 'itens'])->find($id);
    }

    public function findByNumero(string $numero): ?Orcamento
    {
        return $this->model->where('numero_orcamento', $numero)->first();
    }

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = $this->model->with(['empresa', 'cliente', 'preCliente']);

        $query = $this->aplicarFiltros($query, $filtros);

        $ordenacao = $filtros['ordenacao'] ?? 'created_at';
        $direcao = $filtros['direcao'] ?? 'desc';

        return $query->orderBy($ordenacao, $direcao)->paginate($filtros['por_pagina'] ?? 15);
    }

    public function listarPorEmpresa(int $empresaId, array $filtros = []): LengthAwarePaginator
    {
        $filtros['empresa_id'] = $empresaId;

        return $this->listar($filtros);
    }

    public function listarPorCliente(int $clienteId): LengthAwarePaginator
    {
        return $this->model->with(['empresa'])
            ->where('cliente_id', $clienteId)
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function listarPorStatus(string $status): LengthAwarePaginator
    {
        return $this->model->with(['empresa', 'cliente'])
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function gerarNumero(int $empresaId): string
    {
        return Orcamento::gerarNumero($empresaId);
    }

    private $fillable = [
        'empresa_id',
        'atendimento_id',
        'cliente_id',
        'pre_cliente_id',
        'numero_orcamento',
        'status',
        'descricao',
        'valor_total',
        'desconto',
        'taxas',
        'descricao_taxas',
        'forma_pagamento',
        'prazo_pagamento',
        'validade',
        'observacoes',
        'created_by',
    ];

    private function aplicarFiltros($query, array $filtros): \Illuminate\Database\Eloquent\Builder
    {
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

        return $query;
    }
}
