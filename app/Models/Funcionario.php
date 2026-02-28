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

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class);
    }

    public function jornadasVinculos()
    {
        return $this->hasMany(FuncionarioJornada::class);
    }

    public function jornadas()
    {
        return $this->belongsToMany(Jornada::class, 'funcionario_jornadas')
            ->withPivot(['id', 'data_inicio', 'data_fim'])
            ->withTimestamps();
    }

    public function documentos()
    {
        return $this->hasMany(FuncionarioDocumento::class);
    }

    public function episVinculos()
    {
        return $this->hasMany(FuncionarioEpi::class);
    }

    public function beneficios()
    {
        return $this->hasMany(FuncionarioBeneficio::class);
    }

    public function ferias()
    {
        return $this->hasMany(Ferias::class);
    }

    public function afastamentos()
    {
        return $this->hasMany(Afastamento::class);
    }

    public function advertencias()
    {
        return $this->hasMany(Advertencia::class);
    }

    public function ajustesPonto()
    {
        return $this->hasMany(RhAjustePonto::class);
    }
}
