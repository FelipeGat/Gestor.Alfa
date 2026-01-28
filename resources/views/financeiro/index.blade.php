<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            {{-- TÍTULO --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Painel de Cobrança
                </h2>
            </div>

            {{-- BOTÃO VOLTAR --}}
            <a href="{{ route('financeiro.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-indigo-600 transition-all shadow-sm group"
                title="Voltar para Financeiro">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= 1. NAVEGAÇÃO ================= --}}
            <div class="section-card financeiro-nav mb-6">
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-black font-bold rounded-lg transition shadow-sm border border-yellow-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Bancos
                </a>

                <a href="{{ route('financeiro.contasareceber' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-md border border-emerald-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-2-2m2 2l2-2" />
                    </svg>
                    Contas a Receber
                </a>

                <a href="#"
                    class="inline-flex items-center px-4 py-2.5 bg-gray-50 text-gray-400 font-bold rounded-lg cursor-not-allowed border border-gray-200 opacity-60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12V6m0 0l-2 2m2-2l2 2" />
                    </svg>
                    Contas a Pagar
                </a>
            </div>

            {{-- ================= 2. TÍTULO DA SEÇÃO ================= --}}
            <div class="section-card mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Orçamentos ou Contratos Aguardando Ação do Financeiro</h3>
                    <p class="text-sm text-gray-500">
                        Orçamentos Aprovados que precisam da Geração de Cobrança.
                    </p>
                </div>
            </div>

            {{-- ================= 3. FILTROS (Mantendo suas classes originais) ================= --}}
            <div class="section-card mb-6">
                <form method="GET" action="{{ route('financeiro.index') }}" class="filter-form p-6">
                    <div class="filter-grid">
                        <div class="lg:col-span-6">
                            <label class="filter-label">BUSCAR</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Pesquisar por número ou cliente...">
                        </div>

                        <div class="lg:col-span-4">
                            <label class="filter-label">EMPRESA</label>
                            <select name="empresa_id[]" multiple class="filter-select h-24">
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(in_array($empresa->id, request('empresa_id', [])))>
                                    {{ $empresa->nome_fantasia }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-2 filter-actions">
                            <button class="btn btn-primary w-full">Filtrar</button>
                            <a href="{{ route('financeiro.index') }}" class="btn btn-secondary w-full text-center mt-2">Limpar</a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Mensagens de Feedback --}}
            @if(session('error'))
            <div class="mb-4">
                <div class="alert alert-error">{{ session('error') }}</div>
            </div>
            @endif
            @if(session('success'))
            <div class="mb-4">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
            @endif

            {{-- ================= 4. TABELA ================= --}}
            <div class="section-card">
                <div class="table-container">
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-left">ORÇAMENTO</th>
                                    <th class="text-left">CLIENTE</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-right">VALOR</th>
                                    <th class="text-center">AÇÕES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPagina = 0; @endphp
                                @forelse($orcamentos as $orcamento)
                                @php $totalPagina += $orcamento->valor_total; @endphp
                                <tr>
                                    <td class="text-left">
                                        <span class="font-bold text-gray-900">{{ $orcamento->numero_orcamento }}</span>
                                        <div class="text-xs text-gray-500">{{ $orcamento->empresa->nome_fantasia ?? '—' }}</div>
                                    </td>
                                    <td class="text-left">
                                        {{ $orcamento->cliente?->nome_fantasia ?? $orcamento->preCliente?->nome_fantasia ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge {{ $orcamento->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $orcamento->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-right font-bold text-gray-900">
                                        R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($orcamento->status === 'financeiro')
                                            @php
                                            $__orcData = [
                                            'id' => $orcamento->id,
                                            'numero_orcamento' => $orcamento->numero_orcamento,
                                            'valor_total' => $orcamento->valor_total,
                                            'cliente' => ['nome_fantasia' => $orcamento->cliente?->nome_fantasia ?? $orcamento->preCliente?->nome_fantasia ?? 'N/A'],
                                            'forma_pagamento' => $orcamento->forma_pagamento,
                                            ];
                                            @endphp
                                            <button type="button" class="btn btn-primary btn-sm" data-role="gerar-cobranca" data-orc='@json($__orcData)'>
                                                💰 Gerar
                                            </button>
                                            @else
                                            <span class="text-xs text-gray-400">Gerada</span>
                                            @endif
                                            <a href="{{ route('orcamentos.imprimir', $orcamento->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Imprimir Orçamento">
                                                🖨️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-12 text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <span class="text-3xl mb-2">📂</span>
                                            Nenhum orçamento encontrado.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TOTAL --}}
                <div class="table-footer">
                    <div class="footer-total">
                        <span class="label">Total da Página:</span>
                        <span class="value">R$ {{ number_format($totalPagina, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="pagination-container p-4">
                    {{ $orcamentos->links() }}
                </div>
            </div>

        </div>
    </div>
    @include('financeiro.partials.modal-gerar-cobranca')
</x-app-layout>