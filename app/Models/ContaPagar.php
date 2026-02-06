<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ContaPagar extends Model
{
    use SoftDeletes;

    /**
     * Usuário responsável pelo pagamento
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    use SoftDeletes;

    protected $table = 'contas_pagar';

    protected $fillable = [
        'centro_custo_id',
        'fornecedor_id',
        'conta_id',
        'conta_financeira_id',
        'conta_fixa_pagar_id',
        'descricao',
        'valor',
        'juros_multa',
        'data_vencimento',
        'data_inicial',
        'data_fim',
        'periodicidade',
        'status',
        'tipo',
        'pago_em',
        'data_pagamento',
        'forma_pagamento',
        'observacoes',
        'orcamento_id',
    ];

    /**
     * Accessor para empresa relacionada via Orçamento ou Conta Financeira
     */
    public function getEmpresaRelacionadaAttribute()
    {
        // Se houver orçamento, prioriza empresa do orçamento
        if ($this->orcamento && $this->orcamento->empresa) {
            return $this->orcamento->empresa;
        }
        // Se houver conta financeira, retorna empresa da conta financeira
        if ($this->contaFinanceira && $this->contaFinanceira->empresa) {
            return $this->contaFinanceira->empresa;
        }
        return null;
    }
    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'data_inicial' => 'date',
        'data_fim' => 'date',
        'pago_em' => 'datetime',
        'valor' => 'decimal:2',
        'juros_multa' => 'decimal:2',
    ];

    protected $appends = [
        'status_financeiro',
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

    public function contaFinanceira()
    {
        return $this->belongsTo(ContaFinanceira::class);
    }

    public function contaFixaPagar()
    {
        return $this->belongsTo(ContaFixaPagar::class);
    }

    public function anexos()
    {
        return $this->hasMany(ContaPagarAnexo::class);
    }

    /* ================= ACCESSORS ================= */

    public function getStatusFinanceiroAttribute(): string
    {
        if ($this->status === 'pago') {
            return 'pago';
        }

        if ($this->data_vencimento->isPast()) {
            return 'vencido';
        }

        if ($this->data_vencimento->isToday()) {
            return 'vence_hoje';
        }

        return 'em_aberto';
    }
}
