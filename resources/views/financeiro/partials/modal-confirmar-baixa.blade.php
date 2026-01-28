@php
$todasContasFinanceiras = \App\Models\ContaFinanceira::where('ativo', true)
->select('id', 'nome', 'tipo', 'empresa_id')
->get();
@endphp

<div
    x-data="{ 
        open: false, 
        action: '',
        empresaId: null,
        cobrancaId: null,
        contaFinanceiraId: '',
        formaPagamento: '',
        valorTotal: 0,
        valorPago: 0,
        dataVencimento: '',
        contasDisponiveis: [],
        todasContas: @js($todasContasFinanceiras),
        
        getValorRestante() {
            const restante = this.valorTotal - this.valorPago;
            return restante > 0 ? restante.toFixed(2) : '0.00';
        },
        
        formatarMoeda(valor) {
            return parseFloat(valor || 0).toLocaleString('pt-BR', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        },
        
        validarValorPago() {
            if (this.valorPago > this.valorTotal) {
                alert('O valor pago não pode ser maior que o valor total da cobrança.');
                this.valorPago = this.valorTotal;
            }
            if (this.valorPago < 0) {
                this.valorPago = 0;
            }
        }
    }"
    x-on:confirmar-baixa.window="
        open = true;
        action = $event.detail.action;
        empresaId = $event.detail.empresaId;
        cobrancaId = $event.detail.cobrancaId;
        valorTotal = parseFloat($event.detail.valorTotal || 0);
        valorPago = valorTotal;
        dataVencimento = $event.detail.dataVencimento || '';
        
        // Filtrar contas pela empresa
        contasDisponiveis = empresaId ? todasContas.filter(c => c.empresa_id == empresaId) : todasContas;
        
        // Resetar seleção
        contaFinanceiraId = '';
        formaPagamento = '';
    "
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            Confirmar Baixa
        </h3>

        <p class="text-gray-600 mb-4 leading-relaxed">
            Preencha os dados para confirmar o <strong>recebimento</strong> desta cobrança:
        </p>

        <form :action="action" method="POST">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Banco / Conta Financeira <span class="text-red-500">*</span>
                </label>
                <select
                    name="conta_financeira_id"
                    x-model="contaFinanceiraId"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Selecione a conta</option>
                    <template x-for="conta in contasDisponiveis" :key="conta.id">
                        <option :value="conta.id" x-text="`${conta.nome} - ${conta.tipo.toUpperCase()}`"></option>
                    </template>
                </select>
                <p x-show="contasDisponiveis.length === 0" class="text-xs text-red-600 mt-1">
                    Nenhuma conta ativa encontrada para esta empresa.
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Forma de Pagamento <span class="text-red-500">*</span>
                </label>
                <select
                    name="forma_pagamento"
                    x-model="formaPagamento"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Selecione a forma</option>
                    <option value="pix">PIX</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                    <option value="cartao_credito">Cartão de Crédito</option>
                    <option value="cartao_debito">Cartão de Débito</option>
                    <option value="boleto">Boleto</option>
                </select>
            </div>

            {{-- VALORES --}}
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        💰 Valor Total da Cobrança
                    </label>
                    <input
                        type="text"
                        :value="'R$ ' + formatarMoeda(valorTotal)"
                        disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-semibold">
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        💵 Valor Pago <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="valor_pago"
                        step="0.01"
                        min="0"
                        x-model.number="valorPago"
                        @blur="validarValorPago()"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-semibold text-emerald-700">
                    <p class="text-xs text-gray-500 mt-1">
                        Digite o valor efetivamente recebido
                    </p>
                </div>

                <div x-show="parseFloat(getValorRestante()) > 0" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm font-medium text-yellow-800 mb-1">
                        ⚠️ Baixa Parcial
                    </p>
                    <p class="text-sm text-yellow-700">
                        Valor restante: <strong>R$ <span x-text="formatarMoeda(getValorRestante())"></span></strong>
                    </p>
                    <p class="text-xs text-yellow-600 mt-1">
                        Uma nova cobrança será criada automaticamente com o valor restante na mesma data de vencimento.
                    </p>
                </div>
            </div>

            {{-- CAMPOS OCULTOS PARA BAIXA PARCIAL --}}
            <input type="hidden" name="criar_nova_cobranca" :value="parseFloat(getValorRestante()) > 0 ? '1' : '0'">
            <input type="hidden" name="valor_restante" :value="getValorRestante()">
            <input type="hidden" name="data_vencimento_original" :value="dataVencimento"

                <div class="flex gap-3">
            <button
                type="button"
                class="flex-1 px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                x-on:click="open = false">
                Cancelar
            </button>

            <button
                type="submit"
                class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                Confirmar Baixa
            </button>
    </div>
    </form>
</div>
</div>