<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FuncionarioDocumento extends Model
{
    protected $table = 'funcionario_documentos';

    protected $fillable = [
        'funcionario_id',
        'tipo',
        'numero',
        'data_emissao',
        'data_vencimento',
        'arquivo',
        'status',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function getArquivoUrlAttribute(): ?string
    {
        if (!$this->arquivo) {
            return null;
        }

        $arquivo = str_replace('\\', '/', trim((string) $this->arquivo));

        if (Str::startsWith($arquivo, ['http://', 'https://'])) {
            return $arquivo;
        }

        $arquivo = ltrim($arquivo, '/');
        if (Str::startsWith($arquivo, 'public/')) {
            $arquivo = Str::after($arquivo, 'public/');
        }
        if (Str::startsWith($arquivo, 'storage/')) {
            $arquivo = Str::after($arquivo, 'storage/');
        }

        return Storage::disk('public')->url($arquivo);
    }

    public function getArquivoNomeAttribute(): ?string
    {
        if (!$this->arquivo) {
            return null;
        }

        $arquivo = trim((string) $this->arquivo);

        if (Str::startsWith($arquivo, ['http://', 'https://'])) {
            $path = parse_url($arquivo, PHP_URL_PATH) ?: $arquivo;
            return basename($path);
        }

        return basename(str_replace('\\', '/', $arquivo));
    }
}
