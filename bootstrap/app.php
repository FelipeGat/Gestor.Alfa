<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'cliente' => \App\Http\Middleware\ClienteMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'funcionario' => \App\Http\Middleware\FuncionarioMiddleware::class,
            'primeiro_acesso' => \App\Http\Middleware\PrimeiroAcesso::class,
            'admin.panel' => \App\Http\Middleware\AdminPanelMiddleware::class,
            'dashboard.comercial' => \App\Http\Middleware\DashboardComercialMiddleware::class,
            'dashboard.admin' => \App\Http\Middleware\DashboardAdminMiddleware::class,
            'financeiro' => \App\Http\Middleware\FinanceiroMiddleware::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'rate.uploads' => \App\Http\Middleware\RateLimitUploads::class,
            'rate.api' => \App\Http\Middleware\RateLimitApi::class,
            'rate.forms' => \App\Http\Middleware\RateLimitForms::class,
            'api.version' => \App\Http\Middleware\ApiVersion::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // Geração automática de cobranças de contratos
        $schedule->command('cobrancas:contratos')->daily();
    })
    ->create();
