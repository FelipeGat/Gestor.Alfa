<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JornadaEscala extends Model
{
    protected $table = 'jornadas_escala';

    protected $fillable = [
        'jornada_id',
        'dia_semana',
        'hora_entrada',
        'hora_saida',
        'intervalo_minutos',
        'carga_horaria_dia',
    ];

    protected $casts = [
        'dia_semana' => 'integer',
        'intervalo_minutos' => 'integer',
        'carga_horaria_dia' => 'decimal:2',
    ];

    public function jornada()
    {
        return $this->belongsTo(Jornada::class);
    }
}
