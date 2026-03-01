<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhFechamentoPonto extends Model
{
    protected $table = 'rh_fechamentos_ponto';

    protected $fillable = [
        'funcionario_id',
        'competencia',
        'fechado_em',
        'fechado_por_user_id',
    ];

    protected $casts = [
        'competencia' => 'date',
        'fechado_em' => 'datetime',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function fechadoPor()
    {
        return $this->belongsTo(User::class, 'fechado_por_user_id');
    }
}
