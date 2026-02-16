<?php

namespace App\Observers;

use App\Models\ContaPagar;
use Illuminate\Support\Facades\Log;

class ContaPagarObserver
{
    public function created(ContaPagar $conta): void
    {
        Log::info('Conta a pagar criada', [
            'id' => $conta->id,
            'descricao' => $conta->descricao,
            'valor' => $conta->valor,
            'vencimento' => $conta->data_vencimento,
        ]);
    }

    public function updated(ContaPagar $conta): void
    {
        if ($conta->isDirty('status') && $conta->status === 'pago') {
            Log::info('Conta a pagar marcada como paga', [
                'id' => $conta->id,
                'valor_pago' => $conta->valor_pago,
                'data_pagamento' => $conta->data_pagamento,
            ]);
        }

        if ($conta->isDirty('status') && $conta->getOriginal('status') !== 'pago' && $conta->status === 'vencido') {
            Log::warning('Conta a pagar vencida', [
                'id' => $conta->id,
                'data_vencimento' => $conta->data_vencimento,
            ]);
        }
    }

    public function deleted(ContaPagar $conta): void
    {
        Log::warning('Conta a pagar excluída', [
            'id' => $conta->id,
            'descricao' => $conta->descricao,
        ]);
    }
}
