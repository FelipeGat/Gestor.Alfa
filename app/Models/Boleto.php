<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Boleto extends Model
{
    protected $table = 'boletos';

    /**
     * Campos permitidos para criação/atualização
     */
    protected $fillable = [
        'cliente_id',
        'mes',
        'ano',
        'valor',
        'arquivo',
        'status',
        'data_vencimento',
        'baixado_em',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'valor'           => 'decimal:2',
        'data_vencimento' => 'date',
        'baixado_em'      => 'datetime',
    ];

    /**
     * Status possíveis (ENUM do banco)
     */
    public const STATUS_ABERTO  = 'aberto';
    public const STATUS_PAGO   = 'pago';
    public const STATUS_VENCIDO = 'vencido';

    /**
     * Relacionamentos
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cobranca()
    {
        return $this->hasOne(Cobranca::class, 'boleto_id');
    }

    

    /**
     * Helpers
     */
    public function foiBaixado(): bool
    {
        return !is_null($this->baixado_em);
    }

    /**
     * Referência formatada (ex: 2026-01)
     */
    public function getReferenciaAttribute(): string
    {
        return $this->ano . '-' . str_pad($this->mes, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica se está vencido
     */
    public function isVencido(): bool
    {
        return $this->status !== self::STATUS_PAGO
            && $this->data_vencimento->isPast();
    }
}