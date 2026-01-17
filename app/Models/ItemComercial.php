<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemComercial extends Model
{
    protected $table = 'itens_comerciais';

    protected $fillable = [
        'tipo',
        'nome',
        'sku_ou_referencia',
        'codigo_barras_ean',
        'categoria_id',
        'preco_venda',
        'preco_custo',
        'margem_lucro',
        'unidade_medida',
        'estoque_atual',
        'estoque_minimo',
        'gerencia_estoque',
        'finalidade',
        'ncm',
        'cfop_padrao',
        'codigo_servico_iss',
        'aliquota_icms',
        'aliquota_iss',
        'marca',
        'modelo',
        'estado',
        'custo_frete',
        'ativo',
    ];

    protected $casts = [
        'gerencia_estoque' => 'boolean',
        'ativo'            => 'boolean',
        'preco_venda'      => 'decimal:2',
        'preco_custo'      => 'decimal:2',
        'margem_lucro'     => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(Assunto::class, 'categoria_id');
    }
}