<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jornada extends Model
{
    protected $fillable = [
        'nome',
        'hora_inicio',
        'hora_fim',
        'intervalo_minutos',
        'carga_horaria_semanal',
    ];

    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class, 'funcionario_jornadas')
            ->withPivot(['id', 'data_inicio', 'data_fim'])
            ->withTimestamps();
    }
}
