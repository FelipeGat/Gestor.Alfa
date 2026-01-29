<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentroCusto extends Model
{
    protected $table = 'centros_custo';

    protected $fillable = [
        'nome',
        'tipo',
        'empresa_id',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /* ================= RELACIONAMENTOS ================= */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function contasPagar()
    {
        return $this->hasMany(ContaPagar::class);
    }

    public function contasFixasPagar()
    {
        return $this->hasMany(ContaFixaPagar::class);
    }
}
