<x-app-layout>
    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    <style>
        /* Cards de Seção */
        .section-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        .section-card .card-header {
            padding: 1.5rem 2rem 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }
        .section-card .card-header h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
        }
        .section-card h4 {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: #111827;
        }
        /* Inputs e Selects */
        .filter-select:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Tabela de Itens */
        .table-header {
            font-size: 14px;
            font-weight: 600;
            color: rgb(17, 24, 39);
            text-transform: uppercase;
        }
        /* Botões */
        .btn-success {
            background: #22c55e !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);
            transition: all 0.2s;
        }
        .btn-success:hover {
            box-shadow: 0 4px 6px rgba(34, 197, 94, 0.4);
        }
    </style>
    @endpush

    @push('scripts')
    @vite('resources/js/orcamento.js')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Orçamentos', 'url' => route('orcamentos.index')],
            ['label' => 'Editar #' . $orcamento->numero_orcamento]
        ]" />
    </x-slot>

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
            <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-5 rounded-xl shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg></div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800 uppercase tracking-wide">Erros encontrados:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $erro) <li>{{ $erro }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" class="space-y-6" id="form-orcamento">
                @csrf
                @method('PUT')

                @php
                $taxasSalvas = [];
                if (!empty($orcamento->descricao_taxas)) {
                $decoded = json_decode($orcamento->descricao_taxas, true);
                if (is_array($decoded)) {
                $taxasSalvas = $decoded;
                }
                }
                @endphp

                {{-- Root do JS com dados para edição - CRUCIAL PARA O JS --}}
                <div id="orcamento-root"
                    data-url-busca="{{ url('/itemcomercial/buscar') }}"
                    data-itens="{{ json_encode($itensArray ?? []) }}"
                    data-taxas="{{ json_encode($taxasSalvas) }}"
                    data-taxa-valor="{{ $orcamento->taxas ?? 0 }}">
                </div>

                <div id="inputs-itens-hidden"></div>

                {{-- HIDDEN INPUTS PARA EXTRAS --}}
                <input type="hidden" name="desconto" id="desconto-hidden" value="{{ $orcamento->desconto }}">
                <input type="hidden" name="taxas" id="taxas-hidden" value="{{ $orcamento->taxas }}">

                @if(isset($orcamento->atendimento_id))
                <input type="hidden" name="atendimento_id" value="{{ $orcamento->atendimento_id }}">
                @endif

                {{-- ================= INFORMAÇÕES BÁSICAS ================= --}}
                <div class="section-card">
                    <div class="card-header">
                        <h3 class="text-base font-semibold text-gray-800">Dados do Orçamento</h3>
                    </div>

                    <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-6">
                            <label class="filter-label">Empresa <span class="text-red-500">*</span></label>
                            <select name="empresa_id" required class="filter-select w-full">
                                <option value="">Selecione a empresa</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @if(old('empresa_id', $orcamento->empresa_id) == $empresa->id) selected @endif>
                                    {{ $empresa->nome_fantasia ?? $empresa->nome }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-6">
                            <label class="filter-label">Número do Orçamento</label>
                            <input type="text" name="numero_orcamento" readonly value="{{ $orcamento->numero_orcamento }}" class="filter-select w-full bg-gray-50 font-bold text-blue-600">
                        </div>

                        <div class="md:col-span-12">
                            <label class="filter-label">Descrição / Referência <span class="text-red-500">*</span></label>
                            <input type="text" name="descricao" required value="{{ old('descricao', $orcamento->descricao) }}" class="filter-select w-full">
                        </div>

                        <div class="md:col-span-8 relative">
                            <label class="filter-label">Cliente</label>
                            <input type="text" name="cliente_nome" id="cliente_nome" autocomplete="off" value="{{ old('cliente_nome', $orcamento->cliente?->nome ?? $orcamento->preCliente?->nome_fantasia ?? $orcamento->preCliente?->razao_social ?? '') }}" placeholder="Buscar cliente..." class="filter-select w-full">
                            <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id', $orcamento->cliente_id) }}">
                            <input type="hidden" name="pre_cliente_id" id="pre_cliente_id" value="{{ old('pre_cliente_id', $orcamento->pre_cliente_id) }}">
                            <input type="hidden" name="cliente_tipo" id="cliente_tipo" value="{{ old('cliente_tipo', $orcamento->cliente_id ? 'cliente' : ($orcamento->pre_cliente_id ? 'pre_cliente' : '')) }}">
                            <div id="cliente-resultados" class="search-results-container hidden"></div>
                            <button type="button" id="btn-pre-cadastro" class="hidden mt-2 text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg">➕ Novo Pré-Cadastro</button>
                        </div>

                        <div class="md:col-span-4">
                            <label class="filter-label">Validade</label>
                            <input type="date" name="validade" value="{{ old('validade', $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->format('Y-m-d') : '') }}" class="filter-select w-full">
                        </div>
                    </div>
                </div>

                {{-- ITENS DO ORÇAMENTO --}}
                <div class="section-card">
                    <div class="card-header flex justify-between items-center">
                        <h3 class="text-base font-semibold text-gray-800">Itens e Serviços</h3>
                    </div>

                    <div class="p-6 sm:p-8 space-y-8">
                        {{-- Serviços --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="filter-label flex items-center gap-2"><span class="w-2 h-2 bg-orange-400 rounded-lg"></span> Serviços</h4>
                                <button type="button" id="btn-add-servico" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Adicionar
                                </button>
                            </div>
                            <div class="relative hidden mb-4" id="busca-servico-wrapper">
                                <input type="text" id="busca-servico" placeholder="Pesquisar serviço..." class="filter-select w-full">
                                <div id="resultado-servico" class="search-results-container hidden"></div>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="table-header">Descrição</th>
                                            <th class="table-header text-center w-24">Qtd</th>
                                            <th class="table-header text-right w-32">Valor Unit.</th>
                                            <th class="table-header text-right w-32">Subtotal</th>
                                            <th class="table-header text-center w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itens-servicos" class="bg-white divide-y divide-gray-50"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Produtos --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="filter-label flex items-center gap-2"><span class="w-2 h-2 bg-blue-400 rounded-lg"></span> Materiais e Produtos</h4>
                                <button type="button" id="btn-add-produto" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    Adicionar
                                </button>
                            </div>
                            <div class="relative hidden mb-4" id="busca-produto-wrapper">
                                <input type="text" id="busca-produto" placeholder="Pesquisar produto..." class="filter-select w-full">
                                <div id="resultado-produto" class="search-results-container hidden"></div>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="table-header">Nome do Produto</th>
                                            <th class="table-header text-center w-24">Qtd</th>
                                            <th class="table-header text-right w-32">Valor Unit.</th>
                                            <th class="table-header text-right w-32">Subtotal</th>
                                            <th class="table-header text-center w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itens-produtos" class="bg-white divide-y divide-gray-50"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">

                        {{-- DESCONTOS --}}
                        <div class="section-card">
                            <div class="card-header">
                                <h3 class="text-base font-semibold text-gray-800">Descontos por Categoria</h3>
                            </div>
                            <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="filter-label">Desconto em Serviços</label>
                                    <div class="flex gap-2 mt-1">
                                        <input type="number" step="0.01" id="desconto-servico-valor" name="desconto_servico_valor" value="{{ old('desconto_servico_valor', $orcamento->desconto_servico_valor) }}" class="filter-select flex-1" placeholder="0,00">
                                        <select id="desconto-servico-tipo" name="desconto_servico_tipo" class="filter-select w-24">
                                            <option value="valor" @if(old('desconto_servico_tipo', $orcamento->desconto_servico_tipo) == 'valor') selected @endif>R$</option>
                                            <option value="percentual" @if(old('desconto_servico_tipo', $orcamento->desconto_servico_tipo) == 'percentual') selected @endif>%</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="filter-label">Desconto em Materiais</label>
                                    <div class="flex gap-2 mt-1">
                                        <input type="number" step="0.01" id="desconto-produto-valor" name="desconto_produto_valor" value="{{ old('desconto_produto_valor', $orcamento->desconto_produto_valor) }}" class="filter-select flex-1" placeholder="0,00">
                                        <select id="desconto-produto-tipo" name="desconto_produto_tipo" class="filter-select w-24">
                                            <option value="valor" @if(old('desconto_produto_tipo', $orcamento->desconto_produto_tipo) == 'valor') selected @endif>R$</option>
                                            <option value="percentual" @if(old('desconto_produto_tipo', $orcamento->desconto_produto_tipo) == 'percentual') selected @endif>%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TAXAS ADICIONAIS - DEIXANDO O JS CONTROLAR --}}
                        <div class="section-card">
                            <div class="card-header flex justify-between items-center">
                                <h3 class="text-base font-semibold text-gray-800">Taxas e Impostos</h3>
                                <button type="button" id="btn-add-taxa" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 18px; height: 18px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Adicionar
                                </button>
                            </div>
                            <div class="p-6">
                                <div id="lista-taxas" class="space-y-3">
                                </div>
                            </div>
                        </div>

                        {{-- OBSERVAÇÕES --}}
                        <div class="section-card">
                            <div class="card-header">
                                <h3 class="text-base font-semibold text-gray-800">Observações</h3>
                            </div>
                            <div class="p-4"><textarea name="observacoes" rows="4" class="filter-select w-full bg-gray-50">{{ old('observacoes', $orcamento->observacoes) }}</textarea></div>
                        </div>

                        {{-- FORMAS DE PAGAMENTO --}}
                        <div class="section-card">
                            <div class="card-header">
                                <h3 class="text-base font-semibold text-gray-800">Condições de Pagamento</h3>
                            </div>

                            <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- COLUNA 1 --}}
                                <div class="space-y-3">

                                    {{-- PIX --}}
                                    <label
                                        class="group flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-all">
                                        <input type="radio" name="forma_pagamento" value="pix"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 fp-check"
                                            @checked(old('forma_pagamento', $orcamento->forma_pagamento) === 'pix')>

                                        <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-700">
                                            À Vista (Pix / Dinheiro)
                                        </span>
                                    </label>

                                    {{-- DÉBITO --}}
                                    <label
                                        class="group flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-all">
                                        <input type="radio" name="forma_pagamento" value="debito"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 fp-check"
                                            @checked(old('forma_pagamento', $orcamento->forma_pagamento) === 'debito')>

                                        <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-700">
                                            Cartão de Débito
                                        </span>
                                    </label>

                                </div>

                                {{-- COLUNA 2 --}}
                                <div class="space-y-3">

                                    {{-- CRÉDITO --}}
                                    <label
                                        class="group flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-all">
                                        <input type="radio" name="forma_pagamento" value="credito"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 fp-check"
                                            @checked(old('forma_pagamento', $orcamento->forma_pagamento) === 'credito')>

                                        <div class="flex-1 flex items-center justify-between ml-3">
                                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">
                                                Cartão de Crédito
                                            </span>

                                            <div class="flex items-center gap-1 bg-gray-100 rounded-md px-3 py-1.5 w-32">
                                                <input type="number" name="prazo_pagamento" min="1" max="28"
                                                    value="{{ old('prazo_pagamento', $orcamento->prazo_pagamento ?? 1) }}"
                                                    class="w-16 bg-transparent border-none text-center text-sm font-medium text-gray-700 focus:outline-none fp-parcelas"
                                                    @disabled(old('forma_pagamento', $orcamento->forma_pagamento) !== 'credito')
                                                onclick="event.stopPropagation()">
                                                <span class="text-xs font-bold text-gray-400">x</span>
                                            </div>
                                        </div>
                                    </label>

                                    {{-- BOLETO --}}
                                    <label
                                        class="group flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-all">
                                        <input type="radio" name="forma_pagamento" value="boleto"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 fp-check"
                                            @checked(old('forma_pagamento', $orcamento->forma_pagamento) === 'boleto')>

                                        <div class="flex-1 flex items-center justify-between ml-3">
                                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">
                                                Boleto
                                            </span>

                                            <div class="flex items-center gap-1 bg-gray-100 rounded-md px-3 py-1.5 w-32">
                                                <input type="number" name="prazo_pagamento" min="1" max="28"
                                                    value="{{ old('prazo_pagamento', $orcamento->prazo_pagamento ?? 1) }}"
                                                    class="w-16 bg-transparent border-none text-center text-sm font-medium text-gray-700 focus:outline-none fp-parcelas"
                                                    @disabled(old('forma_pagamento', $orcamento->forma_pagamento) !== 'boleto')
                                                onclick="event.stopPropagation()">
                                                <span class="text-xs font-bold text-gray-400">x</span>
                                            </div>
                                        </div>
                                    </label>

                                    {{-- FATURADO --}}
                                    <label
                                        class="group flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-all">
                                        <input type="radio" name="forma_pagamento" value="faturado"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 fp-check"
                                            @checked(old('forma_pagamento', $orcamento->forma_pagamento) === 'faturado')>

                                        <div class="flex-1 flex items-center justify-between ml-3">
                                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">
                                                Faturado
                                            </span>

                                            <div class="flex items-center gap-1 bg-gray-100 rounded-md px-3 py-1.5 w-32">
                                                <input type="number" name="prazo_pagamento" min="1" max="28"
                                                    value="{{ old('prazo_pagamento', $orcamento->prazo_pagamento ?? 1) }}"
                                                    class="w-16 bg-transparent border-none text-center text-sm font-medium text-gray-700 focus:outline-none fp-parcelas"
                                                    @disabled(old('forma_pagamento', $orcamento->forma_pagamento) !== 'faturado')
                                                onclick="event.stopPropagation()">
                                                <span class="text-xs font-bold text-gray-400">dias</span>
                                            </div>
                                        </div>
                                    </label>

                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- RESUMO --}}
                    <div class="space-y-6">
                        <div class="section-card bg-gray-50 border-none">
                            <div class="card-header">
                                <h3 class="text-base font-semibold text-gray-800">Resumo Financeiro</h3>
                            </div>
                            <div class="p-6 sm:p-8 space-y-4">
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Serviços</span><span class="font-bold">R$ <span id="resumo-servicos">0,00</span></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-600">Materiais</span><span class="font-bold">R$ <span id="resumo-produtos">0,00</span></span></div>
                                <div id="resumo-desconto-wrapper" class="hidden flex justify-between text-sm text-red-600"><span>Descontos</span><span class="font-bold">- R$ <span id="resumo-desconto">0,00</span></span></div>
                                <div id="resumo-taxas-wrapper" class="hidden flex justify-between text-sm text-orange-600"><span>Taxas</span><span class="font-bold">+ R$ <span id="resumo-taxas">0,00</span></span></div>
                                <div class="pt-4 border-t border-gray-200 flex justify-between items-end">
                                    <div>
                                        <p class="text-xs font-bold text-gray-500 uppercase">Total Geral</p>
                                        <p class="text-3xl font-bold text-blue-600">R$ <span id="total-orcamento">0,00</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-100 border-t border-gray-200">
                                <button type="submit" class="btn btn-primary w-full justify-center" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #3f9cae; border-radius: 9999px; box-shadow: 0 2px 4px rgba(63, 156, 174, 0.3); transition: all 0.2s;" onmouseover="this.style.background='#358a96'; this.style.boxShadow='0 4px 6px rgba(63, 156, 174, 0.4)'" onmouseout="this.style.background='#3f9cae'; this.style.boxShadow='0 2px 4px rgba(63, 156, 174, 0.3)'">
                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Atualizar
                                </button>
                                <a href="{{ route('orcamentos.index') }}" class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition duration-200" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #ef4444; color: white; border: none; border-radius: 9999px; min-width: 130px; justify-content: center; margin-top: 0.75rem; width: 100%; box-shadow: none;" onmouseover="this.style.boxShadow='0 4px 6px rgba(239, 68, 68, 0.4)'" onmouseout="this.style.boxShadow='none'">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>