<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreCliente extends Model
{
    use HasFactory;

    protected $table = 'pre_clientes';

    protected $fillable = [
        'tipo_pessoa',
        'cpf_cnpj',
        'razao_social',
        'nome_fantasia',
        'email',
        'telefone',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'origem',
        'created_by',
        'convertido_em_cliente',
        'cliente_id',
    ];

    /* ================= RELACIONAMENTOS ================= */

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

}