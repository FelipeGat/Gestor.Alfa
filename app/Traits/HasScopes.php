<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasScopes
{
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeInativos(Builder $query): Builder
    {
        return $query->where('ativo', false);
    }

    public function scopePesquisar(Builder $query, string $search, array $columns = []): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $searchTerm = '%'.$search.'%';

        return $query->where(function ($q) use ($searchTerm, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', $searchTerm);
            }
        });
    }

    public function scopeDataEntre(Builder $query, string $dateColumn, string $startDate, string $endDate): Builder
    {
        return $query->whereDate($dateColumn, '>=', $startDate)
            ->whereDate($dateColumn, '<=', $endDate);
    }

    public function scopeDataMaiorQue(Builder $query, string $dateColumn, string $date): Builder
    {
        return $query->whereDate($dateColumn, '>=', $date);
    }

    public function scopeDataMenorQue(Builder $query, string $dateColumn, string $date): Builder
    {
        return $query->whereDate($dateColumn, '<=', $date);
    }

    public function scopeOrderByData(Builder $query, string $direction = 'desc', string $dateColumn = 'created_at'): Builder
    {
        return $query->orderBy($dateColumn, $direction);
    }
}
