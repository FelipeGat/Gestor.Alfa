<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncionarioDocumento extends Model
{
    protected $table = 'funcionario_documentos';

    protected $fillable = [
        'funcionario_id',
        'tipo',
        'numero',
        'data_emissao',
        'data_vencimento',
        'arquivo',
        'status',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
