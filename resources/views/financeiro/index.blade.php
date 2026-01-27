<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💰 Painel de Cobrança
        </h2>
    </x-slot><br>

    <div class="page-container">
        <div class="space-y-6">

            {{-- ================= NAVEGAÇÃO ================= --}}
            <div class="section-card financeiro-nav">
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Bancos
                </a>

                <a href="{{ route('financeiro.contasareceber' ) }}" class="btn btn-success">
                    🧾 Contas a Receber
                </a>

                <a href="#" class="btn btn-secondary opacity-60 cursor-not-allowed">
                    💸 Contas a Pagar
                </a>
            </div>

            {{-- ================= ORÇAMENTOS ================= --}}
            <div class="section-card">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Orçamentos ou Contratos Aguardando Ação do Financeiro</h3>
                    <p class="text-sm text-gray-500">
                        Orçamentos Aprovados que precisam da Geração de Cobrança.
                    </p>
                </div>

                {{-- Filtros --}}
                <form method="GET" action="{{ route('financeiro.index') }}" class="filter-form">
                    <div class="filter-grid">
                        <div class="lg:col-span-6">
                            <label class="filter-label">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Pesquisar por número ou cliente...">
                        </div>

                        <div class="lg:col-span-4">
                            <label class="filter-label">Empresa</label>
                            <select name="empresa_id[]" multiple class="filter-select h-24">
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}"
                                    @selected(in_array($empresa->id, request('empresa_id', [])))>
                                    {{ $empresa->nome_fantasia }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-2 filter-actions">
                            <button class="btn btn-primary w-full">Filtrar</button>
                            <a href="{{ route('financeiro.index') }}" class="btn btn-secondary w-full text-center">Limpar</a>
                        </div>
                    </div>
                </form>

                {{-- Mensagens --}}
                @if(session('error'))
                <div class="px-6 pb-4">
                    <div class="alert alert-error">{{ session('error') }}</div>
                </div>
                @endif

                @if(session('success'))
                <div class="px-6 pb-4">
                    <div class="alert alert-success">{{ session('success') }}</div>
                </div>
                @endif

                {{-- ================= TABELA ================= --}}
                <div class="table-container">
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-left">Orçamento</th>
                                    <th class="text-left">Cliente</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Valor</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $totalPagina = 0; @endphp

                                @forelse($orcamentos as $orcamento)
                                @php $totalPagina += $orcamento->valor_total; @endphp

                                <tr>
                                    {{-- ORÇAMENTO --}}
                                    <td class="text-left">
                                        <span class="font-bold text-gray-900">{{ $orcamento->numero_orcamento }}</span>
                                        <div class="text-xs text-gray-500">
                                            {{ $orcamento->empresa->nome_fantasia ?? '—' }}
                                        </div>
                                    </td>

                                    {{-- CLIENTE --}}
                                    <td class="text-left">
                                        {{ $orcamento->cliente?->nome_fantasia
                                            ?? $orcamento->preCliente?->nome_fantasia
                                            ?? '—' }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        <span class="status-badge {{ $orcamento->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $orcamento->status)) }}
                                        </span>
                                    </td>

                                    {{-- VALOR --}}
                                    <td class="text-right font-bold text-gray-900">
                                        R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                                    </td>

                                    {{-- AÇÕES --}}
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

                                            <button
                                                type="button"
                                                class="btn btn-primary btn-sm"
                                                data-role="gerar-cobranca"
                                                data-orc='@json($__orcData)'>
                                                💰 Gerar
                                            </button>
                                            @else
                                            <span class="text-xs text-gray-400">Gerada</span>
                                            @endif

                                            <a href="{{ route('orcamentos.imprimir', $orcamento->id) }}"
                                                target="_blank"
                                                class="btn btn-secondary btn-sm"
                                                title="Imprimir Orçamento">
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

                <div class="pagination-container">
                    {{ $orcamentos->links() }}
                </div>
            </div>
        </div>
    </div>
    @include('financeiro.partials.modal-gerar-cobranca')
</x-app-layout>