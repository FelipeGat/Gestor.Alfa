<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait LogsUserActivity
{
    protected function registrarLog(string $acao, $modelo = null, array $propriedades = []): void
    {
        try {
            $logEntry = activity()
                ->causedBy(Auth::user())
                ->withProperties($propriedades)
                ->event($acao);

            if ($modelo !== null) {
                $logEntry = $logEntry->performedOn($modelo);
            }

            $logEntry->log($acao);
        } catch (\Throwable $e) {
            Log::warning("Falha ao registrar log de atividade [{$acao}]: " . $e->getMessage());
        }
    }
}
