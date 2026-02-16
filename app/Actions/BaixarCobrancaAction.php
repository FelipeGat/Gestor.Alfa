<?php

namespace App\Actions;

use App\Actions\DTO\BaixarCobrancaDTO;
use App\Domain\Events\CobrancaPaga;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\ValueObjects\Dinheiro;
use App\Models\Cobranca;
use App\Models\MovimentacaoFinanceira;
use Illuminate\Support\Facades\DB;

class BaixarCobrancaAction
{
    public function execute(BaixarCobrancaDTO $dto): Cobranca
    {
        return DB::transaction(function () use ($dto) {
            $cobranca = Cobranca::with(['orcamento'])->find($dto->cobrancaId);

            if (! $cobranca) {
                throw new BusinessRuleException('Cobrança não encontrada');
            }

            if ($cobranca->status === 'pago') {
                throw new BusinessRuleException('Esta cobrança já foi paga');
            }

            $valorOriginal = new Dinheiro($cobranca->valor);
            $valorPago = new Dinheiro($dto->valor);

            if ($valorPago->ehMaiorQue($valorOriginal)) {
                throw new BusinessRuleException(
                    'Valor pago não pode ser maior que o valor da cobrança',
                    ['valor_original' => $valorOriginal->formatado(), 'valor_pago' => $valorPago->formatado()]
                );
            }

            $cobranca->update([
                'status' => 'pago',
                'data_pagamento' => $dto->dataPagamento,
                'valor_pago' => $dto->valor,
                'forma_pagamento' => $dto->formaPagamento,
                'observacoes' => $dto->observacoes,
            ]);

            if ($cobranca->orcamento) {
                MovimentacaoFinanceira::create([
                    'orcamento_id' => $cobranca->orcamento_id,
                    'conta_id' => $dto->contaFinanceiraId,
                    'tipo' => 'entrada',
                    'valor' => $dto->valor,
                    'data_movimentacao' => $dto->dataPagamento,
                    'descricao' => "Recebimento: Cobranca #{$cobranca->id}",
                    'created_by' => $dto->usuarioId,
                ]);
            }

            event(new CobrancaPaga($cobranca, $dto->valor, $dto->usuarioId));

            return $cobranca->fresh();
        });
    }
}
