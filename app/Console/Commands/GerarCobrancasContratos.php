<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Cobranca;
use Carbon\Carbon;

class GerarCobrancasContratos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cobrancas:contratos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera cobranças mensais para clientes com contrato ativo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoje = Carbon::today();

        $clientes = Cliente::where('tipo_cliente', 'CONTRATO')
            ->whereNotNull('valor_mensal')
            ->whereNotNull('dia_vencimento')
            ->get();

        foreach ($clientes as $cliente) {

            $vencimento = Carbon::create(
                $hoje->year,
                $hoje->month,
                min($cliente->dia_vencimento, $hoje->daysInMonth)
            );

            // Evita duplicar cobrança no mesmo mês
            $jaExiste = Cobranca::where('cliente_id', $cliente->id)
                ->whereMonth('data_vencimento', $vencimento->month)
                ->whereYear('data_vencimento', $vencimento->year)
                ->exists();

            if ($jaExiste) {
                continue;
            }

            Cobranca::create([
                'cliente_id'      => $cliente->id,
                'descricao'       => 'Mensalidade contrato',
                'valor'           => $cliente->valor_mensal,
                'data_vencimento' => $vencimento,
                'status'          => 'pendente',
            ]);
        }

        $this->info('Cobranças de contratos geradas com sucesso.');

        return Command::SUCCESS;
    }
}
