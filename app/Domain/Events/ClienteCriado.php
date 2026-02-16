<?php

namespace App\Domain\Events;

use App\Models\Cliente;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClienteCriado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Cliente $cliente,
        public int $usuarioId
    ) {}
}
