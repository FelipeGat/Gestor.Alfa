<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cliente;
use Carbon\Carbon;

class Cobranca extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'cliente_id',
        'pre_cliente_id',
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

    public function preCliente()
    {
        return $this->belongsTo(PreCliente::class, 'pre_cliente_id');
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

    public function getStatusFinanceiroAttribute(): string
    {
        if ($this->status === 'pago') {
            return 'pago';
        }

        $hoje = Carbon::today();
        $vencimento = Carbon::parse($this->data_vencimento);

        if ($hoje->lt($vencimento)) {
            return 'a_vencer';
        }

        if ($hoje->equalTo($vencimento)) {
            return 'vence_hoje';
        }

        return 'vencido';
    }
}
