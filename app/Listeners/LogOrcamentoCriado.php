<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class LogOrcamentoCriado implements ShouldHandleEventsAfterCommit
{
    public function handle(object $event): void
    {
        $usuario = isset($event->usuarioId) ? User::find($event->usuarioId) : auth()->user();

        if (isset($event->orcamento)) {
            activity()
                ->causedBy($usuario)
                ->performedOn($event->orcamento)
                ->withProperties([
                    'numero' => $event->orcamento->numero_orcamento,
                    'valor'  => $event->orcamento->valor_total,
                ])
                ->log('orçamento criado (evento)');
        } elseif (isset($event->cliente)) {
            activity()
                ->causedBy($usuario)
                ->performedOn($event->cliente)
                ->log('cliente criado (evento)');
        } elseif (isset($event->conta)) {
            activity()
                ->causedBy($usuario)
                ->performedOn($event->conta)
                ->withProperties(['descricao' => $event->conta->descricao])
                ->log('conta a pagar criada (evento)');
        }
    }
}
