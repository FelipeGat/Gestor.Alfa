<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Boleto;
use App\Mail\BoletoMensalMail;
use App\Mail\PrimeiroAcessoMail;
use Illuminate\Support\Facades\Mail;

class EnviarBoletosMensais extends Command
{
    protected $signature = 'boletos:enviar-email';

    protected $description = 'Envia e-mail mensal de boletos no dia 01';

    public function handle()
    {
        $mes = now()->month;
        $ano = now()->year;

        $this->info("Iniciando envio de boletos {$mes}/{$ano}...");

        $boletos = Boleto::with(['cliente.user', 'cobranca'])
            ->where('mes', $mes)
            ->where('ano', $ano)
            ->where('status', 'aberto')
            ->whereNull('email_enviado_em') // 🔒 evita reenvio
            ->whereHas('cobranca', function ($q) {
                $q->where('status', '!=', 'pago');
            })
            ->get();

        if ($boletos->isEmpty()) {
            $this->info('Nenhum boleto para envio.');
            return Command::SUCCESS;
        }

        foreach ($boletos as $boleto) {

            $cliente = $boleto->cliente;
            $user    = $cliente?->user;

            if (!$cliente || !$user) {
                $this->warn("Boleto {$boleto->id} sem cliente ou usuário.");
                continue;
            }

            /**
             * 🔐 PRIMEIRO ACESSO — UMA ÚNICA VEZ
             */
            if ($user->primeiro_acesso) {

                Mail::to($user->email)
                    ->send(new PrimeiroAcessoMail($user));

                $this->info("Primeiro acesso enviado para {$user->email}");

                // 🔒 evita reenvio eterno
                $user->update([
                    'primeiro_acesso' => false,
                ]);

                continue;
            }

            /**
             * 📧 BOLETO MENSAL
             */
            Mail::to($user->email)
                ->send(new BoletoMensalMail($boleto));

            // 🔒 Marca como enviado
            $boleto->update([
                'email_enviado_em' => now(),
            ]);

            $this->info("Boleto enviado para {$user->email}");
        }

        $this->info('Envio de boletos finalizado.');
        return Command::SUCCESS;
    }
}