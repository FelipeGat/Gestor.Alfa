<x-app-layout>

    {{-- ================= SCRIPTS DE BUSCA ================= --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const empresaSelect = document.querySelector('[name="empresa_id"]');
        const numeroInput = document.querySelector('[name="numero_orcamento"]');

        if (!empresaSelect || !numeroInput) return;

        empresaSelect.addEventListener('change', async () => {
            const empresaId = empresaSelect.value;

            if (!empresaId) return;

            try {
                const response = await fetch(`/orcamentos/gerar-numero/${empresaId}`);
                if (!response.ok) throw new Error('Erro ao gerar número');
                const data = await response.json();
                numeroInput.value = data.numero ?? '';
            } catch (e) {
                console.error('Erro ao gerar número do orçamento:', e);
            }
        });
    });
    </script>

    {{-- ================= SCRIPT DE BUSCA DE CLIENTES ================= --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputNome = document.getElementById('cliente_nome');
        const inputClienteId = document.getElementById('cliente_id');
        const inputPreClienteId = document.getElementById('pre_cliente_id');
        const inputTipo = document.getElementById('cliente_tipo');
        const resultados = document.getElementById('cliente-resultados');
        const btnPreCadastro = document.getElementById('btn-pre-cadastro');

        let timeout = null;

        async function buscar(q = '') {
            try {
                const res = await fetch('/busca-clientes?q=' + encodeURIComponent(q));
                if (!res.ok) throw new Error('Erro na busca');
                return await res.json();
            } catch (e) {
                console.error('Erro ao buscar clientes:', e);
                return [];
            }
        }

        function limparSelecao() {
            inputClienteId.value = '';
            inputPreClienteId.value = '';
            inputTipo.value = '';
        }

        function render(lista) {
            resultados.innerHTML = '';

            if (!lista.length) {
                resultados.classList.add('hidden');
                btnPreCadastro.classList.remove('hidden');
                return;
            }

            btnPreCadastro.classList.add('hidden');

            lista.forEach(item => {
                const nomeExibido = item.nome_fantasia || item.razao_social || '—';

                const div = document.createElement('div');
                div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b transition';

                div.innerHTML = `
                    <strong class="text-gray-900">${nomeExibido}</strong><br>
                    <span class="text-xs text-gray-500">
                        ${item.cpf_cnpj} • ${item.tipo === 'cliente' ? 'Cliente' : 'Pré-Cliente'}
                    </span>
                `;

                div.onclick = () => {
                    inputNome.value = nomeExibido;
                    limparSelecao();

                    if (item.tipo === 'cliente') {
                        inputClienteId.value = item.id;
                        inputTipo.value = 'cliente';
                    }

                    if (item.tipo === 'pre_cliente') {
                        inputPreClienteId.value = item.id;
                        inputTipo.value = 'pre_cliente';
                    }

                    resultados.classList.add('hidden');
                };

                resultados.appendChild(div);
            });

            resultados.classList.remove('hidden');
        }

        /* BUSCA AO DIGITAR */
        inputNome.addEventListener('input', () => {
            clearTimeout(timeout);
            limparSelecao();

            const q = inputNome.value.trim();

            timeout = setTimeout(async () => {
                if (!q) {
                    resultados.classList.add('hidden');
                    btnPreCadastro.classList.add('hidden');
                    return;
                }

                const data = await buscar(q);
                render(data);
            }, 300);
        });

        /* FECHAR AO CLICAR FORA */
        document.addEventListener('click', e => {
            if (!e.target.closest('#cliente_nome') && !e.target.closest('#cliente-resultados')) {
                resultados.classList.add('hidden');
            }
        });
    });
    </script>

    {{-- ================= SCRIPT PRÉ-CADASTRO ================= --}}
    <script>
    document.getElementById('btn-pre-cadastro')?.addEventListener('click', () => {
        const nome = document.getElementById('cliente_nome').value || '';

        const url = new URL('/pre-clientes/create', window.location.origin);
        url.searchParams.set('q', nome);
        url.searchParams.set('from', 'orcamento');

        window.location.href = url.toString();
    });
    </script>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ✏️ Editar Orçamento
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= HEADER DO FORMULÁRIO ================= --}}
            <div
                class="bg-gradient-to-r from-blue-50 to-indigo-50 shadow-lg rounded-lg px-6 py-4 mb-6 border-l-4 border-blue-500">
                <h1 class="text-2xl font-bold text-gray-900">Editar Orçamento</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Atualize os dados do orçamento abaixo
                </p>
            </div>

            {{-- ================= ERROS ================= --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-red-800 mb-2">Erros encontrados:</h3>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            {{-- ================= SUCESSO ================= --}}
            @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm text-green-700">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div id="inputs-itens-hidden"></div>

                {{-- ================= INFORMAÇÕES BÁSICAS ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-blue-500">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Informações Básicas</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- EMPRESA --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Empresa <span
                                    class="text-red-500">*</span></label>
                            <select name="empresa_id" required
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected($orcamento->empresa_id == $empresa->id)>
                                    {{ $empresa->nome_fantasia ?? $empresa->nome }}
                                </option>
                                @endforeach
                            </select>
                            @error('empresa_id')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Nº ORÇAMENTO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Nº do Orçamento</label>
                            <input type="text" name="numero_orcamento" readonly placeholder="Gerado automaticamente"
                                value="{{ $orcamento->numero_orcamento }}"
                                class="mt-1 w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-600">
                        </div>

                        {{-- DESCRIÇÃO / REFERÊNCIA --}}
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Descrição / Referência <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="descricao" required
                                value="{{ old('descricao', $orcamento->descricao) }}"
                                placeholder="Ex: Manutenção preventiva, Instalação de câmeras..."
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('descricao')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CLIENTE (DIGITÁVEL) --}}
                        <div class="md:col-span-2 relative">
                            <label class="text-sm font-medium text-gray-700">Cliente</label>

                            <input type="text" name="cliente_nome" id="cliente_nome" autocomplete="off"
                                value="{{ old('cliente_nome', $orcamento->cliente?->nome ?? '') }}"
                                placeholder="Digite nome ou CPF/CNPJ"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                            <input type="hidden" name="cliente_id" id="cliente_id"
                                value="{{ old('cliente_id', $orcamento->cliente_id ?? '') }}">

                            <input type="hidden" name="pre_cliente_id" id="pre_cliente_id"
                                value="{{ old('pre_cliente_id', $orcamento->pre_cliente_id ?? '') }}">

                            <input type="hidden" name="cliente_tipo" id="cliente_tipo"
                                value="{{ old('cliente_tipo', $orcamento->cliente_id ? 'cliente' : ($orcamento->pre_cliente_id ? 'pre_cliente' : '')) }}">

                            <div id="cliente-resultados"
                                class="absolute z-10 w-full bg-white border rounded-lg shadow mt-1 hidden max-h-64 overflow-y-auto">
                            </div>

                            {{-- BOTÃO PRÉ-CADASTRO --}}
                            <div class="mt-2">
                                <button type="button" id="btn-pre-cadastro"
                                    class="text-sm text-blue-600 hover:text-blue-800 hover:underline hidden flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10.5 1.5H5.75A2.25 2.25 0 003.5 3.75v12.5A2.25 2.25 0 005.75 18.5h8.5a2.25 2.25 0 002.25-2.25V9.5M10.5 1.5v4M10.5 1.5H14.25M10.5 5.5h3.75"
                                            stroke="currentColor" stroke-width="1.5" fill="none" />
                                    </svg>
                                    ➕ Cliente não possui cadastro
                                </button>
                            </div>
                        </div>

                        {{-- VALIDADE --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Validade do Orçamento</label>
                            <input type="date" name="validade"
                                value="{{ old('validade', $orcamento->validade?->format('Y-m-d')) }}"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('validade')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ================= ITENS DO ORÇAMENTO ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-green-500">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">🧾 Serviços e Produtos</h3>

                    {{-- ================= BUSCA DE SERVIÇOS ================= --}}
                    <div class="mb-6 pb-6 border-b">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z" />
                                    <path fill-rule="evenodd"
                                        d="M3 10a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6z"
                                        clip-rule="evenodd" />
                                </svg>
                                Serviços
                            </h4>
                            <button type="button" id="btn-add-servico"
                                class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM11 7a1 1 0 11-2 0 1 1 0 012 0zM8 9a1 1 0 100 2h4a1 1 0 100-2H8z"
                                        clip-rule="evenodd" />
                                </svg>
                                ➕ Adicionar Serviço
                            </button>
                        </div>

                        <div class="relative hidden" id="busca-servico-wrapper">
                            <input type="text" id="busca-servico" placeholder="Digite o nome do serviço..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                            <div id="resultado-servico"
                                class="absolute z-20 w-full bg-white border rounded-lg shadow mt-1 hidden max-h-60 overflow-auto">
                            </div>
                        </div>

                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full table-auto border-collapse">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700">Serviço</th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700">Qtd</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700">Valor Unit.
                                        </th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700">Subtotal
                                        </th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="itens-servicos" class="divide-y"></tbody>
                            </table>
                            <div class="text-right mt-2 font-semibold text-gray-900">
                                Total Serviços: <span class="text-green-600">R$ <span
                                        id="total-servicos">0,00</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- ================= BUSCA DE PRODUTOS ================= --}}
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z" />
                                    <path
                                        d="M16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                </svg>
                                Materiais / Produtos
                            </h4>
                            <button type="button" id="btn-add-produto"
                                class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM11 7a1 1 0 11-2 0 1 1 0 012 0zM8 9a1 1 0 100 2h4a1 1 0 100-2H8z"
                                        clip-rule="evenodd" />
                                </svg>
                                ➕ Adicionar Produto
                            </button>
                        </div>

                        <div class="relative hidden" id="busca-produto-wrapper">
                            <input type="text" id="busca-produto" placeholder="Digite o nome do produto..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                            <div id="resultado-produto"
                                class="absolute z-20 w-full bg-white border rounded-lg shadow mt-1 hidden max-h-60 overflow-auto">
                            </div>
                        </div>

                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full table-auto border-collapse">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700">Material
                                        </th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700">Qtd</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700">Valor Unit.
                                        </th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700">Subtotal
                                        </th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="itens-produtos" class="divide-y"></tbody>
                            </table>
                            <div class="text-right mt-2 font-semibold text-gray-900">
                                Total Materiais: <span class="text-green-600">R$ <span
                                        id="total-produtos">0,00</span></span>
                            </div>
                        </div>
                    </div>

                    {{-- ================= RESUMO FINAL ================= --}}
                    <div class="mt-6 pt-4 border-t space-y-2 text-right bg-gray-50 p-4 rounded">
                        <div class="flex justify-between">
                            <span class="text-gray-700">Serviços:</span>
                            <span class="font-semibold text-gray-900">R$ <span id="resumo-servicos">0,00</span></span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-700">Materiais:</span>
                            <span class="font-semibold text-gray-900">R$ <span id="resumo-produtos">0,00</span></span>
                        </div>

                        <div id="resumo-desconto-wrapper" class="hidden flex justify-between">
                            <span class="text-gray-700">Descontos:</span>
                            <span class="font-semibold text-red-600">− R$ <span id="resumo-desconto">0,00</span></span>
                        </div>

                        <div id="resumo-taxas-wrapper" class="hidden flex justify-between">
                            <span class="text-gray-700">Taxas Adicionais:</span>
                            <span class="font-semibold text-orange-600">R$ <span id="resumo-taxas">0,00</span></span>
                        </div>

                        <div class="flex justify-between border-t pt-2 mt-2">
                            <span class="text-lg font-bold text-gray-900">Total:</span>
                            <span class="text-lg font-bold text-blue-600">R$ <span
                                    id="total-orcamento">0,00</span></span>
                        </div>
                    </div>

                </div>

                {{-- ================= VALORES E PAGAMENTO ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-purple-500">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Valores e Pagamento</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- DESCONTO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Desconto</label>
                            <input type="number" step="0.01" name="desconto" placeholder="0,00"
                                value="{{ old('desconto', $orcamento->desconto) }}"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('desconto')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- TAXAS --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Taxas Adicionais</label>
                            <input type="number" step="0.01" name="taxas" placeholder="0,00"
                                value="{{ old('taxas', $orcamento->taxas) }}"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('taxas')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- FORMA DE PAGAMENTO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Forma de Pagamento</label>
                            <select name="forma_pagamento"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione</option>
                                <option value="pix" @selected(old('forma_pagamento', $orcamento->forma_pagamento) ==
                                    'pix')>PIX</option>
                                <option value="boleto" @selected(old('forma_pagamento', $orcamento->forma_pagamento) ==
                                    'boleto')>Boleto</option>
                                <option value="credito" @selected(old('forma_pagamento', $orcamento->forma_pagamento) ==
                                    'credito')>Cartão de Crédito</option>
                                <option value="debito" @selected(old('forma_pagamento', $orcamento->forma_pagamento) ==
                                    'debito')>Cartão de Débito</option>
                                <option value="transferencia" @selected(old('forma_pagamento', $orcamento->
                                    forma_pagamento) == 'transferencia')>Transferência</option>
                            </select>
                            @error('forma_pagamento')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ================= OBSERVAÇÕES ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-indigo-500">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Observações</h3>
                    <textarea name="observacoes" rows="4" placeholder="Observações importantes sobre o orçamento..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('observacoes', $orcamento->observacoes) }}</textarea>
                    @error('observacoes')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ================= AÇÕES ================= --}}
                <div class="flex justify-end gap-3 bg-white shadow rounded-lg p-6">
                    <a href="{{ route('orcamentos.index') }}"
                        class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        ❌ Cancelar
                    </a>

                    <button type="submit"
                        class="px-6 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        💾 Atualizar Orçamento
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ================= CARREGAR ITENS EXISTENTES =================  --}}
    @php
    $itensArray = $orcamento->itens->map(function($item) {
    return [
    'id' => $item->item_comercial_id,
    'nome' => $item->nome,
    'tipo' => $item->tipo,
    'preco_venda' => (float) $item->valor_unitario,
    'quantidade' => (int) $item->quantidade,
    ];
    })->values()->toArray();
    @endphp

    <script>
    document.addEventListener('DOMContentLoaded', () => {

        const urlBusca = "{{ url('/itemcomercial/buscar') }}";

        // ================= ELEMENTOS =================
        const tabelaServicos = document.getElementById('itens-servicos');
        const tabelaProdutos = document.getElementById('itens-produtos');

        const totalServicosSpan = document.getElementById('total-servicos');
        const totalProdutosSpan = document.getElementById('total-produtos');

        const resumoServicos = document.getElementById('resumo-servicos');
        const resumoProdutos = document.getElementById('resumo-produtos');
        const resumoDesconto = document.getElementById('resumo-desconto');
        const resumoTaxas = document.getElementById('resumo-taxas');

        const wrapperDesconto = document.getElementById('resumo-desconto-wrapper');
        const wrapperTaxas = document.getElementById('resumo-taxas-wrapper');

        const totalOrcamentoSpan = document.getElementById('total-orcamento');

        const inputDesconto = document.querySelector('[name="desconto"]');
        const inputTaxas = document.querySelector('[name="taxas"]');

        let itens = [];

        // ================= CARREGAR ITENS EXISTENTES =================
        const itensExistentes = {
            {
                json_encode($itensArray)
            }
        };

        if (itensExistentes && itensExistentes.length > 0) {
            itens = itensExistentes;
        }

        function atualizarInputsHidden() {
            const container = document.getElementById('inputs-itens-hidden');
            container.innerHTML = '';

            itens.forEach((item, index) => {
                container.innerHTML += `
                    <input type="hidden" name="itens[${index}][item_comercial_id]" value="${item.id}">
                    <input type="hidden" name="itens[${index}][quantidade]" value="${item.quantidade}">
                `;
            });
        }

        // ================= TOGGLE BUSCA =================
        document.getElementById('btn-add-servico').onclick = () => toggleBusca('servico');
        document.getElementById('btn-add-produto').onclick = () => toggleBusca('produto');

        function toggleBusca(tipo) {
            document.getElementById('busca-servico-wrapper').classList.add('hidden');
            document.getElementById('busca-produto-wrapper').classList.add('hidden');

            const wrapper = document.getElementById(`busca-${tipo}-wrapper`);
            wrapper.classList.remove('hidden');
            wrapper.querySelector('input').focus();
        }

        // ================= BUSCA =================
        function setupBusca(tipo) {
            const input = document.getElementById(`busca-${tipo}`);
            const resultado = document.getElementById(`resultado-${tipo}`);
            let timeout;

            input.addEventListener('input', () => {
                clearTimeout(timeout);
                const q = input.value.trim();

                if (!q) {
                    resultado.classList.add('hidden');
                    return;
                }

                timeout = setTimeout(async () => {
                    try {
                        const res = await fetch(`${urlBusca}?q=${encodeURIComponent(q)}`);
                        if (!res.ok) throw new Error('Erro na busca');
                        const data = await res.json();

                        const filtrado = data.filter(i => i.tipo === tipo);
                        renderResultado(filtrado, resultado);
                    } catch (e) {
                        console.error('Erro ao buscar itens:', e);
                    }
                }, 300);
            });
        }

        function renderResultado(lista, container) {
            container.innerHTML = '';

            if (!lista.length) {
                container.classList.add('hidden');
                return;
            }

            lista.forEach(item => {
                const div = document.createElement('div');
                div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                div.innerHTML = `
                <strong>${item.nome}</strong><br>
                <span class="text-xs text-gray-500">
                    R$ ${item.preco_venda.toFixed(2)} / ${item.unidade_medida}
                </span>
            `;

                div.onclick = () => {
                    adicionarItem(item);
                    container.classList.add('hidden');
                };

                container.appendChild(div);
            });

            container.classList.remove('hidden');
        }

        // ================= ADICIONAR ITEM =================
        function adicionarItem(item) {
            itens.push({
                id: item.id,
                nome: item.nome,
                tipo: item.tipo,
                preco_venda: parseFloat(item.preco_venda),
                quantidade: 1
            });

            renderTabela();
        }

        // ================= RENDER TABELAS =================
        function renderTabela() {
            tabelaServicos.innerHTML = '';
            tabelaProdutos.innerHTML = '';

            let totalServicos = 0;
            let totalProdutos = 0;

            itens.forEach((item, index) => {
                const subtotal = item.quantidade * item.preco_venda;

                const linha = `
                <tr>
                    <td class="px-3 py-2 text-sm">${item.nome}</td>
                    <td class="px-3 py-2 text-center">
                        <input type="number" min="1" value="${item.quantidade}"
                            class="w-16 border rounded text-center"
                            onchange="atualizarQtd(${index}, this.value)">
                    </td>
                    <td class="px-3 py-2 text-right text-sm">
                        R$ ${formatar(item.preco_venda)}
                    </td>
                    <td class="px-3 py-2 text-right text-sm">
                        R$ ${formatar(subtotal)}
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" onclick="removerItem(${index})">🗑️</button>
                    </td>
                </tr>
            `;

                if (item.tipo === 'servico') {
                    totalServicos += subtotal;
                    tabelaServicos.innerHTML += linha;
                } else {
                    totalProdutos += subtotal;
                    tabelaProdutos.innerHTML += linha;
                }
            });

            totalServicosSpan.textContent = formatar(totalServicos);
            totalProdutosSpan.textContent = formatar(totalProdutos);

            resumoServicos.textContent = formatar(totalServicos);
            resumoProdutos.textContent = formatar(totalProdutos);

            calcularTotalGeral(totalServicos, totalProdutos);
            atualizarInputsHidden();
        }

        // ================= TOTAIS =================
        function calcularTotalGeral(totalServicos, totalProdutos) {
            const desconto = parseFloat(inputDesconto?.value || 0);
            const taxas = parseFloat(inputTaxas?.value || 0);

            const total = totalServicos + totalProdutos - desconto + taxas;

            totalOrcamentoSpan.textContent = formatar(total);

            toggleResumo(wrapperDesconto, desconto);
            toggleResumo(wrapperTaxas, taxas);

            resumoDesconto.textContent = formatar(desconto);
            resumoTaxas.textContent = formatar(taxas);
        }

        function toggleResumo(wrapper, valor) {
            wrapper.classList.toggle('hidden', valor <= 0);
        }

        function formatar(valor) {
            return Number(valor).toFixed(2).replace('.', ',');
        }

        // ================= FUNÇÕES GLOBAIS =================
        window.atualizarQtd = (index, qtd) => {
            itens[index].quantidade = Math.max(1, parseInt(qtd));
            renderTabela();
        };

        window.removerItem = (index) => {
            itens.splice(index, 1);
            renderTabela();
        };

        // Recalcular ao mudar desconto ou taxas
        inputDesconto?.addEventListener('input', () => renderTabela());
        inputTaxas?.addEventListener('input', () => renderTabela());

        // Inicializa buscas
        setupBusca('servico');
        setupBusca('produto');

        // Renderiza tabela com itens existentes
        renderTabela();

    });
    </script>



</x-app-layout>