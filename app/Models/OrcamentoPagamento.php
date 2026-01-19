<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoPagamento extends Model
{
    protected $table = 'orcamento_pagamentos';

    protected $fillable = [
        'orcamento_id',
        'tipo',    
        'parcelas',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}
