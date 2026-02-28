<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipamentoResponsavel extends Model
{
    protected $table = 'equipamento_responsaveis';

    protected $fillable = [
        'cliente_id',
        'nome',
        'cargo',
        'telefone',
        'email',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function equipamentos()
    {
        return $this->hasMany(Equipamento::class, 'responsavel_id');
    }
}
