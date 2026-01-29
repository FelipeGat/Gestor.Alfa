<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobrancaAnexo extends Model
{
    use HasFactory;

    protected $table = 'cobranca_anexos';

    protected $fillable = [
        'cobranca_id',
        'tipo',
        'nome_original',
        'nome_arquivo',
        'caminho',
        'tamanho',
    ];

    /**
     * Appends - adicionar atributos virtuais
     */
    protected $appends = [
        'tamanho_formatado',
        'tipo_formatado',
    ];

    /**
     * Relacionamento com Cobrança
     */
    public function cobranca()
    {
        return $this->belongsTo(Cobranca::class);
    }

    /**
     * Obter URL completa do arquivo
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->caminho);
    }

    /**
     * Obter tamanho formatado
     */
    public function getTamanhoFormatadoAttribute()
    {
        $tamanho = $this->tamanho;

        if ($tamanho < 1024) {
            return $tamanho . ' B';
        } elseif ($tamanho < 1048576) {
            return round($tamanho / 1024, 2) . ' KB';
        } else {
            return round($tamanho / 1048576, 2) . ' MB';
        }
    }

    /**
     * Obter nome do tipo formatado
     */
    public function getTipoFormatadoAttribute()
    {
        return $this->tipo === 'nf' ? 'Nota Fiscal' : 'Boleto';
    }
}
