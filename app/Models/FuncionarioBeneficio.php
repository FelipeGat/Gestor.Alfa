<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncionarioBeneficio extends Model
{
    protected $table = 'funcionario_beneficios';

    protected $fillable = [
        'funcionario_id',
        'tipo',
        'valor',
        'desconto_percentual',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'desconto_percentual' => 'decimal:2',
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
