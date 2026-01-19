<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoTaxa extends Model
{
    protected $table = 'orcamento_taxas';

    protected $fillable = [
        'orcamento_id',
        'nome',
        'tipo',
        'valor',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}
