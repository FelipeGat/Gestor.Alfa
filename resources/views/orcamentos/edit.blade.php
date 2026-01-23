<x-app-layout>
    @push('styles')
        @vite('resources/css/atendimentos/index.css')
    @endpush

    @push('scripts')
        @vite('resources/js/orcamento.js')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                <span class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </span>
                Editar Orçamento #{{ $orcamento->numero_orcamento }}
            </h2>
            <div class="text-sm text-gray-500 font-medium">{{ now()->format('d/m/Y') }}</div>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= ERROS ================= --}}
            @if ($errors->any())
            <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-5 rounded-xl shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800 uppercase tracking-wide">Erros encontrados:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $erro) <li>{{ $erro }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                {{-- Root do JS com dados para edição --}}
                <div id="orcamento-root" 
                    data-url-busca="{{ url('/itemcomercial/buscar') }}"
                    data-itens="{{ json_encode($orcamento->itens) }}"
                    data-taxas="{{ json_encode($orcamento->taxas_detalhe ?? []) }}"
                    data-taxa-valor="{{ $orcamento->taxas ?? 0 }}">
                </div>

                <div id="inputs-itens-hidden"></div>

                {{-- HIDDEN INPUTS PARA EXTRAS --}}
                <input type="hidden" name="desconto" id="desconto-hidden">
                <input type="hidden" name="taxas" id="taxas-hidden">

                @if(isset($orcamento->atendimento_id))
                    <input type="hidden" name="atendimento_id" value="{{ $orcamento->atendimento_id }}">
                @endif

                {{-- ================= INFORMAÇÕES BÁSICAS ================= --}}
                <div class="form-card">
                    <div class="card-header">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Dados do Orçamento
                        </h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-6">
                            <label class="label-text">Empresa <span class="text-red-500">*</span></label>
                            <select name="empresa_id" required class="input-field w-full">
                                <option value="">Selecione a empresa</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}"
                                        @if(old('empresa_id', $orcamento->empresa_id) == $empresa->id) selected @endif>
                                        {{ $empresa->nome_fantasia ?? $empresa->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-6">
                            <label class="label-text">Número do Orçamento</label>
                            <input type="text" name="numero_orcamento" readonly
                                value="{{ $orcamento->numero_orcamento }}"
                                class="input-field w-full bg-gray-50 font-mono font-bold text-blue-600">
                        </div>

                        <div class="md:col-span-12">
                            <label class="label-text">Descrição / Referência <span class="text-red-500">*</span></label>
                            <input type="text" name="descricao" required
                                value="{{ old('descricao', $orcamento->descricao) }}"
                                class="input-field w-full">
                        </div>

                        <div class="md:col-span-8 relative">
                            <label class="label-text">Cliente</label>
                            <input type="text" name="cliente_nome" id="cliente_nome" autocomplete="off"
                                value="{{ old('cliente_nome', $orcamento->cliente?->nome ?? $orcamento->preCliente?->nome ?? '') }}"
                                placeholder="Buscar cliente..." class="input-field w-full">

                            <input type="hidden" name="cliente_id" id="cliente_id"
                                value="{{ old('cliente_id', $orcamento->cliente_id) }}">

                            <input type="hidden" name="pre_cliente_id" id="pre_cliente_id"
                                value="{{ old('pre_cliente_id', $orcamento->pre_cliente_id) }}">

                            <input type="hidden" name="cliente_tipo" id="cliente_tipo"
                                value="{{ old('cliente_tipo',
                                    $orcamento->cliente_id ? 'cliente' :
                                    ($orcamento->pre_cliente_id ? 'pre_cliente' : '')
                                ) }}">

                            <div id="cliente-resultados" class="search-results-container hidden"></div>

                            <button type="button" id="btn-pre-cadastro"
                                    class="hidden mt-2 text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg">
                                ➕ Novo Pré-Cadastro
                            </button>
                        </div>

                        <div class="md:col-span-4">
                            <label class="label-text">Validade</label>
                            <input type="date" name="validade"
                                value="{{ old('validade', $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->format('Y-m-d') : '') }}"
                                class="input-field w-full">
                        </div>
                    </div>
                </div>

                {{-- ================= ITENS DO ORÇAMENTO ================= --}}
                <div class="form-card border-t-4 border-t-green-500">
                    <div class="card-header">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                            </svg>
                            Itens e Serviços
                        </h3>
                    </div>

                    <div class="p-6 space-y-8">
                        {{-- SERVIÇOS --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-2 h-2 bg-orange-400 rounded-full"></span> Serviços
                                </h4>
                                <button type="button" id="btn-add-servico"
                                        class="text-xs font-bold text-orange-600 hover:bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100 transition-all">
                                    ➕ Adicionar Serviço
                                </button>
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

                        {{-- PRODUTOS --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-2 h-2 bg-blue-400 rounded-full"></span> Materiais e Produtos
                                </h4>
                                <button type="button" id="btn-add-produto"
                                        class="text-xs font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 transition-all">
                                    ➕ Adicionar Produto
                                </button>
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

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        
                        {{-- ================= BLOCO DE DESCONTOS ================= --}}
                        <div class="form-card border-t-4 border-t-red-400">
                            <div class="card-header">
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                    Descontos por Categoria
                                </h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="label-text">Desconto em Serviços</label>
                                    <div class="flex gap-2 mt-1">
                                        <input type="number" step="0.01" id="desconto-servico-valor" name="desconto_servico_valor" value="{{ old('desconto_servico_valor', $orcamento->desconto_servico_valor) }}" class="input-field flex-1" placeholder="0,00">
                                        <select id="desconto-servico-tipo" name="desconto_servico_tipo" class="input-field w-24">
                                            <option value="valor" @if(old('desconto_servico_tipo', $orcamento->desconto_servico_tipo) == 'valor') selected @endif>R$</option>
                                            <option value="percentual" @if(old('desconto_servico_tipo', $orcamento->desconto_servico_tipo) == 'percentual') selected @endif>%</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="label-text">Desconto em Materiais</label>
                                    <div class="flex gap-2 mt-1">
                                        <input type="number" step="0.01" id="desconto-produto-valor" name="desconto_produto_valor" value="{{ old('desconto_produto_valor', $orcamento->desconto_produto_valor) }}" class="input-field flex-1" placeholder="0,00">
                                        <select id="desconto-produto-tipo" name="desconto_produto_tipo" class="input-field w-24">
                                            <option value="valor" @if(old('desconto_produto_tipo', $orcamento->desconto_produto_tipo) == 'valor') selected @endif>R$</option>
                                            <option value="percentual" @if(old('desconto_produto_tipo', $orcamento->desconto_produto_tipo) == 'percentual') selected @endif>%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TAXAS ADICIONAIS --}}
                        <div class="form-card border-t-4 border-t-purple-500">
                            <div class="card-header">
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Taxas e Impostos
                                </h3>
                                <button type="button" id="btn-add-taxa" class="text-xs font-bold text-purple-600 hover:underline">➕ Nova Taxa</button>
                            </div>
                            <div class="p-6"><div id="lista-taxas" class="space-y-3"></div></div>
                        </div>

                        {{-- FORMAS DE PAGAMENTO --}}
                        <div class="form-card border-t-4 border-t-emerald-500">
                            <div class="card-header">
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    Condições de Pagamento
                                </h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <label class="flex items-center p-3 border border-gray-100 rounded-xl hover:bg-gray-50 cursor-pointer transition-all">
                                        <input type="radio" name="forma_pagamento" value="pix"
                                            class="rounded text-emerald-500 mr-3"
                                            @if(old('forma_pagamento', $orcamento->forma_pagamento) === 'pix') checked @endif>
                                        <span class="text-sm font-medium text-gray-700">À Vista (Pix / Dinheiro)</span>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-100 rounded-xl hover:bg-gray-50 cursor-pointer transition-all">
                                        <input type="radio" name="forma_pagamento" value="debito"
                                            class="rounded text-emerald-500 mr-3"
                                            @if(old('forma_pagamento', $orcamento->forma_pagamento) === 'debito') checked @endif>
                                        <span class="text-sm font-medium text-gray-700">Cartão de Débito</span>
                                    </label>
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-center p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition-all">
                                        <input type="radio" name="forma_pagamento" value="credito"
                                            class="rounded text-emerald-500 mr-3 fp-check"
                                            @if(old('forma_pagamento', $orcamento->forma_pagamento) === 'credito') checked @endif>
                                        <div class="flex-1 flex items-center justify-between ml-1">
                                            <span class="text-sm font-medium text-gray-700">Crédito</span>
                                            <div class="flex items-center gap-1">
                                                <input type="number" name="parcelas_credito" min="1"
                                                    value="{{ old('parcelas_credito', 1) }}"
                                                    class="w-14 border-gray-200 rounded text-xs p-1 text-center fp-parcelas"
                                                    @if(old('forma_pagamento', $orcamento->forma_pagamento) !== 'credito') disabled @endif>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase">x</span>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition-all">
                                        <input type="radio" name="forma_pagamento" value="boleto"
                                            class="rounded text-emerald-500 mr-3 fp-check"
                                            @if(old('forma_pagamento', $orcamento->forma_pagamento) === 'boleto') checked @endif>
                                        <div class="flex-1 flex items-center justify-between ml-1">
                                            <span class="text-sm font-medium text-gray-700">Boleto</span>
                                            <div class="flex items-center gap-1">
                                                <input type="number" name="parcelas_boleto" min="1"
                                                    value="{{ old('parcelas_boleto', 1) }}"
                                                    class="w-14 border-gray-200 rounded text-xs p-1 text-center fp-parcelas"
                                                    @if(old('forma_pagamento', $orcamento->forma_pagamento) !== 'boleto') disabled @endif>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase">x</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- COLUNA DIREITA: RESUMO --}}
                    <div class="space-y-8">
                        <div class="form-card bg-gray-900 border-none text-green-600 sticky top-8">
                            <div class="px-6 py-4 border-b border-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                <h3 class="text-sm font-bold uppercase tracking-widest">Resumo Financeiro</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between text-sm"><span class="text-gray-400">Serviços</span><span class="font-mono font-bold">R$ <span id="resumo-servicos">0,00</span></span></div>
                                <div class="flex justify-between text-sm"><span class="text-gray-400">Materiais</span><span class="font-mono font-bold">R$ <span id="resumo-produtos">0,00</span></span></div>
                                <div id="resumo-desconto-wrapper" class="hidden flex justify-between text-sm text-red-400"><span>Descontos</span><span class="font-mono font-bold">- R$ <span id="resumo-desconto">0,00</span></span></div>
                                <div id="resumo-taxas-wrapper" class="hidden flex justify-between text-sm text-orange-400"><span>Taxas</span><span class="font-mono font-bold">+ R$ <span id="resumo-taxas">0,00</span></span></div>
                                <div class="pt-4 border-t border-gray-800 flex justify-between items-end">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">Total Geral</p>
                                        <p class="text-3xl font-bold text-blue-400 font-mono">R$ <span id="total-orcamento">0,00</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-800/50">
                                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold text-sm transition-all shadow-lg flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    ATUALIZAR ORÇAMENTO
                                </button>
                                <a href="{{ route('orcamentos.index') }}" class="block text-center mt-3 text-red-700 hover:text-red-300 font-medium uppercase tracking-widest">Cancelar</a>
                            </div>
                        </div>

                        <div class="form-card border-t-4 border-t-indigo-500">
                            <div class="card-header"><h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Observações</h3></div>
                            <div class="p-4"><textarea name="observacoes" rows="4" class="input-field w-full bg-gray-50 border-none focus:ring-indigo-100">{{ old('observacoes', $orcamento->observacoes) }}</textarea></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
        @push('scripts')
            <script>
            document.addEventListener('DOMContentLoaded', function () {

                const extras = window.extras || { desconto: 0, taxas: 0 };

                const descontoInput = document.getElementById('desconto-hidden');
                const taxasInput    = document.getElementById('taxas-hidden');

                if (descontoInput) descontoInput.value = extras.desconto;
                if (taxasInput)    taxasInput.value    = extras.taxas;

                if (typeof recalcularTotais === 'function') {
                    recalcularTotais();
                }

            });
            </script>
            @endpush


</x-app-layout>
