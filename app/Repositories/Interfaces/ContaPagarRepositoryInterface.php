<?php

namespace App\Repositories\Interfaces;

use App\Models\ContaPagar;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ContaPagarRepositoryInterface
{
    public function findById(int $id): ?ContaPagar;
    
    public function all(): Collection;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function create(array $data): ContaPagar;
    
    public function update(int $id, array $data): ?ContaPagar;
    
    public function delete(int $id): bool;
    
    public function findWithRelations(int $id): ?ContaPagar;
    
    public function findByFornecedor(int $fornecedorId): Collection;
    
    public function findByStatus(string $status): Collection;
    
    public function findVencidas(): Collection;
    
    public function findByPeriodo(string $inicio, string $fim): Collection;
    
    public function somaPorStatus(string $status): float;
    
    public function somaTotal(): float;
}
