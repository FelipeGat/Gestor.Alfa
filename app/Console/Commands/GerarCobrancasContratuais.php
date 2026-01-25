<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Boleto;
use App\Models\NotaFiscal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GerarCobrancasContratuais extends Command
{
    protected $signature = 'financeiro:gerar-cobrancas-contratuais';

    protected $description = 'Gera cobranças mensais para clientes com contrato ativo';

    public function handle()
    {
        $mes = now()->month;
        $ano = now()->year;

        $this->info("🔄 Gerando cobranças contratuais {$mes}/{$ano}");

        $clientes = Cliente::where('ativo', true)
            ->where('tipo_cliente', 'CONTRATO')
            ->get();

        foreach ($clientes as $cliente) {

            if (! $cliente->isContratoMensal()) {
                continue;
            }

            // Evita duplicidade
            $jaExiste = Cobranca::where('cliente_id', $cliente->id)
                ->where('origem', 'CONTRATO')
                ->whereMonth('data_vencimento', $mes)
                ->whereYear('data_vencimento', $ano)
                ->exists();

            if ($jaExiste) {
                $this->warn("⏭️ {$cliente->nome_exibicao} já possui cobrança {$mes}/{$ano}");
                continue;
            }

            DB::transaction(function () use ($cliente, $mes, $ano) {

                $dataVencimento = $cliente->gerarDataVencimento($mes, $ano);

                // COBRANÇA
                $cobranca = Cobranca::create([
                    'cliente_id'      => $cliente->id,
                    'descricao'       => "Mensalidade {$mes}/{$ano}",
                    'valor'           => $cliente->valor_mensal,
                    'data_vencimento' => $dataVencimento,
                    'status'          => 'pendente',
                    'origem'          => 'CONTRATO',
                ]);

                // BOLETO (arquivo será anexado depois)
                $boleto = Boleto::create([
                    'cliente_id'      => $cliente->id,
                    'cobranca_id'     => $cobranca->id,
                    'mes'             => $mes,
                    'ano'             => $ano,
                    'valor'           => $cliente->valor_mensal,
                    'data_vencimento' => $dataVencimento,
                    'status'          => 'aberto',
                ]);

                // NOTA FISCAL (se o cliente exigir)
                if ($cliente->inscricao_municipal || $cliente->tipo_pessoa === 'PJ') {

                    NotaFiscal::create([
                        'cliente_id' => $cliente->id,
                        'tipo'       => 'NFS-e',
                        'arquivo'    => null,
                    ]);
                }
            });

            $this->info("✔ Cobrança criada para {$cliente->nome_exibicao}");
        }

        $this->info('✅ Processo finalizado.');
        return Command::SUCCESS;
    }
}
