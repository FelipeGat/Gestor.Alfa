<?php

namespace App\Services\Financeiro;

use App\Models\Orcamento;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class GeradorCobrancaOrcamento
{
    protected Orcamento $orcamento;
    protected array $dados;

    public function __construct(Orcamento $orcamento, array $dados)
    {
        $this->orcamento = $orcamento;
        $this->dados = $dados;
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODO PRINCIPAL
    |--------------------------------------------------------------------------
    */
    public function gerar(): array
    {
        $this->validarOrcamento();
        $this->validarFormaPagamento();
        $this->validarParcelas();

        return $this->montarParcelas();
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÕES
    |--------------------------------------------------------------------------
    */

    protected function validarOrcamento(): void
    {
        if ($this->orcamento->status !== 'financeiro') {
            throw ValidationException::withMessages([
                'orcamento' => 'Este orçamento não está disponível para cobrança.',
            ]);
        }

        if (!$this->orcamento->cliente_id) {
            throw ValidationException::withMessages([
                'cliente' => 'Não é possível gerar cobrança para Pré-Cliente.',
            ]);
        }

        if ($this->orcamento->cobranca) {
            throw ValidationException::withMessages([
                'orcamento' => 'Este orçamento já possui cobrança.',
            ]);
        }
    }

    protected function validarFormaPagamento(): void
    {
        $formasPermitidas = [
            'pix',
            'debito',
            'credito',
            'faturado',
            'boleto',
        ];

        if (
            empty($this->dados['forma_pagamento']) ||
            !in_array($this->dados['forma_pagamento'], $formasPermitidas)
        ) {
            throw ValidationException::withMessages([
                'forma_pagamento' => 'Forma de pagamento inválida.',
            ]);
        }
    }

    protected function validarParcelas(): void
    {
        $forma = $this->dados['forma_pagamento'];

        // Pagamento imediato
        if (in_array($forma, ['pix', 'debito'])) {
            return;
        }

        if (empty($this->dados['parcelas']) || $this->dados['parcelas'] < 1) {
            throw ValidationException::withMessages([
                'parcelas' => 'Informe a quantidade de parcelas.',
            ]);
        }

        if (
            empty($this->dados['vencimentos']) ||
            count($this->dados['vencimentos']) !== (int) $this->dados['parcelas']
        ) {
            throw ValidationException::withMessages([
                'vencimentos' => 'Datas de vencimento inválidas.',
            ]);
        }

        $ultimaData = null;

        foreach ($this->dados['vencimentos'] as $data) {
            $dataCarbon = Carbon::parse($data);

            // Permitir data de hoje, bloquear apenas datas anteriores a hoje
            if ($dataCarbon->isBefore(Carbon::today())) {
                throw ValidationException::withMessages([
                    'vencimentos' => 'Não é permitido vencimento no passado.',
                ]);
            }

            if ($ultimaData && $dataCarbon->lessThanOrEqualTo($ultimaData)) {
                throw ValidationException::withMessages([
                    'vencimentos' => 'As datas de vencimento devem ser crescentes.',
                ]);
            }

            $ultimaData = $dataCarbon;
        }

        // Validação da soma das parcelas
        $valoresCustomizados = $this->dados['valores_parcelas'] ?? [];
        if (!empty($valoresCustomizados)) {
            $somaParcelas = array_sum(array_map('floatval', $valoresCustomizados));
            $valorTotal = (float) $this->orcamento->valor_total;
            // Aceita diferença de até 10 centavos usando bccomp
            if (bccomp((string)$somaParcelas, (string)($valorTotal + 0.10), 2) === 1) {
                throw ValidationException::withMessages([
                    'parcelas' => 'A soma das Parcelas (R$ ' . number_format($somaParcelas, 2, ',', '.') . ') ultrapassa o valor total da Cobranca (R$ ' . number_format($valorTotal, 2, ',', '.') . ')',
                ]);
            } elseif (bccomp((string)$somaParcelas, (string)($valorTotal - 0.10), 2) === -1) {
                throw ValidationException::withMessages([
                    'parcelas' => 'A soma das Parcelas (R$ ' . number_format($somaParcelas, 2, ',', '.') . ') é inferior ao valor total da Cobranca (R$ ' . number_format($valorTotal, 2, ',', '.') . ')',
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MONTAGEM DAS PARCELAS
    |--------------------------------------------------------------------------
    */

    protected function montarParcelas(): array
    {
        $forma = $this->dados['forma_pagamento'];
        $valorTotal = (float) $this->orcamento->valor_total;

        // Pagamento à vista
        if (in_array($forma, ['pix', 'debito'])) {
            return [[
                'cliente_id'      => $this->orcamento->cliente_id,
                'orcamento_id'    => $this->orcamento->id,
                'valor'           => $valorTotal,
                'data_vencimento' => Carbon::today()->toDateString(),
                'descricao'       => "Orçamento {$this->orcamento->numero_orcamento}",
                'origem'          => 'orcamento',
            ]];
        }

        // Parcelado
        $parcelas = (int) $this->dados['parcelas'];
        $valoresCustomizados = $this->dados['valores_parcelas'] ?? [];

        // Verificar se há valores customizados
        $usarValoresCustomizados = !empty($valoresCustomizados) && count($valoresCustomizados) === $parcelas;

        if ($usarValoresCustomizados) {
            // Usar valores customizados do modal
            $resultado = [];

            foreach ($this->dados['vencimentos'] as $index => $data) {
                $valor = isset($valoresCustomizados[$index]) ? (float) $valoresCustomizados[$index] : 0;

                $resultado[] = [
                    'cliente_id'      => $this->orcamento->cliente_id,
                    'orcamento_id'    => $this->orcamento->id,
                    'valor'           => $valor,
                    'data_vencimento' => Carbon::parse($data)->toDateString(),
                    'descricao'       => "Orçamento {$this->orcamento->numero_orcamento} - Parcela " . ($index + 1) . "/{$parcelas}",
                    'origem'          => 'orcamento',
                ];
            }

            return $resultado;
        }

        // Valor dividido igualmente (comportamento padrão)
        $valorParcela = round($valorTotal / $parcelas, 2);
        $resto = round($valorTotal - ($valorParcela * $parcelas), 2);

        $resultado = [];

        foreach ($this->dados['vencimentos'] as $index => $data) {
            $valor = $valorParcela;

            // Ajuste de centavos na última parcela
            if ($index === ($parcelas - 1)) {
                $valor += $resto;
            }

            $resultado[] = [
                'cliente_id'      => $this->orcamento->cliente_id,
                'orcamento_id'    => $this->orcamento->id,
                'valor'           => $valor,
                'data_vencimento' => Carbon::parse($data)->toDateString(),
                'descricao'       => "Orçamento {$this->orcamento->numero_orcamento} - Parcela " . ($index + 1) . "/{$parcelas}",
                'origem'          => 'orcamento',
            ];
        }

        return $resultado;
    }
}
