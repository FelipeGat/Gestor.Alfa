<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class AtendimentoAndamentoFoto extends Model
{
    protected $fillable = ['atendimento_andamento_id', 'arquivo'];

    public function andamento()
{
    return $this->belongsTo(
        AtendimentoAndamento::class,
        'atendimento_andamento_id'
    );
}

    public function getArquivoStoragePathAttribute(): string
    {
        return $this->normalizarCaminho($this->arquivo);
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

        return asset('storage/' . $this->normalizarCaminho($arquivo));
    }

    private function normalizarCaminho(?string $caminho): string
    {
        $arquivo = str_replace('\\', '/', trim((string) $caminho));
        $arquivo = ltrim($arquivo, '/');

        if (Str::startsWith($arquivo, 'public/')) {
            $arquivo = Str::after($arquivo, 'public/');
        }

        if (Str::startsWith($arquivo, 'storage/')) {
            $arquivo = Str::after($arquivo, 'storage/');
        }

        return $arquivo;
    }



}
