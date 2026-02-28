<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipamentoLimpeza extends Model
{
    protected $table = 'equipamento_limpezas';

    protected $fillable = [
        'equipamento_id',
        'data',
        'descricao',
        'realizado_por',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function equipamento(): BelongsTo
    {
        return $this->belongsTo(Equipamento::class);
    }
}
