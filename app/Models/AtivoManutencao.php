<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtivoManutencao extends Model
{
    protected $table = 'ativos_manutencoes';

    public $timestamps = false;

    protected $fillable = [
        'ativo_id',
        'data_manutencao',
        'tipo',
        'descricao',
        'tecnico_responsavel',
        'custo',
        'pecas_trocadas',
        'tempo_parado_horas',
    ];

    protected $casts = [
        'data_manutencao' => 'date',
        'custo' => 'decimal:2',
        'tempo_parado_horas' => 'integer',
        'created_at' => 'datetime',
    ];

    public function ativo(): BelongsTo
    {
        return $this->belongsTo(Equipamento::class, 'ativo_id');
    }
}
