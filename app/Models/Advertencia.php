<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertencia extends Model
{
    protected $table = 'advertencias';

    protected $fillable = [
        'funcionario_id',
        'tipo',
        'descricao',
        'data',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
}
