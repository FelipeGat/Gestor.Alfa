<?php

namespace App\Observers;

use App\Models\ContaPagar;

class ContaPagarObserver
{
    public function created(ContaPagar $conta): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($conta)
            ->withProperties([
                'descricao'  => $conta->descricao,
                'valor'      => $conta->valor,
                'vencimento' => optional($conta->data_vencimento)->format('d/m/Y'),
            ])
            ->log('conta a pagar criada');
    }

    public function updated(ContaPagar $conta): void
    {
        if ($conta->isDirty('status') && $conta->status === 'pago') {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($conta)
                ->withProperties([
                    'valor_pago'     => $conta->valor_pago,
                    'data_pagamento' => optional($conta->data_pagamento)->format('d/m/Y'),
                ])
                ->log('conta a pagar paga');
        }

        if ($conta->isDirty('status') && $conta->status === 'vencido') {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($conta)
                ->withProperties([
                    'data_vencimento' => optional($conta->data_vencimento)->format('d/m/Y'),
                ])
                ->log('conta a pagar vencida');
        }
    }

    public function deleted(ContaPagar $conta): void
    {
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'id'        => $conta->id,
                'descricao' => $conta->descricao,
            ])
            ->log('conta a pagar excluída');
    }
}
