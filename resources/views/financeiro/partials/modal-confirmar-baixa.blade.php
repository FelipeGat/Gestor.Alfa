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
        contaFinanceiraId: '',
        formaPagamento: '',
        contasDisponiveis: [],
        todasContas: @js($todasContasFinanceiras)
    }"
    x-on:confirmar-baixa.window="
        open = true;
        action = $event.detail.action;
        empresaId = $event.detail.empresaId;
        
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