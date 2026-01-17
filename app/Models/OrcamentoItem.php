<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class OrcamentoItem extends Model
{
    use HasFactory;

    protected $table = 'orcamento_itens';

    protected $fillable = [
        'orcamento_id',
        'item_comercial_id',
        'tipo',
        'nome',
        'quantidade',
        'valor_unitario',
        'subtotal',
    ];

    protected $casts = [
    'quantidade'     => 'integer',
    'valor_unitario' => 'decimal:2',
    'subtotal'       => 'decimal:2',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemComercial::class, 'item_comercial_id');
    }
 }