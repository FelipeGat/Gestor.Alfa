<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Password;

class PrimeiroAcessoMail extends Mailable
{
    public User $user;
    public string $link;

    public function __construct(User $user)
    {
        $this->user = $user;

        $token = Password::createToken($user);
        $this->link = url("/reset-password/{$token}?email={$user->email}");
    }

    public function build()
    {
        return $this->subject('Crie sua senha de acesso')
            ->view('emails.primeiro-acesso');
    }
}