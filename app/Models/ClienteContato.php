<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteContato extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'tipo',
        'valor',
        'principal',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}