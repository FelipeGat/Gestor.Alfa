<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cobranca;
use App\Models\Boleto;
use App\Models\User;
use App\Models\Email;
use App\Models\Telefone;
use App\Models\NotaFiscal;


class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'ativo',
        'valor_mensal',
        'dia_vencimento',
        'tipo_pessoa',
        'cpf_cnpj',
        'razao_social',
        'tipo_cliente',
        'data_cadastro',
        'cep',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'complemento',
        'inscricao_estadual',
        'inscricao_municipal',
        'observacoes',
    ];

    protected $casts = [
        'ativo'          => 'boolean',
        'valor_mensal'   => 'decimal:2',
        'dia_vencimento'=> 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    public function telefones()
    {
        return $this->hasMany(Telefone::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function notasFiscais()
    {
        return $this->hasMany(NotaFiscal::class);
    }

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class);
    }

    public function getNomeExibicaoAttribute(): string
{
    // Pessoa Física
    if ($this->tipo_pessoa === 'PF') {
        return $this->nome;
    }

    // Pessoa Jurídica
    if (!empty($this->nome)) {
        return $this->nome;
    }

    return $this->razao_social;
}

}