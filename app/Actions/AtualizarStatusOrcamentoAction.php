<?php

namespace App\Actions;

use App\Actions\DTO\AtualizarStatusOrcamentoDTO;
use App\Domain\Exceptions\BusinessRuleException;
use App\Models\Orcamento;
use Illuminate\Support\Facades\DB;

class AtualizarStatusOrcamentoAction
{
    private const TRANSITIONS = [
        'rascunho' => ['enviado', 'cancelado'],
        'enviado' => ['aprovado', 'rejeitado', 'rascunho'],
        'aprovado' => ['financeiro', 'cancelado'],
        'financeiro' => ['executando', 'cancelado'],
        'executando' => ['concluido', 'cancelado'],
        'rejeitado' => ['rascunho'],
        'cancelado' => [],
        'concluido' => [],
    ];

    public function execute(AtualizarStatusOrcamentoDTO $dto): Orcamento
    {
        return DB::transaction(function () use ($dto) {
            $orcamento = Orcamento::find($dto->orcamentoId);

            if (! $orcamento) {
                throw new BusinessRuleException('Orçamento não encontrado');
            }

            $statusAtual = $orcamento->status;
            $novoStatus = $dto->novoStatus;

            if ($statusAtual === $novoStatus) {
                throw new BusinessRuleException('Orçamento já está neste status');
            }

            $transicoesPermitidas = self::TRANSITIONS[$statusAtual] ?? [];

            if (! in_array($novoStatus, $transicoesPermitidas)) {
                throw new BusinessRuleException(
                    "Não é possível alterar de '{$statusAtual}' para '{$novoStatus}'",
                    ['status_atual' => $statusAtual, 'status_solicitado' => $novoStatus]
                );
            }

            $orcamento->update(['status' => $novoStatus]);

            $orcamento->historico()->create([
                'status_de' => $statusAtual,
                'status_para' => $novoStatus,
                'observacao' => $dto->observacao,
                'user_id' => $dto->usuarioId,
            ]);

            return $orcamento;
        });
    }
}
