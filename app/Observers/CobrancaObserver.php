<?php

namespace App\Observers;

use App\Models\Cobranca;

class CobrancaObserver
{
    public function created(Cobranca $cobranca): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($cobranca)
            ->withProperties([
                'orcamento_id' => $cobranca->orcamento_id,
                'valor'        => $cobranca->valor,
                'vencimento'   => optional($cobranca->data_vencimento)->format('d/m/Y'),
            ])
            ->log('cobrança criada');
    }

    public function updated(Cobranca $cobranca): void
    {
        if ($cobranca->isDirty('status') && $cobranca->status === 'pago') {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($cobranca)
                ->withProperties([
                    'valor_pago'      => $cobranca->valor_pago,
                    'data_pagamento'  => optional($cobranca->data_pagamento)->format('d/m/Y'),
                ])
                ->log('cobrança paga');
        }

        if ($cobranca->isDirty('status') && $cobranca->status === 'vencido') {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($cobranca)
                ->withProperties([
                    'data_vencimento' => optional($cobranca->data_vencimento)->format('d/m/Y'),
                ])
                ->log('cobrança vencida');
        }
    }

    public function deleted(Cobranca $cobranca): void
    {
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'id'           => $cobranca->id,
                'orcamento_id' => $cobranca->orcamento_id,
            ])
            ->log('cobrança excluída');
    }
}
