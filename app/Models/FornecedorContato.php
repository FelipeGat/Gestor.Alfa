<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FornecedorContato extends Model
{
    protected $table = 'fornecedor_contatos';

    protected $fillable = [
        'fornecedor_id',
        'nome',
        'cargo',
        'email',
        'telefone',
        'principal',
    ];

    protected $casts = [
        'principal' => 'boolean',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
