<?php

namespace App\Repositories\Interfaces;

use App\Models\Cobranca;
use Illuminate\Pagination\LengthAwarePaginator;

interface CobrancaRepositoryInterface
{
    public function findById(int $id): ?Cobranca;

    public function listar(array $filtros): LengthAwarePaginator;

    public function listarPorOrcamento(int $orcamentoId): LengthAwarePaginator;

    public function listarPorStatus(string $status): LengthAwarePaginator;

    public function listarVencidas(): LengthAwarePaginator;

    public function listarVenceHoje(): LengthAwarePaginator;

    public function listarPendentes(): LengthAwarePaginator;
}
