<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Meu Financeiro — {{ $cliente->nome_fantasia ?? $cliente->nome }}
            </h2>
            <a href="{{ route('portal.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg shadow transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline">Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper">

        {{-- ================= FILTROS DE PERÍODO ================= --}}
        <div class="portal-filters">
            <form method="GET" action="{{ route('portal.financeiro') }}" class="space-y-4">

                {{-- Navegação de Meses --}}
                <div class="portal-filter-group">
                    <label class="portal-filter-label">Navegação Rápida</label>
                    <div class="flex items-center gap-2">
                        @php
                        $dataAtual = request('data_inicio')
                        ? \Carbon\Carbon::parse(request('data_inicio'))
                        : \Carbon\Carbon::now();

                        $mesAnterior = $dataAtual->copy()->subMonth();
                        $proximoMes = $dataAtual->copy()->addMonth();

                        $meses = [
                        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                        ];
                        $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                        @endphp

                        <a href="{{ route('portal.financeiro', [
                            'data_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'),
                            'data_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')
                        ]) }}"
                            class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                            title="Mês Anterior">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>

                        <div class="flex-1 text-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm">
                            {{ $mesAtualNome }}
                        </div>

                        <a href="{{ route('portal.financeiro', [
                            'data_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'),
                            'data_fim' => $proximoMes->endOfMonth()->format('Y-m-d')
                        ]) }}"
                            class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                            title="Próximo Mês">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Período Personalizado (colapsável) --}}
                <div x-data="{ mostrarPeriodo: false }">
                    <button
                        type="button"
                        @click="mostrarPeriodo = !mostrarPeriodo"
                        class="text-sm text-[#3f9cae] hover:text-[#2d7a8a] font-medium flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="mostrarPeriodo ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span x-text="mostrarPeriodo ? 'Ocultar período personalizado' : 'Escolher período personalizado'"></span>
                    </button>
                    <div x-show="mostrarPeriodo" x-transition class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="portal-filter-group">
                            <label class="portal-filter-label">Data Início</label>
                            <input type="date" name="data_inicio" value="{{ $dataInicio }}"
                                class="portal-filter-input w-full">
                        </div>
                        <div class="portal-filter-group">
                            <label class="portal-filter-label">Data Fim</label>
                            <input type="date" name="data_fim" value="{{ $dataFim }}"
                                class="portal-filter-input w-full">
                        </div>
                        <div class="sm:col-span-2 flex gap-2 justify-end">
                            <button type="submit" class="portal-btn portal-btn--primary portal-btn--sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Filtrar
                            </button>
                            <a href="{{ route('portal.financeiro') }}"
                                class="portal-btn portal-btn--secondary portal-btn--sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ================= RESUMO FINANCEIRO / KPIs ================= --}}
        <div class="portal-stats-grid">
            <div class="portal-stat-card portal-stat-card--blue">
                <p class="portal-stat-label">Total em Aberto</p>
                <p class="portal-stat-value">
                    R$ {{ number_format($resumo['total_pendente'], 2, ',', '.') }}
                </p>
            </div>

            <div class="portal-stat-card portal-stat-card--green">
                <p class="portal-stat-label">Total Pago</p>
                <p class="portal-stat-value">
                    R$ {{ number_format($resumo['total_pago'], 2, ',', '.') }}
                </p>
            </div>

            <div class="portal-stat-card portal-stat-card--red">
                <p class="portal-stat-label">Total Vencido</p>
                <p class="portal-stat-value">
                    R$ {{ number_format($resumo['total_vencido'], 2, ',', '.') }}
                </p>
            </div>

            <div class="portal-stat-card portal-stat-card--gray">
                <p class="portal-stat-label">Total Geral</p>
                <p class="portal-stat-value">
                    R$ {{ number_format($resumo['total_geral'], 2, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- ================= LISTA DE COBRANÇAS ================= --}}
        <div class="rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Histórico de Cobranças
                    <span class="text-sm font-normal text-gray-500 ml-2">({{ $cobrancas->count() }} registro(s))</span>
                </h3>
            </div>
            @include('portal.partials.live-table-filter', [
                'inputId' => 'filtroCobrancas',
                'tableId' => 'tabelaCobrancas',
                'placeholder' => 'Digite descrição, status ou valor'
            ])

            {{-- Versão Desktop (Tabela) --}}
            <div class="overflow-x-auto">
                <table id="tabelaCobrancas" class="portal-table w-full">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th class="text-center">Vencimento</th>
                            <th class="text-right">Valor</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Anexos</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cobrancas as $cobranca)
                        <tr>
                            <td class="portal-font-semibold">
                                {{ $cobranca->descricao }}
                            </td>
                            <td class="text-center">
                                {{ $cobranca->data_vencimento->format('d/m/Y') }}
                            </td>
                            <td class="text-right portal-font-semibold">
                                R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($cobranca->status === 'pago')
                                <span class="portal-badge portal-badge--success">Pago</span>
                                @elseif($cobranca->data_vencimento->isToday())
                                <span class="portal-badge portal-badge--warning">Vence Hoje</span>
                                @elseif($cobranca->data_vencimento->isPast())
                                <span class="portal-badge portal-badge--danger">Vencido</span>
                                @else
                                <span class="portal-badge portal-badge--info">A Vencer</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($cobranca->anexos && $cobranca->anexos->count() > 0)
                                <div class="flex flex-wrap justify-center gap-2">
                                    @foreach($cobranca->anexos as $anexo)
                                    <a
                                        href="{{ route('portal.cobrancas.anexos.download', $anexo) }}"
                                        target="_blank"
                                        class="portal-btn portal-btn--sm {{ $anexo->tipo === 'nf' ? 'portal-btn--primary' : 'portal-btn--secondary' }}">
                                        @if($anexo->tipo === 'nf')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        NF
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        Boleto
                                        @endif
                                    </a>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($cobranca->orcamento_id)
                                <a
                                    href="{{ route('portal.orcamentos.imprimir', $cobranca->orcamento_id) }}"
                                    target="_blank"
                                    class="portal-btn portal-btn--secondary portal-btn--sm"
                                    title="Imprimir Orçamento">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Imprimir
                                </a>
                                @else
                                <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">
                                Nenhuma cobrança encontrada no período selecionado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Versão Mobile (Cards) --}}
            <div class="portal-mobile-cards px-4 pb-4">
                @forelse($cobrancas as $cobranca)
                <div class="portal-mobile-card">
                    <div class="portal-mobile-card-header">
                        <div>
                            <div class="portal-mobile-card-title">
                                {{ $cobranca->descricao }}
                            </div>
                            <div class="portal-mobile-card-subtitle">
                                Vencimento: {{ $cobranca->data_vencimento->format('d/m/Y') }}
                            </div>
                        </div>
                        @if($cobranca->status === 'pago')
                        <span class="portal-badge portal-badge--success">Pago</span>
                        @elseif($cobranca->data_vencimento->isToday())
                        <span class="portal-badge portal-badge--warning">Vence Hoje</span>
                        @elseif($cobranca->data_vencimento->isPast())
                        <span class="portal-badge portal-badge--danger">Vencido</span>
                        @else
                        <span class="portal-badge portal-badge--info">A Vencer</span>
                        @endif
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Valor</span>
                        <span class="portal-mobile-card-value portal-font-bold">
                            R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                        </span>
                    </div>
                    @if($cobranca->anexos && $cobranca->anexos->count() > 0)
                    <div class="portal-mobile-card-actions">
                        @foreach($cobranca->anexos as $anexo)
                        <a
                            href="{{ route('portal.cobrancas.anexos.download', $anexo) }}"
                            target="_blank"
                            class="portal-btn portal-btn--primary portal-btn--sm flex-1">
                            @if($anexo->tipo === 'nf')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            NF
                            @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Boleto
                            @endif
                        </a>
                        @endforeach
                    </div>
                    @endif
                    @if($cobranca->orcamento_id)
                    <div class="portal-mobile-card-actions portal-mt-2">
                        <a
                            href="{{ route('portal.orcamentos.imprimir', $cobranca->orcamento_id) }}"
                            target="_blank"
                            class="portal-btn portal-btn--secondary portal-btn--sm flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Imprimir Orçamento
                        </a>
                    </div>
                    @endif
                </div>
                @empty
                <div class="portal-empty-state">
                    <p class="portal-empty-state-title">Nenhuma cobrança encontrada</p>
                    <p class="portal-empty-state-text">
                        Não há cobranças no período selecionado.
                    </p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
