<form method="POST" action="{{ route('financeiro.contasapagar.store') }}" x-data="contaPagarModal()"
    @submit.prevent="salvar()" @editar-conta-pagar.window="
        // Definir isEditing ANTES de carregar para garantir que resetForm não limpe nada
        isEditing = true;
        carregarConta($event.detail.contaId).then(() => {
            // Aguardar o próximo tick para garantir que todos os campos foram atualizados
            $nextTick(() => {
                // Abrir o modal com skipReset para evitar o reset automático do listener de open-modal
                $dispatch('open-modal', { name: 'modal-conta-pagar', skipReset: true });
            });
        }).catch(error => {
            isEditing = false;
            console.error('Erro ao carregar conta:', error);
        });
    " @open-modal.window="
        if (typeof $event.detail === 'object') {
            if ($event.detail.name === 'modal-conta-pagar' && !$event.detail.skipReset) {
                resetForm();
            }
        } else if ($event.detail === 'modal-conta-pagar') {
            resetForm();
        }
    ">
    @csrf
    <x-modal name="modal-conta-pagar" maxWidth="2xl" title="Despesa Variada">
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

            {{-- Vencimento e Forma de Pagamento --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1">Vencimento <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="data_vencimento" x-model="dataVencimento" required
                        class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                </div>

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
            </div>

            {{-- Conta Bancária (oculta quando for cartão de crédito) --}}
            <div x-show="formaPagamento !== 'CARTAO_CREDITO'">
                <label class="text-sm font-medium text-gray-700 mb-1">Conta Bancária</label>
                <select name="conta_financeira_id" x-model="contaFinanceiraId"
                    class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                    <option value="">Selecione...</option>
                    @foreach(\App\Models\ContaFinanceira::where('ativo', true)->where('tipo', '!=', 'credito')->orderBy('nome')->get() as $contaBancaria)
                        <option value="{{ $contaBancaria->id }}">{{ $contaBancaria->nome }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Campos exclusivos de Cartão de Crédito --}}
            <div x-show="formaPagamento === 'CARTAO_CREDITO'" x-cloak>
                <div class="rounded-xl p-4" style="background: rgba(63,156,174,0.05); border: 1px solid rgba(63,156,174,0.3);">
                    <p class="text-xs font-semibold text-[#3f9cae] mb-3 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Lançamento em Cartão de Crédito
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1">Selecione o Cartão</label>
                            <select name="cartao_credito_id" x-model="cartaoCreditoId"
                                class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                <option value="">Selecione o cartão...</option>
                                @foreach(\App\Models\ContaFinanceira::where('ativo', true)->where('tipo', 'credito')->orderBy('nome')->get() as $cartao)
                                    <option value="{{ $cartao->id }}">
                                        {{ $cartao->nome }}{{ $cartao->bandeira ? ' (' . $cartao->bandeira . ')' : '' }}
                                        — Disponível: R$ {{ number_format(max(0, (float)$cartao->limite_credito - (float)$cartao->limite_credito_utilizado), 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1">Número de Parcelas</label>
                            <div class="flex items-center gap-3">
                                <input type="number" name="parcelas" x-model="parcelas" min="1" max="48" value="1"
                                    class="w-28 rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                <span class="text-xs text-gray-500"
                                    x-show="parcelas > 1 && valor"
                                    x-text="`= ${parcelas}x de R$ ${(parseFloat(valor) / parseInt(parcelas || 1)).toFixed(2).replace('.', ',')}`">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vínculo com Orçamento --}}
            <div class="p-4 rounded-xl border"
                style="background-color: rgba(63, 156, 174, 0.05); border-color: #3f9cae;">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-[#3f9cae]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd"
                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                            clip-rule="evenodd" />
                    </svg>
                    <label class="text-sm font-medium text-gray-700">Vincular a Orçamento/Serviço</label>
                </div>

                <div class="flex items-center gap-2 mb-3">
                    <input type="checkbox" x-model="vincularOrcamento" id="vincular-orcamento"
                        class="rounded border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae]">
                    <label for="vincular-orcamento" class="text-sm text-gray-600">Esta despesa está vinculada a um
                        orçamento ou serviço</label>
                </div>

                <div x-show="vincularOrcamento" class="mt-4 space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1">Cliente</label>
                        <select x-model="clienteId" @change="carregarOrcamentos()"
                            class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                            <option value="">Selecione...</option>
                            <template x-for="cliente in clientes" :key="cliente.id">
                                <option :value="cliente.id" x-text="cliente.nome_fantasia"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1">Orçamento</label>
                        <select name="orcamento_id" x-model="orcamentoId" :disabled="!clienteId"
                            class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
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
                <textarea name="observacoes" x-model="observacoes" rows="2" maxlength="1000"
                    class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20"></textarea>
            </div>
        </div>

        <x-slot name="footer">
            <x-button type="button" variant="danger" size="sm" class="min-w-[130px]"
                @click="$dispatch('close-modal', 'modal-conta-pagar')">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
                Cancelar
            </x-button>
            <x-button type="submit" variant="primary" size="sm" class="min-w-[130px]" x-bind:disabled="loading">
                <svg x-show="!loading" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
                <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span x-text="loading ? 'Salvando...' : 'Salvar'"></span>
            </x-button>
        </x-slot>
    </x-modal>
</form>

<script>
    function contaPagarModal() {
        return {
            loading: false,
            categorias: @js(\App\Models\Categoria::where('ativo', true)->orderBy('nome')->get()),
            subcategorias: [],
            contas: [],
            categoriaId: '',
            subcategoriaId: '',
            contaId: '',
            centroCustoId: '',
            descricao: '',
            valor: '',
            dataVencimento: '',
            formaPagamento: '',
            contaFinanceiraId: '',
            cartaoCreditoId: '',
            parcelas: 1,
            observacoes: '',

            fornecedorBusca: '',
            fornecedores: @js(\App\Models\Fornecedor::where('ativo', true)->orderBy('razao_social')->get(['id', 'razao_social', 'nome_fantasia'])),
            fornecedoresFiltrados: [],
            mostrarListaFornecedores: false,

            vincularOrcamento: false,
            clienteId: '',
            clientes: @js(\App\Models\Cliente::where('ativo', true)->orderBy('nome_fantasia')->get(['id', 'nome_fantasia'])),
            orcamentos: [],
            orcamentoId: '',

            // Estado para controlar se estamos editando ou criando
            isEditing: false,

            async salvar() {
                if (this.loading) return;

                // Validações básicas
                if (!this.centroCustoId) { alert('Por favor, selecione um Centro de Custo.'); return; }
                if (!this.categoriaId) { alert('Por favor, selecione uma Categoria.'); return; }
                if (!this.subcategoriaId) { alert('Por favor, selecione uma Subcategoria.'); return; }
                if (!this.contaId) { alert('Por favor, selecione uma Conta.'); return; }
                if (!this.descricao || this.descricao.trim() === '') { alert('Por favor, preencha a Descrição.'); return; }
                if (!this.valor || parseFloat(this.valor) <= 0) { alert('Por favor, preencha um Valor válido.'); return; }
                if (!this.dataVencimento) { alert('Por favor, selecione a Data de Vencimento.'); return; }

                this.loading = true;
                try {
                    // Como o form agora envolve o x-modal, this.$el é o próprio form
                    const form = this.$el;
                    // Forçar a submissão nativa após o Alpine processar
                    this.$nextTick(() => {
                        HTMLFormElement.prototype.submit.call(form);
                    });
                } catch (error) {
                    console.error('Erro ao salvar:', error);
                    this.loading = false;
                    alert('Ocorreu um erro ao salvar. Por favor, tente novamente.');
                }
            },

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
                this.categoriaId = '';
                this.subcategoriaId = '';
                this.contaId = '';
                this.centroCustoId = '';
                this.descricao = '';
                this.valor = '';
                this.dataVencimento = '';
                this.formaPagamento = '';
                this.contaFinanceiraId = '';
                this.cartaoCreditoId = '';
                this.parcelas = 1;
                this.observacoes = '';

                this.subcategorias = [];
                this.contas = [];
                this.fornecedorBusca = '';
                this.fornecedoresFiltrados = [];
                this.mostrarListaFornecedores = false;
                this.vincularOrcamento = false;
                this.clienteId = '';
                this.orcamentos = [];
                this.orcamentoId = '';

                this.isEditing = false; // Garantir que saia do modo de edição
                this.loading = false;

                const form = this.$el;
                if (form) {
                    form.reset();
                    // Restaurar action original para criação
                    form.setAttribute('action', '{{ route("financeiro.contasapagar.store") }}');
                    // Remover campo _method se existir (vindo de uma edição anterior)
                    const methodInput = form.querySelector('input[name="_method"]');
                    if (methodInput) methodInput.remove();

                    const fornecedorIdField = form.querySelector('[name="fornecedor_id"]');
                    if (fornecedorIdField) fornecedorIdField.value = '';
                }
            },

            async carregarConta(id) {
                let _step = 'fetch';
                try {
                    const contaUrl = (contaId) => '{{ route("financeiro.contasapagar.show", ["conta" => "__ID__"]) }}'.replace('__ID__', contaId);
                    const response = await fetch(contaUrl(id), {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) {
                        let errMsg = `Erro HTTP ${response.status}`;
                        try {
                            const errData = await response.json();
                            if (errData && errData.error) errMsg += ': ' + errData.error;
                        } catch(e) {}
                        throw new Error(errMsg);
                    }
                    _step = 'parse-json';
                    const data = await response.json();
                    console.log('Dados recebidos:', data);

                    // Definir estado de edição para impedir reset acidental
                    this.isEditing = true;

                    _step = 'campos-simples';
                    // 1. Preencher campos simples diretamente
                    this.centroCustoId = data.centro_custo_id || '';
                    this.descricao = data.descricao || '';
                    this.valor = data.valor || '';
                    this.observacoes = data.observacoes || '';
                    this.formaPagamento = data.forma_pagamento || '';
                    this.contaFinanceiraId = data.conta_financeira_id || '';
                    this.cartaoCreditoId = data.cartao_credito_id || '';
                    this.dataVencimento = data.data_vencimento ? data.data_vencimento.split('T')[0] : '';

                    _step = 'categoria';
                    // 2. Carregar categoria, subcategoria e conta
                    if (data.conta && data.conta.subcategoria && data.conta.subcategoria.categoria_id) {
                        this.categoriaId = String(data.conta.subcategoria.categoria_id);
                        await this.loadSubcategorias();
                        this.subcategoriaId = String(data.conta.subcategoria_id || '');
                        await this.loadContas();
                        this.contaId = String(data.conta_id || '');
                    } else {
                        this.categoriaId = '';
                        this.subcategoriaId = '';
                        this.contaId = '';
                        this.subcategorias = [];
                        this.contas = [];
                    }

                    _step = 'fornecedor';
                    // 3. Preencher fornecedor
                    const fornecedorInput = this.$refs.fornecedorId;
                    if (data.fornecedor_id) {
                        if (fornecedorInput) fornecedorInput.value = data.fornecedor_id;
                        const f = this.fornecedores.find(f => f.id == data.fornecedor_id);
                        if (f) this.fornecedorBusca = f.razao_social || f.nome_fantasia;
                    } else {
                        if (fornecedorInput) fornecedorInput.value = '';
                        this.fornecedorBusca = '';
                    }

                    _step = 'orcamento';
                    // 4. Vínculo com Orçamento
                    this.vincularOrcamento = !!data.orcamento_id;
                    if (data.orcamento_id && data.orcamento) {
                        this.clienteId = data.orcamento.cliente_id || '';
                        await this.carregarOrcamentos();
                        this.orcamentoId = data.orcamento_id;
                    } else {
                        this.clienteId = '';
                        this.orcamentos = [];
                        this.orcamentoId = '';
                    }

                    _step = 'form-action';
                    // 5. Atualizar action e method do form
                    const form = this.$el;
                    if (form) {
                        form.setAttribute('action', contaUrl(data.id));
                        let methodInput = form.querySelector('input[name="_method"]');
                        if (!methodInput) {
                            methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            form.appendChild(methodInput);
                        }
                        methodInput.value = 'PUT';
                    }

                    console.log('Formulário preenchido com sucesso');
                    return Promise.resolve();
                } catch (error) {
                    console.error('Erro em carregarConta() na etapa [' + _step + ']:', error);
                    alert('Erro ao carregar dados da conta.\nEtapa: ' + _step + '\n' + error.message);
                    return Promise.reject(error);
                }
            }
        }
    }
</script>
