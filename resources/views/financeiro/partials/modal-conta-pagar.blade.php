{{-- MODAL: NOVA CONTA A PAGAR --}}
<div x-data="{ 
    open: false,
    editando: false,
    contaId: null,
    categorias: @js(\App\Models\Categoria::where('ativo', true)->orderBy('nome')->get()),
    subcategorias: [],
    contas: [],
    categoriaId: '',
    subcategoriaId: '',
    
    async carregarConta(id) {
        try {
            const response = await fetch(`/financeiro/contas-a-pagar/${id}`);
            if (!response.ok) throw new Error('Erro ao carregar conta');
            const data = await response.json();
            
            // Carregar selects em cascata primeiro
            if (data.conta?.subcategoria?.categoria_id) {
                this.categoriaId = data.conta.subcategoria.categoria_id;
                await this.loadSubcategorias();
                
                if (data.conta?.subcategoria_id) {
                    this.subcategoriaId = data.conta.subcategoria_id;
                    await this.loadContas();
                }
            }
            
            // Aguardar renderização completa
            await this.$nextTick();
            
            // Preencher TODOS os campos após selects carregarem
            if (data.fornecedor_id) {
                document.querySelector('[name=fornecedor_id]').value = data.fornecedor_id;
            }
            if (data.centro_custo_id) {
                document.querySelector('[name=centro_custo_id]').value = data.centro_custo_id;
            }
            if (data.conta_id) {
                document.querySelector('[name=conta_id]').value = data.conta_id;
            }
            if (data.descricao) {
                document.querySelector('[name=descricao]').value = data.descricao;
            }
            if (data.valor) {
                document.querySelector('[name=valor]').value = data.valor;
            }
            if (data.data_vencimento) {
                document.querySelector('[name=data_vencimento]').value = data.data_vencimento.split('T')[0];
            }
            if (data.observacoes) {
                document.querySelector('[name=observacoes]').value = data.observacoes;
            }
            if (data.forma_pagamento) {
                document.querySelector('[name=forma_pagamento]').value = data.forma_pagamento;
            }
            if (data.conta_financeira_id) {
                document.querySelector('[name=conta_financeira_id]').value = data.conta_financeira_id;
            }
            
            this.editando = true;
            this.contaId = id;
            this.open = true;
        } catch (error) {
            console.error('Erro ao carregar conta:', error);
            alert('Erro ao carregar dados da conta');
        }
    },
    
    async loadSubcategorias() {
        if (!this.categoriaId) {
            this.subcategorias = [];
            this.contas = [];
            this.subcategoriaId = '';
            return;
        }
        this.subcategorias = [];
        this.subcategoriaId = '';
        try {
            const r = await fetch(`/financeiro/api/subcategorias/${this.categoriaId}`);
            if (!r.ok) throw new Error('Erro ao carregar subcategorias');
            const data = await r.json();
            this.subcategorias = data;
            this.contas = [];
        } catch (err) {
            console.error('Erro:', err);
            alert('Erro ao carregar subcategorias');
        }
    },
    
    async loadContas() {
        if (!this.subcategoriaId) {
            this.contas = [];
            return;
        }
        this.contas = [];
        try {
            const r = await fetch(`/financeiro/api/contas/${this.subcategoriaId}`);
            if (!r.ok) throw new Error('Erro ao carregar contas');
            const data = await r.json();
            this.contas = data;
        } catch (err) {
            console.error('Erro:', err);
            alert('Erro ao carregar contas');
        }
    }
}"
    @abrir-modal-conta-pagar.window="editando = false; contaId = null; open = true"
    @editar-conta-pagar.window="carregarConta($event.detail.contaId)"
    x-show="open"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

            <form method="POST" :action="editando ? `/financeiro/contas-a-pagar/${contaId}` : '{{ route('financeiro.contasapagar.store') }}'">
                @csrf
                <input type="hidden" name="_method" x-bind:value="editando ? 'PUT' : 'POST'">

                <div class="bg-gradient-to-br from-orange-50 to-red-50 border-b border-orange-200 px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-xl font-bold text-gray-900" x-text="editando ? 'Editar Conta a Pagar' : 'Nova Conta a Pagar'"></h3>
                    </div>
                </div>

                <div class="bg-white px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Fornecedor --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
                            <select name="fornecedor_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Selecione (opcional)...</option>
                                @foreach(\App\Models\Fornecedor::where('ativo', true)->orderBy('razao_social')->get() as $fornecedor)
                                <option value="{{ $fornecedor->id }}">{{ $fornecedor->razao_social }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Centro de Custo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo <span class="text-red-500">*</span></label>
                            <select name="centro_custo_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Selecione...</option>
                                @foreach(\App\Models\CentroCusto::where('ativo', true)->orderBy('nome')->get() as $centro)
                                <option value="{{ $centro->id }}">{{ $centro->nome }} ({{ $centro->tipo }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Categoria --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria <span class="text-red-500">*</span></label>
                            <select x-model="categoriaId" @change="loadSubcategorias()" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Selecione...</option>
                                <template x-for="cat in categorias" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.nome"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Subcategoria --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subcategoria <span class="text-red-500">*</span></label>
                            <select x-model="subcategoriaId" @change="loadContas()" :disabled="!categoriaId" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Selecione...</option>
                                <template x-for="sub in subcategorias" :key="sub.id">
                                    <option :value="sub.id" x-text="sub.nome"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Conta --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conta <span class="text-red-500">*</span></label>
                            <select name="conta_id" :disabled="!subcategoriaId" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Selecione...</option>
                                <template x-for="conta in contas" :key="conta.id">
                                    <option :value="conta.id" x-text="conta.nome"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Descrição --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                            <input type="text" name="descricao" required maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        </div>

                        {{-- Valor --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor <span class="text-red-500">*</span></label>
                            <input type="number" name="valor" step="0.01" min="0.01" max="999999.99" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        </div>

                        {{-- Vencimento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vencimento <span class="text-red-500">*</span></label>
                            <input type="date" name="data_vencimento" required min="{{ date('Y-m-d') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        </div>

                        {{-- Forma de Pagamento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                            <select name="forma_pagamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Não definida</option>
                                <option value="PIX">PIX</option>
                                <option value="BOLETO">Boleto</option>
                                <option value="TRANSFERENCIA">Transferência Bancária</option>
                                <option value="CARTAO_CREDITO">Cartão de Crédito</option>
                                <option value="CARTAO_DEBITO">Cartão de Débito</option>
                                <option value="DINHEIRO">Dinheiro</option>
                                <option value="CHEQUE">Cheque</option>
                                <option value="OUTROS">Outros</option>
                            </select>
                        </div>

                        {{-- Conta Bancária --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conta Bancária (para pagamento)</label>
                            <select name="conta_financeira_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Selecione (opcional)...</option>
                                @foreach(\App\Models\ContaFinanceira::where('ativo', true)->orderBy('nome')->get() as $contaBancaria)
                                <option value="{{ $contaBancaria->id }}">{{ $contaBancaria->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Observações --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                            <textarea name="observacoes" rows="2" maxlength="1000"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition shadow-md">
                        Salvar Conta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>