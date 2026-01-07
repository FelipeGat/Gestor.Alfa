<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Boleto;
use App\Models\Cobranca;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScanBoletos extends Command
{
    protected $signature = 'boletos:scan';

    protected $description = 'Lê a pasta storage/app/boletos e cria cobranças automaticamente';

    public function handle()
    {
        $this->info('Iniciando leitura de boletos...');

        $basePath = storage_path('app/boletos');

        if (!is_dir($basePath)) {
            $this->error('Pasta storage/app/boletos não encontrada.');
            return Command::FAILURE;
        }

        $pastasClientes = scandir($basePath);

        foreach ($pastasClientes as $pastaCliente) {

            if ($pastaCliente === '.' || $pastaCliente === '..') {
                continue;
            }

            if (!str_starts_with($pastaCliente, 'cliente_')) {
                continue;
            }

            $clienteId = (int) str_replace('cliente_', '', $pastaCliente);
            $cliente   = Cliente::find($clienteId);

            if (!$cliente) {
                $this->warn("Cliente {$clienteId} não encontrado. Ignorando pasta.");
                continue;
            }

            if (!$cliente->valor_mensal || !$cliente->dia_vencimento) {
                $this->warn("Cliente {$cliente->nome} sem valor mensal ou dia de vencimento.");
                continue;
            }

            $this->line("Processando boletos do cliente {$cliente->nome} (ID {$clienteId})");

            $pastaBoletosCliente = $basePath . DIRECTORY_SEPARATOR . $pastaCliente;
            $arquivos = scandir($pastaBoletosCliente);

            foreach ($arquivos as $arquivo) {

                if ($arquivo === '.' || $arquivo === '..') {
                    continue;
                }

                // boleto_01_2026.pdf
                if (!preg_match('/^boleto_(\d{2})_(\d{4})\.pdf$/', $arquivo, $matches)) {
                    continue;
                }

                $mes = (int) $matches[1];
                $ano = (int) $matches[2];

                // Evita duplicação
                $existeCobranca = Cobranca::where('cliente_id', $clienteId)
                    ->whereMonth('data_vencimento', $mes)
                    ->whereYear('data_vencimento', $ano)
                    ->exists();

                if ($existeCobranca) {
                    $this->line("• Cobrança {$mes}/{$ano} já existe. Ignorada.");
                    continue;
                }

                $dia = min($cliente->dia_vencimento, 28);
                $dataVencimento = Carbon::create($ano, $mes, $dia);

                DB::transaction(function () use (
                    $clienteId,
                    $cliente,
                    $mes,
                    $ano,
                    $arquivo,
                    $pastaCliente,
                    $dataVencimento
                ) {

                    // ✅ 1. Cria o BOLETO
                    $arquivoRelativo = "boletos/{$pastaCliente}/{$arquivo}";

                    $boleto = Boleto::create([
                        'cliente_id'      => $clienteId,
                        'mes'             => $mes,
                        'ano'             => $ano,
                        'valor'           => $cliente->valor_mensal,
                        'data_vencimento' => $dataVencimento,
                        'arquivo'         => $arquivoRelativo,
                        'status'          => Boleto::STATUS_ABERTO,
                    ]);

                    // ✅ 2. Cria a COBRANÇA vinculada ao boleto
                    Cobranca::create([
                        'cliente_id'      => $clienteId,
                        'boleto_id'       => $boleto->id,
                        'descricao'       => "Mensalidade {$mes}/{$ano}",
                        'valor'           => $boleto->valor,
                        'data_vencimento' => $dataVencimento,
                        'status'          => 'pendente',
                    ]);
                });

                $this->info("✔ Cobrança e boleto {$mes}/{$ano} criados com sucesso.");
            }
        }

        $this->info('Leitura de boletos finalizada com sucesso.');
        return Command::SUCCESS;
    }
}