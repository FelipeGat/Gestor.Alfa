<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfis';

    protected $fillable = ['nome', 'slug'];

    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'perfil_permissao')
            ->withPivot(['ler', 'incluir', 'imprimir', 'excluir']);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'perfil_user');
    }
}