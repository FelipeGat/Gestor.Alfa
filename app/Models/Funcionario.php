<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Empresa;

class Funcionario extends Model
{

    use SoftDeletes;

    protected $table = 'funcionarios';

    protected $fillable = [
        'nome',
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

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class);
    }

   public function user()
    {
        return $this->hasOne(User::class);
    }
}