<?php

namespace App\Actions;

use App\Domain\Events\OrcamentoExcluido;
use App\Domain\Exceptions\BusinessRuleException;
use App\Models\Orcamento;
use Illuminate\Support\Facades\DB;

class ExcluirOrcamentoAction
{
    public function execute(int $orcamentoId, int $usuarioId): bool
    {
        return DB::transaction(function () use ($orcamentoId, $usuarioId) {
            $orcamento = Orcamento::find($orcamentoId);

            if (! $orcamento) {
                throw new BusinessRuleException('Orçamento não encontrado');
            }

            $statusPermitidosExclusao = ['rascunho', 'rejeitado', 'cancelado'];

            if (! in_array($orcamento->status, $statusPermitidosExclusao)) {
                throw new BusinessRuleException(
                    'Orçamento não pode ser excluído no status atual. Apenas orçamentos em rascunho, rejeitado ou cancelado podem ser excluídos.',
                    ['status_atual' => $orcamento->status]
                );
            }

            $temCobrancas = $orcamento->cobrancas()->exists();
            if ($temCobrancas) {
                throw new BusinessRuleException('Orçamento possui cobranças associadas e não pode ser excluído');
            }

            $temMovimentacoes = $orcamento->movimentacoes()->exists();
            if ($temMovimentacoes) {
                throw new BusinessRuleException('Orçamento possui movimentações financeiras e não pode ser excluído');
            }

            $orcamento->itens()->delete();
            $orcamento->delete();

            event(new OrcamentoExcluido($orcamentoId, $usuarioId));

            return true;
        });
    }
}
