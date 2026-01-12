<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{

    protected $table = 'permissoes';
    protected $fillable = ['recurso', 'descricao'];

    public function perfis()
    {
        return $this->belongsToMany(Perfil::class, 'perfil_permissao')
            ->withPivot(['ler', 'incluir', 'imprimir', 'excluir']);
    }
}