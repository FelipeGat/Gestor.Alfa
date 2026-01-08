<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Assunto;

class Atendimento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_atendimento',
        'cliente_id',
        'nome_solicitante',
        'telefone_solicitante',
        'email_solicitante',
        'assunto_id',
        'descricao',
        'prioridade',
        'empresa_id',
        'funcionario_id',
        'status',
        'data_atendimento',
    ];

    protected $casts = [
        'data_atendimento' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function assunto()
    {
        return $this->belongsTo(Assunto::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}