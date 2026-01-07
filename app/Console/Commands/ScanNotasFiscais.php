<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\NotaFiscal;

class ScanNotasFiscais extends Command
{
    protected $signature = 'nfs:scan';

    protected $description = 'Lê a pasta de clientes e cadastra Notas Fiscais automaticamente';

    public function handle()
    {
        $this->info('Iniciando leitura de Notas Fiscais...');

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
            $cliente = Cliente::find($clienteId);

            if (!$cliente) {
                continue;
            }

            $this->line("Processando NFs do cliente {$cliente->nome}");

            $pastaClientePath = $basePath . DIRECTORY_SEPARATOR . $pastaCliente;
            $arquivos = scandir($pastaClientePath);

            foreach ($arquivos as $arquivo) {

                if ($arquivo === '.' || $arquivo === '..') {
                    continue;
                }

                /**
                 * Ex: 185-NFS-e-Felipe-Gat.pdf
                 */
                if (!preg_match('/^(\d+)-NFS-e-.*\.pdf$/i', $arquivo, $matches)) {
                    continue;
                }

                $numero = (int) $matches[1];

                // Evita duplicação
                $existe = NotaFiscal::where('cliente_id', $clienteId)
                    ->where('numero', $numero)
                    ->exists();

                if ($existe) {
                    continue;
                }

                $arquivoRelativo = "boletos/{$pastaCliente}/{$arquivo}";

                NotaFiscal::create([
                    'cliente_id' => $clienteId,
                    'numero'     => $numero,
                    'tipo'       => 'NFS-e',
                    'arquivo'    => $arquivoRelativo,
                ]);

                $this->info("✔ NF {$numero} cadastrada.");
            }
        }

        $this->info('Leitura de Notas Fiscais finalizada.');
        return Command::SUCCESS;
    }
}