<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Boleto;

class MarcarBoletosVencidos extends Command
{
    protected $signature = 'boletos:marcar-vencidos';

    protected $description = 'Marca boletos em aberto como vencidos';

    public function handle()
    {
        $hoje = now()->startOfDay();

        $boletos = Boleto::where('status', 'aberto')
            ->whereDate('data_vencimento', '<', $hoje)
            ->get();

        foreach ($boletos as $boleto) {
            $boleto->update([
                'status' => 'vencido',
            ]);

            $this->info(
                "Boleto {$boleto->id} ({$boleto->mes}/{$boleto->ano}) marcado como vencido."
            );
        }

        if ($boletos->isEmpty()) {
            $this->info('Nenhum boleto vencido encontrado.');
        }

        return Command::SUCCESS;
    }
}