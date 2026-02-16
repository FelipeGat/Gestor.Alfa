<?php

namespace App\Listeners;

use App\Domain\Events\CobrancaVencida;
use Illuminate\Support\Facades\Log;

class NotificarCobrancaVencida
{
    public function handle(CobrancaVencida $event): void
    {
        $cobranca = $event->cobranca;

        Log::warning('Cobrança vencida', [
            'cobranca_id' => $cobranca->id,
            'orcamento_id' => $cobranca->orcamento_id,
            'valor' => $cobranca->valor,
            'data_vencimento' => $cobranca->data_vencimento,
        ]);
    }
}
