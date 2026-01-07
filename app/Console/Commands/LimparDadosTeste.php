<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Boleto;
use Illuminate\Support\Facades\DB;

class LimparDadosTeste extends Command
{
    protected $signature = 'sistema:limpar-teste';

    protected $description = 'Limpa dados do sistema mantendo apenas o admin';

    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Boleto::truncate();
        Cobranca::truncate();
        Cliente::truncate();

        User::where('email', '!=', 'felipehenriquegat@gmail.com')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Sistema limpo com sucesso. Admin mantido.');
        return Command::SUCCESS;
    }
}