<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assunto extends Model
{
    use SoftDeletes;

    protected $table = 'assuntos';

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'categoria',
        'subcategoria',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

}