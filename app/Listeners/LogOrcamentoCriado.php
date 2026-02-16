<?php

namespace App\Listeners;

use App\Domain\Events\OrcamentoCriado;
use Illuminate\Support\Facades\Log;

class LogOrcamentoCriado
{
    public function handle(OrcamentoCriado $event): void
    {
        Log::info('Orçamento criado', [
            'orcamento_id' => $event->orcamento->id,
            'numero' => $event->orcamento->numero_orcamento,
            'valor' => $event->orcamento->valor_total,
            'usuario_id' => $event->usuarioId,
        ]);
    }
}
