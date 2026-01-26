<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cliente;

class Cobranca extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'cliente_id',
        'boleto_id',
        'descricao',
        'valor',
        'data_vencimento',
        'status',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    public function telefones()
    {
        return $this->hasMany(Telefone::class);
    }

    public function boleto()
    {
        return $this->belongsTo(Boleto::class, 'boleto_id');
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}
