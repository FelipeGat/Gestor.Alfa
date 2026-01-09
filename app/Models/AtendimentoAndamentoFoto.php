<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtendimentoAndamentoFoto extends Model
{
    protected $fillable = ['atendimento_andamento_id', 'arquivo'];

    public function andamento()
    {
        return $this->belongsTo(AtendimentoAndamento::class);
    }
}