<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cliente;
use App\Models\Funcionario;

class Empresa extends Model
{
    use SoftDeletes;
    
    protected $table = 'empresas';

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

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class);
    }

    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class);
    }

    public function assuntos()
    {
        return $this->hasMany(Assunto::class);
    }

    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class);
    }

}