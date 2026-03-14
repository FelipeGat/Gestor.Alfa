<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    <style>
        /* Filtros */
        .filters-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        /* Inputs e Selects */
        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Tabela */
        .tabela-orcamentos thead th {
            color: rgb(17, 24, 39) !important;
            font-size: 14px;
            font-weight: 600;
        }
        .tabela-orcamentos tbody td {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-orcamentos tbody td.font-medium {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-orcamentos tbody td:nth-child(1),
        .tabela-orcamentos tbody td:nth-child(2),
        .tabela-orcamentos tbody td:nth-child(4),
        .tabela-orcamentos tbody td:nth-child(5) {
            font-family: 'Inter', sans-serif !important;
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        /* Paginação */
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
        /* Cards KPI */
        .kpi-card {
            background: white;
            border: 1px solid;
            border-left-width: 4px;
            border-top-width: 1px;
            border-right-width: 1px;
            border-bottom-width: 1px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .kpi-card-blue { border-color: #2563eb; }
        .kpi-card-green { border-color: #16a34a; }
        .kpi-card-yellow { border-color: #eab308; }
        .kpi-card-purple { border-color: #7c3aed; }
        /* Botões filtro rápido */
        .btn-filtro-rapido {
            padding: 0.4rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 500;
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-filtro-rapido.ativo {
            background: #3f9cae;
            color: white;
        }
        .btn-filtro-rapido.inativo {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        .btn-filtro-rapido.inativo:hover { background: #e5e7eb; }
        /* Status badges no modal */
        .modal-status-badge {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600;
            border-radius: 9999px; text-transform: uppercase; white-space: nowrap;
        }
        .badge-em_elaboracao        { background:#f3f4f6; color:#374151; }
        .badge-aguardando_aprovacao { background:#fef3c7; color:#92400e; }
        .badge-aprovado             { background:#dcfce7; color:#166534; }
        .badge-aguardando_pagamento { background:#fef9c3; color:#854d0e; }
        .badge-agendado             { background:#ede9fe; color:#5b21b6; }
        .badge-em_andamento         { background:#e0f2fe; color:#075985; }
        .badge-financeiro           { background:#fee2e2; color:#dc2626; }
        .badge-concluido            { background:#dcfce7; color:#15803d; }
        .badge-recusado             { background:#fee2e2; color:#991b1b; }
        .badge-garantia             { background:#ffedd5; color:#9a3412; }
        .badge-cancelado            { background:#f3f4f6; color:#6b7280; }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Comercial', 'url' => route('comercial.index')],
            ['label' => 'Orçamentos']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Mensagens de erro/sucesso --}}
            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            {{-- ================= FILTROS ================= --}}
            <div x-data="{
                periodo: '{{ request('periodo', '') }}',
                mostrarCustom: {{ request('periodo') === 'intervalo' ? 'true' : 'false' }},
                tipoCliente: '{{ request('tipo_cliente', 'todos') }}',
                aplicarFiltro(tipo) {
                    this.periodo = tipo;
                    if (tipo !== 'intervalo') {
                        this.mostrarCustom = false;
                        const form = this.$refs.formFiltro;
                        const di = form.querySelector('[name=data_inicio]');
                        const df = form.querySelector('[name=data_fim]');
                        if (di) di.disabled = true;
                        if (df) df.disabled = true;
                        setTimeout(() => form.submit(), 10);
                    } else {
                        this.mostrarCustom = true;
                    }
                },
                aplicarTipoCliente(tipo) {
                    this.tipoCliente = tipo;
                    this.$refs.formFiltro.querySelector('[name=tipo_cliente]').value = tipo;
                    setTimeout(() => this.$refs.formFiltro.submit(), 10);
                }
            }" class="space-y-4">

                {{-- Card 1: Busca + Empresa + Status + Botões --}}
                <form method="GET" x-ref="formFiltro" action="{{ route('orcamentos.index') }}" class="filters-card p-6">
                    <input type="hidden" name="periodo" :value="periodo">
                    <input type="hidden" name="tipo_cliente" :value="tipoCliente">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                        {{-- Busca --}}
                        <div class="flex flex-col lg:col-span-12">
                            <label class="text-sm font-medium text-gray-700 mb-2">Buscar</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cliente, Empresa, Status, Nº orçamento, Descrição, Valor..."
                                    class="border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            </div>
                        </div>

                        {{-- Empresa --}}
                        <div class="flex flex-col lg:col-span-4">
                            <label class="text-sm font-medium text-gray-700 mb-2">Empresa</label>
                            <select name="empresa_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                                <option value="">Todas as Empresas</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(request('empresa_id') == $empresa->id)>{{ $empresa->nome_fantasia }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="flex flex-col lg:col-span-4">
                            <label class="text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                                <option value="">Todos os Status</option>
                                @foreach($statusList as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Botões --}}
                        <div class="flex items-end gap-2 lg:col-span-4">
                            <button type="submit" style="padding:.5rem 1rem;font-size:.875rem;width:130px;justify-content:center;background:#3f9cae;border-radius:9999px;display:inline-flex;align-items:center;gap:.5rem;color:white;font-weight:500;border:none;cursor:pointer;box-shadow:0 2px 4px rgba(63,156,174,.3);" onmouseover="this.style.background='#358a96'" onmouseout="this.style.background='#3f9cae'">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/></svg>
                                Filtrar
                            </button>
                            <a href="{{ route('orcamentos.index') }}" style="padding:.5rem 1rem;font-size:.875rem;width:130px;justify-content:center;background:#9ca3af;border-radius:9999px;box-shadow:0 2px 4px rgba(156,163,175,.3);display:inline-flex;align-items:center;gap:.5rem;color:white;font-weight:500;text-decoration:none;" onmouseover="this.style.background='#6b7280'" onmouseout="this.style.background='#9ca3af'">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                Limpar
                            </a>
                        </div>

                        {{-- Datas customizadas --}}
                        <div x-show="mostrarCustom" x-cloak x-transition class="lg:col-span-12 flex flex-wrap items-end gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                                <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                            </div>
                            <button type="submit" style="padding:.5rem 1.25rem;background:#6366f1;color:white;border:none;border-radius:.375rem;font-size:.875rem;font-weight:500;cursor:pointer;">Aplicar</button>
                        </div>

                    </div>
                </form>

                {{-- Card 2: Período + Origem --}}
                <div class="filters-card p-5">
                    <div class="flex flex-wrap w-full justify-between items-center gap-3">
                        <div class="flex gap-2 items-center flex-wrap">
                            <span class="text-gray-700 font-semibold text-sm">Filtrar por período:</span>
                            <button type="button" @click="aplicarFiltro('mes_anterior')" :class="periodo === 'mes_anterior' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">Mês Anterior</button>
                            <button type="button" @click="aplicarFiltro('dia')"          :class="periodo === 'dia'          ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">Dia</button>
                            <button type="button" @click="aplicarFiltro('semana')"       :class="periodo === 'semana'       ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">Semana</button>
                            <button type="button" @click="aplicarFiltro('mes')"          :class="periodo === 'mes'          ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">Mês</button>
                            <button type="button" @click="aplicarFiltro('ano')"          :class="periodo === 'ano'          ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">Ano</button>
                            <button type="button" @click="aplicarFiltro('intervalo')"    :class="periodo === 'intervalo'    ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">Outro período</button>
                        </div>
                        <div class="flex gap-2 items-center">
                            <button type="button" @click="aplicarTipoCliente('todos')"        :class="tipoCliente === 'todos'        ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">TODOS</button>
                            <button type="button" @click="aplicarTipoCliente('clientes')"     :class="tipoCliente === 'clientes'     ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">CLIENTES</button>
                            <button type="button" @click="aplicarTipoCliente('pre_clientes')" :class="tipoCliente === 'pre_clientes' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'" class="btn-filtro-rapido">PRÉ-CLIENTES</button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ================= RESUMO ================= --}}
            @php
            $totalOrcamentos = (int) ($resumo->total_orcamentos ?? $orcamentos->total());
            $aprovados = (int) ($resumo->aprovados ?? 0);
            $pendentes = (int) ($resumo->pendentes ?? 0);
            $valorTotal = (float) ($resumo->valor_total ?? 0);
            @endphp

            <style>
            @media (min-width: 1024px) {
                .resumo-grid {
                    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                }
            }
            </style>

            <div class="resumo-grid gap-4 mb-6" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));">
                <div class="kpi-card kpi-card-blue w-full max-w-none p-6 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105"
                    onclick="abrirModalResumo('total', 'Total de Orçamentos')">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Orçamentos</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalOrcamentos }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>

                <div class="kpi-card kpi-card-green w-full max-w-none p-6 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105"
                    onclick="abrirModalResumo('aprovados', 'Aprovados')">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Aprovados</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $aprovados }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>

                <div class="kpi-card kpi-card-yellow w-full max-w-none p-6 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105"
                    onclick="abrirModalResumo('pendentes', 'Pendentes')">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Pendentes</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $pendentes }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>

                <div class="kpi-card kpi-card-purple w-full max-w-none p-6 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105"
                    onclick="abrirModalResumo('valor_total', 'Valor Total')">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Valor Total</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">R$ {{ number_format($valorTotal, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>
            </div>

            {{-- ================= ATENDIMENTOS AGUARDANDO ORÇAMENTO ================= --}}
            @if(isset($atendimentosParaOrcamento) && $atendimentosParaOrcamento->count())
            <div class="section-card mb-6">
                <div class="card-header">
                    <h3 class="font-bold text-gray-800">Atendimentos aguardando orçamento</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        @if($errors->has('delete'))
                        <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                            {{ $errors->first('delete') }}
                        </div>
                        @endif
                        <thead>
                            <tr>
                                <th>Nº Atendimento</th>
                                <th>Cliente</th>
                                <th>Empresa</th>
                                <th>Data</th>
                                <th class="text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atendimentosParaOrcamento as $atendimento)
                            <tr>
                                <td class="font-mono font-bold text-blue-600">#{{ $atendimento->numero_atendimento }}</td>
                                <td>
                                    @if($atendimento->cliente)
                                        {{ $atendimento->cliente->nome }}
                                    @elseif(!empty($atendimento->nome_solicitante))
                                        {{ $atendimento->nome_solicitante }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $atendimento->empresa?->nome_fantasia ?? '—' }}</td>
                                <td>{{ $atendimento->created_at->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('orcamentos.create', ['atendimento_id' => $atendimento->id]) }}" class="btn btn-pre btn-sm">
                                            Criar Orçamento
                                        </a>
                                        <a href="{{ route('atendimentos.edit', $atendimento->id) }}" target="_blank" class="btn btn-sm btn-secondary" title="Imprimir Atendimento">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ================= LISTA DE ORÇAMENTOS ================= --}}
            @if($orcamentos->count() > 0)
            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <a href="{{ route('orcamentos.create') }}" class="btn btn-success inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @php
            function sortLink($label, $column) {
            $direction = request('direction') === 'asc' ? 'desc' : 'asc';

            return '<a href="' . request()->fullUrlWithQuery([
                        'sort' => $column,
                        'direction' => $direction
                    ]) . '" class="flex items-center gap-1 hover:text-blue-600">'
                . $label .
                (request('sort') === $column
                ? (request('direction') === 'asc' ? ' ▲' : ' ▼')
                : '') .
                '</a>';
            }
            @endphp
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto tabela-orcamentos">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Nº', 'numero_orcamento') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Cliente', 'nome_cliente') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Status', 'status') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Valor Total', 'valor_total') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Data', 'created_at') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($orcamentos as $orcamento)
                            <tr class="hover:bg-gray-50 transition" data-orcamento-id="{{ $orcamento->id }}" data-status-url="{{ route('orcamentos.updateStatus', $orcamento) }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $orcamento->numero_orcamento }}</span>
                                    @if($orcamento->empresa?->nome_fantasia)
                                    <span class="block text-[11px] text-gray-400">{{ $orcamento->empresa->nome_fantasia }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex flex-col gap-0.5 min-w-0">
                                        <span class="{{ $orcamento->pre_cliente_id ? 'font-bold' : 'font-medium' }} text-gray-900">{{ $orcamento->nome_cliente }}</span>
                                        @if($orcamento->descricao)
                                        <span class="text-[11px] text-gray-400 truncate max-w-[220px]" title="{{ $orcamento->descricao }}">{{ $orcamento->descricao }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-left" onclick="event.stopPropagation();">
                                    <form action="{{ route('orcamentos.updateStatus', $orcamento) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="orcamento_status" class="status-select status-{{ $orcamento->status }} text-xs"
                                            style="border: none !important; text-align: center; @switch($orcamento->status)
                                                @case('em_elaboracao') background-color: #f3f4f6; color: #374151; @break
                                                @case('aguardando_aprovacao') background-color: #fef3c7; color: #92400e; @break
                                                @case('aprovado') background-color: #dcfce7; color: #166534; @break
                                                @case('aguardando_pagamento') background-color: #fef9c3; color: #854d0e; @break
                                                @case('agendado') background-color: #ede9fe; color: #5b21b6; @break
                                                @case('em_andamento') background-color: #e0f2fe; color: #075985; @break
                                                @case('financeiro') background-color: #fee2e2; color: #dc2626; @break
                                                @case('concluido') background-color: #dcfce7; color: #15803d; @break
                                                @case('recusado') background-color: #fee2e2; color: #991b1b; @break
                                                @case('garantia') background-color: #ffedd5; color: #9a3412; @break
                                                @case('cancelado') background-color: #f3f4f6; color: #6b7280; @break
                                            @endswitch">
                                            @foreach($statusList as $key => $label)
                                            <option value="{{ $key }}" @selected($orcamento->status === $key)>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    {{ $orcamento->valor_total ? 'R$ ' . number_format($orcamento->valor_total, 2, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $orcamento->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-left">
                                    <div class="flex gap-1 items-center justify-start">
                                        @php
                                            $temAgendamento = $orcamento->atendimento && $orcamento->atendimento->data_inicio_agendamento;
                                            $dataAgendamento = $temAgendamento ? $orcamento->atendimento->data_inicio_agendamento : null;
                                        @endphp
                                        @if($temAgendamento)
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition btn-reprogramar-agendamento"
                                            title="Reprogramar Agendamento ({{ $dataAgendamento->format('d/m/Y') }})"
                                            data-orcamento-id="{{ $orcamento->id }}"
                                            data-orcamento-status="{{ $orcamento->status }}"
                                            data-funcionario-id="{{ $orcamento->atendimento->funcionario_id }}"
                                            data-data-agendamento="{{ $dataAgendamento->format('Y-m-d') }}"
                                            data-periodo="{{ $orcamento->atendimento->periodo_agendamento }}"
                                            data-hora-inicio="{{ $dataAgendamento->format('H:i') }}"
                                            data-duracao="{{ $orcamento->atendimento->duracao_agendamento_minutos ? intdiv($orcamento->atendimento->duracao_agendamento_minutos, 60) : 1 }}"
                                            data-tecnicos-adicionais="{{ $orcamento->atendimento->tecnicosAdicionais->pluck('id')->implode(',') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                        @else
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center w-8 h-8 bg-teal-600 hover:bg-teal-700 text-white rounded-full transition btn-agendar-tecnico"
                                            title="Agendar Técnico"
                                            data-orcamento-id="{{ $orcamento->id }}"
                                            data-orcamento-status="{{ $orcamento->status }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                        @endif
                                        <a href="{{ route('orcamentos.imprimir', $orcamento->id) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-gray-800 hover:bg-gray-900 text-white rounded-full transition" title="Imprimir">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('orcamentos.duplicate', $orcamento) }}" method="POST" onsubmit="return confirm('Deseja duplicar este orçamento?')" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition" title="Duplicar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('orcamentos.edit', $orcamento) }}" class="btn btn-sm btn-edit" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Editar">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('orcamentos.destroy', $orcamento) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este orçamento?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-delete" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Excluir">
                                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= PAGINAÇÃO ================= --}}
            <div class="pagination-container">
                {{ $orcamentos->links() }}
            </div>

            @else
            <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <h3 class="text-lg font-medium text-gray-900">Nenhum orçamento encontrado</h3>
                <a href="{{ route('orcamentos.create') }}" class="btn btn-success mt-4 inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(34, 197, 94, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(34, 197, 94, 0.3)'">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

{{-- ================= MODAL RESUMO CARDS ================= --}}
<div id="modal-resumo-orcamentos" style="display:none; position:fixed; inset:0; background:rgba(107,114,128,.6); z-index:55; overflow-y:auto;">
    <div style="max-width:1100px; margin:3vh auto 3vh; background:#fff; border-radius:0.5rem; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,.2); border:1px solid #3f9cae; border-top-width:4px;">
        <!-- Header -->
        <div style="padding:1rem 1.5rem; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 id="modal-resumo-titulo" style="font-weight:700; font-size:1.1rem; color:#111827;"></h3>
                <p id="modal-resumo-subtitulo" style="font-size:0.8rem; color:#6b7280; margin-top:2px;"></p>
            </div>
            <button onclick="fecharModalResumo()" style="font-size:1.5rem; color:#9ca3af; line-height:1;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#9ca3af'">&times;</button>
        </div>
        <!-- Loading -->
        <div id="modal-resumo-loading" style="padding:3rem; text-align:center; color:#6b7280;">
            <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="#3f9cae" stroke-width="4"></circle>
                <path style="opacity:.75" fill="#3f9cae" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Carregando orçamentos...
        </div>
        <!-- Tabela -->
        <div id="modal-resumo-tabela" style="display:none; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="background:rgba(63,156,174,.05); border-bottom:1px solid #3f9cae;">
                    <tr>
                        <th style="padding:.75rem 1rem; text-align:left; font-size:13px; font-weight:600; color:#111827; text-transform:uppercase;">Número</th>
                        <th style="padding:.75rem 1rem; text-align:left; font-size:13px; font-weight:600; color:#111827; text-transform:uppercase;">Cliente</th>
                        <th style="padding:.75rem 1rem; text-align:left; font-size:13px; font-weight:600; color:#111827; text-transform:uppercase;">Vendedor</th>
                        <th style="padding:.75rem 1rem; text-align:left; font-size:13px; font-weight:600; color:#111827; text-transform:uppercase;">Valor</th>
                        <th style="padding:.75rem 1rem; text-align:left; font-size:13px; font-weight:600; color:#111827; text-transform:uppercase;">Status</th>
                        <th style="padding:.75rem 1rem; text-align:left; font-size:13px; font-weight:600; color:#111827; text-transform:uppercase;">Data</th>
                        <th style="padding:.75rem 1rem; text-align:left; font-size:13px; font-weight:600; color:#111827; text-transform:uppercase;">Ações</th>
                    </tr>
                </thead>
                <tbody id="modal-resumo-tbody" style="border-top:1px solid #e5e7eb;"></tbody>
            </table>
        </div>
        <!-- Vazio -->
        <div id="modal-resumo-vazio" style="display:none; padding:3rem; text-align:center; color:#9ca3af;">
            Nenhum orçamento encontrado para este filtro.
        </div>
        <!-- Footer -->
        <div style="padding:.875rem 1.5rem; border-top:1px solid #e5e7eb; background:#f9fafb; display:flex; justify-content:flex-end;">
            <button onclick="fecharModalResumo()"
                style="padding:.5rem 1.5rem; background:#ef4444; color:#fff; border-radius:9999px; font-size:.875rem; font-weight:500; display:inline-flex; align-items:center; gap:.4rem;"
                onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Fechar
            </button>
        </div>
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
(function() {
    const urlModal = '{{ route('orcamentos.modal') }}';
    // Coleta os filtros ativos da URL atual
    function getFiltrosAtivos() {
        const params = new URLSearchParams(window.location.search);
        const allowed = ['search', 'empresa_id', 'periodo', 'data_inicio', 'data_fim'];
        const out = new URLSearchParams();
        allowed.forEach(k => { if (params.has(k)) out.set(k, params.get(k)); });
        return out;
    }

    window.abrirModalResumo = function(tipo, titulo) {
        const modal   = document.getElementById('modal-resumo-orcamentos');
        const loading = document.getElementById('modal-resumo-loading');
        const tabela  = document.getElementById('modal-resumo-tabela');
        const vazio   = document.getElementById('modal-resumo-vazio');
        const tbody   = document.getElementById('modal-resumo-tbody');

        document.getElementById('modal-resumo-titulo').textContent = titulo;
        document.getElementById('modal-resumo-subtitulo').textContent = '';
        modal.style.display   = 'block';
        loading.style.display = 'block';
        tabela.style.display  = 'none';
        vazio.style.display   = 'none';
        tbody.innerHTML       = '';

        const params = getFiltrosAtivos();
        params.set('tipo', tipo);

        fetch(urlModal + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            if (!data.success || !data.orcamentos.length) {
                vazio.style.display = 'block';
                return;
            }
            document.getElementById('modal-resumo-subtitulo').textContent =
                data.total + ' orçamento(s) | Valor Total: R$ ' +
                data.valor_total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });

            data.orcamentos.forEach(orc => {
                const tr = document.createElement('tr');
                tr.style.cssText = 'border-bottom:1px solid #f3f4f6;';
                tr.onmouseover = () => tr.style.background = '#f9fafb';
                tr.onmouseout  = () => tr.style.background = '';
                tr.innerHTML = `
                    <td style="padding:.7rem 1rem; font-size:.85rem; color:#111827;">
                        <span style="font-weight:600;">${orc.numero}</span>
                        <span style="display:block; font-size:.7rem; color:#9ca3af;">${orc.empresa}</span>
                    </td>
                    <td style="padding:.7rem 1rem; font-size:.85rem; color:#111827;">${orc.cliente}</td>
                    <td style="padding:.7rem 1rem; font-size:.85rem; color:#111827;">${orc.vendedor}</td>
                    <td style="padding:.7rem 1rem; font-size:.85rem; font-weight:600; color:#111827; white-space:nowrap;">R$ ${orc.valor_total}</td>
                    <td style="padding:.7rem 1rem;">
                        <span class="modal-status-badge badge-${orc.status}">${orc.status_label}</span>
                    </td>
                    <td style="padding:.7rem 1rem; font-size:.85rem; color:#6b7280;">${orc.data}</td>
                    <td style="padding:.7rem 1rem;">
                        <a href="${orc.url}" target="_blank"
                            style="display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;background:#1f2937;color:#fff;border-radius:9999px;"
                            title="Imprimir">
                            <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z"/>
                            </svg>
                        </a>
                    </td>`;
                tbody.appendChild(tr);
            });
            tabela.style.display = 'block';
        })
        .catch(() => {
            loading.style.display = 'none';
            vazio.style.display   = 'block';
            document.getElementById('modal-resumo-subtitulo').textContent = 'Erro ao carregar dados.';
        });
    };

    window.fecharModalResumo = function() {
        document.getElementById('modal-resumo-orcamentos').style.display = 'none';
    };

    document.getElementById('modal-resumo-orcamentos').addEventListener('click', function(e) {
        if (e.target === this) fecharModalResumo();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') fecharModalResumo();
    });
})();
</script>

<div id="modal-agendamento-orcamento" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:60;">
    <div style="max-width:920px; margin:3vh auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,.25);">
        <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-weight:700; color:#1f2937;" id="modal-titulo-agendamento">Agendar Técnico</h3>
            <button type="button" id="fechar-modal-agendamento" style="font-size:20px; color:#6b7280;">&times;</button>
        </div>
        <div style="padding:18px; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px;">
            <div>
                <label class="text-sm font-medium text-gray-700">Status</label>
                <input id="agendamento_orcamento_status_label" type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Técnico Principal</label>
                <select id="agendamento_funcionario_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Selecione</option>
                    @foreach($funcionariosTecnicos as $funcionario)
                    <option value="{{ $funcionario->id }}">{{ $funcionario->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Data</label>
                <input id="agendamento_data" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Período</label>
                <select id="agendamento_periodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Selecione</option>
                    <option value="dia_todo">Dia todo (08:00-17:00, 9 horas)</option>
                    <option value="manha">Manhã (08:00-12:00)</option>
                    <option value="tarde">Tarde (13:00-18:00)</option>
                    <option value="noite">Noite (18:01-21:59)</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Hora início</label>
                <input id="agendamento_hora_inicio" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Duração (horas)</label>
                <select id="agendamento_duracao_horas" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Selecione</option>
                    <option value="1">1 hora</option>
                    <option value="2">2 horas</option>
                    <option value="3">3 horas</option>
                    <option value="4">4 horas</option>
                    <option value="5">5 horas</option>
                    <option value="6">6 horas</option>
                    <option value="7">7 horas</option>
                    <option value="8">8 horas</option>
                    <option value="9">9 horas</option>
                </select>
            </div>
        </div>

        {{-- Técnicos Adicionais --}}
        <div style="padding:0 18px 12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <label class="text-sm font-semibold text-gray-700">Técnicos Adicionais</label>
                <button type="button" id="btn-add-tecnico-orc"
                    style="font-size:12px; background:#3f9cae; color:#fff; border:none; border-radius:9999px; padding:3px 14px; cursor:pointer; font-weight:600;">
                    + Adicionar Técnico
                </button>
            </div>
            <div id="lista-tecnicos-adicionais-orc" style="display:flex; flex-direction:column; gap:6px;">
                <p style="font-size:12px; color:#9ca3af; margin:0;">Nenhum técnico adicional adicionado.</p>
            </div>
        </div>
        <div style="padding:0 18px 12px;">
            <div class="text-sm font-semibold text-gray-700 mb-2">Agenda do dia (calendário por técnico)</div>
            <div id="agenda-calendario-orcamento" style="max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:8px; padding:10px;"></div>
        </div>
        <div style="padding:14px 18px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:8px;">
            <button type="button" id="cancelar-agendamento-orcamento" class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancelar</button>
            <button type="button" id="confirmar-agendamento-orcamento" class="px-4 py-2 rounded-lg bg-[#3f9cae] text-white text-sm">Salvar agendamento</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modal-agendamento-orcamento');
        const dataInput = document.getElementById('agendamento_data');
        const periodoInput = document.getElementById('agendamento_periodo');
        const funcionarioInput = document.getElementById('agendamento_funcionario_id');
        const horaInicioInput = document.getElementById('agendamento_hora_inicio');
        const duracaoInput = document.getElementById('agendamento_duracao_horas');
        const statusLabelInput = document.getElementById('agendamento_orcamento_status_label');
        const calendarioWrapper = document.getElementById('agenda-calendario-orcamento');
        const tituloModal = document.getElementById('modal-titulo-agendamento');
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

        let contextoAgendamento = null;

        // ===== TÉCNICOS ADICIONAIS =====
        let tecnicosAdicionaisOrc = [];
        const funcionariosOrcOpts = [
            @foreach($funcionariosTecnicos as $f)
            { id: '{{ $f->id }}', nome: '{{ addslashes($f->nome) }}' },
            @endforeach
        ];

        function renderTecnicosAdicionaisOrc() {
            const container = document.getElementById('lista-tecnicos-adicionais-orc');
            if (!container) return;
            if (tecnicosAdicionaisOrc.length === 0) {
                container.innerHTML = '<p style="font-size:12px;color:#9ca3af;margin:0;">Nenhum técnico adicional adicionado.</p>';
                return;
            }
            container.innerHTML = tecnicosAdicionaisOrc.map((id, i) => {
                const opts = funcionariosOrcOpts
                    .map(f => `<option value="${f.id}" ${String(f.id) === String(id) ? 'selected' : ''}>${f.nome}</option>`)
                    .join('');
                return `<div style="display:flex;gap:6px;align-items:center;">
                    <select onchange="window._orcTecUpdate(${i},this.value)" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm" style="flex:1;font-size:13px;">
                        <option value="">Selecione o técnico</option>${opts}
                    </select>
                    <button type="button" onclick="window._orcTecRemove(${i})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:9999px;width:26px;height:26px;cursor:pointer;font-size:14px;line-height:1;flex-shrink:0;">✕</button>
                </div>`;
            }).join('');
        }

        window._orcTecUpdate = (i, val) => { tecnicosAdicionaisOrc[i] = val; };
        window._orcTecRemove = (i) => { tecnicosAdicionaisOrc.splice(i, 1); renderTecnicosAdicionaisOrc(); };

        document.getElementById('btn-add-tecnico-orc')?.addEventListener('click', function() {
            tecnicosAdicionaisOrc.push('');
            renderTecnicosAdicionaisOrc();
        });

        // Evento para selecionar "Dia todo" automaticamente
        periodoInput.addEventListener('change', function() {
            if (this.value === 'dia_todo') {
                horaInicioInput.value = '08:00';
                duracaoInput.value = '9'; // 08:00-17:00 (9 horas líquidas)
                horaInicioInput.disabled = true;
                duracaoInput.disabled = true;
            } else {
                horaInicioInput.disabled = false;
                duracaoInput.disabled = false;
            }
        });

        function abrirModal({ select = null, orcamentoId = null, statusSelecionado = 'agendado', isReprogramacao = false, dadosAgendamento = null }) {
            contextoAgendamento = {
                select,
                status: statusSelecionado,
                orcamentoId,
                valorAnterior: select?.dataset?.prevValue || '',
                isReprogramacao,
                dadosAgendamento
            };

            statusLabelInput.value = statusSelecionado === 'aprovado' ? 'Aprovado' : 'Agendado';

            if (isReprogramacao && dadosAgendamento) {
                tituloModal.textContent = 'Reprogramar Agendamento';
                funcionarioInput.value = dadosAgendamento.funcionarioId || '';
                dataInput.value = dadosAgendamento.data || new Date().toISOString().slice(0, 10);
                periodoInput.value = dadosAgendamento.periodo || '';
                horaInicioInput.value = dadosAgendamento.horaInicio || '';
                duracaoInput.value = dadosAgendamento.duracao || '1';

                // Carregar técnicos adicionais existentes
                tecnicosAdicionaisOrc = dadosAgendamento.tecnicosAdicionais || [];
                renderTecnicosAdicionaisOrc();

                if (periodoInput.value === 'dia_todo') {
                    horaInicioInput.disabled = true;
                    duracaoInput.disabled = true;
                }
            } else {
                tituloModal.textContent = 'Agendar Técnico';
                dataInput.value = dataInput.value || new Date().toISOString().slice(0, 10);
                funcionarioInput.value = '';
                periodoInput.value = '';
                horaInicioInput.value = '';
                duracaoInput.value = '1';
                tecnicosAdicionaisOrc = [];
                renderTecnicosAdicionaisOrc();
            }

            modal.style.display = 'block';
            carregarAgendaCalendario();
        }

        function fecharModal(restaurarStatus = true) {
            if (restaurarStatus && contextoAgendamento?.select) {
                contextoAgendamento.select.value = contextoAgendamento.valorAnterior;
            }
            modal.style.display = 'none';
            contextoAgendamento = null;
            tecnicosAdicionaisOrc = [];
            renderTecnicosAdicionaisOrc();
        }

        async function carregarAgendaCalendario() {
            const data = dataInput.value;
            const periodo = periodoInput.value;

            if (!data) {
                calendarioWrapper.innerHTML = '<div class="text-sm text-gray-500">Selecione a data para visualizar a agenda.</div>';
                return;
            }

            calendarioWrapper.innerHTML = '<div class="text-sm text-gray-500">Carregando agenda...</div>';

            try {
                const url = `{{ route('agenda-tecnica.disponibilidade') }}?data=${encodeURIComponent(data)}${periodo ? `&periodo=${encodeURIComponent(periodo)}` : ''}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const json = await response.json();

                const blocos = (json.tecnicos || []).map(tecnico => {
                    const agendaTecnico = (json.agendamentos || []).filter(item => String(item.funcionario_id) === String(tecnico.id));
                    const linhas = agendaTecnico.length
                        ? agendaTecnico.map(item => `<div style="font-size:12px;color:#374151;margin-top:3px;">${item.inicio} - ${item.fim} • #${item.numero_atendimento} • ${item.cliente}</div>`).join('')
                        : '<div style="font-size:12px;color:#16a34a;margin-top:3px;">Livre</div>';

                    return `<div style="border-bottom:1px solid #f3f4f6;padding:8px 0;"><div style="font-weight:600;font-size:13px;">${tecnico.nome}</div>${linhas}</div>`;
                });

                calendarioWrapper.innerHTML = blocos.length ? blocos.join('') : '<div class="text-sm text-gray-500">Nenhum técnico encontrado.</div>';
            } catch (error) {
                calendarioWrapper.innerHTML = '<div class="text-sm text-red-600">Não foi possível carregar a agenda.</div>';
            }
        }

        document.querySelectorAll('select[name="orcamento_status"]').forEach(select => {
            select.addEventListener('focus', function() {
                this.dataset.prevValue = this.value;
            });
        });

        document.querySelectorAll('.btn-agendar-tecnico').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const orcamentoId = this.dataset.orcamentoId || row?.getAttribute('data-orcamento-id');
                const statusAtual = this.dataset.orcamentoStatus || 'agendado';
                const statusSelecionado = ['aprovado', 'agendado'].includes(statusAtual) ? statusAtual : 'agendado';

                abrirModal({
                    select: null,
                    orcamentoId,
                    statusSelecionado,
                });
            });
        });

        document.querySelectorAll('.btn-reprogramar-agendamento').forEach(button => {
            button.addEventListener('click', function() {
                const orcamentoId = this.dataset.orcamentoId;
                const statusAtual = this.dataset.orcamentoStatus || 'agendado';

                abrirModal({
                    select: null,
                    orcamentoId,
                    statusSelecionado: statusAtual,
                    isReprogramacao: true,
                    dadosAgendamento: {
                        funcionarioId: this.dataset.funcionarioId,
                        data: this.dataset.dataAgendamento,
                        periodo: this.dataset.periodo,
                        horaInicio: this.dataset.horaInicio,
                        duracao: this.dataset.duracao,
                        tecnicosAdicionais: (this.dataset.tecnicosAdicionais || '').split(',').filter(Boolean)
                    }
                });
            });
        });

        document.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT' && e.target.name === 'orcamento_status') {
                e.preventDefault();
                e.stopPropagation();

                if (['aprovado', 'agendado'].includes(e.target.value)) {
                    const row = e.target.closest('tr');
                    abrirModal({
                        select: e.target,
                        orcamentoId: row?.getAttribute('data-orcamento-id') || null,
                        statusSelecionado: e.target.value,
                    });
                    return;
                }

                // Navegar manualmente para encontrar o formulário
                var element = e.target;
                var form = null;

                // Subir na árvore do DOM até encontrar o FORM
                while (element && element.tagName !== 'BODY') {
                    if (element.tagName === 'FORM') {
                        form = element;
                        break;
                    }
                    element = element.parentElement;
                }

                if (form) {
                    form.submit();
                } else {
                    // Fallback: criar form dinamicamente
                    var status = e.target.value;
                    var row = e.target.closest('tr');
                    var orcamentoId = row ? row.getAttribute('data-orcamento-id') : null;

                    if (!orcamentoId) {
                        console.error('ID do orçamento não encontrado');
                        return;
                    }

                    // Criar form dinamicamente
                    var tempForm = document.createElement('form');
                    tempForm.method = 'POST';
                    tempForm.action = row?.getAttribute('data-status-url') || ('/orcamentos/' + orcamentoId + '/status');

                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    tempForm.appendChild(csrfInput);

                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PATCH';
                    tempForm.appendChild(methodInput);

                    var statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'orcamento_status';
                    statusInput.value = status;
                    tempForm.appendChild(statusInput);

                    document.body.appendChild(tempForm);
                    tempForm.submit();
                }
            }

            if (e.target.id === 'agendamento_data' || e.target.id === 'agendamento_periodo') {
                carregarAgendaCalendario();
            }
        });

        document.getElementById('fechar-modal-agendamento').addEventListener('click', function() {
            fecharModal(true);
        });

        document.getElementById('cancelar-agendamento-orcamento').addEventListener('click', function() {
            fecharModal(true);
        });

        document.getElementById('confirmar-agendamento-orcamento').addEventListener('click', function() {
            if (!contextoAgendamento) {
                return;
            }

            const funcionarioId = funcionarioInput.value;
            const data = dataInput.value;
            const periodo = periodoInput.value;
            const horaInicio = horaInicioInput.value;
            const duracaoHoras = duracaoInput.value;

            if (!funcionarioId || !data || !periodo || !horaInicio || !duracaoHoras) {
                alert('Preencha todos os campos de agendamento.');
                return;
            }

            const row = contextoAgendamento.select ? contextoAgendamento.select.closest('tr') : null;
            const orcamentoId = contextoAgendamento.orcamentoId || (row ? row.getAttribute('data-orcamento-id') : null);

            if (!orcamentoId) {
                alert('Não foi possível identificar o orçamento para agendar.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';

            // Usa endpoint diferente para reprogramação
            if (contextoAgendamento.isReprogramacao) {
                form.action = `{{ url('orcamentos') }}/${orcamentoId}/reprogramar-agendamento`;
            } else {
                form.action = `{{ url('orcamentos') }}/${orcamentoId}/agendar-tecnico`;
            }

            const payload = {
                _token: token,
                funcionario_id: funcionarioId,
                data_agendamento: data,
                periodo_agendamento: periodo,
                hora_inicio: horaInicio,
                duracao_horas: duracaoHoras,
            };

            // Adiciona status apenas para novo agendamento
            if (!contextoAgendamento.isReprogramacao) {
                payload.orcamento_status = contextoAgendamento.status;
            }

            Object.entries(payload).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });

            // Técnicos adicionais (excluindo o técnico principal)
            tecnicosAdicionaisOrc.filter(id => id && id !== funcionarioId).forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'tecnicos_adicionais[]';
                inp.value = id;
                form.appendChild(inp);
            });

            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
