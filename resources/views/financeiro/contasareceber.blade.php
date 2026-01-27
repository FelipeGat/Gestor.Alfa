<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/contasareceber.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🧾 Contas a Receber
        </h2>
    </x-slot><br>

    <div class="page-container">
        {{-- ================= FILTROS ================= --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('financeiro.contasareceber') }}">
                <div class="filter-grid">
                    {{-- Busca --}}
                    <div class="lg:col-span-4">
                        <label for="search" class="filter-label">Buscar por Cliente ou Descrição</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Ex: Invest, Manutenção..." class="filter-input">
                    </div>
                    {{-- Vencimento Início --}}
                    <div class="lg:col-span-3">
                        <label for="vencimento_inicio" class="filter-label">Vencimento (Início)</label>
                        <input type="date" id="vencimento_inicio" name="vencimento_inicio" value="{{ request('vencimento_inicio') }}" class="filter-input">
                    </div>
                    {{-- Vencimento Fim --}}
                    <div class="lg:col-span-3">
                        <label for="vencimento_fim" class="filter-label">Vencimento (Fim)</label>
                        <input type="date" id="vencimento_fim" name="vencimento_fim" value="{{ request('vencimento_fim') }}" class="filter-input">
                    </div>
                    {{-- Ações --}}
                    <div class="lg:col-span-2 filter-actions">
                        <button type="submit" class="btn btn-primary w-full">Filtrar</button>
                        <a href="{{ route('financeiro.contasareceber') }}" class="btn btn-secondary w-full">Limpar</a>
                    </div>
                </div>
            </form>

            {{-- Filtros Rápidos de Status --}}
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
        </div>

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
        <div class="table-container">
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
                                {{ $cobranca->cliente?->nome_fantasia ?? '_' }}
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
        action: '{{ route('financeiro.contasareceber.pagar', $cobranca) }}'
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

        {{-- Paginação --}}
        <div class="mt-6 px-4">
            {{ $cobrancas->links() }}
        </div>
    </div>
    @include('financeiro.partials.modal-confirmar-baixa')

</x-app-layout>