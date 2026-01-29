{{-- MODAL: CONFIRMAR PAGAMENTO --}}
<div x-data="{
    open: false,
    action: '',
    contaId: null,
    valorTotal: 0
}" @confirmar-pagamento.window="
    open = true;
    action = $event.detail.action;
    contaId = $event.detail.contaId;
    valorTotal = $event.detail.valorTotal;
" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <form :action="action" method="POST">
                @csrf
                @method('PATCH')

                <div class="bg-green-50 px-6 py-4 border-b border-green-200">
                    <h3 class="text-lg font-bold text-gray-900">Confirmar Pagamento</h3>
                </div>

                <div class="bg-white px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-600">Informe os dados do pagamento:</p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento <span class="text-red-500">*</span></label>
                        <select name="forma_pagamento" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Selecione...</option>
                            <option value="pix">PIX</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="transferencia">Transferência</option>
                            <option value="boleto">Boleto</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conta Bancária</label>
                        <select name="conta_financeira_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Sem conta bancária</option>
                            @foreach(\App\Models\ContaFinanceira::where('ativo', true)->orderBy('nome')->get() as $conta)
                            <option value="{{ $conta->id }}">{{ $conta->nome }} - {{ $conta->banco }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Valor:</span>
                            <span class="text-lg font-bold text-gray-900" x-text="'R$ ' + valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                        Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>