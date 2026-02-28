<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroPontoPortal extends Model
{
    protected $table = 'registro_pontos_portal';

    protected $fillable = [
        'funcionario_id',
        'data_referencia',
        'entrada_em',
        'intervalo_inicio_em',
        'intervalo_fim_em',
        'saida_em',
        'entrada_foto_path',
        'saida_foto_path',
        'entrada_latitude',
        'entrada_longitude',
        'intervalo_inicio_latitude',
        'intervalo_inicio_longitude',
        'intervalo_fim_latitude',
        'intervalo_fim_longitude',
        'saida_latitude',
        'saida_longitude',
        'registrado_fora_atendimento',
        'distancia_atendimento_metros',
        'justificativa_fora_atendimento',
        'registrado_por_user_id',
        'observacao',
    ];

    protected $casts = [
        'data_referencia' => 'date',
        'entrada_em' => 'datetime',
        'intervalo_inicio_em' => 'datetime',
        'intervalo_fim_em' => 'datetime',
        'saida_em' => 'datetime',
        'entrada_latitude' => 'float',
        'entrada_longitude' => 'float',
        'intervalo_inicio_latitude' => 'float',
        'intervalo_inicio_longitude' => 'float',
        'intervalo_fim_latitude' => 'float',
        'intervalo_fim_longitude' => 'float',
        'saida_latitude' => 'float',
        'saida_longitude' => 'float',
        'registrado_fora_atendimento' => 'boolean',
        'distancia_atendimento_metros' => 'integer',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
