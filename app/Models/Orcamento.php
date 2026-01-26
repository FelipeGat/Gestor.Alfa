<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\OrcamentoItem;
use App\Models\OrcamentoTaxa;
use App\Models\OrcamentoPagamento;


class Orcamento extends Model
{
    use HasFactory;

    protected $table = 'orcamentos';

    protected $fillable = [
        'empresa_id',
        'atendimento_id',
        'descricao',
        'cliente_id',
        'pre_cliente_id',
        'numero_orcamento',
        'status',
        'valor_total',
        'desconto',
        'desconto_servico_valor',
        'desconto_servico_tipo',
        'desconto_produto_valor',
        'desconto_produto_tipo',
        'taxas',
        'descricao_taxas',
        'forma_pagamento',
        'prazo_pagamento',
        'validade',
        'observacoes',
        'created_by',
    ];

    protected $casts = [
        'valor_total' => 'float',
        'desconto' => 'float',
        'taxas' => 'float',
        'descricao_taxas' => 'array',
        'desconto_servico_valor' => 'float',
        'desconto_produto_valor' => 'float',
    ];


    /* ===================== RELACIONAMENTOS ===================== */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function gerarNumero(int $empresaId): string
    {
        $ano = now()->year;

        $ultimo = self::where('empresa_id', $empresaId)
            ->whereYear('created_at', $ano)
            ->select(DB::raw("MAX(CAST(SUBSTRING_INDEX(numero_orcamento, '/', 1) AS UNSIGNED)) as ultimo"))
            ->value('ultimo');

        $sequencial = str_pad(($ultimo ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return "{$sequencial}/{$ano}";
    }

    public function preCliente()
    {
        return $this->belongsTo(\App\Models\PreCliente::class, 'pre_cliente_id');
    }

    public function getNomeClienteAttribute(): string
    {
        if ($this->cliente) {
            return $this->cliente->nome_fantasia
                ?? $this->cliente->razao_social
                ?? '—';
        }

        if ($this->preCliente) {
            return $this->preCliente->nome_fantasia
                ?? $this->preCliente->razao_social
                ?? '—';
        }

        return '—';
    }

    public function itens()
    {
        return $this->hasMany(OrcamentoItem::class);
    }

    public function taxasItens()
    {
        return $this->hasMany(OrcamentoTaxa::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(OrcamentoPagamento::class);
    }

    public function cobranca()
    {
        return $this->hasOne(\App\Models\Cobranca::class);
    }
}
