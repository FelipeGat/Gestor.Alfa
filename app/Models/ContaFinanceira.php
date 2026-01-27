<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContaFinanceira extends Model
{
    use SoftDeletes;

    protected $table = 'contas_financeiras';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'limite_credito',
        'limite_credito_utilizado',
        'limite_cheque_especial',
        'saldo',
        'ativo',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'ativo' => 'boolean',
        'limite_credito' => 'decimal:2',
        'limite_credito_utilizado' => 'decimal:2',
        'limite_cheque_especial' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS – REGRA FINANCEIRA REAL
    |--------------------------------------------------------------------------
    */

    /**
     * Cheque especial utilizado automaticamente
     */
    public function getChequeEspecialUtilizadoAttribute()
    {
        return $this->saldo < 0 ? abs($this->saldo) : 0;
    }

    /**
     * Saldo disponível real da conta
     */
    public function getSaldoDisponivelAttribute()
    {
        $saldoPositivo = max($this->saldo, 0);

        return $saldoPositivo
            + ($this->limite_cheque_especial - $this->cheque_especial_utilizado);
    }

    /**
     * Crédito disponível no cartão
     */
    public function getCreditoDisponivelAttribute()
    {
        return max(
            $this->limite_credito - $this->limite_credito_utilizado,
            0
        );
    }

    /**
     * Saldo total exibido no sistema
     */
    public function getSaldoTotalAttribute()
    {
        return $this->tipo === 'credito'
            ? $this->credito_disponivel
            : $this->saldo_disponivel;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeDaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }
}
