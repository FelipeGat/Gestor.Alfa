<?php

namespace App\Console\Commands;

use App\Models\ContaPagar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListarRegistrosTeste extends Command
{
    protected $signature = 'financeiro:listar-testes {--dry-run : Apenas lista os registros sem excluir}';
    protected $description = 'Lista registros de teste na tabela contas_pagar para revisão antes da exclusão';

    private array $padroesFaker = [
        'Exercitationem',
        'ipsam',
        'aperiam',
        'Deserunt',
        'eveniet',
        'dolorem',
        'Sunt',
        'itaque',
    ];

    public function handle(): int
    {
        $dataTeste = '2026-02-15';

        $query = ContaPagar::where(function ($q) use ($dataTeste) {
            // Registros criados em 15/02/2026
            $q->whereDate('created_at', $dataTeste)
                ->orWhereDate('updated_at', $dataTeste);
        });

        $registros = $query->orderBy('created_at')->get();

        if ($registros->isEmpty()) {
            $this->info('Nenhum registro encontrado com a data de 15/02/2026.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$registros->count()} registros criados em 15/02/2026:");
        $this->newLine();

        $headers = ['ID', 'Data Vencimento', 'Descrição', 'Valor', 'Status', 'Criado em'];
        $rows = $registros->map(function ($r) {
            return [
                $r->id,
                $r->data_vencimento?->format('d/m/Y') ?? '-',
                substr($r->descricao, 0, 50) . (strlen($r->descricao) > 50 ? '...' : ''),
                'R$ ' . number_format($r->valor, 2, ',', '.'),
                $r->status,
                $r->created_at?->format('d/m/Y H:i:s'),
            ];
        })->toArray();

        $this->table($headers, $rows);

        $this->newLine();
        $this->warn('ATENÇÃO: Estes registros serão listados para revisão.');
        $this->warn('Execute com --dry-run para apenas listar, ou confirme abaixo para excluir.');

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('Modo dry-run: nenhum registro foi excluído.');
            return Command::SUCCESS;
        }

        if (!$this->confirm("Deseja excluir os {$registros->count()} registros listados acima?")) {
            $this->info('Operação cancelada. Nenhum registro foi excluído.');
            return Command::SUCCESS;
        }

        $ids = $registros->pluck('id')->toArray();
        $deleted = ContaPagar::whereIn('id', $ids)->delete();

        $this->info("{$deleted} registro(s) excluído(s) com sucesso!");

        return Command::SUCCESS;
    }
}
