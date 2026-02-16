<?php

namespace App\Actions\DTO;

use InvalidArgumentException;

class AtualizarStatusOrcamentoDTO
{
    private const STATUS_VALIDOS = [
        'rascunho', 'enviado', 'aprovado', 'rejeitado',
        'financeiro', 'executando', 'concluido', 'cancelado',
    ];

    public function __construct(
        public int $orcamentoId,
        public string $novoStatus,
        public ?string $observacao = null,
        public int $usuarioId = 0
    ) {
        if ($orcamentoId <= 0) {
            throw new InvalidArgumentException('Orçamento é obrigatório');
        }

        if (! in_array($novoStatus, self::STATUS_VALIDOS)) {
            throw new InvalidArgumentException('Status inválido: '.implode(', ', self::STATUS_VALIDOS));
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            orcamentoId: (int) $data['orcamento_id'],
            novoStatus: $data['novo_status'],
            observacao: $data['observacao'] ?? null,
            usuarioId: (int) ($data['usuario_id'] ?? 0)
        );
    }
}
