<?php

namespace App\Repositories\Eloquent;

use App\Models\Cliente;
use App\Repositories\Interfaces\ClienteRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ClienteRepository implements ClienteRepositoryInterface
{
    private Cliente $model;

    public function __construct(Cliente $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?Cliente
    {
        return $this->model->with(['empresa', 'contatos'])->find($id);
    }

    public function findByCpf(string $cpf): ?Cliente
    {
        $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);

        return $this->model->where('cpf', $cpfLimpo)->first();
    }

    public function findByCnpj(string $cnpj): ?Cliente
    {
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        return $this->model->where('cnpj', $cnpjLimpo)->first();
    }

    public function findByEmail(string $email): ?Cliente
    {
        return $this->model->where('email', $email)->first();
    }

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = $this->model->with(['empresa']);

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

    public function listarAtivos(int $empresaId): LengthAwarePaginator
    {
        return $this->model->with(['empresa'])
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome_fantasia')
            ->paginate(15);
    }

    private function aplicarFiltros($query, array $filtros): \Illuminate\Database\Eloquent\Builder
    {
        if (! empty($filtros['search'])) {
            $search = '%'.$filtros['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('razao_social', 'like', $search)
                    ->orWhere('nome_fantasia', 'like', $search)
                    ->orWhere('cpf', 'like', $search)
                    ->orWhere('cnpj', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('telefone', 'like', $search);
            });
        }

        if (! empty($filtros['empresa_id'])) {
            $query->where('empresa_id', $filtros['empresa_id']);
        }

        if (isset($filtros['ativo'])) {
            $query->where('ativo', $filtros['ativo']);
        }

        if (! empty($filtros['tipo_pessoa'])) {
            $query->where('tipo_pessoa', $filtros['tipo_pessoa']);
        }

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['cidade'])) {
            $query->where('cidade', 'like', '%'.$filtros['cidade'].'%');
        }

        return $query;
    }
}
