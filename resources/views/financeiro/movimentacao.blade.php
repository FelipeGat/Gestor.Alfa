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
            
            <form method="GET" action="{{ route('financeiro.movimentacao') }}" class="filters-card">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4 mb-2">
                    <div class="filter-group relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" id="busca-geral" value="{{ request('search') }}"
                            placeholder="Descrição, centro de custo, cliente ou fornecedor..."
                            autocomplete="off"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <input type="hidden" name="fornecedor_id" id="busca-fornecedor-id" value="{{ request('fornecedor_id') }}">
                        <div id="autocomplete-fornecedor" class="absolute left-0 right-0 z-10 bg-white border border-gray-200 rounded shadow max-h-40 overflow-y-auto hidden"></div>
                    </div>
                    <div class="filter-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                        <select name="empresa_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">Todas as Empresas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome_fantasia }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo</label>
                        <select name="centro_custo_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">Todos os Centros de Custo</option>
                            @foreach($centrosCusto as $centro)
                            <option value="{{ $centro->id }}" {{ request('centro_custo_id') == $centro->id ? 'selected' : '' }}>
                                {{ $centro->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 mb-4">
                    <div class="filter-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Navegação Rápida</label>
                        <div class="w-full flex justify-center">
                            <div class="flex items-center gap-2 flex-wrap" style="max-width: 700px; width: 100%; justify-content: center;">
                            @php
                                $hoje = \Carbon\Carbon::today();
                                $ontem = \Carbon\Carbon::yesterday();
                            @endphp
                            <a href="{{ route('financeiro.movimentacao', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $ontem->format('Y-m-d'), 'data_fim' => $ontem->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('data_inicio') == $ontem->format('Y-m-d') && request('data_fim') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}"
                                >
                                Ontem
                            </a>
                            <a href="{{ route('financeiro.movimentacao', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $hoje->format('Y-m-d'), 'data_fim' => $hoje->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('data_inicio') == $hoje->format('Y-m-d') && request('data_fim') == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}"
                                >
                                Hoje
                            </a>
                            @php
                            $dataAtual = request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio')) : \Carbon\Carbon::now();
                            $mesAnterior = $dataAtual->copy()->subMonth();
                            $proximoMes = $dataAtual->copy()->addMonth();
                            $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                            $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                            @endphp

                            <a href="{{ route('financeiro.movimentacao', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'), 'data_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>

                            <div class="flex-1 text-center font-bold text-gray-700 bg-white px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                                {{ $mesAtualNome }}
                            </div>

                            <a href="{{ route('financeiro.movimentacao', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'), 'data_fim' => $proximoMes->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            </div>
                        </div>
                        <details class="mt-2" id="periodoPersonalizadoDetails">
                            <summary id="periodoPersonalizadoSummary" class="cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                🗓️ Período Personalizado
                            </summary>
                            <div id="periodoPersonalizadoContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        @push('scripts')
                                        <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const details = document.getElementById('periodoPersonalizadoDetails');
                                            const summary = document.getElementById('periodoPersonalizadoSummary');
                                            if (details && summary) {
                                                summary.addEventListener('click', function (e) {
                                                    // Força abrir ao clicar em qualquer parte do summary
                                                    if (!details.open) {
                                                        e.preventDefault();
                                                        details.open = true;
                                                    }
                                                });
                                            }
                                            // Preencher data final automaticamente ao escolher data inicial
                                            const dataInicio = document.querySelector('input[name="data_inicio"]');
                                            const dataFim = document.querySelector('input[name="data_fim"]');
                                            if (dataInicio && dataFim) {
                                                dataInicio.addEventListener('change', function () {
                                                    if (dataInicio.value) {
                                                        const data = new Date(dataInicio.value);
                                                        data.setDate(data.getDate() + 1);
                                                        const nextDay = data.toISOString().slice(0, 10);
                                                        dataFim.value = nextDay;
                                                    }
                                                });
                                            }
                                        });
                                        </script>
                                        @endpush
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Data Inicial</label>
                                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Data Final</label>
                                    <input type="date" name="data_fim" value="{{ request('data_fim') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                </div>
                            </div>
                        </details>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 items-center justify-between mt-2">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('financeiro.movimentacao', array_merge(request()->except('tipo_movimentacao'), ['tipo_movimentacao' => 'entrada'])) }}"
                            class="quick-filter-btn status-recebido {{ request('tipo_movimentacao') === 'entrada' ? 'active' : '' }}"
                            @if(request('tipo_movimentacao') === 'entrada') style="background:#16a34a;color:#fff;border-color:#16a34a" @endif>
                            <span>Recebidos</span>
                            <span class="count">{{ $contadoresStatus['recebido'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('financeiro.movimentacao', array_merge(request()->except('tipo_movimentacao'), ['tipo_movimentacao' => 'saida'])) }}"
                            class="quick-filter-btn status-pago {{ request('tipo_movimentacao') === 'saida' ? 'active' : '' }}"
                            @if(request('tipo_movimentacao') === 'saida') style="background:#dc2626;color:#fff;border-color:#dc2626" @endif>
                            <span>Pagos</span>
                            <span class="count">{{ $contadoresStatus['pago'] ?? 0 }}</span>
                        </a>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('financeiro.movimentacao') }}" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </form>

            {{-- ================= KPI ================= --}}
            <div class="kpi-grid mb-6">
                <div class="kpi-card border-green">
                    <div class="label">Total de Entradas no Período</div>
                    <div class="value text-green-600">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-red">
                    <div class="label">Total de Saídas no Período</div>
                    <div class="value text-red-600">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-blue">
                    <div class="label">Saldo Líquido do Período</div>
                    <div class="value {{ ($totalEntradas - $totalSaidas) >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        R$ {{ number_format($totalEntradas - $totalSaidas, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- ================= TABELA (EXTRATO) ================= --}}
            <div class="section-card">
                <div class="table-container">
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>TIPO</th>
                                    <th>
                                        <a href="{{ route('financeiro.movimentacao', array_merge(request()->except('order_by', 'order_dir'), [
                                            'order_by' => 'data',
                                            'order_dir' => request('order_dir') === 'asc' ? 'desc' : 'asc'
                                        ])) }}" class="flex items-center gap-1 text-indigo-700 hover:underline">
                                            DATA
                                            @if(request('order_by') === 'data')
                                                @if(request('order_dir') === 'asc')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>DESCRIÇÃO</th>
                                    <th>CLIENTE/FORNECEDOR</th>
                                    <th>VALOR</th>
                                    <th>BANCO ORIGEM</th>
                                    <th>CENTRO DE CUSTO</th>
                                    <th>FORMA PAGTO</th>
                                    <th>AÇÕES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPagina = 0; @endphp

                                @forelse($movimentacoes as $mov)

                                @php
                                    $isEntrada = $mov->tipo_movimentacao === 'entrada';
                                    $totalPagina += $isEntrada ? $mov->valor : -$mov->valor;
                                    $descricaoMov = $mov->descricao ?? '-';
                                    $clienteMov = null;
                                    $contaPagar = null;
                                    $fornecedorMov = null;
                                    $contaMov = null;
                                    $formaPagtoMov = null;
                                    $empresaMov = null;
                                    // Corrige descrição e cliente para movimentações financeiras de recebimento
                                    if ($isEntrada && isset($mov->observacao) && preg_match('/Recebimento de cobrança ID (\d+)/', $mov->observacao, $matches)) {
                                        $cobranca = \App\Models\Cobranca::with(['cliente', 'orcamento'])->find($matches[1]);
                                        if ($cobranca) {
                                            $descricaoMov = $cobranca->descricao ?? ($cobranca->orcamento ? $cobranca->orcamento->descricao : '-');
                                            $clienteMov = $cobranca->cliente;
                                        }
                                    }
                                    // Detecta se é ajuste de saída de conta a pagar
                                    if(isset($mov->observacao) && preg_match('/Pagamento de conta a pagar ID (\d+)/', $mov->observacao, $matches)) {
                                        $contaPagar = \App\Models\ContaPagar::with(['fornecedor', 'conta', 'contaFinanceira', 'orcamento'])->find($matches[1]);
                                        if($contaPagar) {
                                            $descricaoMov = $contaPagar->descricao;
                                            $fornecedorMov = $contaPagar->fornecedor;
                                            $contaMov = $contaPagar->conta;
                                            $formaPagtoMov = $contaPagar->forma_pagamento;
                                            $empresaMov = $contaPagar->orcamento && $contaPagar->orcamento->empresa ? $contaPagar->orcamento->empresa : null;
                                        }
                                    }
                                @endphp
                                <tr class="{{ $isEntrada ? 'bg-green-50' : 'bg-red-50' }}">
                                    {{-- TIPO --}}
                                    <td>
                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded {{ $isEntrada ? 'bg-green-600' : 'bg-red-600' }} text-white">
                                            {{ $isEntrada ? 'ENTRADA' : 'SAÍDA' }}
                                        </span>
                                        @if(isset($mov->tipo))
                                            <div class="text-xs text-gray-700 mt-1">
                                                {{
                                                    [
                                                        'ajuste_entrada' => 'AJUSTE',
                                                        'ajuste_saida' => 'AJUSTE',
                                                        'injeção_receita' => 'INJEÇÃO DE RECEITA',
                                                        'transferencia_entrada' => 'TRANSFERÊNCIA',
                                                        'transferencia_saida' => 'TRANSFERÊNCIA',
                                                    ][$mov->tipo] ?? strtoupper($mov->tipo ?? $mov->tipo_movimentacao)
                                                }}
                                            </div>
                                        @endif
                                    </td>
                                    {{-- DATA --}}
                                    <td>
                                        {{ \Carbon\Carbon::parse($mov->data_movimentacao ?? $mov->pago_em)->format('d/m/Y') }}
                                    </td>
                                    {{-- DESCRIÇÃO --}}
                                    <td>
                                        {{ $descricaoMov ?? '-' }}
                                        @if(isset($mov->observacao) && $mov->observacao)
                                            @php
                                                // Remove exibição da observação se for 'Recebimento de cobrança ID ...'
                                                $obs = $mov->observacao;
                                            @endphp
                                            @if(!preg_match('/^Recebimento de cobrança ID \d+$/', $obs))
                                                <div class="text-xs text-blue-700 font-semibold mt-1">
                                                    {{ $obs }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    {{-- CLIENTE/FORNECEDOR --}}
                                    <td>
                                        @if($isEntrada && $clienteMov)
                                            {{ $clienteMov->nome_fantasia ?? $clienteMov->nome ?? $clienteMov->razao_social ?? '—' }}
                                        @elseif(!$isEntrada && isset($mov->fornecedor))
                                            {{ $mov->fornecedor?->nome_fantasia ?? $mov->fornecedor?->razao_social ?? '—' }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- VALOR --}}
                                    <td class="text-right font-bold {{ $isEntrada ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $isEntrada ? '+' : '-' }} R$ {{ number_format($mov->valor, 2, ',', '.') }}
                                    </td>

                                    {{-- BANCO --}}
                                    {{-- BANCO ORIGEM --}}
                                    <td>
                                        @if($contaPagar && $contaPagar->contaFinanceira)
                                            {{ $contaPagar->contaFinanceira->nome }}
                                        @elseif(property_exists($mov, 'contaOrigem') && $mov->contaOrigem)
                                            {{ $mov->contaOrigem->nome }}
                                        @elseif(isset($mov->contaFinanceira))
                                            {{ $mov->contaFinanceira?->nome ?? '—' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    {{-- CENTRO DE CUSTO --}}
                                    <td>
                                        @if($isEntrada && isset($mov->orcamento) && isset($mov->orcamento->centroCusto))
                                            {{ $mov->orcamento->centroCusto->nome ?? '—' }}
                                        @elseif(!$isEntrada && ($contaPagar && $contaPagar->centroCusto))
                                            {{ $contaPagar->centroCusto->nome ?? '—' }}
                                        @elseif(isset($mov->centroCusto))
                                            {{ $mov->centroCusto->nome ?? '—' }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- FORMA PAGAMENTO --}}
                                    <td>
                                        @if($contaPagar && $formaPagtoMov)
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                                                {{ strtoupper(str_replace('_', ' ', $formaPagtoMov)) }}
                                            </span>
                                        @elseif($mov->forma_pagamento)
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

                                            @if($isEntrada)
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
                                            @else
                                            {{-- Estornar Conta a Pagar --}}
                                            <form method="POST"
                                                action="{{ route('financeiro.contasapagar.estornar', $mov) }}"
                                                onsubmit="return confirm('Deseja estornar este pagamento e devolvê-lo para Contas a Pagar?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    title="Estornar Pagamento">
                                                    ↩️
                                                </button>
                                            </form>
                                            @endif

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