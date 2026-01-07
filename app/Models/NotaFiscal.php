<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaFiscal extends Model
{
    protected $table = 'notas_fiscais';

    protected $fillable = [
        'cliente_id',
        'numero',
        'tipo',
        'arquivo',
        'baixado_em',
    ];

    protected $casts = [
        'baixado_em' => 'datetime',
    ];

    /**
     * Relacionamento
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Verifica se já foi baixada
     */
    public function foiBaixada(): bool
    {
        return !is_null($this->baixado_em);
    }
}