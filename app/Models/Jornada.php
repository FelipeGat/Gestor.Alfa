<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jornada extends Model
{
    protected $fillable = [
        'nome',
        'tipo_jornada',
        'dias_trabalhados',
        'hora_entrada_padrao',
        'hora_saida_padrao',
        'hora_inicio',
        'hora_fim',
        'intervalo_minutos',
        'carga_horaria_semanal',
        'tolerancia_entrada_min',
        'tolerancia_saida_min',
        'tolerancia_intervalo_min',
        'minimo_horas_para_extra',
        'permitir_ponto_fora_horario',
        'ativo',
    ];

    protected $casts = [
        'dias_trabalhados' => 'array',
        'carga_horaria_semanal' => 'decimal:2',
        'intervalo_minutos' => 'integer',
        'tolerancia_entrada_min' => 'integer',
        'tolerancia_saida_min' => 'integer',
        'tolerancia_intervalo_min' => 'integer',
        'minimo_horas_para_extra' => 'integer',
        'permitir_ponto_fora_horario' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class, 'funcionario_jornadas')
            ->withPivot(['id', 'data_inicio', 'data_fim'])
            ->withTimestamps();
    }

    public function escalas()
    {
        return $this->hasMany(JornadaEscala::class);
    }

    public function feriados()
    {
        return $this->belongsToMany(Feriado::class, 'feriado_jornada')
            ->withTimestamps();
    }
}
