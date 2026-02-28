{{-- MODAL: NOVA/EDITAR CONTA FIXA A PAGAR --}}
<x-modal name="modal-conta-fixa-pagar" maxWidth="2xl" title="Despesa Fixa">
    <form method="POST" action="{{ route('financeiro.contasapagar.storeContaFixa') }}" x-data="contaFixaPagarModal()"
        @editar-conta-fixa-pagar.window="
            // Definir isEditing ANTES de carregar para garantir que resetForm não limpe nada
            isEditing = true;
            carregarContaFixa($event.detail.contaFixaId).then(() => {
                // Aguardar o próximo tick para garantir que todos os campos foram atualizados
                $nextTick(() => {
                    // Abrir o modal com skipReset para evitar o reset automático do listener de open-modal
                    $dispatch('open-modal', { name: 'modal-conta-fixa-pagar', skipReset: true });
                });
            }).catch(error => {
                isEditing = false;
                console.error('Erro ao carregar conta fixa:', error);
            });
        " @open-modal.window="
            if (typeof $event.detail === 'object') {
                if ($event.detail.name === 'modal-conta-fixa-pagar' && !$event.detail.skipReset) {
                    resetForm();
                }
            } else if ($event.detail === 'modal-conta-fixa-pagar') {
                resetForm();
            }
        ">
        @csrf

        <div class="space-y-5">
            {{-- Fornecedor --}}
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1">Fornecedor <span
                        class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" x-model="fornecedorBusca" @input="filtrarFornecedores()"
                        @focus="mostrarListaFornecedores = true" @click.away="mostrarListaFornecedores = false"
                        placeholder="Buscar fornecedor..."
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                    <input type="hidden" name="fornecedor_id" x-ref="fornecedorId">

                    <div x-show="mostrarListaFornecedores && fornecedoresFiltrados.length > 0"
                        class="absolute z-10 w-full mt-1 max-h-40 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg">
                        <template x-for="f in fornecedoresFiltrados" :key="f.id">
                            <div @click="selecionarFornecedor(f)"
                                class="px-3 py-2 cursor-pointer hover:bg-emerald-50 text-sm"
                                x-text="f.razao_social || f.nome_fantasia"></div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Centro de Custo e Categoria --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Centro de Custo <span
                            class="text-red-500">*</span></label>
                    <select name="centro_custo_id" x-model="centroCustoId" required
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\CentroCusto::where('ativo', true)->orderBy('nome')->get() as $centro)
                            <option value="{{ $centro->id }}">{{ $centro->nome }} ({{ $centro->tipo }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Categoria <span
                            class="text-red-500">*</span></label>
                    <select x-model="categoriaId" @change="loadSubcategorias()" required
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        <template x-for="cat in categorias" :key="cat.id">
                            <option :value="cat.id" x-text="cat.nome"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- Subcategoria e Conta --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Subcategoria <span
                            class="text-red-500">*</span></label>
                    <select x-model="subcategoriaId" @change="loadContas()" :disabled="!categoriaId" required
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        <template x-for="sub in subcategorias" :key="sub.id">
                            <option :value="sub.id" x-text="sub.nome"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Conta <span
                            class="text-red-500">*</span></label>
                    <select name="conta_id" x-model="contaId" :disabled="!subcategoriaId" required
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        <template x-for="conta in contas" :key="conta.id">
                            <option :value="conta.id" x-text="conta.nome"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- Descrição e Valor --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Descrição <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="descricao" x-model="descricao" required maxlength="255"
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Valor <span
                            class="text-red-500">*</span></label>
                    <div class="relative">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm z-10">R$</span>
                        <input type="number" name="valor" x-model="valor" step="0.01" min="0.01" required
                            class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm pl-10 focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                    </div>
                </div>
            </div>

            {{-- Periodicidade e Dia de Vencimento --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Periodicidade <span
                            class="text-red-500">*</span></label>
                    <select name="periodicidade" x-model="periodicidade" required
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        <option value="MENSAL">Mensal</option>
                        <option value="BIMESTRAL">Bimestral</option>
                        <option value="TRIMESTRAL">Trimestral</option>
                        <option value="SEMESTRAL">Semestral</option>
                        <option value="ANUAL">Anual</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Dia de Vencimento <span
                            class="text-red-500">*</span></label>
                    <input type="number" name="dia_vencimento" x-model="diaVencimento" min="1" max="31"
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"
                        placeholder="Ex: 10">
                </div>
            </div>

            {{-- Data Inicial e Data Fim --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Data Inicial <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="data_inicial" x-model="dataInicial" required
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                    <input type="date" name="data_fim" x-model="dataFim"
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                </div>
            </div>

            {{-- Forma de Pagamento e Conta Bancária --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                    <select name="forma_pagamento" x-model="formaPagamento"
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
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

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Conta Bancária</label>
                    <select name="conta_financeira_id" x-model="contaFinanceiraId"
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\ContaFinanceira::where('ativo', true)->orderBy('nome')->get() as $contaBancaria)
                            <option value="{{ $contaBancaria->id }}">{{ $contaBancaria->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Observações --}}
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="observacoes" x-model="observacoes" rows="2" maxlength="1000"
                    class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"></textarea>
            </div>
        </div>

        <x-slot name="footer">
            <x-button type="button" variant="danger" size="sm" class="min-w-[130px]"
                @click="$dispatch('close-modal', 'modal-conta-fixa-pagar')">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
                Cancelar
            </x-button>
            <x-button type="submit" variant="primary" size="sm" class="min-w-[130px]">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
                Salvar
            </x-button>
        </x-slot>
    </form>
</x-modal>

<script>
function contaFixaPagarModal() {
    return {
        categorias: @js(\App\Models\Categoria::where('ativo', true)->orderBy('nome')->get()),
        subcategorias: [],
        contas: [],
        categoriaId: '',
        subcategoriaId: '',
        contaId: '',
        centroCustoId: '',
        descricao: '',
        valor: '',
        periodicidade: '',
        diaVencimento: '',
        dataInicial: '',
        dataFim: '',
        formaPagamento: '',
        contaFinanceiraId: '',
        observacoes: '',
        
        fornecedorBusca: '',
        fornecedores: @js(\App\Models\Fornecedor::where('ativo', true)->orderBy('razao_social')->get(['id', 'razao_social', 'nome_fantasia'])),
        fornecedoresFiltrados: [],
        mostrarListaFornecedores: false,
        
        // Estado para controlar se estamos editando ou criando
        isEditing: false,
        
        filtrarFornecedores() {
            if (!this.fornecedorBusca || this.fornecedorBusca.length < 2) {
                this.fornecedoresFiltrados = [];
                return;
            }
            const filtro = this.fornecedorBusca.toLowerCase();
            this.fornecedoresFiltrados = this.fornecedores.filter(f => 
                (f.razao_social || '').toLowerCase().includes(filtro) || 
                (f.nome_fantasia || '').toLowerCase().includes(filtro)
            );
        },
        
        selecionarFornecedor(fornecedor) {
            this.$refs.fornecedorId.value = fornecedor.id;
            this.fornecedorBusca = fornecedor.razao_social || fornecedor.nome_fantasia;
            this.mostrarListaFornecedores = false;
        },
        
        async loadSubcategorias() {
            if (!this.categoriaId) {
                this.subcategorias = [];
                this.contas = [];
                this.subcategoriaId = '';
                this.contaId = '';
                return;
            }
            try {
                const r = await fetch(`/financeiro/api/subcategorias/${this.categoriaId}`);
                if (!r.ok) throw new Error('Erro');
                this.subcategorias = await r.json();
            } catch (err) {
                console.error('Erro:', err);
            }
        },
        
        async loadContas() {
            if (!this.subcategoriaId) {
                this.contas = [];
                this.contaId = '';
                return;
            }
            try {
                const r = await fetch(`/financeiro/api/contas/${this.subcategoriaId}`);
                if (!r.ok) throw new Error('Erro');
                this.contas = await r.json();
            } catch (err) {
                console.error('Erro:', err);
            }
        },
        
        resetForm() {
            if (!this.isEditing) {
                this.categoriaId = '';
                this.subcategoriaId = '';
                this.contaId = '';
                this.centroCustoId = '';
                this.descricao = '';
                this.valor = '';
                this.periodicidade = '';
                this.diaVencimento = '';
                this.dataInicial = '';
                this.dataFim = '';
                this.formaPagamento = '';
                this.contaFinanceiraId = '';
                this.observacoes = '';
                
                this.subcategorias = [];
                this.contas = [];
                this.fornecedorBusca = '';
                this.fornecedoresFiltrados = [];
                this.mostrarListaFornecedores = false;
                
                const form = this.$el.querySelector('form');
                if (form) {
                    form.reset();
                    const fornecedorIdField = form.querySelector('[name="fornecedor_id"]');
                    if (fornecedorIdField) fornecedorIdField.value = '';
                }
            } else {
                this.isEditing = false;
            }
        },
        
        async carregarContaFixa(id) {
            try {
                const response = await fetch(`/financeiro/contas-fixas-pagar/${id}`);
                if (!response.ok) throw new Error('Erro');
                const data = await response.json();

                this.isEditing = true;

                // 1. Dados simples
                this.centroCustoId = data.centro_custo_id || '';
                this.descricao = data.descricao || '';
                this.valor = data.valor || '';
                this.periodicidade = data.periodicidade || '';
                this.diaVencimento = data.dia_vencimento || '';
                this.formaPagamento = data.forma_pagamento || '';
                this.contaFinanceiraId = data.conta_financeira_id || '';
                this.observacoes = data.observacoes || '';
                this.dataInicial = data.data_inicial ? data.data_inicial.split('T')[0] : '';
                this.dataFim = data.data_fim ? data.data_fim.split('T')[0] : '';

                // 2. Hierarquia de conta
                if (data.conta && data.conta.subcategoria && data.conta.subcategoria.categoria_id) {
                    this.categoriaId = String(data.conta.subcategoria.categoria_id);
                    await this.loadSubcategorias();
                    this.subcategoriaId = String(data.conta.subcategoria_id);
                    await this.loadContas();
                    this.contaId = String(data.conta_id);
                } else {
                    this.categoriaId = '';
                    this.subcategoriaId = '';
                    this.contaId = '';
                    this.subcategorias = [];
                    this.contas = [];
                }

                // 3. Fornecedor
                if (data.fornecedor_id) {
                    this.$refs.fornecedorId.value = data.fornecedor_id;
                    const f = this.fornecedores.find(f => f.id == data.fornecedor_id);
                    if (f) this.fornecedorBusca = f.razao_social || f.nome_fantasia;
                } else {
                    this.$refs.fornecedorId.value = '';
                    this.fornecedorBusca = '';
                }

                // 4. Atualizar action do form
                const form = this.$el.querySelector('form');
                if (form) {
                    form.setAttribute('action', `/financeiro/contas-fixas-pagar/${id}`);
                    let methodInput = form.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        form.appendChild(methodInput);
                    }
                    methodInput.value = 'PUT';
                }
                
                return Promise.resolve();
            } catch (error) {
                console.error('Erro ao carregar conta fixa:', error);
                alert('Erro ao carregar dados da conta fixa');
                return Promise.reject(error);
            }
        }
    }
}
</script>