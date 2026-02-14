<?php

namespace App\Repositories\Eloquent;

use App\Models\ContaPagar;
use App\Repositories\Interfaces\ContaPagarRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContaPagarRepository implements ContaPagarRepositoryInterface
{
    protected $model;

    public function __construct(ContaPagar $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?ContaPagar
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function create(array $data): ContaPagar
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?ContaPagar
    {
        $registro = $this->findById($id);
        if ($registro) {
            $registro->update($data);
        }
        return $registro;
    }

    public function delete(int $id): bool
    {
        $registro = $this->findById($id);
        return $registro ? $registro->delete() : false;
    }

    public function findWithRelations(int $id): ?ContaPagar
    {
        return $this->model->with(['fornecedor', 'centroCusto', 'conta', 'contaFinanceira', 'anexos'])->find($id);
    }

    public function findByFornecedor(int $fornecedorId): Collection
    {
        return $this->model->where('fornecedor_id', $fornecedorId)->get();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function findVencidas(): Collection
    {
        return $this->model->where('status', '!=', 'pago')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->get();
    }

    public function findByPeriodo(string $inicio, string $fim): Collection
    {
        return $this->model->whereDate('data_vencimento', '>=', $inicio)
            ->whereDate('data_vencimento', '<=', $fim)
            ->get();
    }

    public function somaPorStatus(string $status): float
    {
        return (float) $this->model->where('status', $status)->sum('valor');
    }

    public function somaTotal(): float
    {
        return (float) $this->model->sum('valor');
    }

    public function queryBase()
    {
        return $this->model->query();
    }

    public function applyFiltros($query, array $filtros)
    {
        if (!empty($filtros['search'])) {
            $searchTerm = '%' . $filtros['search'] . '%';
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

        if (!empty($filtros['centro_custo_id'])) {
            $query->where('centro_custo_id', $filtros['centro_custo_id']);
        }

        if (!empty($filtros['categoria_id'])) {
            $query->whereHas('conta', function ($q) use ($filtros) {
                $q->where('categoria_id', $filtros['categoria_id']);
            });
        }

        if (!empty($filtros['subcategoria_id'])) {
            $query->whereHas('conta', function ($q) use ($filtros) {
                $q->where('subcategoria_id', $filtros['subcategoria_id']);
            });
        }

        if (!empty($filtros['conta_id'])) {
            $query->where('conta_id', $filtros['conta_id']);
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
        }

        return $query;
    }
}
