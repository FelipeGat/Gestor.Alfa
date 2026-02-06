<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixFotoCaminhos extends Command
{
    protected $signature = 'fotos:fix-caminhos';
    protected $description = 'Remove o prefixo storage/ do campo arquivo das fotos de andamento';

    public function handle()
    {
        $count = DB::table('atendimento_andamento_fotos')
            ->where('arquivo', 'like', 'storage/%')
            ->update([
                'arquivo' => DB::raw("REPLACE(arquivo, 'storage/', '')")
            ]);

        $this->info("Corrigidos $count registros com prefixo duplicado.");
        return 0;
    }
}
