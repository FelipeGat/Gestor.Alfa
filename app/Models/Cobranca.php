<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cobranca extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->dontSubmitEmptyLogs();
    }

    /**
     * Accessor para empresa relacionada via Conta Fixa ou Orçamento
     */
    public function getEmpresaRelacionadaAttribute()
    {
        // Se houver orçamento, prioriza empresa do orçamento
        if ($this->orcamento && $this->orcamento->empresa) {
            return $this->orcamento->empresa;
        }
        // Se for receita fixa, retorna empresa da conta fixa
        if ($this->contaFixa && $this->contaFixa->empresa) {
            return $this->contaFixa->empresa;
        }
        return null;
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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
        'user_id',
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

        // Sem data de vencimento definida (cobranças em aberto sem prazo)
        if (! $this->data_vencimento) {
            return 'em_aberto';
        }

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
