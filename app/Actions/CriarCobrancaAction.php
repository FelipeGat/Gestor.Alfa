<?php

namespace App\Actions;

use App\Actions\DTO\CriarCobrancaDTO;
use App\Domain\Events\CobrancaCriada;
use App\Domain\Exceptions\BusinessRuleException;
use App\Models\Cobranca;
use App\Models\Orcamento;
use Illuminate\Support\Facades\DB;

class CriarCobrancaAction
{
    public function execute(CriarCobrancaDTO $dto): Cobranca
    {
        return DB::transaction(function () use ($dto) {
            $orcamento = Orcamento::find($dto->orcamentoId);

            if (! $orcamento) {
                throw new BusinessRuleException('Orçamento não encontrado');
            }

            if (! in_array($orcamento->status, ['aprovado', 'financeiro'])) {
                throw new BusinessRuleException('Orçamento precisa estar aprovado ou em financeiro para gerar cobrança');
            }

            $cobranca = Cobranca::create([
                'orcamento_id' => $dto->orcamentoId,
                'status' => 'pendente',
                'valor' => $dto->valor,
                'data_vencimento' => $dto->dataVencimento,
                'descricao' => $dto->descricao,
                'observacoes' => $dto->observacoes,
            ]);

            if ($dto->gerarBoleto) {
                // Aqui seria chamada a integração com serviço de boleto
            }

            event(new CobrancaCriada($cobranca, $dto->usuarioId));

            return $cobranca;
        });
    }
}
