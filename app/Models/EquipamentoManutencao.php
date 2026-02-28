<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipamentoManutencao extends Model
{
    protected $table = 'equipamento_manutencoes';

    protected $fillable = [
        'equipamento_id',
        'data',
        'tipo',
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
