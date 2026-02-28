<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncionarioJornada extends Model
{
    protected $table = 'funcionario_jornadas';

    protected $fillable = [
        'funcionario_id',
        'jornada_id',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function jornada()
    {
        return $this->belongsTo(Jornada::class);
    }
}
