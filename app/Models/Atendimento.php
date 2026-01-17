<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Assunto;
use App\Models\Orcamento;

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
        'status_atual',
        'is_orcamento',
        'atendimento_origem_id',
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
        return $this->belongsTo(Assunto::class)->withDefault([
            'nome' => '—',
        ]);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class)->withDefault([
            'nome_fantasia' => '—',
        ]);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function historicos()
    {
        return $this->hasMany(AtendimentoStatusHistorico::class);
    }

    public function atendimentoOrigem()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_origem_id');
    }

    public function andamentos()
    {
        return $this->hasMany(AtendimentoAndamento::class)
                    ->orderBy('created_at', 'desc');
    }

    public function orcamento()
    {
        return $this->hasOne(Orcamento::class);
    }

    public function statusHistoricos()
    {
        return $this->hasMany(
            \App\Models\AtendimentoStatusHistorico::class,
            'atendimento_id'
        )->orderBy('created_at', 'desc');
    }


}