<?php

namespace App\Actions;

use App\Actions\DTO\AtualizarOrcamentoDTO;
use App\Domain\Events\OrcamentoAtualizado;
use App\Domain\Exceptions\BusinessRuleException;
use App\Models\Orcamento;
use Illuminate\Support\Facades\DB;

class AtualizarOrcamentoAction
{
    public function execute(int $orcamentoId, AtualizarOrcamentoDTO $dto, int $usuarioId): Orcamento
    {
        return DB::transaction(function () use ($orcamentoId, $dto, $usuarioId) {
            $orcamento = Orcamento::find($orcamentoId);

            if (! $orcamento) {
                throw new BusinessRuleException('Orçamento não encontrado');
            }

            $statusPermitidosEdicao = ['rascunho', 'enviado', 'rejeitado'];

            if (! in_array($orcamento->status, $statusPermitidosEdicao)) {
                throw new BusinessRuleException('Orçamento não pode ser editado no status atual');
            }

            $dados = $dto->toArray();

            if (! empty($dados['cliente_id']) && ! empty($dados['pre_cliente_id'])) {
                throw new BusinessRuleException('Não é permitido informar cliente e pré-cliente simultaneamente');
            }

            if (! empty($dados['valor_total'])) {
                $orcamentoItem = Orcamento::where('id', $orcamentoId)
                    ->with('itens')
                    ->first();

                $totalItens = $orcamentoItem?->itens->sum('valor_total') ?? 0;

                if ($dados['valor_total'] < $totalItens) {
                    throw new BusinessRuleException('Valor total não pode ser menor que a soma dos itens');
                }
            }

            $dados['updated_by'] = $usuarioId;

            $orcamento->update(array_filter($dados, fn ($value) => $value !== null));

            event(new OrcamentoAtualizado($orcamento, $usuarioId));

            return $orcamento->fresh();
        });
    }
}
