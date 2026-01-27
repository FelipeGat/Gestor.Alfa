<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💰 Painel Financeiro
        </h2>
    </x-slot>

    <br>

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

                <a href="{{ route('financeiro.contasareceber') }}" class="btn btn-success">
                    🧾 Contas a Receber
                </a>

                <a href="#" class="btn btn-secondary opacity-60 cursor-not-allowed">
                    💸 Contas a Pagar
                </a>
            </div>

            {{-- ================= KPIs ================= --}}
            <div class="kpi-grid">
                <div class="kpi-card border-blue">
                    <div class="label">Total a Receber</div>
                    <div class="value">R$ {{ number_format($kpisGerais['total_receber'], 2, ',', '.') }}</div>
                </div>

                <div class="kpi-card border-green">
                    <div class="label">Total Pago</div>
                    <div class="value">R$ {{ number_format($kpisGerais['total_pago'], 2, ',', '.') }}</div>
                </div>

                <div class="kpi-card border-red">
                    <div class="label">Total Vencido</div>
                    <div class="value">R$ {{ number_format($kpisGerais['total_vencido'], 2, ',', '.') }}</div>
                </div>

                <div class="kpi-card border-yellow">
                    <div class="label">Vence Hoje</div>
                    <div class="value">R$ {{ number_format($kpisGerais['vence_hoje'], 2, ',', '.') }}</div>
                </div>
            </div>

            {{-- ================= ORÇAMENTOS ================= --}}
            <div class="section-card">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Orçamentos Aguardando Ação</h3>
                    <p class="text-sm text-gray-500">
                        Orçamentos aprovados que precisam da geração de cobrança.
                    </p>
                </div>

                {{-- Filtros --}}
                <form method="GET" action="{{ route('financeiro.index') }}" class="filter-form">
                    <div class="filter-grid">
                        <div class="lg:col-span-6">
                            <label class="filter-label">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="filter-input">
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
                            <a href="{{ route('financeiro.index') }}" class="btn btn-secondary w-full">Limpar</a>
                        </div>
                    </div>
                </form>

                {{-- Mensagens --}}
                @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- ================= TABELA ================= --}}
                <div class="table-container">
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
                                {{-- ORÇAMENTO --}}
                                <td>
                                    <strong>{{ $orcamento->numero_orcamento }}</strong>
                                    <div class="text-xs text-gray-500">
                                        {{ $orcamento->empresa->nome_fantasia ?? '—' }}
                                    </div>
                                </td>

                                {{-- CLIENTE --}}
                                <td>
                                    {{ $orcamento->cliente?->nome_fantasia
                            ?? $orcamento->preCliente?->nome_fantasia
                            ?? '—' }}
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    <span class="status-badge {{ $orcamento->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $orcamento->status)) }}
                                    </span>
                                </td>

                                {{-- VALOR --}}
                                <td class="text-right font-bold">
                                    R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                                </td>

                                {{-- AÇÃO --}}
                                <td class="text-center">
                                    @if($orcamento->status === 'financeiro')
                                    <button
                                        type="button"
                                        class="btn btn-primary"
                                        @click='$store.modalCobranca.abrir({
                                            id: {{ $orcamento->id }},
                                            numero_orcamento: "{{ $orcamento->numero_orcamento }}",
                                            valor_total: {{ $orcamento->valor_total }},
                                            cliente: {
                                                nome_fantasia: "{{ $orcamento->cliente?->nome_fantasia ?? $orcamento->preCliente?->nome_fantasia ?? 'N/A' }}"
                                            },
                                            forma_pagamento: "{{ $orcamento->forma_pagamento }}"
                                        })'>
                                        💰 Gerar Cobrança
                                    </button>
                                    @else
                                    <span class="text-xs text-gray-400">
                                        Cobrança já gerada
                                    </span>
                                    @endif
                                </td>

                            </tr>

                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">
                                    Nenhum orçamento encontrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                {{-- TOTAL --}}
                <div class="table-footer">
                    <strong>Total da Página:</strong>
                    R$ {{ number_format($totalPagina, 2, ',', '.') }}
                </div>

                {{ $orcamentos->links() }}
            </div>
        </div>
    </div>
    @include('financeiro.partials.modal-gerar-cobranca')

</x-app-layout>