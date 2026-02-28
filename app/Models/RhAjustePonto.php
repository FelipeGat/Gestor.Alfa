<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhAjustePonto extends Model
{
    protected $table = 'rh_ajustes_ponto';

    protected $fillable = [
        'funcionario_id',
        'atendimento_id',
        'minutos_ajuste',
        'tipo_ajuste',
        'justificativa',
        'ajustado_por_user_id',
        'autorizado_por_user_id',
        'ajustado_em',
    ];

    protected $casts = [
        'ajustado_em' => 'datetime',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class);
    }

    public function ajustadoPor()
    {
        return $this->belongsTo(User::class, 'ajustado_por_user_id');
    }

    public function autorizadoPor()
    {
        return $this->belongsTo(User::class, 'autorizado_por_user_id');
    }
}
