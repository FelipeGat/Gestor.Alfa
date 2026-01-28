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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-2-2m2 2l2-2" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Contas a Receber
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
    </x-slot><br>

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
                        <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('status'), ['status' => ['pendente']])) }}"
                            class="quick-filter-btn status-pendente {{ in_array('pendente', request('status', [])) ? 'active' : '' }}">
                            <span>Pendente</span>
                            <span class="count">{{ $contadoresStatus['pendente'] }}</span>
                        </a>
                        <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('status'), ['status' => ['vencido']])) }}"
                            class="quick-filter-btn status-vencido {{ in_array('vencido', request('status', [])) ? 'active' : '' }}">
                            <span>Vencido</span>
                            <span class="count">{{ $contadoresStatus['vencido'] }}</span>
                        </a>
                    </div>

                    {{-- Grupo de Botões (Direita) --}}
                    <div class="filters-actions">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('financeiro.contasareceber') }}" class="btn btn-secondary">Limpar</a>
                    </div>

                </div>
            </form>


            @if(request('vencimento_inicio') || request('vencimento_fim'))
            <div class="mb-4 px-4 py-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
                Conciliação ativa no período:
                <strong>
                    {{ request('vencimento_inicio') ? \Carbon\Carbon::parse(request('vencimento_inicio'))->format('d/m/Y') : 'início' }}
                    —
                    {{ request('vencimento_fim') ? \Carbon\Carbon::parse(request('vencimento_fim'))->format('d/m/Y') : 'hoje' }}
                </strong>
            </div>
            @endif

            {{-- ================= KPIs ================= --}}
            <div class="kpi-grid mb-6">
                <div class="kpi-card border-blue">
                    <div class="label">A Receber (no período)</div>
                    <div class="value">R$ {{ number_format($kpis['a_receber'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-green">
                    <div class="label">Recebido (no período)</div>
                    <div class="value">R$ {{ number_format($kpis['recebido'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-red">
                    <div class="label">Vencido (no período)</div>
                    <div class="value">R$ {{ number_format($kpis['vencido'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-yellow">
                    <div class="label">Vence Hoje</div>
                    <div class="value">R$ {{ number_format($kpis['vence_hoje'], 2, ',', '.') }}</div>
                </div>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($cobrancas->count())
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vencimento</th>
                                <th>Cliente</th>
                                <th>Descrição</th>
                                <th class="text-right">Valor</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalPagina = 0; @endphp
                            @forelse($cobrancas as $cobranca)
                            @php
                            $totalPagina += $cobranca->valor;
                            $statusClass = $cobranca->status_financeiro;
                            @endphp
                            @php
                            $venceHoje = $cobranca->status !== 'pago'
                            && $cobranca->data_vencimento->isToday();
                            @endphp
                            @if($venceHoje)
                            @php $statusClass = 'vence-hoje'; @endphp
                            @endif
                            <tr class="{{ $venceHoje ? 'bg-yellow-50 border-l-4 border-yellow-400' : '' }}">
                                <td data-label="Vencimento">
                                    <span class="status-indicator {{ $statusClass }}"></span>
                                    {{ $cobranca->data_vencimento->format('d/m/Y') }}
                                </td>
                                <td data-label="Cliente">
                                    {{ $cobranca->cliente?->nome ?? $cobranca->cliente?->nome_fantasia ?? $cobranca->cliente?->razao_social ?? '—' }}
                                </td>
                                <td data-label="Descrição">
                                    {{ $cobranca->descricao }}
                                    <small class="block text-gray-400">{{ $cobranca->orcamento_id ? 'Origem: Orçamento' : 'Origem: Contrato' }}</small>
                                </td>
                                <td data-label="Valor" class="text-right font-semibold">
                                    R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                                </td>
                                <td data-label="Ações">
                                    <div class="table-actions">
                                        @if($cobranca->status !== 'pago')
                                        <form method="POST" action="{{ route('financeiro.contasareceber.pagar', $cobranca) }}">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                        <form method="POST" action="{{ route('financeiro.contasareceber.pagar', $cobranca) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="button"
                                                x-data
                                                class="btn action-btn btn-success"
                                                x-on:click="$dispatch('confirmar-baixa', {
        action: '{{ route('financeiro.contasareceber.pagar', $cobranca) }}',
        empresaId: {{ $cobranca->orcamento?->empresa_id ?? 'null' }}
    })">
                                                Confirmar Baixa
                                            </button>
                                        </form>
                                        @endif
                                        {{-- <a href="{{ route('cobrancas.edit', $cobranca) }}" class="btn action-btn btn-icon" title="Editar">...</a> --}}
                                        <form method="POST" action="{{ route('financeiro.contasareceber.destroy', $cobranca) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta cobrança?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn action-btn btn-icon" title="Excluir">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-gray-500">
                                    Nenhuma cobrança encontrada para os filtros aplicados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Rodapé com Totais --}}
                <div class="table-footer">
                    <div class="footer-total">
                        <span class="label">Total na Página:</span>
                        <span class="value">R$ {{ number_format($totalPagina, 2, ',', '.' ) }}</span>
                    </div>
                    <div class="footer-total">
                        <span class="label">Total Geral (Filtrado):</span>
                        <span class="value">R$ {{ number_format($totalGeralFiltrado, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{ $cobrancas->links() }}
            @else
            <div class="empty-state">
                <h3 class="empty-state-title">Nenhuma cobrança encontrada</h3>
            </div>
            @endif

        </div>
    </div>
    @include('financeiro.partials.modal-confirmar-baixa')

</x-app-layout>