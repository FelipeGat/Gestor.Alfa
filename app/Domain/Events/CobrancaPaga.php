<?php

namespace App\Domain\Events;

use App\Models\Cobranca;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CobrancaPaga
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Cobranca $cobranca,
        public float $valorPago,
        public int $usuarioId
    ) {}
}
