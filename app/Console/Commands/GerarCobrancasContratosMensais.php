<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Cobranca;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GerarCobrancasContratosMensais extends Command
{
    protected $signature = 'cobrancas:gerar-contratos';

    protected $description = 'Gera cobranças mensais para clientes de contrato';

    public function handle()
    {
        $mesAtual = now()->month;
        $anoAtual = now()->year;

        $this->info("Gerando cobranças de contratos {$mesAtual}/{$anoAtual}");

        $clientes = Cliente::whereNotNull('valor_mensal')
            ->whereNotNull('dia_vencimento')
            ->get();

        foreach ($clientes as $cliente) {

            // Evita duplicidade
            $existe = Cobranca::where('cliente_id', $cliente->id)
                ->whereMonth('data_vencimento', $mesAtual)
                ->whereYear('data_vencimento', $anoAtual)
                ->exists();

            if ($existe) {
                $this->line("• {$cliente->nome}: cobrança já existe");
                continue;
            }

            $dia = min($cliente->dia_vencimento, 28);
            $dataVencimento = Carbon::create($anoAtual, $mesAtual, $dia);

            DB::transaction(function () use ($cliente, $dataVencimento) {

                Cobranca::create([
                    'cliente_id'      => $cliente->id,
                    'descricao'       => 'Mensalidade Contrato',
                    'valor'           => $cliente->valor_mensal,
                    'data_vencimento' => $dataVencimento,
                    'status'          => 'pendente',
                ]);
            });

            $this->info("✔ Cobrança criada para {$cliente->nome}");
        }

        $this->info('Processo finalizado.');
        return Command::SUCCESS;
    }
}
