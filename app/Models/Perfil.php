<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Perfil extends Model
{
    protected $table = 'perfis';

    protected $fillable = ['nome', 'slug'];


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'perfil_user',
            'perfil_id',
            'user_id'
        );
    }

    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'perfil_permissao')
            ->withPivot(['ler', 'incluir', 'imprimir', 'excluir']);
    }
}
