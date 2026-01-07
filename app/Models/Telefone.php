<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telefone extends Model
{
    protected $fillable = [
        'cliente_id',
        'valor',
        'principal',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}