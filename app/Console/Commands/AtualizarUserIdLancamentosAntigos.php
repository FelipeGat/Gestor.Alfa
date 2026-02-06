<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ContaPagar;
use App\Models\Cobranca;

class AtualizarUserIdLancamentosAntigos extends Command
{
    protected $signature = 'lancamentos:atualizar-user-id-antigos';
    protected $description = 'Atualiza o user_id dos lançamentos antigos de contas a pagar e cobranças para o usuário Felipe (id=1)';

    public function handle()
    {
        $this->info('Atualizando contas a pagar...');
        $contas = ContaPagar::where('status', 'pago')->whereNull('user_id')->update(['user_id' => 1]);
        $this->info("Contas a pagar atualizadas: {$contas}");

        $this->info('Atualizando cobranças...');
        $cobrancas = Cobranca::where('status', 'pago')->whereNull('user_id')->update(['user_id' => 1]);
        $this->info("Cobranças atualizadas: {$cobrancas}");

        $this->info('Processo concluído!');
        return 0;
    }
}
