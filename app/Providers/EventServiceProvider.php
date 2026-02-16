<?php

namespace App\Providers;

use App\Domain\Events\CobrancaVencida;
use App\Domain\Events\ContaPaga;
use App\Domain\Events\OrcamentoCriado;
use App\Listeners\LogContaPaga;
use App\Listeners\LogOrcamentoCriado;
use App\Listeners\NotificarCobrancaVencida;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrcamentoCriado::class => [
            LogOrcamentoCriado::class,
        ],
        ContaPaga::class => [
            LogContaPaga::class,
        ],
        CobrancaVencida::class => [
            NotificarCobrancaVencida::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
