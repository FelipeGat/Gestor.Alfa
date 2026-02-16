<?php

namespace App\Providers;

use App\Models\Cobranca;
use App\Models\ContaPagar;
use App\Models\Orcamento;
use App\Observers\CobrancaObserver;
use App\Observers\ContaPagarObserver;
use App\Observers\OrcamentoObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Orcamento::observe(OrcamentoObserver::class);
        Cobranca::observe(CobrancaObserver::class);
        ContaPagar::observe(ContaPagarObserver::class);
    }
}
