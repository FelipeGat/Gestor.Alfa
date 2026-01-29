<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaFixaPagar extends Model
{
    protected $table = 'contas_fixas_pagar';

    protected $fillable = [
        'centro_custo_id',
        'fornecedor_id',
        'conta_id',
        'descricao',
        'valor',
        'dia_vencimento',
        'forma_pagamento',
        'periodicidade',
        'data_inicial',
        'data_fim',
        'ativo',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'ativo' => 'boolean',
        'dia_vencimento' => 'integer',
        'data_inicial' => 'date',
        'data_fim' => 'date',
    ];

    /* ================= RELACIONAMENTOS ================= */

    public function centroCusto()
    {
        return $this->belongsTo(CentroCusto::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function contasPagar()
    {
        return $this->hasMany(ContaPagar::class);
    }
}
