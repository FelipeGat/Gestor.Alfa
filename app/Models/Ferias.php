<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ferias extends Model
{
    protected $table = 'ferias';

    protected $fillable = [
        'funcionario_id',
        'periodo_aquisitivo_inicio',
        'periodo_aquisitivo_fim',
        'periodo_gozo_inicio',
        'periodo_gozo_fim',
        'status',
    ];

    protected $casts = [
        'periodo_aquisitivo_inicio' => 'date',
        'periodo_aquisitivo_fim' => 'date',
        'periodo_gozo_inicio' => 'date',
        'periodo_gozo_fim' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
