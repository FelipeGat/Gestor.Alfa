<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncionarioEpi extends Model
{
    protected $table = 'funcionario_epis';

    protected $fillable = [
        'funcionario_id',
        'epi_id',
        'data_entrega',
        'data_prevista_troca',
        'status',
    ];

    protected $casts = [
        'data_entrega' => 'date',
        'data_prevista_troca' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function epi()
    {
        return $this->belongsTo(Epi::class);
    }
}
