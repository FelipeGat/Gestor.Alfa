<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipamentoSetor extends Model
{
    protected $table = 'equipamento_setores';

    protected $fillable = [
        'cliente_id',
        'nome',
        'descricao',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function equipamentos()
    {
        return $this->hasMany(Equipamento::class, 'setor_id');
    }
}
