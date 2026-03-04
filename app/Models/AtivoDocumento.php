<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtivoDocumento extends Model
{
    protected $table = 'ativos_documentos';

    public $timestamps = false;

    protected $fillable = [
        'ativo_id',
        'nome_documento',
        'arquivo',
        'tipo_documento',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function ativo(): BelongsTo
    {
        return $this->belongsTo(Equipamento::class, 'ativo_id');
    }
}
