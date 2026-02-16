<?php

namespace App\Actions;

use App\Actions\DTO\PagarContaDTO;
use App\Domain\Events\ContaPaga;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\ValueObjects\Dinheiro;
use App\Models\ContaPagar;
use App\Models\MovimentacaoFinanceira;
use Illuminate\Support\Facades\DB;

class PagarContaAction
{
    public function execute(PagarContaDTO $dto): ContaPagar
    {
        return DB::transaction(function () use ($dto) {
            $conta = ContaPagar::with(['conta'])->find($dto->contaId);

            if (! $conta) {
                throw new BusinessRuleException('Conta não encontrada');
            }

            if ($conta->status === 'pago') {
                throw new BusinessRuleException('Esta conta já foi paga');
            }

            $valorOriginal = new Dinheiro($conta->valor);
            $valorPago = new Dinheiro($dto->valor);

            if ($valorPago->ehMaiorQue($valorOriginal)) {
                throw new BusinessRuleException(
                    'Valor pago não pode ser maior que o valor da conta',
                    ['valor_original' => $valorOriginal->formatado(), 'valor_pago' => $valorPago->formatado()]
                );
            }

            $conta->update([
                'status' => 'pago',
                'data_pagamento' => $dto->dataPagamento,
                'valor_pago' => $dto->valor,
                'conta_id' => $dto->contaFinanceiraId,
            ]);

            MovimentacaoFinanceira::create([
                'conta_pagar_id' => $conta->id,
                'conta_id' => $dto->contaFinanceiraId,
                'tipo' => 'saida',
                'valor' => $dto->valor,
                'data_movimentacao' => $dto->dataPagamento,
                'descricao' => "Pagamento: {$conta->descricao}",
                'created_by' => $dto->usuarioId,
            ]);

            event(new ContaPaga($conta, $dto->valor, $dto->usuarioId));

            return $conta->fresh();
        });
    }
}
