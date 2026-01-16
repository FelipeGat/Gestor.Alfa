<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use App\Models\Cliente;
use App\Models\Perfil;
use App\Models\Empresa;

/**
 * @property bool $primeiro_acesso
 * @property string $tipo
 * @property int|null $cliente_id
 */

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tipo',
        'cliente_id',
        'funcionario_id',
        'primeiro_acesso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'primeiro_acesso' => 'boolean',
        ];
    }

    public function canPermissao(string $recurso, string $acao): bool
    {
        foreach ($this->perfis as $perfil) {
            foreach ($perfil->permissoes as $permissao) {
                if (
                    $permissao->recurso === $recurso &&
                    $permissao->pivot->{$acao}
                ) {
                    return true;
                }
            }
        }

        return false;
    }


    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function perfis()
    {
        return $this->belongsToMany(Perfil::class, 'perfil_user');
    }

    public function isAdminPanel(): bool
    {
        return $this->perfis()->whereIn('slug', ['admin', 'administrativo'])->exists();
    }

        public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'user_empresa');
    }
}