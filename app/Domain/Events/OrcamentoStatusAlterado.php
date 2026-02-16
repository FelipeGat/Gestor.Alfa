<?php

namespace App\Domain\Events;

use App\Models\Orcamento;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrcamentoStatusAlterado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Orcamento $orcamento,
        public string $statusAnterior,
        public string $statusNovo,
        public int $usuarioId
    ) {}
}
