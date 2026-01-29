<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = [
        'nome',
        'tipo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /* ================= RELACIONAMENTOS ================= */

    public function subcategorias()
    {
        return $this->hasMany(Subcategoria::class);
    }
}
