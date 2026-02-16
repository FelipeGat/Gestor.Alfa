<?php

namespace App\Domain\Events;

use App\Models\ContaPagar;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContaPagarCriada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ContaPagar $conta,
        public int $usuarioId
    ) {}
}
