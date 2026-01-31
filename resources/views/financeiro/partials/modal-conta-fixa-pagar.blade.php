{{-- MODAL: NOVA/EDITAR CONTA FIXA A PAGAR --}}
<div x-data="{ 
    open: false,
    editando: false,
    contaFixaId: null,
    categorias: @js(\App\Models\Categoria::where('ativo', true)->orderBy('nome')->get()),
    subcategorias: [],
    contas: [],
    categoriaId: '',
    subcategoriaId: '',
    fornecedorId: '',
    centroCustoId: '',
    contaId: '',
    descricao: '',
    valor: '',
    dataInicial: '',
    dataFim: '',
    diaVencimento: '',
    periodicidade: 'MENSAL',
    formaPagamento: '',
    contaFinanceiraId: '',
    
    async carregarContaFixa(id) {
        try {
            const response = await fetch(`/financeiro/contas-fixas-pagar/${id}`);
            if (!response.ok) throw new Error('Erro ao carregar conta fixa');
            const data = await response.json();
            
            console.log('Dados conta fixa recebidos:', data);
            
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
            await this.$nextTick();
            
            // Preencher variáveis do Alpine
            this.fornecedorId = data.fornecedor_id || '';
            this.centroCustoId = data.centro_custo_id || '';
            this.contaId = data.conta_id || '';
            this.descricao = data.descricao || '';
            this.valor = data.valor || '';
            this.dataInicial = data.data_inicial?.split('T')[0] || '';
            this.dataFim = data.data_fim?.split('T')[0] || '';
            this.diaVencimento = data.dia_vencimento || '';
            this.periodicidade = data.periodicidade || 'MENSAL';
            this.formaPagamento = data.forma_pagamento || '';
            this.contaFinanceiraId = data.conta_financeira_id || '';
            
            console.log('Campos Alpine atualizados');
        } catch (error) {
            console.error('Erro ao carregar conta fixa:', error);
            alert('Erro ao carregar dados da conta fixa');
        }
    },
    
    resetForm() {
        this.categoriaId = '';
        this.subcategoriaId = '';
        this.subcategorias = [];
        this.contas = [];
        this.fornecedorId = '';
        this.centroCustoId = '';
        this.contaId = '';
        this.descricao = '';
        this.valor = '';
        this.dataInicial = '';
        this.dataFim = '';
        this.diaVencimento = '';
        this.periodicidade = 'MENSAL';
        this.formaPagamento = '';
        this.contaFinanceiraId = '';
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
    @abrir-modal-conta-fixa-pagar.window="resetForm(); editando = false; contaFixaId = null; open = true"
    @editar-conta-fixa-pagar.window="editando = true; contaFixaId = $event.detail.contaFixaId; carregarContaFixa($event.detail.contaFixaId); open = true"
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

            <form method="POST" :action="editando ? `/financeiro/contas-fixas-pagar/${contaFixaId}` : '{{ route('financeiro.contasapagar.storeContaFixa') }}'">
                @csrf
                <input type="hidden" name="_method" :value="editando ? 'PUT' : 'POST'">

                <div class="bg-gradient-to-br from-red-50 to-pink-50 border-b border-red-200 px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-xl font-bold text-gray-900" x-text="editando ? 'Editar Despesa Fixa' : 'Nova Despesa Fixa Mensal'"></h3>
                    </div>
                </div>

                <div class="bg-white px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Fornecedor --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
                            <input type="text" id="busca-fornecedor-fixa" placeholder="Buscar fornecedor..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 mb-2">
                            <div id="lista-fornecedores-fixa" class="max-h-32 overflow-y-auto border rounded-md p-2 space-y-1 bg-white">
                                <label class="block text-gray-500 text-sm">Carregando fornecedores...</label>
                            </div>
                            <input type="hidden" name="fornecedor_id" id="fornecedor-id-fixa">
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Busca e lista de fornecedores (conta fixa)
                            const inputBusca = document.getElementById('busca-fornecedor-fixa');
                            const lista = document.getElementById('lista-fornecedores-fixa');
                            const inputHidden = document.getElementById('fornecedor-id-fixa');
                            let fornecedores = [];

                            fornecedores = [
                                @foreach(\App\Models\Fornecedor::where('ativo', true)->orderBy('razao_social')->get() as $fornecedor)
                                { id: {{ $fornecedor->id }}, nome: @json($fornecedor->razao_social) },
                                @endforeach
                            ];

                            function renderLista(filtro = '') {
                                lista.innerHTML = '';
                                const filtrados = fornecedores.filter(f => f.nome.toLowerCase().includes(filtro.toLowerCase()));
                                if (filtrados.length === 0) {
                                    lista.innerHTML = '<span class="block text-gray-400 text-sm">Nenhum fornecedor encontrado</span>';
                                    return;
                                }
                                filtrados.forEach(f => {
                                    const label = document.createElement('label');
                                    label.className = 'flex items-center gap-2 cursor-pointer hover:bg-red-50 rounded px-2 py-1';
                                    label.innerHTML = `<input type=\"radio\" name=\"fornecedor_radio_fixa\" value=\"${f.id}\"> <span class=\"text-sm\">${f.nome}</span>`;
                                    label.onclick = () => {
                                        inputHidden.value = f.id;
                                        inputBusca.value = f.nome;
                                        lista.querySelectorAll('label').forEach(l => l.classList.remove('bg-red-100'));
                                        label.classList.add('bg-red-100');
                                    };
                                    lista.appendChild(label);
                                });
                            }

                            renderLista();
                            inputBusca.addEventListener('input', e => renderLista(e.target.value));
                            inputBusca.addEventListener('focus', () => renderLista(inputBusca.value));
                        });
                        </script>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo <span class="text-red-500">*</span></label>
                            <select name="centro_custo_id" x-model="centroCustoId" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Selecione...</option>
                                @foreach(\App\Models\CentroCusto::where('ativo', true)->orderBy('nome')->get() as $centro)
                                <option value="{{ $centro->id }}">{{ $centro->nome }} ({{ $centro->tipo }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria <span class="text-red-500">*</span></label>
                            <select x-model="categoriaId" @change="loadSubcategorias()" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Selecione...</option>
                                <template x-for="cat in categorias" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.nome"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subcategoria <span class="text-red-500">*</span></label>
                            <select x-model="subcategoriaId" @change="loadContas()" :disabled="!categoriaId" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Selecione...</option>
                                <template x-for="sub in subcategorias" :key="sub.id">
                                    <option :value="sub.id" x-text="sub.nome"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conta <span class="text-red-500">*</span></label>
                            <select name="conta_id" x-model="contaId" :disabled="!subcategoriaId" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Selecione...</option>
                                <template x-for="conta in contas" :key="conta.id">
                                    <option :value="conta.id" x-text="conta.nome"></option>
                                </template>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                            <input type="text" name="descricao" x-model="descricao" required maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor <span class="text-red-500">*</span></label>
                            <input type="number" name="valor" x-model="valor" step="0.01" min="0.01" max="999999.99" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>

                        {{-- Data Inicial --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial/Vencimento <span class="text-red-500">*</span></label>
                            <input type="date" name="data_inicial" x-model="dataInicial" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <p class="text-xs text-gray-500 mt-1">Data do primeiro vencimento</p>
                        </div>

                        {{-- Periodicidade --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periodicidade <span class="text-red-500">*</span></label>
                            <select name="periodicidade" x-model="periodicidade" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="MENSAL">Mensal</option>
                                <option value="SEMANAL">Semanal</option>
                                <option value="QUINZENAL">Quinzenal</option>
                                <option value="TRIMESTRAL">Trimestral</option>
                                <option value="SEMESTRAL">Semestral</option>
                                <option value="ANUAL">Anual</option>
                            </select>
                        </div>

                        {{-- Data Final (Opcional) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                            <input type="date" name="data_fim" x-model="dataFim"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <p class="text-xs text-gray-500 mt-1">Deixe em branco para despesa permanente</p>
                        </div>

                        {{-- Forma de Pagamento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                            <select name="forma_pagamento" x-model="formaPagamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Não definida</option>
                                <option value="PIX">PIX</option>
                                <option value="BOLETO">Boleto</option>
                                <option value="TRANSFERENCIA">Transferência Bancária</option>
                                <option value="CARTAO_CREDITO">Cartão de Crédito</option>
                                <option value="CARTAO_DEBITO">Cartão de Débito</option>
                                <option value="DINHEIRO">Dinheiro</option>
                                <option value="CHEQUE">Cheque</option>
                                <option value="DEBITO_AUTOMATICO">Débito Automático</option>
                            </select>
                        </div>

                        {{-- Conta Bancária --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conta Bancária (para pagamento)</label>
                            <select name="conta_financeira_id" x-model="contaFinanceiraId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Selecione (opcional)...</option>
                                @foreach(\App\Models\ContaFinanceira::where('ativo', true)->orderBy('nome')->get() as $contaBancaria)
                                <option value="{{ $contaBancaria->id }}">{{ $contaBancaria->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-md"
                        x-text="editando ? 'Atualizar Despesa Fixa' : 'Criar Despesa Fixa'">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>