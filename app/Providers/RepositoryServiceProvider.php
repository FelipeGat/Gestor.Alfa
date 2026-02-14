<?php

namespace App\Providers;

use App\Repositories\Interfaces\ContaPagarRepositoryInterface;
use App\Repositories\Eloquent\ContaPagarRepository;
use App\Models\ContaPagar;
use App\Services\Financeiro\ContaPagarService;
use App\Services\Financeiro\ContaReceberService;
use App\Services\Comercial\OrcamentoService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ContaPagarRepositoryInterface::class,
            function ($app) {
                return new ContaPagarRepository(new ContaPagar());
            }
        );

        $this->app->bind(ContaPagarService::class);
        $this->app->bind(ContaReceberService::class);
        $this->app->bind(OrcamentoService::class);
    }

    public function boot(): void
    {
        //
    }
}
