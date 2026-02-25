<div x-data="{
    init() {
        this.$watch('\$store.modalCobranca.open', (value) => {
            if (value) {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modal-gerar-cobranca' }));
            } else {
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-gerar-cobranca' }));
            }
        });
    }
}">
<x-modal name="modal-gerar-cobranca" maxWidth="lg" title="Gerar Cobrança">
    <form 
        id="formGerarCobranca"
        method="POST"
        x-data="gerarCobranca()"
        :action="`{{ url('/financeiro/orcamentos') }}/${$store.modalCobranca.orcamento?.id}/gerar-cobranca`"
        @submit.prevent="validarEEnviar($event)"
    >
        @csrf

        <input type="hidden" name="valor" :value="$store.modalCobranca.orcamento?.valor_total">
        <input type="hidden" name="descricao" :value="`Cobrança do orçamento ${$store.modalCobranca.orcamento?.numero_orcamento}`">

        <div class="space-y-5">
            <template x-if="$store.modalCobranca.orcamento && $store.modalCobranca.orcamento.pre_cliente_id">
                <div class="p-4 rounded-xl border" style="background-color: #fef3c7; border-color: #f59e0b;">
                    <div class="flex flex-col items-start gap-3">
                        <div class="flex items-center gap-2" style="color: #92400e;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" style="color: #f59e0b;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" /></svg>
                            <span class="font-semibold">Não é possível gerar uma cobrança para um pré-cliente.</span>
                        </div>
                        <div class="text-sm" style="color: #78350f;">Converta este pré-cliente em cliente para continuar.</div>
                        <a :href="`/pre-clientes/${$store.modalCobranca.orcamento.pre_cliente_id}/edit`" target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 text-white font-bold rounded transition shadow pointer-events-auto relative z-20"
                            style="background-color: #f59e0b;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Converter para Cliente
                        </a>
                    </div>
                </div>
            </template>

            <div class="p-4 rounded-xl border" style="background-color: #f0f9ff; border-color: #3f9cae;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium" style="color: #3f9cae;">Cliente</span>
                        <div class="text-gray-900 font-medium" x-text="$store.modalCobranca.orcamento?.cliente?.nome_fantasia || 'N/A'"></div>
                    </div>
                    <div>
                        <span class="text-sm font-medium" style="color: #3f9cae;">Orçamento</span>
                        <div class="text-gray-900 font-medium" x-text="$store.modalCobranca.orcamento?.numero_orcamento"></div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col">
                <label for="forma_pagamento" class="text-sm font-medium text-gray-700 mb-1">
                    Forma de Pagamento <span class="text-red-500">*</span>
                </label>
                <select 
                    name="forma_pagamento" 
                    id="forma_pagamento"
                    x-model="forma"
                    @change="atualizarForma()"
                    class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"
                    required
                >
                    <option value="">Selecione</option>
                    <option value="pix">Pix</option>
                    <option value="debito">Cartão de Débito</option>
                    <option value="credito">Cartão de Crédito</option>
                    <option value="boleto">Boleto</option>
                    <option value="faturado">Faturado</option>
                </select>
            </div>

            <template x-if="mostrarParcelas">
                <div class="space-y-4">
                    <div>
                        <label for="parcelas" class="text-sm font-medium text-gray-700 mb-1">Quantidade de Parcelas</label>
                        <input 
                            type="number" 
                            name="parcelas" 
                            id="parcelas"
                            min="1" 
                            max="12"
                            x-model.number="parcelas"
                            @input="gerarVencimentos()"
                            class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"
                            style="max-width: 120px;"
                        >
                    </div>

                    <template x-if="vencimentos.length > 0">
                        <div class="p-4 rounded-xl border" style="background-color: #f0f9ff; border-color: #3f9cae;">
                            <div class="mb-3 flex justify-between items-center">
                                <div>
                                    <strong>Valor Total: R$ <span x-text="formatarMoeda($store.modalCobranca.orcamento?.valor_total)"></span></strong>
                                </div>
                                <div style="color: #666;">Distribuído: R$ <span x-text="formatarMoeda(getValorTotal())"></span></div>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(v, index) in vencimentos" :key="index">
                                    <div class="p-3 rounded-lg border" style="background-color: #f9fafb; border-color: #e5e7eb;">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-sm font-medium text-gray-700 mb-1">Vencimento</label>
                                                <input 
                                                    type="date" 
                                                    :name="`vencimentos[${index}]`"
                                                    x-model="vencimentos[index]"
                                                    @change="recalcularDatas(index)"
                                                    class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"
                                                    required
                                                >
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                                                <input 
                                                    type="number" 
                                                    step="0.01"
                                                    min="0"
                                                    :name="`valores_parcelas[${index}]`"
                                                    x-model="valoresParcelas[index]"
                                                    @focus="salvarValorOriginal(index)"
                                                    @blur="ajustarValores(index)"
                                                    class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"
                                                    required
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <x-button class="min-w-[130px]" variant="danger" size="sm" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-gerar-cobranca' }))">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Cancelar
            </x-button>
            <x-button class="min-w-[130px]" variant="primary" size="sm" type="submit" form="formGerarCobranca">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Salvar
            </x-button>
        </div>
    </form>
</x-modal>
</div>
