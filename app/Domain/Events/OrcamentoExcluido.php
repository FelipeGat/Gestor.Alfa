<?php

namespace App\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrcamentoExcluido
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $orcamentoId,
        public int $usuarioId
    ) {}
}
