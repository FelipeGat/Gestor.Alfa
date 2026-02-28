<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Afastamento extends Model
{
    protected $table = 'afastamentos';

    protected $fillable = [
        'funcionario_id',
        'tipo',
        'data_inicio',
        'data_fim',
        'motivo',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
