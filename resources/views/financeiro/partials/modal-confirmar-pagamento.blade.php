{{-- MODAL: CONFIRMAR PAGAMENTO --}}
<div x-data="{
    open: false,
    action: '',
    contaId: null,
    valorTotal: 0,
    valorPago: 0,
    jurosMulta: 0,
    dataVencimento: '',
    dataPagamento: '',
    contaFinanceiraId: '',
    formaPagamento: '',
    
    getValorRestante() {
        const restante = this.valorTotal - this.valorPago;
        return restante > 0 ? restante.toFixed(2) : '0.00';
    },
    
    getValorTotalComJuros() {
        return (parseFloat(this.valorTotal) + parseFloat(this.jurosMulta || 0)).toFixed(2);
    },
    
    formatarMoeda(valor) {
        return parseFloat(valor || 0).toLocaleString('pt-BR', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    },
    
    validarValorPago() {
        if (this.valorPago < 0) {
            this.valorPago = 0;
        }
    },
    
    validarJurosMulta() {
        if (this.jurosMulta < 0) {
            this.jurosMulta = 0;
        }
    }
}" @confirmar-pagamento.window="
    open = true;
    action = $event.detail.action;
    contaId = $event.detail.contaId;
    valorTotal = parseFloat($event.detail.valorTotal || 0);
    valorPago = valorTotal;
    jurosMulta = 0;
    dataVencimento = $event.detail.dataVencimento || '';
    dataPagamento = new Date().toISOString().split('T')[0];
    contaFinanceiraId = '';
    formaPagamento = '';
" x-show="open" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data do Pagamento <span class="text-red-500">*</span></label>
                        <input type="date" name="data_pagamento" x-model="dataPagamento" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <p class="text-xs text-gray-500 mt-1">Data em que o pagamento foi realizado</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento <span class="text-red-500">*</span></label>
                        <select name="forma_pagamento" x-model="formaPagamento" required
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
                        <select name="conta_financeira_id" x-model="contaFinanceiraId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Sem conta bancária</option>
                            @foreach(\App\Models\ContaFinanceira::where('ativo', true)->orderBy('nome')->get() as $conta)
                            <option value="{{ $conta->id }}">{{ $conta->nome }} - {{ $conta->banco }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- VALORES --}}
                    <div class="space-y-3 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                💰 Valor Total da Conta <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                name="valor_total"
                                step="0.01"
                                min="0"
                                x-model.number="valorTotal"
                                @blur="if(valorTotal < 0) valorTotal = 0; valorPago = valorTotal + parseFloat(jurosMulta || 0);"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-semibold text-blue-700">
                            <p class="text-xs text-gray-500 mt-1">
                                Corrija o valor da conta, se necessário, para este pagamento. O valor original será mantido para os próximos meses.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                ⚠️ Juros / Multa
                            </label>
                            <input type="number"
                                name="juros_multa"
                                step="0.01"
                                min="0"
                                x-model.number="jurosMulta"
                                @input="valorPago = parseFloat(getValorTotalComJuros())"
                                @blur="validarJurosMulta()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 font-semibold text-orange-700">
                            <p class="text-xs text-gray-500 mt-1">
                                Valor adicional de juros ou multa (se houver)
                            </p>
                        </div>

                        <div x-show="parseFloat(jurosMulta) > 0" class="p-2 bg-orange-50 border border-orange-200 rounded">
                            <p class="text-sm text-orange-700">
                                <strong>Total com juros:</strong> R$ <span x-text="formatarMoeda(getValorTotalComJuros())"></span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                💵 Valor Pago <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                name="valor_pago"
                                step="0.01"
                                min="0"
                                x-model.number="valorPago"
                                @blur="validarValorPago()"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 font-semibold text-green-700">
                            <p class="text-xs text-gray-500 mt-1">
                                Digite o valor efetivamente pago
                            </p>
                        </div>

                        <div x-show="parseFloat(getValorRestante()) > 0" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm font-medium text-yellow-800 mb-1">
                                ⚠️ Pagamento Parcial
                            </p>
                            <p class="text-sm text-yellow-700">
                                Valor restante: <strong>R$ <span x-text="formatarMoeda(getValorRestante())"></span></strong>
                            </p>
                            <p class="text-xs text-yellow-600 mt-1">
                                Uma nova conta será criada automaticamente com o valor restante na mesma data de vencimento.
                            </p>
                        </div>
                    </div>

                    {{-- CAMPOS OCULTOS PARA PAGAMENTO PARCIAL --}}
                    <input type="hidden" name="criar_nova_conta" :value="parseFloat(getValorRestante()) > 0 ? '1' : '0'">
                    <input type="hidden" name="valor_restante" :value="getValorRestante()">
                    <input type="hidden" name="data_vencimento_original" :value="dataVencimento">
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