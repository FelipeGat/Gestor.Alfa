<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopularDataPagamento extends Command
{
    protected $signature = 'financeiro:popular-data-pagamento';
    protected $description = 'Popula o campo data_pagamento com base no pago_em';

    public function handle()
    {
        $this->info('Atualizando cobranças...');

        $cobrancas = DB::table('cobrancas')
            ->whereNotNull('pago_em')
            ->whereNull('data_pagamento')
            ->update(['data_pagamento' => DB::raw('DATE(pago_em)')]);

        $this->info("✓ {$cobrancas} cobranças atualizadas");

        $this->info('Atualizando contas a pagar...');

        $contas = DB::table('contas_pagar')
            ->whereNotNull('pago_em')
            ->whereNull('data_pagamento')
            ->update(['data_pagamento' => DB::raw('DATE(pago_em)')]);

        $this->info("✓ {$contas} contas a pagar atualizadas");

        $this->info('✅ Concluído!');

        return 0;
    }
}
