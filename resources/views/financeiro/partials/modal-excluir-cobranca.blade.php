<div
    x-data="{ 
        open: false, 
        cobrancaId: null,
        orcamentoId: null,
        totalParcelas: 0,
        contaFixaId: null,
        totalCobrancasContrato: 0,
        dataVencimento: null,
        
        abrirModal(cobrancaId, orcamentoId, totalParcelas, contaFixaId, totalCobrancasContrato, dataVencimento) {
            this.open = true;
            this.cobrancaId = cobrancaId;
            this.orcamentoId = orcamentoId;
            this.totalParcelas = totalParcelas;
            this.contaFixaId = contaFixaId;
            this.totalCobrancasContrato = totalCobrancasContrato;
            this.dataVencimento = dataVencimento;
        },
        
        excluirParcela(tipo) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/financeiro/contas-a-receber') }}/${this.cobrancaId}`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const tipoInput = document.createElement('input');
            tipoInput.type = 'hidden';
            tipoInput.name = 'tipo_exclusao';
            tipoInput.value = tipo;
            
            if (this.dataVencimento) {
                const dataInput = document.createElement('input');
                dataInput.type = 'hidden';
                dataInput.name = 'data_vencimento';
                dataInput.value = this.dataVencimento;
                form.appendChild(dataInput);
            }
            
            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            form.appendChild(tipoInput);
            
            document.body.appendChild(form);
            form.submit();
        },
        
        get isContrato() {
            return this.contaFixaId !== null && this.contaFixaId !== 'null';
        },
        
        get isOrcamento() {
            return this.orcamentoId !== null && this.orcamentoId !== 'null';
        }
    }"
    x-on:excluir-cobranca.window="abrirModal($event.detail.cobrancaId, $event.detail.orcamentoId, $event.detail.totalParcelas, $event.detail.contaFixaId, $event.detail.totalCobrancasContrato, $event.detail.dataVencimento)"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div
        class="bg-white rounded-lg shadow-xl w-full max-w-md p-6"
        @click.away="open = false">
        <div class="flex items-start mb-4">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-lg font-semibold text-gray-900">
                    Excluir Cobrança
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    <template x-if="isContrato && totalCobrancasContrato > 1">
                        <span>Esta cobrança faz parte de um <strong class="text-blue-600">contrato recorrente</strong> com <strong class="text-red-600"><span x-text="totalCobrancasContrato"></span> cobrança(s) futura(s)</strong> (incluindo esta). Como deseja proceder?</span>
                    </template>
                    <template x-if="isOrcamento && totalParcelas > 1 && !isContrato">
                        <span>Esta cobrança possui <strong class="text-red-600"><span x-text="totalParcelas"></span> parcela(s)</strong> relacionada(s). Como deseja proceder?</span>
                    </template>
                    <template x-if="!isContrato && (!isOrcamento || totalParcelas <= 1)">
                        <span>Tem certeza que deseja excluir esta cobrança? Esta ação não pode ser desfeita.</span>
                    </template>
                </p>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-3">
            {{-- Opções para CONTRATOS --}}
            <template x-if="isContrato && totalCobrancasContrato > 1">
                <div class="space-y-2">
                    <button
                        type="button"
                        class="w-full px-4 py-3 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm"
                        @click="excluirParcela('todas_contrato')">
                        🗑️ Excluir Esta e Todas as Futuras (<span x-text="totalCobrancasContrato"></span> cobranças)
                    </button>

                    <button
                        type="button"
                        class="w-full px-4 py-3 text-sm font-semibold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition"
                        @click="excluirParcela('unica')">
                        Excluir Apenas Esta Cobrança
                    </button>
                </div>
            </template>

            {{-- Opções para ORÇAMENTOS --}}
            <template x-if="isOrcamento && totalParcelas > 1 && !isContrato">
                <div class="space-y-2">
                    <button
                        type="button"
                        class="w-full px-4 py-3 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm"
                        @click="excluirParcela('todas')">
                        🗑️ Excluir Todas as <span x-text="totalParcelas"></span> Parcelas
                    </button>

                    <button
                        type="button"
                        class="w-full px-4 py-3 text-sm font-semibold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition"
                        @click="excluirParcela('unica')">
                        Excluir Apenas Esta Parcela
                    </button>
                </div>
            </template>

            {{-- Opções para COBRANÇAS SIMPLES --}}
            <template x-if="!isContrato && (!isOrcamento || totalParcelas <= 1)">
                <div class="flex gap-3">
                    <button
                        type="button"
                        class="flex-1 px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                        @click="open = false">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
                        @click="excluirParcela('unica')">
                        Confirmar Exclusão
                    </button>
                </div>
            </template>

            <button
                type="button"
                class="w-full px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                @click="open = false">
                Cancelar
            </button>
        </div>
    </div>
</div>