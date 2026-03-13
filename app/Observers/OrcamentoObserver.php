<?php

namespace App\Observers;

use App\Models\Orcamento;

class OrcamentoObserver
{
    public function created(Orcamento $orcamento): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($orcamento)
            ->withProperties([
                'numero' => $orcamento->numero_orcamento,
                'valor'  => $orcamento->valor_total,
            ])
            ->log('orçamento criado');
    }

    public function updated(Orcamento $orcamento): void
    {
        if ($orcamento->isDirty('status')) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($orcamento)
                ->withProperties([
                    'status_anterior' => $orcamento->getOriginal('status'),
                    'status_novo'     => $orcamento->status,
                ])
                ->log('status do orçamento alterado');
        }
    }

    public function deleted(Orcamento $orcamento): void
    {
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'id'     => $orcamento->id,
                'numero' => $orcamento->numero_orcamento,
            ])
            ->log('orçamento excluído');
    }

    public function restored(Orcamento $orcamento): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($orcamento)
            ->withProperties(['numero' => $orcamento->numero_orcamento])
            ->log('orçamento restaurado');
    }
}
