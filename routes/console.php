<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('boletos:enviar-email')
    ->monthlyOn(1, '08:00');

Schedule::command('boletos:marcar-vencidos')
    ->dailyAt('00:05');