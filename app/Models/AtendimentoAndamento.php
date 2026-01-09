<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtendimentoAndamento extends Model
{
    protected $fillable = [
        'atendimento_id',
        'user_id',
        'descricao',
    ];

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fotos()
    {
        return $this->hasMany(AtendimentoAndamentoFoto::class);
    }

}