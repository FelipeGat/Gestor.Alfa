<x-app-layout>

    @push('styles')
        @vite('resources/css/atendimentos/index.css')
    @endpush

    @push('scripts')
        @vite('resources/js/orcamento.js')
    @endpush

   <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ➕ Novo Orçamento
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= HEADER DO FORMULÁRIO ================= --}}
            <div
                class="bg-gradient-to-r from-blue-50 to-indigo-50 shadow-lg rounded-lg px-6 py-4 mb-6 border-l-4 border-blue-500">
                <h1 class="text-2xl font-bold text-gray-900">Cadastro de Orçamento</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Preencha os dados abaixo para criar um novo orçamento
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

            {{-- ================= VÍNCULO COM ATENDIMENTO ================= --}}
            @if(isset($atendimento))
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg shadow">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <strong class="text-blue-900">Orçamento vinculado ao atendimento:</strong>
                        <p class="text-blue-800 text-sm mt-1">
                            Nº {{ $atendimento->numero_atendimento }}
                            @if($atendimento->cliente)
                            — {{ $atendimento->cliente->nome }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('orcamentos.store') }}" method="POST" class="space-y-6">
                @csrf

            {{-- ROOT PARA JS DO ORÇAMENTO --}}
                    <div id="orcamento-root"
                        data-url-busca="{{ url('/itemcomercial/buscar') }}">
                    </div>

                <div id="inputs-itens-hidden"></div>

                @if(isset($atendimento))
                <input type="hidden" name="atendimento_id" value="{{ $atendimento->id }}">
                @endif

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
                                <option value="{{ $empresa->id }}" @if(isset($atendimento) && $atendimento->empresa_id
                                    == $empresa->id) selected @endif>
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
                                class="mt-1 w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-600">
                        </div>

                        {{-- DESCRIÇÃO / REFERÊNCIA --}}
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Descrição / Referência <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="descricao" required
                                value="{{ old('descricao', $atendimento->descricao ?? '') }}"
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
                                value="{{ old('cliente_nome', $atendimento?->cliente?->nome ?? '') }}"
                                placeholder="Digite nome ou CPF/CNPJ"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                            <input type="hidden" name="cliente_id" id="cliente_id"
                                value="{{ old('cliente_id', $atendimento?->cliente_id ?? '') }}">

                            <input type="hidden" name="pre_cliente_id" id="pre_cliente_id"
                                value="{{ old('pre_cliente_id') }}">

                            <input type="hidden" name="cliente_tipo" id="cliente_tipo"
                                value="{{ old('cliente_tipo', $atendimento?->cliente_id ? 'cliente' : '') }}">

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
                                value="{{ old('validade', now()->addDays(5)->format('Y-m-d')) }}"
                                min="{{ now()->addDays(5)->format('Y-m-d') }}"
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

                {{-- ================= DESCONTOS E VALORES ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-purple-500">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Descontos e Valores</h3>

                    {{-- DESCONTOS --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

                        {{-- DESCONTO SERVIÇO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Desconto Serviços</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" id="desconto-servico-valor"
                                    class="w-full border rounded px-3 py-2 text-sm">
                                <select id="desconto-servico-tipo"
                                    class="border rounded px-2 text-sm">
                                    <option value="valor">R$</option>
                                    <option value="percentual">%</option>
                                </select>
                            </div>
                        </div>

                        {{-- DESCONTO PRODUTO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Desconto Materiais</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" id="desconto-produto-valor"
                                    class="w-full border rounded px-3 py-2 text-sm">
                                <select id="desconto-produto-tipo"
                                    class="border rounded px-2 text-sm">
                                    <option value="valor">R$</option>
                                    <option value="percentual">%</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    {{-- TAXAS ADICIONAIS --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-sm font-medium text-gray-700">Taxas Adicionais</label>
                            <button type="button" id="btn-add-taxa"
                                class="text-sm text-blue-600 hover:underline">
                                ➕ Adicionar Taxa
                            </button>
                        </div>

                        <div id="lista-taxas" class="space-y-2"></div>
                    </div>
                </div>

                {{-- ================= FORMAS DE PAGAMENTO ================= --}}
            <div class="bg-white shadow rounded-lg p-6 border-t-4 border-emerald-500">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">💳 Formas de Pagamento</h3>

                <div id="formas-pagamento" class="space-y-3">

                    {{-- PIX / DINHEIRO --}}
                    <label class="flex items-center gap-2">
                        <input type="checkbox" value="pix" class="fp-check">
                        À Vista (Pix / Dinheiro)
                    </label>

                    {{-- DÉBITO --}}
                    <label class="flex items-center gap-2">
                        <input type="checkbox" value="debito" class="fp-check">
                        Cartão de Débito
                    </label>

                    {{-- CRÉDITO --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" value="credito" class="fp-check">
                        <span>Cartão de Crédito</span>
                        <input type="number" min="1" value="1"
                            class="w-20 border rounded px-2 py-1 text-sm fp-parcelas"
                            placeholder="x"
                            disabled>
                    </div>

                    {{-- BOLETO --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" value="boleto" class="fp-check">
                        <span>Boleto Bancário</span>
                        <input type="number" min="1" value="1"
                            class="w-20 border rounded px-2 py-1 text-sm fp-parcelas"
                            placeholder="x"
                            disabled>
                    </div>

                </div>
            </div>



                {{-- ================= OBSERVAÇÕES ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-indigo-500">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Observações</h3>
                    <textarea name="observacoes" rows="4" placeholder="Observações importantes sobre o orçamento..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('observacoes') }}</textarea>
                    @error('observacoes')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ================= AÇÕES ================= --}}
                <div class="flex justify-end gap-3 bg-white shadow rounded-lg p-6">
                    <a href="{{ route('orcamentos.index') }}"
                        class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition duration-200">

                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>

                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Salvar Orçamento
                    </button>
                </div>

            </form>
        </div>
    </div>


</x-app-layout>