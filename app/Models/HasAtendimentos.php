<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasAtendimentos
{
    /**
     * Relacionamento: Cliente possui muitos Atendimentos
     */
    public function atendimentos(): HasMany
    {
        return $this->hasMany(Atendimento::class);
    }
}
