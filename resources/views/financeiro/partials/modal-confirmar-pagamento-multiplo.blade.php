<div x-data="{ open: false, contaPagarIds: [], action: '' }"
     x-on:confirmar-pagamento-multiplo.window="open = true; contaPagarIds = $event.detail.contaPagarIds; action = '{{ route('financeiro.contasapagar.baixa-multipla') }}'"
     x-show="open"
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-[100dvh] px-4 py-4">
        <div x-show="open" class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="open = false"></div>
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">Confirmar pagamento de <span x-text="contaPagarIds.length"></span> contas selecionadas</h2>
        <form :action="action" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="conta_ids" :value="JSON.stringify(contaPagarIds)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data do pagamento</label>
                <input type="date" name="data_pagamento" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Banco / Conta financeira</label>
                <select name="conta_financeira_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    @foreach($contasFinanceiras as $conta)
                        <option value="{{ $conta->id }}">{{ $conta->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Forma de pagamento</label>
                <select name="forma_pagamento" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="pix">Pix</option>
                    <option value="transferencia">Transferência</option>
                    <option value="boleto">Boleto</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="cartao">Cartão</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 bg-gray-300 rounded-lg">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Confirmar Pagamento</button>
            </div>
        </form>
    </div>
    </div>
</div>
