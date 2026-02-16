<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface OrcamentoRepositoryInterface
{
    public function findById(int $id);

    public function findByNumero(string $numero);

    public function listar(array $filtros): LengthAwarePaginator;

    public function listarPorEmpresa(int $empresaId, array $filtros = []): LengthAwarePaginator;

    public function listarPorCliente(int $clienteId): LengthAwarePaginator;

    public function listarPorStatus(string $status): LengthAwarePaginator;

    public function gerarNumero(int $empresaId): string;
}
