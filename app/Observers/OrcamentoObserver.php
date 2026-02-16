<?php

namespace App\Observers;

use App\Models\Orcamento;
use Illuminate\Support\Facades\Log;

class OrcamentoObserver
{
    public function created(Orcamento $orcamento): void
    {
        Log::info('Orçamento criado via observer', [
            'id' => $orcamento->id,
            'numero' => $orcamento->numero_orcamento,
            'valor' => $orcamento->valor_total,
        ]);
    }

    public function updated(Orcamento $orcamento): void
    {
        if ($orcamento->isDirty('status')) {
            Log::info('Status do orçamento alterado', [
                'id' => $orcamento->id,
                'status_anterior' => $orcamento->getOriginal('status'),
                'status_novo' => $orcamento->status,
            ]);
        }
    }

    public function deleted(Orcamento $orcamento): void
    {
        Log::warning('Orçamento excluído', [
            'id' => $orcamento->id,
            'numero' => $orcamento->numero_orcamento,
        ]);
    }

    public function restored(Orcamento $orcamento): void
    {
        Log::info('Orçamento restaurado', [
            'id' => $orcamento->id,
            'numero' => $orcamento->numero_orcamento,
        ]);
    }
}
