<?php

namespace App\Observers;

use App\Models\Cobranca;
use Illuminate\Support\Facades\Log;

class CobrancaObserver
{
    public function created(Cobranca $cobranca): void
    {
        Log::info('Cobrança criada', [
            'id' => $cobranca->id,
            'orcamento_id' => $cobranca->orcamento_id,
            'valor' => $cobranca->valor,
            'vencimento' => $cobranca->data_vencimento,
        ]);
    }

    public function updated(Cobranca $cobranca): void
    {
        if ($cobranca->isDirty('status') && $cobranca->status === 'pago') {
            Log::info('Cobrança marcada como paga', [
                'id' => $cobranca->id,
                'valor_pago' => $cobranca->valor_pago,
                'data_pagamento' => $cobranca->data_pagamento,
            ]);
        }

        if ($cobranca->isDirty('status') && $cobranca->getOriginal('status') !== 'pago' && $cobranca->status === 'vencido') {
            Log::warning('Cobrança vencida', [
                'id' => $cobranca->id,
                'data_vencimento' => $cobranca->data_vencimento,
            ]);
        }
    }

    public function deleted(Cobranca $cobranca): void
    {
        Log::warning('Cobrança excluída', [
            'id' => $cobranca->id,
            'orcamento_id' => $cobranca->orcamento_id,
        ]);
    }
}
