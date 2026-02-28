{{-- MODAL: NOVA/EDITAR CONTA FIXA A PAGAR --}}
<x-modal name="modal-conta-fixa-pagar" maxWidth="2xl" title="Nova Despesa Fixa">
    <form method="POST" action="{{ route('financeiro.contasapagar.storeContaFixa') }}" x-data="{
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
        resetForm() {
            document.querySelector('form').reset();
            this.categoriaId = '';
            this.subcategoriaId = '';
            this.subcategorias = [];
            this.contas = [];
            this.fornecedorBusca = '';
        }
    }" @open-modal.window="$event.detail === 'modal-conta-fixa-pagar' && resetForm()" @close-modal.window="$event.detail === 'modal-conta-fixa-pagar' && resetForm()">
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

            {{-- Descrição e Valor --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                    <input type="text" name="descricao" required maxlength="255" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Valor <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm z-10">R$</span>
                        <input type="number" name="valor" step="0.01" min="0.01" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm pl-10 focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                    </div>
                </div>
            </div>

            {{-- Periodicidade e Dia de Vencimento --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Periodicidade <span class="text-red-500">*</span></label>
                    <select name="periodicidade" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                        <option value="">Selecione...</option>
                        <option value="MENSAL">Mensal</option>
                        <option value="BIMESTRAL">Bimestral</option>
                        <option value="TRIMESTRAL">Trimestral</option>
                        <option value="SEMESTRAL">Semestral</option>
                        <option value="ANUAL">Anual</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Dia de Vencimento <span class="text-red-500">*</span></label>
                    <input type="number" name="dia_vencimento" min="1" max="31" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20" placeholder="Ex: 10">
                </div>
            </div>

            {{-- Data Inicial e Data Fim --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Data Inicial <span class="text-red-500">*</span></label>
                    <input type="date" name="data_inicial" required class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                    <input type="date" name="data_fim" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
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

            {{-- Observações --}}
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="observacoes" rows="2" maxlength="1000" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"></textarea>
            </div>
        </div>

        <x-slot name="footer">
            <x-button type="button" variant="danger" size="sm" class="min-w-[130px]" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-conta-fixa-pagar' }))">
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
