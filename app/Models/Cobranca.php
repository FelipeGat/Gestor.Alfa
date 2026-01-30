<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Cobranca extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'cliente_id',
        'conta_financeira_id',
        'boleto_id',
        'descricao',
        'valor',
        'juros_multa',
        'data_vencimento',
        'status',
        'tipo',
        'conta_fixa_id',
        'pago_em',
        'data_pagamento',
        'forma_pagamento',
        'parcela_num',
        'parcelas_total',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'pago_em' => 'datetime',
        'juros_multa' => 'decimal:2',
    ];

    /**
     * Campos virtuais expostos automaticamente
     */
    protected $appends = [
        'status_financeiro',
    ];

    /* =========================================================
     | RELAÇÕES
     ========================================================= */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function preCliente()
    {
        return null;
    }

    public function boleto()
    {
        return $this->belongsTo(Boleto::class, 'boleto_id');
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function contaFinanceira()
    {
        return $this->belongsTo(ContaFinanceira::class, 'conta_financeira_id');
    }

    /* =========================================================
     | STATUS FINANCEIRO
     ========================================================= */

    public function getStatusFinanceiroAttribute(): string
    {
        // Se já foi pago, não depende de data
        if ($this->status === 'pago') {
            return 'pago';
        }

        $hoje = Carbon::today();

        if ($this->data_vencimento->isToday()) {
            return 'vence_hoje';
        }

        if ($this->data_vencimento->isFuture()) {
            return 'a_vencer';
        }

        return 'vencido';
    }

    /**
     * Relacionamento com Conta Fixa
     */
    public function contaFixa()
    {
        return $this->belongsTo(ContaFixa::class);
    }

    /**
     * Relacionamento com Anexos
     */
    public function anexos()
    {
        return $this->hasMany(CobrancaAnexo::class);
    }
}
