<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Epi extends Model
{
    protected $fillable = [
        'nome',
        'ca',
        'validade_ca',
        'vida_util_meses',
    ];

    protected $casts = [
        'validade_ca' => 'date',
    ];

    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class, 'funcionario_epis', 'epi_id', 'funcionario_id')
            ->withPivot(['id', 'data_entrega', 'data_prevista_troca', 'status'])
            ->withTimestamps();
    }
}
