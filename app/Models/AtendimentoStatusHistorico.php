<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtendimentoStatusHistorico extends Model
{
    protected $fillable = [
        'atendimento_id',
        'status',
        'observacao',
        'user_id',
    ];

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}