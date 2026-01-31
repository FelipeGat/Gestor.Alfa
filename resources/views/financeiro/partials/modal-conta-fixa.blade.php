{{-- Modal de Cadastro/Edição de Conta Fixa --}}
<div x-data="{ 
    mostrar: false,
    editando: false,
    contaFixaId: null,
    empresas: [],
    clientes: [],
    contasFinanceiras: [],
    form: {
        empresa_id: '',
        cliente_id: '',
        cliente_nome: '',
        categoria: 'Contratos',
        valor: '',
        conta_financeira_id: '',
        forma_pagamento: '',
        periodicidade: '',
        data_inicial: '',
        data_fim: '',
        percentual_renovacao: '',
        data_atualizacao_percentual: '',
        observacao: '',
        ativo: true
    },
    mostrarSugestoesCliente: false,
    sugestoesClientes: [],
    filtrarClientes() {
        if (!this.form.cliente_nome || this.form.cliente_nome.length < 2) {
            this.sugestoesClientes = [];
            return;
        }
        const filtro = this.form.cliente_nome.toLowerCase();
        this.sugestoesClientes = this.clientes.filter(c => {
            const nome = c.nome || c.nome_fantasia || c.razao_social || '';
            return nome.toLowerCase().includes(filtro);
        });
    },
    selecionarCliente(cliente) {
        this.form.cliente_id = cliente.id;
        this.form.cliente_nome = cliente.nome || cliente.nome_fantasia || cliente.razao_social;
        this.mostrarSugestoesCliente = false;
    },
    
    async buscarEmpresas() {
        try {
            const response = await fetch('/api/empresas');
            this.empresas = await response.json();
        } catch (error) {
            console.error('Erro ao buscar empresas:', error);
        }
    },
    
    async buscarClientes() {
        try {
            const response = await fetch('/api/clientes');
            this.clientes = await response.json();
        } catch (error) {
            console.error('Erro ao buscar clientes:', error);
        }
    },
    
    async buscarContasFinanceiras() {
        if (!this.form.empresa_id) {
            this.contasFinanceiras = [];
            return;
        }
        try {
            const response = await fetch(`/api/contas-financeiras/${this.form.empresa_id}`);
            this.contasFinanceiras = await response.json();
        } catch (error) {
            console.error('Erro ao buscar contas financeiras:', error);
        }
    },
    
    async carregarContaFixa(id) {
        try {
            const response = await fetch(`/financeiro/contas-fixas/${id}`);
            const data = await response.json();
            
            this.contaFixaId = data.id;
            this.editando = true;
            this.form = {
                empresa_id: data.empresa_id,
                cliente_id: data.cliente_id,
                categoria: data.categoria,
                valor: data.valor,
                conta_financeira_id: data.conta_financeira_id,
                forma_pagamento: data.forma_pagamento,
                periodicidade: data.periodicidade,
                data_inicial: data.data_inicial,
                data_fim: data.data_fim,
                percentual_renovacao: data.percentual_renovacao,
                data_atualizacao_percentual: data.data_atualizacao_percentual,
                observacao: data.observacao,
                ativo: data.ativo
            };
            
            await this.buscarContasFinanceiras();
            this.mostrar = true;
        } catch (error) {
            console.error('Erro ao carregar conta fixa:', error);
            alert('Erro ao carregar dados da conta fixa');
        }
    },
    
    resetForm() {
        this.editando = false;
        this.contaFixaId = null;
        this.form = {
            empresa_id: '',
            cliente_id: '',
            categoria: 'Contratos',
            valor: '',
            conta_financeira_id: '',
            forma_pagamento: '',
            periodicidade: '',
            data_inicial: '',
            data_fim: '',
            percentual_renovacao: '',
            data_atualizacao_percentual: '',
            observacao: '',
            ativo: true
        };
        this.contasFinanceiras = [];
    },
    
    async salvar() {
        try {
            const url = this.editando 
                ? `/financeiro/contas-fixas/${this.contaFixaId}`
                : '{{ route('financeiro.contas-fixas.store') }}';
            
            const formData = { ...this.form };
            
            // Se estiver editando, adicionar _method para Laravel processar como PUT
            if (this.editando) {
                formData._method = 'PUT';
            }
            
            const response = await fetch(url, {
                method: 'POST', // Sempre POST, mas com _method=PUT quando editando
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify(formData)
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                }
                window.location.reload();
            } else {
                const data = await response.json();
                alert('Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro ao salvar conta fixa:', error);
            alert('Erro ao salvar conta fixa');
        }
    }
}"
    @abrir-modal-conta-fixa.window="resetForm(); mostrar = true; buscarEmpresas(); buscarClientes();"
    @editar-conta-fixa.window="buscarEmpresas(); buscarClientes(); carregarContaFixa($event.detail.contaFixaId);"
    x-show="mostrar"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;">

    {{-- Overlay --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="mostrar = false"></div>

    {{-- Modal Container --}}
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" @click.stop>

            {{-- Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-lg z-10">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span x-text="editando ? 'Editar Conta Fixa com Repetição' : 'Cadastrar Conta Fixa com Repetição'"></span>
                </h3>
                <button @click="mostrar = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-4">
                <form @submit.prevent="salvar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Empresa --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Empresa <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.empresa_id"
                                @change="buscarContasFinanceiras()"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Selecione...</option>
                                <template x-for="empresa in empresas" :key="empresa.id">
                                    <option :value="empresa.id" x-text="empresa.nome_fantasia || empresa.razao_social"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Cliente --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cliente <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                    x-ref="inputCliente"
                                    x-model="form.cliente_nome"
                                    @input="filtrarClientes()"
                                    @focus="mostrarSugestoesCliente = true; filtrarClientes()"
                                    @blur="setTimeout(() => mostrarSugestoesCliente = false, 150)"
                                    placeholder="Digite para buscar..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    autocomplete="off"
                                    required
                                >
                                <input type="hidden" x-model="form.cliente_id">
                                <div x-show="mostrarSugestoesCliente && sugestoesClientes.length > 0" class="absolute left-0 right-0 z-10 bg-white border border-gray-200 rounded shadow max-h-40 overflow-y-auto">
                                    <template x-for="cliente in sugestoesClientes" :key="cliente.id">
                                        <div class="px-3 py-2 cursor-pointer hover:bg-purple-50 text-sm" @mousedown.prevent="selecionarCliente(cliente)">
                                            <span x-text="cliente.nome || cliente.nome_fantasia || cliente.razao_social"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Categoria --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Categoria
                            </label>
                            <input type="text"
                                x-model="form.categoria"
                                readonly
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed">
                        </div>

                        {{-- Valor --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Valor <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                x-model="form.valor"
                                step="0.01"
                                min="0"
                                required
                                placeholder="0.00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        {{-- Conta Destino --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Conta Destino <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.conta_financeira_id"
                                required
                                :disabled="!form.empresa_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">Selecione uma empresa primeiro...</option>
                                <template x-for="conta in contasFinanceiras" :key="conta.id">
                                    <option :value="conta.id" x-text="`${conta.nome} - ${conta.tipo}`"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Forma de Pagamento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Forma de Pagamento <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.forma_pagamento"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Selecione...</option>
                                <option value="pix">PIX</option>
                                <option value="boleto">Boleto</option>
                                <option value="cartao_credito">C.Crédito</option>
                                <option value="cartao_debito">C.Débito</option>
                                <option value="faturado">Faturado</option>
                            </select>
                        </div>

                        {{-- Periodicidade --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Periodicidade <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.periodicidade"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Selecione...</option>
                                <option value="diaria">Diária</option>
                                <option value="semanal">Semanal</option>
                                <option value="quinzenal">Quinzenal</option>
                                <option value="mensal">Mensal</option>
                                <option value="semestral">Semestral</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>

                        {{-- Data Inicial --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Data Inicial <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                x-model="form.data_inicial"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        {{-- Data Fim --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Data Fim <small class="text-gray-500">(deixe em branco para "para sempre")</small>
                            </label>
                            <input type="date"
                                x-model="form.data_fim"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        {{-- % Renovação --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                % Renovação <small class="text-gray-500">(opcional)</small>
                            </label>
                            <input type="number"
                                x-model="form.percentual_renovacao"
                                step="0.01"
                                min="0"
                                max="100"
                                placeholder="0.00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        {{-- Data % Atualização --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Data % Atualização
                            </label>
                            <input type="date"
                                x-model="form.data_atualizacao_percentual"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        {{-- Observação --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Observação
                            </label>
                            <textarea x-model="form.observacao"
                                rows="3"
                                placeholder="Informações adicionais sobre esta conta fixa..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button"
                            @click="mostrar = false"
                            class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-md">
                            <span x-text="editando ? 'Atualizar Conta Fixa' : 'Salvar Conta Fixa'"></span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>