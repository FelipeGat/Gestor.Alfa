<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimentacaoFinanceira extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'movimentacoes_financeiras';

    protected $fillable = [
        'conta_origem_id',
        'conta_destino_id',
        'tipo',
        'valor',
        'saldo_resultante',
        'observacao',
        'user_id',
        'data_movimentacao',
    ];

    protected $dates = [
        'data_movimentacao',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'saldo_resultante' => 'decimal:2',
    ];

    public function contaOrigem()
    {
        return $this->belongsTo(ContaFinanceira::class, 'conta_origem_id');
    }

    public function contaDestino()
    {
        return $this->belongsTo(ContaFinanceira::class, 'conta_destino_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
