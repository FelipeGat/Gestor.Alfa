<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaPagarAnexo extends Model
{
    protected $table = 'conta_pagar_anexos';

    protected $fillable = [
        'conta_pagar_id',
        'tipo',
        'nome_original',
        'nome_arquivo',
        'caminho',
        'tamanho',
    ];

    protected $appends = [
        'tamanho_formatado',
        'tipo_formatado',
    ];

    /* ================= RELACIONAMENTOS ================= */

    public function contaPagar()
    {
        return $this->belongsTo(ContaPagar::class);
    }

    /* ================= ACCESSORS ================= */

    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return number_format($bytes / 1048576, 2) . ' MB';
    }

    public function getTipoFormatadoAttribute(): string
    {
        return match ($this->tipo) {
            'nf' => 'Nota Fiscal',
            'boleto' => 'Boleto',
            default => $this->tipo
        };
    }

    public function getUrlAttribute(): string
    {
        return route('financeiro.contas-pagar.anexos.download', $this->id);
    }
}
