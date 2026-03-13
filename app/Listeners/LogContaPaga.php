<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class LogContaPaga implements ShouldHandleEventsAfterCommit
{
    public function handle(object $event): void
    {
        $usuario = isset($event->usuarioId) ? User::find($event->usuarioId) : auth()->user();

        activity()
            ->causedBy($usuario)
            ->performedOn($event->conta)
            ->withProperties([
                'descricao'  => $event->conta->descricao,
                'valor_pago' => $event->valorPago,
            ])
            ->log('conta paga');
    }
}
