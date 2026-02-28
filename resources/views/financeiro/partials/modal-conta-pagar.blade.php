{{-- MODAL: NOVA/EDITAR CONTA A PAGAR --}}
<x-modal name="modal-conta-pagar" maxWidth="2xl" title="Despesa Variada">
    <form method="POST" action="{{ route('financeiro.contasapagar.store') }}" x-data="{
        categorias: @js(\App\Models\Categoria::where('ativo', true)->orderBy('nome')->get()),
        subcategorias: [],
        contas: [],
        categoriaId: '',
        subcategoriaId: '',
        
        // Fornecedor
        fornecedorBusca: '',
        fornecedores: @js(\App\Models\Fornecedor::where('ativo', true)->orderBy('razao_social')->get(['id', 'razao_social', 'nome_fantasia'])),
        fornecedoresFiltrados: [],
        mostrarListaFornecedores: false,
        
        // Orçamento
        vincularOrcamento: false,
        clienteId: '',
        clientes: @js(\App\Models\Cliente::where('ativo', true)->orderBy('nome_fantasia')->get(['id', 'nome_fantasia'])),
        orcamentos: [],
        orcamentoId: '',
        
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
            document.querySelector('[name=fornecedor_id]').value = fornecedor.id;
            this.fornecedorBusca = fornecedor.razao_social || fornecedor.nome_fantasia;
            this.mostrarListaFornecedores = false;
        },
        async loadSubcategorias() {
            if (!this.categoriaId) {
                this.subcategorias = [];
                this.contas = [];
                this.subcategoriaId = '';
                return;
            }
            try {
                const r = await fetch(`/financeiro/api/subcategorias/${this.categoriaId}`);
                if (!r.ok) throw new Error('Erro');
                this.subcategorias = await r.json();
                this.contas = [];
            } catch (err) {
                console.error('Erro:', err);
            }
        },
        async loadContas() {
            if (!this.subcategoriaId) {
                this.contas = [];
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
        async carregarOrcamentos() {
            if (!this.clienteId) {
                this.orcamentos = [];
                this.orcamentoId = '';
                return;
            }
            try {
                const r = await fetch(`/api/orcamentos-por-cliente/${this.clienteId}`);
                if (!r.ok) throw new Error('Erro');
                this.orcamentos = await r.json();
            } catch (err) {
                console.error('Erro:', err);
            }
        },
        resetForm() {
            document.querySelector('form').reset();
            this.categoriaId = '';
            this.subcategoriaId = '';
            this.subcategorias = [];
            this.contas = [];
            this.fornecedorBusca = '';
            this.vincularOrcamento = false;
            this.clienteId = '';
            this.orcamentos = [];
            this.orcamentoId = '';
        }
    }" @open-modal.window="$event.detail === 'modal-conta-pagar' && resetForm()" @close-modal.window="$event.detail === 'modal-conta-pagar' && resetForm()">
        @csrf
        
        <div class="space-y-5">
            {{-- Fornecedor --}}
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1">Fornecedor <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="fornecedorBusca" 
                        @input="filtrarFornecedores()" 
                        @focus="mostrarListaFornecedores = true"
                        @click.away="mostrarListaFornecedores = false"
                        placeholder="Buscar fornecedor..." 
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"
                    >
                    <input type="hidden" name="fornecedor_id" required>
                    
                    <div x-show="mostrarListaFornecedores && fornecedoresFiltrados.length > 0" 
                         class="absolute z-10 w-full mt-1 max-h-40 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg">
                        @foreach(\App\Models\Fornecedor::where('ativo', true)->orderBy('razao_social')->get() as $fornecedor)
                            <div @click="selecionarFornecedor(@js($fornecedor))" 
                                 class="px-3 py-2 cursor-pointer hover:bg-emerald-50 text-sm">
                                {{ $fornecedor->razao_social ?: $fornecedor->nome_fantasia }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Centro de Custo e Categoria --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Centro de Custo <span class="text-red-500">*</span></label>
                    <select name="centro_custo_id" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\CentroCusto::where('ativo', true)->orderBy('nome')->get() as $centro)
                            <option value="{{ $centro->id }}">{{ $centro->nome }} ({{ $centro->tipo }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Categoria <span class="text-red-500">*</span></label>
                    <select x-model="categoriaId" @change="loadSubcategorias()" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
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
                    <label class="text-sm font-medium text-gray-700 mb-1">Subcategoria <span class="text-red-500">*</span></label>
                    <select x-model="subcategoriaId" @change="loadContas()" :disabled="!categoriaId" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        <template x-for="sub in subcategorias" :key="sub.id">
                            <option :value="sub.id" x-text="sub.nome"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Conta <span class="text-red-500">*</span></label>
                    <select name="conta_id" :disabled="!subcategoriaId" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        <template x-for="conta in contas" :key="conta.id">
                            <option :value="conta.id" x-text="conta.nome"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- Descrição --}}
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                <input type="text" name="descricao" required maxlength="255" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
            </div>

            {{-- Valor e Vencimento --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Valor <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm z-10">R$</span>
                        <input type="number" name="valor" step="0.01" min="0.01" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm pl-10 focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Vencimento <span class="text-red-500">*</span></label>
                    <input type="date" name="data_vencimento" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                </div>
            </div>

            {{-- Forma de Pagamento e Conta Bancária --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                    <select name="forma_pagamento" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
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
                    <select name="conta_financeira_id" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\ContaFinanceira::where('ativo', true)->orderBy('nome')->get() as $conta)
                            <option value="{{ $conta->id }}">{{ $conta->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Vínculo com Orçamento --}}
            <div class="p-4 rounded-xl border" style="background-color: rgba(63, 156, 174, 0.05); border-color: #3f9cae;">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-[#3f9cae]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    <label class="text-sm font-medium text-gray-700">Vincular a Orçamento/Serviço</label>
                </div>
                
                <div class="flex items-center gap-2 mb-3">
                    <input type="checkbox" x-model="vincularOrcamento" id="vincular-orcamento" class="rounded border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae]">
                    <label for="vincular-orcamento" class="text-sm text-gray-600">Esta despesa está vinculada a um orçamento ou serviço</label>
                </div>

                <div x-show="vincularOrcamento" class="mt-4 space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1">Cliente</label>
                        <select x-model="clienteId" @change="carregarOrcamentos()" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                            <option value="">Selecione...</option>
                            <template x-for="cliente in clientes" :key="cliente.id">
                                <option :value="cliente.id" x-text="cliente.nome_fantasia"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1">Orçamento</label>
                        <select name="orcamento_id" x-model="orcamentoId" :disabled="!clienteId" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                            <option value="">Selecione...</option>
                            <template x-for="orc in orcamentos" :key="orc.id">
                                <option :value="orc.id" x-text="`#${orc.numero_orcamento} - ${orc.descricao}`"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Observações --}}
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="observacoes" rows="2" maxlength="1000" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"></textarea>
            </div>
        </div>

        <x-slot name="footer">
            <x-button type="button" variant="danger" size="sm" class="min-w-[130px]" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-conta-pagar' }))">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Cancelar
            </x-button>
            <x-button type="submit" variant="primary" size="sm" class="min-w-[130px]">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Salvar
            </x-button>
        </x-slot>
    </form>
</x-modal>
