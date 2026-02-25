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

            <x-form-select 
                name="forma_pagamento" 
                label="Forma de Pagamento" 
                required 
                placeholder="Selecione"
                x-model="forma"
                @change="atualizarForma()"
            >
                <option value="pix">Pix</option>
                <option value="debito">Cartão de Débito</option>
                <option value="credito">Cartão de Crédito</option>
                <option value="boleto">Boleto</option>
                <option value="faturado">Faturado</option>
            </x-form-select>

            <template x-if="mostrarParcelas">
                <div class="space-y-4">
                    <x-form-input 
                        name="parcelas" 
                        label="Quantidade de Parcelas" 
                        type="number" 
                        min="1" 
                        max="12"
                        x-model.number="parcelas"
                        @input="gerarVencimentos()"
                    />

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
                                            <x-form-input 
                                                :name="`vencimentos[${index}]`" 
                                                label="Vencimento" 
                                                type="date"
                                                x-model="vencimentos[index]"
                                                @change="recalcularDatas(index)"
                                                required
                                            />
                                            <x-form-input 
                                                :name="`valores_parcelas[${index}]`" 
                                                label="Valor (R$)" 
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                x-model="valoresParcelas[index]"
                                                @focus="salvarValorOriginal(index)"
                                                @blur="ajustarValores(index)"
                                                required
                                            />
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
            <x-button variant="danger" size="sm" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-gerar-cobranca' }))">
                Cancelar
            </x-button>
            <x-button variant="primary" size="sm" type="submit" form="formGerarCobranca">
                Salvar
            </x-button>
        </div>
    </form>
</x-modal>
</div>
