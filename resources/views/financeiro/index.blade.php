<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💰 Painel Financeiro
        </h2>
    </x-slot><br>

    <div class="page-container">
        <div class="space-y-6">

            {{-- ================= NAVEGAÇÃO ================= --}}
            <div class="section-card financeiro-nav">
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold rounded-lg transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Bancos
                </a>
                <a href="{{ route('financeiro.contasareceber') }}" class="btn btn-success">🧾 Contas a Receber</a>
                <a href="#" class="btn btn-secondary opacity-60 cursor-not-allowed" title="Em breve">💸 Contas a Pagar</a>
            </div>

            {{-- ================= KPIs GERAIS ================= --}}
            <div class="kpi-grid">
                <div class="kpi-card border-blue">
                    <div class="label">Total a Receber (Geral)</div>
                    <div class="value">R$ {{ number_format($kpisGerais['total_receber'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-green">
                    <div class="label">Total Pago (Geral)</div>
                    <div class="value">R$ {{ number_format($kpisGerais['total_pago'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-red">
                    <div class="label">Total Vencido (Geral)</div>
                    <div class="value">R$ {{ number_format($kpisGerais['total_vencido'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-yellow">
                    <div class="label">Vence Hoje (Geral)</div>
                    <div class="value">R$ {{ number_format($kpisGerais['vence_hoje'], 2, ',', '.') }}</div>
                </div>
            </div>

            {{-- ================= ORÇAMENTOS PENDENTES ================= --}}
            <div class="section-card">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Orçamentos Aguardando Ação</h3>
                    <p class="text-sm text-gray-500">Orçamentos aprovados que precisam da geração de cobrança.</p>
                </div>

                {{-- Filtros --}}
                <form method="GET" action="{{ route('financeiro.index') }}" class="filter-form">
                    <div class="filter-grid">
                        <div class="lg:col-span-6">
                            <label for="search" class="filter-label">Buscar por Nº, Cliente ou Empresa</label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}" class="filter-input">
                        </div>
                        <div class="lg:col-span-4">
                            <label for="empresa_id" class="filter-label">Empresa</label>
                            <select name="empresa_id[]" id="empresa_id" multiple class="filter-select h-24">
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(in_array($empresa->id, request('empresa_id', [])))>
                                    {{ $empresa->nome_fantasia }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-2 filter-actions">
                            <button type="submit" class="btn btn-primary w-full">Filtrar</button>
                            <a href="{{ route('financeiro.index') }}" class="btn btn-secondary w-full">Limpar</a>
                        </div>
                    </div>
                    <div class="quick-filters">
                        <a href="{{ route('financeiro.index', array_merge(request()->except('status'), ['status' => ['financeiro']])) }}"
                            class="quick-filter-btn status-financeiro {{ in_array('financeiro', request('status', [])) ? 'active' : '' }}">
                            <span>Pronto para Cobrança</span>
                            <span class="count">{{ $contadoresStatus['financeiro'] }}</span>
                        </a>
                        <a href="{{ route('financeiro.index', array_merge(request()->except('status'), ['status' => ['aguardando_pagamento']])) }}"
                            class="quick-filter-btn status-aguardando {{ in_array('aguardando_pagamento', request('status', [])) ? 'active' : '' }}">
                            <span>Aguardando Pagamento</span>
                            <span class="count">{{ $contadoresStatus['aguardando_pagamento'] }}</span>
                        </a>
                    </div>
                </form>

                {{-- Mensagens de feedback --}}
                @if (session('error'))
                <div class="mb-4 rounded bg-red-100 border border-red-300 px-4 py-2 text-red-700">
                    {{ session('error') }}
                </div>
                @endif

                @if (session('success'))
                <div class="mb-4 rounded bg-green-100 border border-green-300 px-4 py-2 text-green-700">
                    {{ session('success') }}
                </div>
                @endif

                {{-- Tabela --}}
                <div class="table-container">
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Orçamento</th>
                                    <th>Cliente</th>
                                    <th>Status</th>
                                    <th class="text-right">Valor</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPagina = 0; @endphp
                                @forelse($orcamentos as $orcamento)
                                @php $totalPagina += $orcamento->valor_total; @endphp
                                <tr>
                                    <td data-label="Orçamento">
                                        <div class="font-semibold">{{ $orcamento->numero_orcamento }}</div>
                                        <div class="text-xs text-gray-500">{{ $orcamento->empresa->nome_fantasia ?? 'N/A' }}</div>
                                    </td>
                                    <td data-label="Cliente">{{ $orcamento->cliente?->nome ?? $orcamento->preCliente?->nome_fantasia }}</td>
                                    <td data-label="Status">
                                        <span class="status-badge {{ $orcamento->status }}">{{ ucfirst(str_replace('_', ' ', $orcamento->status)) }}</span>
                                    </td>
                                    <td data-label="Valor" class="text-right font-bold">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
                                    <td data-label="Ação" class="text-center">
                                        @if($orcamento->status === 'financeiro')
                                        <form method="POST" action="{{ route('financeiro.orcamentos.gerar-cobranca', $orcamento) }}">
                                            @csrf
                                            <input type="hidden" name="valor" value="{{ $orcamento->valor_total }}">
                                            <input type="hidden" name="data_vencimento" value="{{ now()->addDays(7)->toDateString() }}">
                                            <input type="hidden" name="descricao" value="Cobrança do orçamento {{ $orcamento->numero_orcamento }}">
                                            <button class="btn btn-primary">Gerar Cobrança</button>
                                        </form>
                                        @else
                                        <span class="text-xs text-gray-400">Cobrança já gerada</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-12 text-gray-500">Nenhum orçamento encontrado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Rodapé da Tabela --}}
                    <div class="table-footer">
                        <div class="footer-total">
                            <span class="label">Total na Página:</span>
                            <span class="value">R$ {{ number_format($totalPagina, 2, ',', '.') }}</span>
                        </div>
                        <div class="footer-total">
                            <span class="label">Total Geral (Filtrado):</span>
                            <span class="value">R$ {{ number_format($totalGeralFiltrado, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                {{-- Paginação --}}
                <div class="pagination-container">
                    {{ $orcamentos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>