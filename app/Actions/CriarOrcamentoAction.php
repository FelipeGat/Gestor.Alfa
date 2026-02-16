<?php

namespace App\Actions;

use App\Actions\DTO\CriarOrcamentoDTO;
use App\Domain\Events\OrcamentoCriado;
use App\Domain\Exceptions\BusinessRuleException;
use App\Models\Empresa;
use App\Models\Orcamento;
use App\Services\Comercial\OrcamentoService;
use Illuminate\Support\Facades\DB;

class CriarOrcamentoAction
{
    public function __construct(
        private OrcamentoService $orcamentoService
    ) {}

    public function execute(CriarOrcamentoDTO $dto): Orcamento
    {
        return DB::transaction(function () use ($dto) {
            $empresa = Empresa::find($dto->empresaId);

            if (! $empresa) {
                throw new BusinessRuleException('Empresa não encontrada');
            }

            if ($dto->clienteId) {
                $cliente = \App\Models\Cliente::find($dto->clienteId);
                if (! $cliente) {
                    throw new BusinessRuleException('Cliente não encontrado');
                }
            }

            if ($dto->valorTotal < 0) {
                throw new BusinessRuleException('Valor total não pode ser negativo');
            }

            $numeroOrcamento = Orcamento::gerarNumero($dto->empresaId);

            $orcamento = Orcamento::create([
                'empresa_id' => $dto->empresaId,
                'atendimento_id' => $dto->atendimentoId,
                'cliente_id' => $dto->clienteId,
                'pre_cliente_id' => $dto->preClienteId,
                'numero_orcamento' => $numeroOrcamento,
                'status' => $dto->status ?? 'rascunho',
                'descricao' => $dto->descricao,
                'valor_total' => $dto->valorTotal,
                'desconto' => $dto->desconto ?? 0,
                'taxas' => $dto->taxas ?? 0,
                'descricao_taxas' => $dto->descricaoTaxas ?? [],
                'forma_pagamento' => $dto->formaPagamento,
                'prazo_pagamento' => $dto->prazoPagamento,
                'validade' => $dto->validade,
                'observacoes' => $dto->observacoes,
                'created_by' => $dto->usuarioId,
            ]);

            if (! empty($dto->itens)) {
                foreach ($dto->itens as $item) {
                    $orcamento->itens()->create([
                        'descricao' => $item['descricao'],
                        'quantidade' => $item['quantidade'],
                        'valor_unitario' => $item['valor_unitario'],
                        'valor_total' => $item['quantidade'] * $item['valor_unitario'],
                        'tipo' => $item['tipo'] ?? 'servico',
                    ]);
                }
            }

            event(new OrcamentoCriado($orcamento, $dto->usuarioId));

            return $orcamento;
        });
    }
}
