<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class CriarCobrancaDTO
{
    public function __construct(
        public int $orcamentoId,
        public float $valor,
        public string $dataVencimento,
        public ?string $descricao = null,
        public ?string $observacoes = null,
        public bool $gerarBoleto = false,
        public int $usuarioId = 0
    ) {
        if ($orcamentoId <= 0) {
            throw new InvalidArgumentException('Orçamento é obrigatório');
        }

        if ($valor <= 0) {
            throw new InvalidArgumentException('Valor deve ser maior que zero');
        }

        if (empty($dataVencimento)) {
            throw new InvalidArgumentException('Data de vencimento é obrigatória');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            orcamentoId: (int) $data['orcamento_id'],
            valor: (float) $data['valor'],
            dataVencimento: $data['data_vencimento'],
            descricao: $data['descricao'] ?? null,
            observacoes: $data['observacoes'] ?? null,
            gerarBoleto: $data['gerar_boleto'] ?? false,
            usuarioId: (int) ($data['usuario_id'] ?? 0)
        );
    }
}
