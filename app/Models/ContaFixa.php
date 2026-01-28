<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaFixa extends Model
{
    use HasFactory;

    protected $table = 'contas_fixas';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'categoria',
        'valor',
        'conta_financeira_id',
        'forma_pagamento',
        'periodicidade',
        'data_inicial',
        'data_fim',
        'percentual_renovacao',
        'data_atualizacao_percentual',
        'observacao',
        'ativo',
    ];

    protected $casts = [
        'data_inicial' => 'date',
        'data_fim' => 'date',
        'data_atualizacao_percentual' => 'date',
        'valor' => 'decimal:2',
        'percentual_renovacao' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento com Empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relacionamento com Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relacionamento com Conta Financeira
     */
    public function contaFinanceira(): BelongsTo
    {
        return $this->belongsTo(ContaFinanceira::class);
    }
}
