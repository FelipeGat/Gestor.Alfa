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
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'endereco',
        'email_comercial',
        'email_administrativo',
        'telefone_comercial',
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
}