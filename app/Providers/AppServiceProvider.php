<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\ContaPagar;
use App\Models\Orcamento;
use App\Repositories\Eloquent\ClienteRepository;
use App\Repositories\Eloquent\CobrancaRepository;
use App\Repositories\Eloquent\ContaPagarRepository;
use App\Repositories\Eloquent\OrcamentoRepository;
use App\Repositories\Interfaces\ClienteRepositoryInterface;
use App\Repositories\Interfaces\CobrancaRepositoryInterface;
use App\Repositories\Interfaces\ContaPagarRepositoryInterface;
use App\Repositories\Interfaces\OrcamentoRepositoryInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrcamentoRepositoryInterface::class, function ($app) {
            return new OrcamentoRepository(new Orcamento);
        });

        $this->app->bind(CobrancaRepositoryInterface::class, function ($app) {
            return new CobrancaRepository(new Cobranca);
        });

        $this->app->bind(ContaPagarRepositoryInterface::class, function ($app) {
            return new ContaPagarRepository(new ContaPagar);
        });

        $this->app->bind(ClienteRepositoryInterface::class, function ($app) {
            return new ClienteRepository(new Cliente);
        });
    }

    public function boot(): void
    {
        // SSL disabled - uncomment when SSL is configured
        // if (app()->environment('production')) {
        //     URL::forceScheme('https');
        // }
    }
}
