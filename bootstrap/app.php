<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'cliente' => \App\Http\Middleware\ClienteMiddleware::class,
            'admin'   => \App\Http\Middleware\AdminMiddleware::class,
            'funcionario' => \App\Http\Middleware\FuncionarioMiddleware::class,
            'primeiro_acesso' => \App\Http\Middleware\PrimeiroAcesso::class,
            'admin.panel' => \App\Http\Middleware\AdminPanelMiddleware::class,
            'dashboard.comercial' => \App\Http\Middleware\DashboardComercialMiddleware::class,
            'dashboard.admin' => \App\Http\Middleware\DashboardAdminMiddleware::class,
            'financeiro' => \App\Http\Middleware\FinanceiroMiddleware::class,


        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
