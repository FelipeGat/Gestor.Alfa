<?php

namespace App\Listeners;

use App\Domain\Events\ContaPaga;
use Illuminate\Support\Facades\Log;

class LogContaPaga
{
    public function handle(ContaPaga $event): void
    {
        Log::info('Conta paga registrada', [
            'conta_id' => $event->conta->id,
            'descricao' => $event->conta->descricao,
            'valor_pago' => $event->valorPago,
            'usuario_id' => $event->usuarioId,
        ]);
    }
}
