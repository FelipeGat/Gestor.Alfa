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
        'registrado_por_user_id',
        'observacao',
    ];

    protected $casts = [
        'data_referencia' => 'date',
        'entrada_em' => 'datetime',
        'intervalo_inicio_em' => 'datetime',
        'intervalo_fim_em' => 'datetime',
        'saida_em' => 'datetime',
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
