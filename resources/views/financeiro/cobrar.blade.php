<x-app-layout>
    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @vite('resources/css/financeiro/contasareceber.css')
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

                {{-- COBRANÇA --}}
                <a href="{{ route('financeiro.cobrar' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition shadow-md border border-indigo-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Cobrar
                </a>

                {{-- CONTAS A RECEBER --}}
                <a href="{{ route('financeiro.contasareceber' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-md border border-emerald-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-2-2m2 2l2-2" />
                    </svg>
                    Receber
                </a>

                {{-- CONTAS A PAGAR --}}
                <a href="{{ route('financeiro.contasapagar') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition shadow-md border border-red-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12V6m0 0l-2 2m2-2l2 2" />
                    </svg>
                    Pagar
                </a>

                {{-- MOVIMENTAÇÃO --}}
                <a href="{{ route('financeiro.movimentacao' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition shadow-md border border-blue-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Extrato
                </a>

            </div>

            {{-- ================= FILTROS ================= --}}
            <form method="GET" action="{{ route('financeiro.contasareceber') }}" class="filters-card">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Buscar por Cliente ou Descrição</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ex: Invest, Manutenção...">
                    </div>

                    <div class="filter-group">
                        <label>Vencimento (Início)</label>
                        <input type="date" name="vencimento_inicio" value="{{ request('vencimento_inicio') }}">
                    </div>

                    <div class="filter-group">
                        <label>Vencimento (Fim)</label>
                        <input type="date" name="vencimento_fim" value="{{ request('vencimento_fim') }}">
                    </div>
                </div>

                <div class="actions-container">
                    {{-- Grupo de Filtros Rápidos (Esquerda) --}}
                    <div class="quick-filters">
                        <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('status'), ['status' => ['financeiro']])) }}"
                            class="quick-filter-btn status-financeiro {{ in_array('financeiro', request('status', [])) ? 'active' : '' }}">
                            <span>Financeiro</span>
                            <span class="count">{{ $contadoresStatus['financeiro'] }}</span>
                        </a>
                    </div>

                    {{-- Grupo de Botões (Direita) --}}
                    <div class="filters-actions">
                        <x-primary-button>Filtrar</x-primary-button>
                        <x-secondary-button href="{{ route('financeiro.contasareceber') }}">Limpar</x-secondary-button>
                    </div>

                </div>
            </form>

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
                                    <th class="text-center">AGENDAR</th>
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
                                        {{ $orcamento->cliente?->nome_fantasia ?? $orcamento->cliente?->razao_social ?? $orcamento->preCliente?->nome_fantasia ?? $orcamento->preCliente?->razao_social ?? '—' }}
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
                                        {{-- Campo para agendar cobrança --}}
                                        <div class="flex flex-col items-center gap-1">
                                            @if(empty($orcamento->data_agendamento))
                                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="abrirCalendarioAgendamento({{ $orcamento->id }})">Agendar</button>
                                                <form method="POST" action="{{ route('financeiro.agendar-cobranca', $orcamento->id) }}" id="form-agendar-{{ $orcamento->id }}" style="display:none; margin-top:4px;" class="flex items-center gap-2">
                                                    @csrf
                                                    <input type="date" name="data_agendamento" min="{{ now()->toDateString() }}" class="rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-2 py-1 text-sm w-36" required>
                                                    <button type="submit" class="btn btn-xs btn-success">Salvar</button>
                                                    <button type="button" class="btn btn-xs btn-secondary" onclick="fecharCalendarioAgendamento({{ $orcamento->id }})">Cancelar</button>
                                                </form>
                                            @else
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs font-semibold">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        Agendado para {{ \Carbon\Carbon::parse($orcamento->data_agendamento)->format('d/m/Y') }}
                                                    </span>
                                                    <form method="POST" action="{{ route('financeiro.cancelar-agendamento', $orcamento->id) }}" onsubmit="return confirm('Deseja cancelar o agendamento?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger" title="Cancelar agendamento">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </script>
                                    <script>
                                    function abrirCalendarioAgendamento(id) {
                                        document.getElementById('form-agendar-' + id).style.display = 'flex';
                                        event.target.style.display = 'none';
                                    }
                                    function fecharCalendarioAgendamento(id) {
                                        document.getElementById('form-agendar-' + id).style.display = 'none';
                                        // Reexibe o botão Agendar
                                        const btns = document.querySelectorAll('[onclick^="abrirCalendarioAgendamento"]');
                                        btns.forEach(btn => {
                                            if (btn.getAttribute('onclick').includes(id)) {
                                                btn.style.display = '';
                                            }
                                        });
                                    }
                                    </script>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($orcamento->status === 'financeiro')
                                            @php
                                            $__orcData = [
                                                'id' => $orcamento->id,
                                                'numero_orcamento' => $orcamento->numero_orcamento,
                                                'valor_total' => $orcamento->valor_total,
                                                'cliente' => [
                                                    'nome_fantasia' => $orcamento->cliente?->nome_fantasia ?? $orcamento->cliente?->razao_social ?? null
                                                ],
                                                'pre_cliente_id' => $orcamento->pre_cliente_id ?? null,
                                                'forma_pagamento' => $orcamento->forma_pagamento,
                                            ];
                                            @endphp
                                            <button type="button" class="btn btn-primary btn-sm" data-role="gerar-cobranca" data-orc='@json($__orcData)'>
                                                💰 Gerar
                                            </button>
                                            @else
                                            <span class="text-xs text-gray-400">Gerada</span>
                                            @endif
                                            <a href="{{ route('orcamentos.imprimir', $orcamento->id) }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-xl shadow-lg transition-all transform hover:scale-105 active:scale-95" title="Imprimir Orçamento">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-6 0v4m0 0h4m-4 0H8" /></svg>
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