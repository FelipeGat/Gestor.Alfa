<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conta extends Model
{
    protected $fillable = [
        'subcategoria_id',
        'nome',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /* ================= RELACIONAMENTOS ================= */

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class);
    }

    public function contasPagar()
    {
        return $this->hasMany(ContaPagar::class);
    }
}
