<?php

namespace App\Domain\Events;

use App\Models\ContaPagar;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContaPaga
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ContaPagar $conta,
        public float $valorPago,
        public int $usuarioId
    ) {}
}
