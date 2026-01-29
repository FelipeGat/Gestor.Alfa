<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    protected $fillable = [
        'categoria_id',
        'nome',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /* ================= RELACIONAMENTOS ================= */

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function contas()
    {
        return $this->hasMany(Conta::class);
    }
}
