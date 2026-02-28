<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feriado extends Model
{
    protected $table = 'feriados';

    protected $fillable = [
        'nome',
        'data',
        'tipo',
        'recorrente_anual',
        'ativo',
    ];

    protected $casts = [
        'data' => 'date',
        'recorrente_anual' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function jornadas()
    {
        return $this->belongsToMany(Jornada::class, 'feriado_jornada')
            ->withTimestamps();
    }
}
