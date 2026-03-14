<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaComercial extends Model
{
    protected $table = 'metas_comerciais';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'mes',
        'ano',
        'valor_meta',
    ];

    protected $casts = [
        'valor_meta' => 'float',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
