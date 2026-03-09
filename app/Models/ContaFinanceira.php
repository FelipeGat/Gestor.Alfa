<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContaFinanceira extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->dontSubmitEmptyLogs();
    }

    protected $table = 'contas_financeiras';

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'bandeira',
        'melhor_dia_compra',
        'dia_fechamento_fatura',
        'dia_vencimento_fatura',
        'limite_credito',
        'limite_credito_utilizado',
        'limite_cheque_especial',
        'saldo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'limite_credito' => 'decimal:2',
        'limite_credito_utilizado' => 'decimal:2',
        'limite_cheque_especial' => 'decimal:2',
        'saldo' => 'decimal:2',
        'melhor_dia_compra' => 'integer',
        'dia_fechamento_fatura' => 'integer',
        'dia_vencimento_fatura' => 'integer',
    ];

    /**
     * Verifica se a conta é um cartão de crédito.
     */
    public function isCartaoCredito(): bool
    {
        return $this->tipo === 'credito';
    }

    /**
     * Retorna o limite disponível do cartão (limite - utilizado).
     */
    public function getLimiteDisponivelAttribute(): float
    {
        return max(0, (float) $this->limite_credito - (float) $this->limite_credito_utilizado);
    }

    /**
     * Recalcula o saldo da conta a partir de uma data, considerando todas as movimentações futuras.
     * Atualiza o campo saldo no banco.
     * @param string|null $dataAjuste (formato Y-m-d ou Y-m-d H:i:s)
     */
    public function reprocessarSaldo($dataAjuste = null)
    {
        $query = $this->movimentacoes()->whereNull('deleted_at');
        if ($dataAjuste) {
            $query->where('data_movimentacao', '>=', $dataAjuste);
        }
        $movs = $query->orderBy('data_movimentacao')->orderBy('id')->get();

        // Busca saldo imediatamente anterior à dataAjuste
        $saldoBase = 0;
        if ($dataAjuste) {
            $saldoBase = $this->movimentacoes()
                ->where('data_movimentacao', '<', $dataAjuste)
                ->whereNull('deleted_at')
                ->orderBy('data_movimentacao', 'desc')
                ->orderBy('id', 'desc')
                ->pluck('saldo_resultante')
                ->first() ?? 0;
        }

        // Recalcula saldo movimentação a movimentação
        $saldo = $saldoBase;
        foreach ($movs as $mov) {
            // Débito (saída) se tipo for saída, crédito (entrada) se tipo for entrada
            // Para tipos customizados, ajuste conforme regra
            if (in_array($mov->tipo, ['TRANSFERENCIA_SAIDA', 'AJUSTE_SALDO_NEGATIVO'])) {
                $saldo -= $mov->valor;
            } else {
                $saldo += $mov->valor;
            }
            // Atualiza saldo_resultante na movimentação (precisa existir na tabela)
            $mov->saldo_resultante = $saldo;
            $mov->save();
        }
        // Atualiza saldo final da conta
        $this->saldo = $saldo;
        $this->save();
    }

    /**
     * Relacionamento com movimentações financeiras (todas onde a conta é origem ou destino)
     */
    public function movimentacoes()
    {
        return $this->hasMany(\App\Models\MovimentacaoFinanceira::class, 'conta_origem_id');
    }

    /**
     * Parcelas em aberto lançadas neste cartão de crédito.
     */
    public function parcelasCartao()
    {
        return $this->hasMany(\App\Models\ContaPagar::class, 'cartao_credito_id')
            ->where('status', '!=', 'pago');
    }

    /**
     * Relacionamento com empresa
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id');
    }
}
