<x-app-layout>

    @push('styles')
    @vite('resources/css/financeiro/contasareceber.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Extrato']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            @php
                $hoje = \Carbon\Carbon::today();
                $ontem = \Carbon\Carbon::yesterday();
                $amanha = \Carbon\Carbon::tomorrow();
                $dataAtual = request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio')) : \Carbon\Carbon::now();
                $mesAnterior = $dataAtual->copy()->subMonth();
                $proximoMes = $dataAtual->copy()->addMonth();
                $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                $dataInicio = request('data_inicio') ?? $dataAtual->copy()->startOfMonth()->format('Y-m-d');
                $dataFim = request('data_fim') ?? $dataAtual->copy()->endOfMonth()->format('Y-m-d');
            @endphp

            <form method="GET" action="{{ route('financeiro.movimentacao') }}"
                class="bg-white rounded-lg p-6"
                style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                {{-- Linha 1: Buscar / Empresa / Centro de Custo --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-4 relative">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Buscar</label>
                        <input type="text" name="search" id="busca-geral" value="{{ request('search') }}"
                            placeholder="Descrição, cliente, fornecedor ou valor..."
                            autocomplete="off"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <input type="hidden" name="fornecedor_id" id="busca-fornecedor-id" value="{{ request('fornecedor_id') }}">
                        <div id="autocomplete-fornecedor" class="absolute left-0 right-0 z-10 bg-white border border-gray-200 rounded shadow max-h-40 overflow-y-auto hidden"></div>
                    </div>

                    <div class="md:col-span-4">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Empresa</label>
                        <select name="empresa_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todas as Empresas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome_fantasia }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Centro de Custo</label>
                        <select name="centro_custo_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todos</option>
                            @foreach($centrosCusto as $centro)
                            <option value="{{ $centro->id }}" {{ request('centro_custo_id') == $centro->id ? 'selected' : '' }}>
                                {{ $centro->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Linha 2: Navegação --}}
                <div class="mb-4">
                    <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Navegação</label>
                    <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('financeiro.movimentacao', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $ontem->format('Y-m-d'), 'data_fim' => $ontem->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('data_inicio') == $ontem->format('Y-m-d') && request('data_fim') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Ontem
                            </a>
                            <a href="{{ route('financeiro.movimentacao', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $hoje->format('Y-m-d'), 'data_fim' => $hoje->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('data_inicio') == $hoje->format('Y-m-d') && request('data_fim') == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Hoje
                            </a>
                            <a href="{{ route('financeiro.movimentacao', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $amanha->format('Y-m-d'), 'data_fim' => $amanha->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('data_inicio') == $amanha->format('Y-m-d') && request('data_fim') == $amanha->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Amanhã
                            </a>
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

                <details class="mt-4">
                    <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                        Período Personalizado
                    </summary>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div>
                            <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Data Inicial</label>
                            <input type="date" name="data_inicio" id="data_inicio_movimentacao" value="{{ $dataInicio }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        <div>
                            <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Data Final</label>
                            <input type="date" name="data_fim" id="data_fim_movimentacao" value="{{ $dataFim }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>
                </details>

                <div class="flex flex-wrap gap-2 mt-4">
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

                <div class="flex justify-start gap-2 mt-4">
                    <x-button type="submit" variant="primary" size="sm" class="min-w-[100px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Filtrar
                    </x-button>
                    <x-button href="{{ route('financeiro.movimentacao') }}" variant="secondary" size="sm" class="min-w-[100px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Limpar
                    </x-button>
                </div>
            </form>

            {{-- ================= KPIs ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <x-kpi-card title="Total de Entradas no Período" :value="'R$ ' . number_format($totalEntradas, 2, ',', '.')" color="green" />
                <x-kpi-card title="Total de Saídas no Período" :value="'R$ ' . number_format($totalSaidas, 2, ',', '.')" color="red" />
                <x-kpi-card title="Saldo Líquido do Período" :value="'R$ ' . number_format($totalEntradas - $totalSaidas, 2, ',', '.')" color="{{ ($totalEntradas - $totalSaidas) >= 0 ? 'blue' : 'red' }}" />
            </div>

            {{-- ================= TABELA ================= --}}
            @if($movimentacoes->count())
            <div class="table-card">
                <div class="rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead style="background-color: rgba(63, 156, 174, 0.05);">
                                <tr>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Tipo</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">
                                        <a href="{{ route('financeiro.movimentacao', array_merge(request()->except('order_by', 'order_dir'), ['order_by' => 'data', 'order_dir' => request('order_dir') === 'asc' ? 'desc' : 'asc'])) }}"
                                            class="inline-flex items-center gap-1 hover:text-[#3f9cae] transition">
                                            Data
                                            @if(request('order_by') === 'data')
                                                @if(request('order_dir') === 'asc')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Descrição</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Cliente/Fornecedor</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">
                                        <a href="{{ route('financeiro.movimentacao', array_merge(request()->except('order_by', 'order_dir'), ['order_by' => 'valor', 'order_dir' => request('order_by') === 'valor' && request('order_dir') === 'asc' ? 'desc' : 'asc'])) }}"
                                            class="inline-flex items-center gap-1 hover:text-[#3f9cae] transition">
                                            Valor
                                            @if(request('order_by') === 'valor')
                                                @if(request('order_dir') === 'asc')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Centro de Custo</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Forma Pagto</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Usuário</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php $totalPagina = 0; @endphp

                                @foreach($movimentacoes as $mov)
                                @php
                                    $isEntrada = $mov->tipo_movimentacao === 'entrada';
                                    $totalPagina += $isEntrada ? $mov->valor : -$mov->valor;
                                    $linhaClass = $isEntrada ? 'bg-green-50 border-l-4 border-green-400' : 'bg-red-50 border-l-4 border-red-400';
                                    $descricaoMov = $mov->descricao ?? '-';
                                    $clienteMov = null;
                                    $contaPagar = null;
                                    $fornecedorMov = null;
                                    $contaMov = null;
                                    $formaPagtoMov = null;
                                    if ($isEntrada && isset($mov->is_financeiro) && !$mov->is_financeiro) {
                                        $clienteMov = $mov->cliente ?? null;
                                    }
                                    if ($isEntrada && isset($mov->observacao) && preg_match('/Recebimento de cobrança ID (\d+)/', $mov->observacao, $matches)) {
                                        $cobranca = \App\Models\Cobranca::with(['cliente', 'orcamento'])->find($matches[1]);
                                        if ($cobranca) {
                                            $descricaoMov = $cobranca->descricao ?? ($cobranca->orcamento ? $cobranca->orcamento->descricao : '-');
                                            $clienteMov = $cobranca->cliente;
                                        }
                                    }
                                    if (isset($mov->observacao) && preg_match('/Pagamento de conta a pagar ID (\d+)/', $mov->observacao, $matches)) {
                                        $contaPagar = \App\Models\ContaPagar::with(['fornecedor', 'conta', 'contaFinanceira', 'orcamento'])->find($matches[1]);
                                        if ($contaPagar) {
                                            $descricaoMov = $contaPagar->descricao;
                                            $fornecedorMov = $contaPagar->fornecedor;
                                            $contaMov = $contaPagar->conta;
                                            $formaPagtoMov = $contaPagar->forma_pagamento;
                                        }
                                    }
                                @endphp
                                <tr class="{{ $linhaClass }} hover:bg-gray-50 transition">

                                    {{-- TIPO --}}
                                    <td class="px-4 py-3" data-label="Tipo">
                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded {{ $isEntrada ? 'bg-green-600' : 'bg-red-600' }} text-white whitespace-nowrap">
                                            {{ $isEntrada ? 'ENTRADA' : 'SAÍDA' }}
                                        </span>
                                        @if(isset($mov->tipo))
                                        <div class="text-xs text-gray-500 mt-1 whitespace-nowrap">
                                            {{ [
                                                'ajuste_entrada'        => 'Ajuste',
                                                'ajuste_saida'          => 'Ajuste',
                                                'injeção_receita'       => 'Injeção de Receita',
                                                'transferencia_entrada' => 'Transferência',
                                                'transferencia_saida'   => 'Transferência',
                                            ][$mov->tipo] ?? ucfirst(str_replace('_', ' ', $mov->tipo ?? $mov->tipo_movimentacao)) }}
                                        </div>
                                        @endif
                                    </td>

                                    {{-- DATA --}}
                                    <td class="px-4 py-3 text-sm whitespace-nowrap" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Data">
                                        {{ \Carbon\Carbon::parse($mov->data_movimentacao ?? $mov->pago_em)->format('d/m') }}
                                    </td>

                                    {{-- DESCRIÇÃO --}}
                                    <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Descrição">
                                        @php
                                            $obsExtra = isset($mov->observacao) && $mov->observacao
                                                && !preg_match('/^Recebimento de cobrança ID \d+$/', $mov->observacao)
                                                && !preg_match('/^Pagamento de conta a pagar ID \d+$/', $mov->observacao)
                                                ? $mov->observacao : null;
                                            $descFull = $descricaoMov . ($obsExtra ? ' · ' . $obsExtra : '');
                                        @endphp
                                        <span class="block truncate max-w-[180px]" title="{{ $descFull }}">{{ $descricaoMov }}</span>
                                        @if($obsExtra)
                                        <span class="block truncate max-w-[180px] text-xs text-blue-600" title="{{ $obsExtra }}">{{ $obsExtra }}</span>
                                        @endif
                                    </td>

                                    {{-- CLIENTE/FORNECEDOR --}}
                                    <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Cliente/Fornecedor">
                                        @php
                                            $nomeParticipante = '—';
                                            if ($isEntrada && $clienteMov) {
                                                $nomeParticipante = $clienteMov->nome_fantasia ?? $clienteMov->nome ?? $clienteMov->razao_social ?? '—';
                                            } elseif (!$isEntrada && $fornecedorMov) {
                                                $nomeParticipante = $fornecedorMov->razao_social ?? $fornecedorMov->nome_fantasia ?? '—';
                                            } elseif (!$isEntrada && isset($mov->fornecedor)) {
                                                $nomeParticipante = $mov->fornecedor?->razao_social ?? $mov->fornecedor?->nome_fantasia ?? '—';
                                            }
                                        @endphp
                                        <span class="block truncate max-w-[160px]" title="{{ $nomeParticipante }}">{{ $nomeParticipante }}</span>
                                    </td>

                                    {{-- VALOR --}}
                                    <td class="px-4 py-3 text-sm whitespace-nowrap font-semibold {{ $isEntrada ? 'text-green-600' : 'text-red-600' }}" data-label="Valor">
                                        {{ $isEntrada ? '+' : '-' }} R$ {{ number_format($mov->valor, 2, ',', '.') }}
                                    </td>

                                    {{-- CENTRO DE CUSTO --}}
                                    <td class="px-4 py-3 text-sm whitespace-nowrap" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Centro de Custo">
                                        @if($isEntrada)
                                            {{ $mov->contaDestino?->nome ?? '—' }}
                                        @elseif($contaPagar && $contaPagar->centroCusto)
                                            {{ $contaPagar->centroCusto->nome }}
                                        @elseif(isset($mov->centroCusto))
                                            {{ $mov->centroCusto->nome ?? '—' }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- FORMA PAGAMENTO --}}
                                    <td class="px-4 py-3 text-sm" data-label="Forma Pagto">
                                        @php
                                            $forma = $formaPagtoMov ?? $mov->forma_pagamento ?? ($mov->contaDestino?->tipo ?? null);
                                        @endphp
                                        @if($forma)
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800 whitespace-nowrap">
                                                {{ strtoupper(str_replace('_', ' ', $forma)) }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- USUÁRIO --}}
                                    <td class="px-4 py-3 text-sm whitespace-nowrap" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Usuário">
                                        @php
                                            $usuario = $mov->usuario ?? $mov->user ?? $mov->cobranca?->usuario ?? $mov->contaPagar?->usuario ?? null;
                                            if (!$usuario && isset($mov->user_id) && $mov->user_id) {
                                                $usuario = \App\Models\User::find($mov->user_id);
                                            }
                                            $nomeCompleto = $usuario?->name ?? $usuario?->nome ?? '—';
                                            if ($nomeCompleto !== '—') {
                                                $partes = array_filter(explode(' ', trim($nomeCompleto)));
                                                $nomeCompleto = count($partes) > 1
                                                    ? reset($partes) . ' ' . end($partes)
                                                    : reset($partes);
                                            }
                                        @endphp
                                        {{ $nomeCompleto }}
                                    </td>

                                    {{-- AÇÕES --}}
                                    <td class="px-4 py-3" data-label="Ações">
                                        <div class="flex gap-1 justify-end">
                                            @if($isEntrada)
                                                @if(!($mov->is_financeiro ?? false) && isset($mov->cobranca))
                                                <a href="{{ route('financeiro.cobrancas.recibo', $mov->cobranca) }}"
                                                    target="_blank"
                                                    class="p-2 rounded-full inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition"
                                                    title="Imprimir Comprovante">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                </a>
                                                @endif
                                                @php
                                                    $cobrancaId = null;
                                                    if (!($mov->is_financeiro ?? false) && isset($mov->cobranca)) {
                                                        $cobrancaId = $mov->cobranca->id;
                                                    } elseif (isset($mov->observacao) && preg_match('/Recebimento de cobrança ID (\d+)/', $mov->observacao, $matches)) {
                                                        $cobrancaId = $matches[1];
                                                    }
                                                @endphp
                                                @if($cobrancaId)
                                                <form method="POST"
                                                    action="{{ route('financeiro.movimentacao.estornar', $cobrancaId) }}"
                                                    onsubmit="return confirm('Deseja estornar esta cobrança e devolvê-la para Contas a Receber?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="p-2 rounded-full inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition"
                                                        title="Estornar Pagamento">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                @else
                                                    <span class="text-xs text-gray-400 px-2">Não estornável</span>
                                                @endif
                                            @else
                                                <form method="POST"
                                                    action="{{ route('financeiro.contasapagar.estornar', $mov) }}"
                                                    onsubmit="return confirm('Deseja estornar este pagamento e devolvê-lo para Contas a Pagar?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="p-2 rounded-full inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition"
                                                        title="Estornar Pagamento">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Rodapé com Totais --}}
                <x-card class="mt-4 mb-6">
                    <div class="p-4 flex justify-end">
                        <div class="text-right space-y-2">
                            <div>
                                <span class="text-sm text-gray-600 uppercase tracking-wide">Total da Página:</span>
                                <span class="ml-2 text-xl font-bold {{ $totalPagina >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                                    R$ {{ number_format($totalPagina, 2, ',', '.') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600 uppercase tracking-wide">Entradas / Saídas (período):</span>
                                <span class="ml-2 text-base font-semibold text-green-600">+R$ {{ number_format($totalEntradas, 2, ',', '.') }}</span>
                                <span class="mx-1 text-gray-400">/</span>
                                <span class="text-base font-semibold text-red-600">-R$ {{ number_format($totalSaidas, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <x-pagination :paginator="$movimentacoes" label="movimentações" />
            @else
            <x-card :padding="false" class="text-center py-12">
                <p class="text-gray-500">Nenhuma movimentação encontrada para os filtros aplicados.</p>
            </x-card>
            @endif

        </div>
    </div>

</x-app-layout>
