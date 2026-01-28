<x-app-layout>
    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @vite('resources/css/financeiro/contasareceber.css')
    @vite('resources/css/financeiro/index.css')
    @endpush

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">

            {{-- TÍTULO --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M9 16h6m-6-4h6" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Movimentação Financeira
                </h2>
            </div>

            {{-- BOTÃO VOLTAR --}}
            <a href="{{ route('financeiro.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                      text-gray-600 bg-white border border-gray-200 rounded-lg
                      hover:bg-gray-50 hover:text-indigo-600 transition-all shadow-sm group">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 transition-transform group-hover:-translate-x-1"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= NAVEGAÇÃO ================= --}}
            <div class="section-card financeiro-nav">
                {{-- BANCOS --}}
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-black font-bold rounded-lg transition shadow-sm border border-yellow-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Bancos
                </a>

                {{-- CONTAS A RECEBER --}}
                <a href="{{ route('financeiro.contasareceber' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-md border border-emerald-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-2-2m2 2l2-2" />
                    </svg>
                    Contas a Receber
                </a>

                {{-- CONTAS A PAGAR --}}
                <a href="#"
                    class="inline-flex items-center px-4 py-2.5 bg-gray-50 text-gray-400 font-bold rounded-lg cursor-not-allowed border border-gray-200 opacity-60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12V6m0 0l-2 2m2-2l2 2" />
                    </svg>
                    Contas a Pagar
                </a>

                {{-- MOVIMENTAÇÃO --}}
                <a href="{{ route('financeiro.movimentacao' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition shadow-md border border-blue-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Movimentação
                </a>

            </div>

            {{-- ================= FILTROS ================= --}}
            <form method="GET" action="{{ route('financeiro.movimentacao') }}" class="filters-card">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Buscar por Cliente ou Descrição</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ex: Invest, Manutenção...">
                    </div>

                    <div class="filter-group">
                        <label>Data Pagamento (Início)</label>
                        <input type="date" name="data_inicio" value="{{ request('data_inicio') }}">
                    </div>

                    <div class="filter-group">
                        <label>Data Pagamento (Fim)</label>
                        <input type="date" name="data_fim" value="{{ request('data_fim') }}">
                    </div>
                </div>

                <div class="filters-actions">
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('financeiro.movimentacao') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </form>

            {{-- ================= KPI ================= --}}
            <div class="section-card mb-6">
                <div class="kpi-card border-blue">
                    <div class="label">Total de Entradas no Período</div>
                    <div class="value">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</div>
                </div>
            </div>

            {{-- ================= TABELA (EXTRATO) ================= --}}
            <div class="section-card">
                <div class="table-container">
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>DATA</th>
                                    <th>DESCRIÇÃO</th>
                                    <th>CLIENTE</th>
                                    <th>Nº PARCELA</th>
                                    <th>VALOR</th>
                                    <th>BANCO</th>
                                    <th>FORMA PAGTO</th>
                                    <th>AÇÕES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPagina = 0; @endphp

                                @forelse($movimentacoes as $mov)
                                @php $totalPagina += $mov->valor; @endphp
                                <tr>

                                    {{-- DATA --}}
                                    <td>
                                        {{ optional($mov->pago_em)->format('d/m/Y') ?? '—' }}
                                    </td>

                                    {{-- DESCRIÇÃO --}}
                                    <td>
                                        {{ $mov->descricao }}
                                        <div class="text-xs text-gray-500">
                                            Origem: Orçamento #{{ $mov->orcamento_id }}
                                        </div>
                                    </td>

                                    {{-- CLIENTE --}}
                                    <td>
                                        {{ $mov->cliente?->nome ?? $mov->cliente?->nome_fantasia ?? $mov->cliente?->razao_social ?? '—' }}
                                    </td>

                                    {{-- Nº PARCELA --}}
                                    <td class="text-center">
                                        @if($mov->parcela_num && $mov->parcelas_total)
                                        {{ $mov->parcela_num }}/{{ $mov->parcelas_total }}
                                        @else
                                        Única
                                        @endif
                                    </td>

                                    {{-- VALOR --}}
                                    <td class="text-right font-bold text-blue-600">
                                        R$ {{ number_format($mov->valor, 2, ',', '.') }}
                                    </td>

                                    {{-- BANCO --}}
                                    <td>
                                        {{ $mov->contaFinanceira?->nome ?? '—' }}
                                    </td>

                                    {{-- FORMA PAGAMENTO --}}
                                    <td>
                                        @if($mov->forma_pagamento)
                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                                            {{ strtoupper(str_replace('_', ' ', $mov->forma_pagamento)) }}
                                        </span>
                                        @else
                                        —
                                        @endif
                                    </td>

                                    {{-- AÇÕES --}}
                                    <td>
                                        <div class="flex items-center gap-2">

                                            {{-- IMPRIMIR COMPROVANTE --}}
                                            <a href="{{ route('financeiro.cobrancas.recibo', $mov) }}"
                                                target="_blank"
                                                class="btn btn-secondary btn-sm"
                                                title="Imprimir Comprovante">
                                                🖨️
                                            </a>

                                            {{-- DELETAR / ESTORNAR --}}
                                            <form method="POST"
                                                action="{{ route('financeiro.movimentacao.estornar', $mov) }}"
                                                onsubmit="return confirm('Deseja estornar esta cobrança e devolvê-la para Contas a Receber?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    title="Estornar Pagamento">
                                                    ↩️
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-12 text-gray-500">
                                        Nenhuma movimentação encontrada.
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
                        <span class="value">
                            R$ {{ number_format($totalPagina, 2, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="pagination-container p-4">
                    {{ $movimentacoes->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>