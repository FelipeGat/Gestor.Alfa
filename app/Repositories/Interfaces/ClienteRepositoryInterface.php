<?php

namespace App\Repositories\Interfaces;

use App\Models\Cliente;
use Illuminate\Pagination\LengthAwarePaginator;

interface ClienteRepositoryInterface
{
    public function findById(int $id): ?Cliente;

    public function findByCpf(string $cpf): ?Cliente;

    public function findByCnpj(string $cnpj): ?Cliente;

    public function findByEmail(string $email): ?Cliente;

    public function listar(array $filtros): LengthAwarePaginator;

    public function listarPorEmpresa(int $empresaId, array $filtros = []): LengthAwarePaginator;

    public function listarAtivos(int $empresaId): LengthAwarePaginator;
}
