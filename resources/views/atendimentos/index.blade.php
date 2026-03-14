<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @vite('resources/css/financeiro/contasareceber.css')
    @vite('resources/css/financeiro/index.css')
    <style>
        /* ===== FILTRO CARDS ===== */
        .filtro-card {
            background: #fff;
            padding: 1.25rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            border-top: 4px solid #3f9cae;
            box-shadow: 0 2px 8px 0 rgba(0,0,0,0.06);
            margin-bottom: 1rem;
        }
        .btn-filtro-rapido {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 0.9rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .btn-filtro-rapido.ativo { background: #3f9cae; color: #fff; }
        .btn-filtro-rapido.inativo { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
        .btn-prio-alta.ativo  { background: #dc2626 !important; color: #fff !important; }
        .btn-prio-alta.inativo  { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
        .btn-prio-media.ativo { background: #f59e0b !important; color: #fff !important; }
        .btn-prio-media.inativo { background: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
        .btn-prio-baixa.ativo { background: #16a34a !important; color: #fff !important; }
        .btn-prio-baixa.inativo { background: #dcfce7; color: #16a34a; border: 1px solid #86efac; }
        .btn-triagem.ativo  { background: #f59e0b !important; color: #fff !important; }
        .btn-triagem.inativo { background: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }

        /* ===== KPI CARDS ===== */
        .kpi-cards-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 1024px) { .kpi-cards-grid { grid-template-columns: repeat(2, 1fr); } }
        .kpi-card-at {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-left: 4px solid;
            border-radius: 0.5rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .kpi-card-at:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); transform: translateY(-2px); }
        .kpi-card-at-teal   { border-left-color: #3f9cae; }
        .kpi-card-at-amber  { border-left-color: #f59e0b; }
        .kpi-card-at-indigo { border-left-color: #6366f1; }
        .kpi-card-at-red    { border-left-color: #ef4444; }
        .kpi-card-at-green  { border-left-color: #16a34a; }

        /* ===== SLA BADGES ===== */
        .sla-badge {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 7px; border-radius: 9999px;
            font-size: 0.68rem; font-weight: 700; white-space: nowrap;
        }
        .sla-verde    { background: #dcfce7; color: #16a34a; }
        .sla-amarelo  { background: #fef9c3; color: #b45309; }
        .sla-vermelho { background: #fee2e2; color: #dc2626; }
        .sla-cinza    { background: #f3f4f6; color: #6b7280; }

        /* ===== QUICK ACTIONS ===== */
        .btn-acao {
            display: inline-flex; align-items: center; justify-content: center;
            width: 24px; height: 24px; border-radius: 9999px; border: none;
            cursor: pointer; font-size: 0.6rem; font-weight: 700;
            transition: all 0.15s; title: attr(title);
        }
        .btn-acao-iniciar  { background: #dcfce7; color: #16a34a; }
        .btn-acao-iniciar:hover  { background: #16a34a; color: #fff; }
        .btn-acao-concluir { background: #dbeafe; color: #2563eb; }
        .btn-acao-concluir:hover { background: #2563eb; color: #fff; }
        .btn-acao-aguardar { background: #fef3c7; color: #d97706; }
        .btn-acao-aguardar:hover { background: #d97706; color: #fff; }

        /* ===== PREVIEW ROW ===== */
        .preview-row td { background: #f8fafc !important; }
        .preview-row .preview-content {
            padding: 0.75rem 1rem;
            display: grid; grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem; font-size: 0.8rem;
        }
        .preview-label { color: #6b7280; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
        .preview-value { color: #1f2937; margin-top: 2px; }

        /* ===== x-cloak ===== */
        [x-cloak] { display: none !important; }

        /* ===== TABELA ===== */
        .table-tight th, .table-tight td {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            white-space: nowrap;
        }
        .truncate-text { max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .table-actions { display: inline-flex; align-items: center; gap: 0.3rem; white-space: nowrap; }
        .table-wrapper { overflow-x: auto; padding-right: 0.75rem; padding-bottom: 0.25rem; }
        .table-tight th:last-child, .table-tight td:last-child { padding-right: 1.25rem !important; }
    </style>
    @endpush

    {{-- ================= HEADER ================= --}}
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Atendimentos']
        ]" />
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= FILTROS ================= --}}
            <div x-data="{
                periodo: '{{ request('periodo', '') }}',
                mostrarCustom: {{ request('periodo') === 'intervalo' ? 'true' : 'false' }},
                prioridade: '{{ request('prioridade', '') }}',
                triagemPendente: {{ request('triagem_pendente') ? 'true' : 'false' }},
                meusChamados: {{ request('meus_chamados') ? 'true' : 'false' }},
                aplicarFiltro(tipo) {
                    this.periodo = tipo;
                    this.mostrarCustom = (tipo === 'intervalo');
                    if (tipo !== 'intervalo') {
                        this.$nextTick(() => document.getElementById('filtroAtForm').submit());
                    }
                },
                aplicarPrioridade(tipo) {
                    this.prioridade = (this.prioridade === tipo) ? '' : tipo;
                    this.$nextTick(() => document.getElementById('filtroAtForm').submit());
                },
                aplicarTriagem() {
                    this.triagemPendente = !this.triagemPendente;
                    this.$nextTick(() => document.getElementById('filtroAtForm').submit());
                },
                aplicarMeusChamados() {
                    this.meusChamados = !this.meusChamados;
                    this.$nextTick(() => document.getElementById('filtroAtForm').submit());
                }
            }">

            {{-- CARD 1: BUSCA E FILTROS --}}
            <div class="filtro-card">
                <form method="GET" id="filtroAtForm">
                    <input type="hidden" name="periodo" :value="periodo">
                    <input type="hidden" name="prioridade" :value="prioridade">
                    <input type="hidden" name="triagem_pendente" :value="triagemPendente ? '1' : ''">
                    <input type="hidden" name="meus_chamados" :value="meusChamados ? '1' : ''">

                    {{-- BUSCA --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Buscar</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                                </svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cliente, Técnico, Nº Atendimento, Assunto..."
                                class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#3f9cae]">
                        </div>
                    </div>

                    {{-- DROPDOWNS --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Empresa</label>
                            <select name="empresa_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#3f9cae]">
                                <option value="">Todas as Empresas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#3f9cae]">
                                <option value="">Todos os Status</option>
                                @foreach([
                                    'orcamento'           => 'Orçamento',
                                    'aberto'              => 'Aberto',
                                    'em_atendimento'      => 'Em Atendimento',
                                    'pendente_cliente'    => 'Pendente Cliente',
                                    'pendente_fornecedor' => 'Pendente Fornecedor',
                                    'garantia'            => 'Garantia',
                                    'finalizacao'         => 'Finalização',
                                    'concluido'           => 'Concluído'
                                ] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Técnico</label>
                            <select name="tecnico_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#3f9cae]">
                                <option value="">Todos os Técnicos</option>
                                @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}" {{ request('tecnico_id') == $funcionario->id ? 'selected' : '' }}>
                                        {{ $funcionario->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cliente</label>
                            <select name="cliente_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#3f9cae]">
                                <option value="">Todos os Clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- BOTÕES --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" style="background:#3f9cae;border-radius:9999px;" class="inline-flex items-center gap-1.5 px-5 py-2 text-white text-sm font-bold hover:opacity-90 transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V15a1 1 0 01-.553.894l-4 2A1 1 0 017 17v-6.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/></svg>
                            Filtrar
                        </button>
                        <a href="{{ route('atendimentos.index') }}" style="background:#9ca3af;border-radius:9999px;" class="inline-flex items-center gap-1.5 px-5 py-2 text-white text-sm font-bold hover:opacity-90 transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            Limpar
                        </a>
                        <a href="{{ route('atendimentos.create') }}" class="ml-auto inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-full transition text-sm">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            Novo Atendimento
                        </a>
                    </div>

                    {{-- DATAS CUSTOMIZADAS --}}
                    <div x-show="mostrarCustom" x-transition class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#3f9cae]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                            <input type="date" name="data_fim" value="{{ request('data_fim') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#3f9cae]">
                        </div>
                    </div>
                </form>
            </div>

            {{-- CARD 2: PERÍODO E PRIORIDADE --}}
            <div class="filtro-card" style="margin-bottom:1.5rem;">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-semibold text-gray-600 shrink-0">Filtrar por período:</span>
                    <div class="flex flex-wrap gap-2">
                        @php $periodoAtivo = request('periodo', ''); @endphp
                        <button type="button" @click="aplicarFiltro('mes_anterior')" class="btn-filtro-rapido {{ $periodoAtivo === 'mes_anterior' ? 'ativo' : 'inativo' }}">Mês Anterior</button>
                        <button type="button" @click="aplicarFiltro('dia')"          class="btn-filtro-rapido {{ $periodoAtivo === 'dia'          ? 'ativo' : 'inativo' }}">Dia</button>
                        <button type="button" @click="aplicarFiltro('semana')"       class="btn-filtro-rapido {{ $periodoAtivo === 'semana'       ? 'ativo' : 'inativo' }}">Semana</button>
                        <button type="button" @click="aplicarFiltro('mes')"          class="btn-filtro-rapido {{ ($periodoAtivo === 'mes' || $periodoAtivo === '') ? 'ativo' : 'inativo' }}">Mês</button>
                        <button type="button" @click="aplicarFiltro('ano')"          class="btn-filtro-rapido {{ $periodoAtivo === 'ano'          ? 'ativo' : 'inativo' }}">Ano</button>
                        <button type="button" @click="aplicarFiltro('intervalo')"    class="btn-filtro-rapido {{ $periodoAtivo === 'intervalo'    ? 'ativo' : 'inativo' }}">Outro período</button>
                    </div>

                    <div class="ml-auto flex flex-wrap gap-2 items-center">
                        <button type="button" @click="aplicarMeusChamados()" class="btn-filtro-rapido" :class="meusChamados ? 'ativo' : 'inativo'">Meus Chamados</button>
                        <div class="w-px h-5 bg-gray-200 mx-1"></div>
                        <button type="button" @click="aplicarTriagem()" class="btn-filtro-rapido btn-triagem" :class="triagemPendente ? 'ativo' : 'inativo'">Pendentes Triagem</button>
                        <div class="w-px h-5 bg-gray-200 mx-1"></div>
                        <button type="button" @click="aplicarPrioridade('')"      class="btn-filtro-rapido" :class="prioridade === '' ? 'ativo' : 'inativo'">TODOS</button>
                        <button type="button" @click="aplicarPrioridade('alta')"  class="btn-filtro-rapido btn-prio-alta"  :class="prioridade === 'alta'  ? 'ativo' : 'inativo'">ALTA</button>
                        <button type="button" @click="aplicarPrioridade('media')" class="btn-filtro-rapido btn-prio-media" :class="prioridade === 'media' ? 'ativo' : 'inativo'">MÉDIA</button>
                        <button type="button" @click="aplicarPrioridade('baixa')" class="btn-filtro-rapido btn-prio-baixa" :class="prioridade === 'baixa' ? 'ativo' : 'inativo'">BAIXA</button>
                    </div>
                </div>
            </div>

            </div>{{-- end x-data --}}

            {{-- ================= KPI CARDS ================= --}}
            <div x-data="{
                modalAberto: false,
                carregando: false,
                tituloModal: '',
                atendimentos: [],
                totalModal: 0,

                async abrirModal(tipo, titulo) {
                    this.tituloModal = titulo;
                    this.modalAberto = true;
                    this.carregando = true;
                    this.atendimentos = [];
                    try {
                        const params = new URLSearchParams(window.location.search);
                        params.set('tipo', tipo);
                        const response = await fetch(`{{ route('atendimentos.modal-dados') }}?${params.toString()}`);
                        const data = await response.json();
                        if (data.success) {
                            this.atendimentos = data.atendimentos;
                            this.totalModal = data.total;
                        }
                    } catch (error) {
                        console.error('Erro ao carregar modal:', error);
                    } finally {
                        this.carregando = false;
                    }
                },

                fecharModal() {
                    this.modalAberto = false;
                    this.atendimentos = [];
                }
            }">

            <div class="kpi-cards-grid">
                {{-- Total --}}
                <div class="kpi-card-at kpi-card-at-teal" style="cursor:pointer;" @click="abrirModal('total', 'Total no Período')">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-1">Total no Período</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalAtendimentos }}</p>
                    <div class="flex gap-2 mt-2 flex-wrap">
                        <span class="text-xs text-gray-500">Abertos: <strong>{{ $qtdAberto }}</strong></span>
                        <span class="text-xs text-gray-500">Em Atend.: <strong>{{ $qtdEmAtendimento }}</strong></span>
                    </div>
                    <p class="text-xs mt-2" style="color:#3f9cae;">Clique para ver detalhes →</p>
                </div>
                {{-- SLA Estourado --}}
                <div class="kpi-card-at kpi-card-at-red" style="cursor:pointer;" @click="abrirModal('sla_estourado', 'SLA Estourado — Abertos há mais de 8h')">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-1">SLA Estourado</p>
                    <p class="text-3xl font-bold" style="color:#dc2626;">{{ $qtdSlaEstourado }}</p>
                    <p class="text-xs text-gray-400 mt-1">Abertos há mais de 8h</p>
                    @if($qtdSlaEstourado > 0)
                        <span class="sla-badge sla-vermelho mt-1">⚠ Atenção urgente</span>
                    @endif
                </div>
                {{-- SLA Alerta --}}
                <div class="kpi-card-at kpi-card-at-amber" style="cursor:pointer;" @click="abrirModal('sla_alerta', 'Alerta SLA — Abertos entre 2h e 8h')">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-1">Alerta SLA</p>
                    <p class="text-3xl font-bold" style="color:#f59e0b;">{{ $qtdSlaAlerta }}</p>
                    <p class="text-xs text-gray-400 mt-1">Abertos entre 2h e 8h</p>
                </div>
                {{-- Aguardando --}}
                <div class="kpi-card-at kpi-card-at-indigo" style="cursor:pointer;" @click="abrirModal('aguardando', 'Aguardando — Pendente Cliente / Fornecedor')">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-1">Aguardando</p>
                    <p class="text-3xl font-bold" style="color:#6366f1;">{{ $qtdPendentes }}</p>
                    <p class="text-xs text-gray-400 mt-1">Pendente cliente / fornecedor</p>
                </div>
                {{-- Resolvidos Hoje --}}
                <div class="kpi-card-at kpi-card-at-green" style="cursor:pointer;" @click="abrirModal('resolvidos_hoje', 'Resolvidos Hoje')">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-1">Resolvidos Hoje</p>
                    <p class="text-3xl font-bold" style="color:#16a34a;">{{ $qtdResolvidosHoje }}</p>
                    <p class="text-xs text-gray-400 mt-1">Concluídos no período: {{ $qtdConcluido }}</p>
                </div>
            </div>

            {{-- ========== MODAL KPI ========== --}}
            <div x-show="modalAberto"
                x-cloak
                @keydown.escape.window="fecharModal()"
                class="fixed inset-0 z-50 overflow-y-auto"
                style="display:none;">

                {{-- Backdrop --}}
                <div x-show="modalAberto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="fecharModal()"
                    class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity">
                </div>

                {{-- Painel --}}
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="modalAberto"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                        @click.stop
                        class="relative bg-white rounded-xl shadow-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden">

                        {{-- Header --}}
                        <div class="px-6 py-5 flex justify-between items-center" style="background:linear-gradient(to right,#3f9cae,#2d7a8a);">
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span x-text="tituloModal"></span>
                            </h3>
                            <button @click="fecharModal()" class="text-white hover:text-gray-200 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Loading --}}
                        <div x-show="carregando" class="flex items-center justify-center p-12">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-16 w-16 border-b-4 mx-auto mb-4" style="border-color:#3f9cae;"></div>
                                <p class="text-gray-600 font-medium">Carregando atendimentos...</p>
                            </div>
                        </div>

                        {{-- Conteúdo --}}
                        <div x-show="!carregando" class="p-6 overflow-y-auto" style="max-height:calc(90vh - 180px);">
                            {{-- Vazio --}}
                            <div x-show="atendimentos.length === 0" class="text-center py-12">
                                <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-500 text-lg">Nenhum atendimento encontrado</p>
                            </div>

                            {{-- Tabela --}}
                            <div x-show="atendimentos.length > 0" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nº</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Cliente</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Empresa</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Técnico</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Assunto</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Prioridade</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">SLA</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Data</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(at, i) in atendimentos" :key="i">
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-900" x-text="at.numero"></td>
                                                <td class="px-4 py-3 text-sm text-gray-700" x-text="at.cliente"></td>
                                                <td class="px-4 py-3 text-sm text-gray-700" x-text="at.empresa"></td>
                                                <td class="px-4 py-3 text-sm text-gray-700" x-text="at.tecnico"></td>
                                                <td class="px-4 py-3 text-sm text-gray-700" x-text="at.assunto"></td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700" x-text="at.status"></span>
                                                </td>
                                                <td class="px-4 py-3 text-sm" x-text="at.prioridade"></td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span :class="{
                                                        'sla-badge sla-vermelho': at.sla_cor === 'vermelho',
                                                        'sla-badge sla-amarelo':  at.sla_cor === 'amarelo',
                                                        'sla-badge sla-verde':    at.sla_cor === 'verde',
                                                        'sla-badge sla-cinza':    at.sla_cor === 'cinza'
                                                    }" x-text="at.sla_texto"></span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600" x-text="at.data"></td>
                                                <td class="px-4 py-3 text-sm">
                                                    <a :href="at.url"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-white text-xs font-medium rounded-md transition"
                                                        style="background:#3f9cae;"
                                                        onmouseover="this.style.background='#2d7a8a'"
                                                        onmouseout="this.style.background='#3f9cae'">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                        Abrir
                                                    </a>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div x-show="!carregando" class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-200">
                            <p class="text-sm text-gray-600">
                                Total: <strong class="text-gray-900" x-text="totalModal"></strong> atendimento(s)
                            </p>
                            <button @click="fecharModal()"
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            </div>{{-- end x-data KPI --}}

            {{-- ================= TABELA (DESKTOP) ================= --}}
        @if($atendimentos->count() > 0)
        @php
            $pendentesEmpresa = $atendimentos->getCollection()->whereNull('empresa_id')->count();
            $pendentesTecnico = $atendimentos->getCollection()->whereNull('funcionario_id')->count();
        @endphp

        @if($pendentesEmpresa > 0 || $pendentesTecnico > 0)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span class="font-semibold">Triagem pendente nesta página:</span>
            <span class="ml-2">Sem empresa: <strong>{{ $pendentesEmpresa }}</strong></span>
            <span class="ml-4">Sem técnico: <strong>{{ $pendentesTecnico }}</strong></span>
        </div>
        @endif

        <div class="table-card">
            <div class="table-wrapper">
                <table class="table table-tight responsive-table">
                    <thead>
                        <tr>
                            <th style="width: 55px;">Nº</th>
                            <th>Solicitante</th>
                            <th style="width: 170px;">Empresa</th>
                            <th style="width: 170px;">Técnico</th>
                            <th style="width: 90px; text-align: center;">Prioridade</th>
                            <th style="width: 115px; text-align: center;">Status</th>
                            <th style="width: 90px; text-align: center;">Tempo</th>
                            <th style="width: 120px; text-align: center;">Última Ação</th>
                            <th style="width: 150px; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($atendimentos as $atendimento)
                        @php
                            $assuntoExibicao = $atendimento->assunto->nome
                                ?? (str_starts_with((string) $atendimento->descricao, 'Assunto:')
                                    ? trim((string) \Illuminate\Support\Str::of($atendimento->descricao)->before("\n"))->replace('Assunto:', '')
                                    : '—');
                            $semEmpresa = empty($atendimento->empresa_id);
                            $semTecnico = empty($atendimento->funcionario_id);

                            // === SLA ===
                            $isConcluido = in_array($atendimento->status_atual, ['concluido', 'finalizacao']);
                            $diffH  = (int) $atendimento->created_at->diffInHours(now());
                            $diffM  = (int) $atendimento->created_at->diffInMinutes(now()) % 60;
                            if ($diffH >= 24) {
                                $dias = floor($diffH / 24); $hr = $diffH % 24;
                                $slaTexto = $dias . 'd ' . $hr . 'h';
                            } elseif ($diffH > 0) {
                                $slaTexto = $diffH . 'h ' . $diffM . 'm';
                            } else {
                                $slaTexto = $diffM . 'm';
                            }
                            $slaCor = $isConcluido ? 'cinza' : ($diffH >= 8 ? 'vermelho' : ($diffH >= 2 ? 'amarelo' : 'verde'));

                            // === ÚLTIMA AÇÃO ===
                            $ultimaAndamentoAt = $atendimento->ultima_andamento_at ? \Carbon\Carbon::parse($atendimento->ultima_andamento_at) : null;
                            $ultimaStatusAt    = $atendimento->ultima_status_at    ? \Carbon\Carbon::parse($atendimento->ultima_status_at)    : null;
                            $ultimaAcaoAt = null;
                            if ($ultimaAndamentoAt && $ultimaStatusAt) {
                                $ultimaAcaoAt = $ultimaAndamentoAt->gt($ultimaStatusAt) ? $ultimaAndamentoAt : $ultimaStatusAt;
                            } elseif ($ultimaAndamentoAt) {
                                $ultimaAcaoAt = $ultimaAndamentoAt;
                            } elseif ($ultimaStatusAt) {
                                $ultimaAcaoAt = $ultimaStatusAt;
                            }
                        @endphp
                        <tr class="expandable-row cursor-pointer" data-id="{{ $atendimento->id }}">
                            {{-- Número --}}
                            <td>
                                <span class="table-number">{{ $atendimento->numero_atendimento }}</span>
                            </td>
                            {{-- Solicitante --}}
                            <td>
                                <div>
                                    <p style="font-weight: 600; color: #1f2937;">
                                        @if($atendimento->cliente)
                                        {{ $atendimento->cliente->nome }}
                                        @else
                                        {{ $atendimento->nome_solicitante }}
                                        @endif
                                    </p>
                                    @if($atendimento->telefone_solicitante)
                                    <p style="font-size: 0.8125rem; color: #6b7280;">
                                        {{ $atendimento->telefone_solicitante }}</p>
                                    @endif
                                </div>
                            </td>
                            <!-- <td>{{ $assuntoExibicao }}</td> -->
                            {{-- Empresa --}}
                            <td>
                                <select data-id="{{ $atendimento->id }}" data-campo="empresa_id"
                                    class="campo-editavel table-select {{ $semEmpresa ? 'border-amber-300 bg-amber-50' : '' }}">
                                    <option value="">—</option>
                                    @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" @selected($atendimento->empresa_id == $empresa->id)>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            {{-- Técnico (Editável) --}}
                            <td>
                                <select data-id="{{ $atendimento->id }}" data-campo="funcionario_id"
                                    id="tecnico_{{ $atendimento->id }}"
                                    class="campo-editavel table-select {{ $semTecnico ? 'border-amber-300 bg-amber-50' : '' }}">
                                    <option value="">—</option>
                                    @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}" @selected($atendimento->funcionario_id == $funcionario->id)>
                                        {{ collect(explode(' ', $funcionario->nome))->first() }}{{ count(explode(' ', $funcionario->nome)) > 1 ? ' ' . collect(explode(' ', $funcionario->nome))->last() : '' }}
                                    </option>
                                    @endforeach
                                </select>
                                @if($atendimento->tecnicosAdicionais->count() > 0)
                                <div style="margin-top:3px; display:flex; flex-wrap:wrap; gap:2px;">
                                    @foreach($atendimento->tecnicosAdicionais as $tecAd)
                                    <span style="font-size:0.62rem; background:#dbeafe; color:#1d4ed8; padding:1px 6px; border-radius:9999px; white-space:nowrap;"
                                          title="{{ $tecAd->nome }}">
                                        +{{ collect(explode(' ', $tecAd->nome))->first() }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                            </td>
                            {{-- Prioridade (Editável) --}}
                            <td style="text-align: center;">
                                <select data-id="{{ $atendimento->id }}" data-campo="prioridade"
                                    class="campo-editavel table-select">
                                    <option value="baixa" @selected($atendimento->prioridade === 'baixa')>Baixa
                                    </option>
                                    <option value="media" @selected($atendimento->prioridade === 'media')>Média
                                    </option>
                                    <option value="alta" @selected($atendimento->prioridade === 'alta')>Alta
                                    </option>
                                </select>
                            </td>
                            {{-- Status (Editável) --}}
                            <td style="text-align: center;">
                                <select data-id="{{ $atendimento->id }}" data-campo="status"
                                    class="campo-editavel table-select">
                                    @foreach([
                                    'orcamento' => 'Orçamento',
                                    'aberto' => 'Aberto',
                                    'em_atendimento' => 'Em Atendimento',
                                    'pendente_cliente' => 'Pendente Cliente',
                                    'pendente_fornecedor' => 'Pendente Fornecedor',
                                    'garantia' => 'Garantia',
                                    'finalizacao' => 'Finalização',
                                    'concluido' => 'Concluído'
                                    ] as $value => $label)
                                    <option value="{{ $value }}" @selected($atendimento->status_atual === $value)>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            {{-- Tempo Aberto (SLA) --}}
                            <td style="text-align:center;">
                                <span class="sla-badge sla-{{ $slaCor }}">
                                    @if($slaCor === 'vermelho') ⚠ @elseif($slaCor === 'amarelo') ● @elseif($slaCor === 'verde') ✓ @else ✔ @endif
                                    {{ $slaTexto }}
                                </span>
                                <div style="font-size:0.65rem;color:#9ca3af;margin-top:2px;">{{ $atendimento->created_at->format('d/m/Y') }}</div>
                            </td>
                            {{-- Última Ação --}}
                            <td style="text-align:center;">
                                @if($ultimaAcaoAt)
                                    <span style="font-size:0.72rem;color:#374151;">{{ $ultimaAcaoAt->diffForHumans(null, true, true, 1) }}</span>
                                    <div style="font-size:0.65rem;color:#9ca3af;">{{ $ultimaAcaoAt->format('d/m H:i') }}</div>
                                @else
                                    <span style="font-size:0.72rem;color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <!--
                            <td>
                                <div class="flex flex-col items-center gap-1">
                                    <button
                                        type="button"
                                        class="btn-agendar-fila inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50"
                                        data-atendimento-id="{{ $atendimento->id }}"
                                        data-funcionario-id="{{ $atendimento->funcionario_id }}"
                                        data-data="{{ $atendimento->data_inicio_agendamento?->format('Y-m-d') }}"
                                        data-periodo="{{ $atendimento->periodo_agendamento }}"
                                        data-hora="{{ $atendimento->data_inicio_agendamento?->format('H:i') }}"
                                        data-duracao="{{ $atendimento->duracao_agendamento_minutos ? max(1, (int) ($atendimento->duracao_agendamento_minutos / 60)) : '' }}"
                                    >
                                        {{ $atendimento->data_inicio_agendamento ? 'Reagendar' : 'Agendar' }}
                                    </button>
                                    @if($atendimento->data_inicio_agendamento)
                                        <span class="text-xs text-gray-400 mt-1">{{ $atendimento->data_inicio_agendamento->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-xs text-gray-300 mt-1">Sem data</span>
                                    @endif
                                </div>
                            </td>
                            -->
                            {{-- Ações --}}
                            <td style="text-align: center;">
                                <div class="table-actions">
                                    {{-- Agendar / Reagendar --}}
                                    <button type="button"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-teal-600 hover:bg-teal-700 text-white rounded-full transition btn-agendar-fila"
                                        title="{{ $atendimento->data_inicio_agendamento ? 'Reagendar' : 'Agendar' }}"
                                        data-atendimento-id="{{ $atendimento->id }}"
                                        data-funcionario-id="{{ $atendimento->funcionario_id }}"
                                        data-data="{{ $atendimento->data_inicio_agendamento?->format('Y-m-d') }}"
                                        data-periodo="{{ $atendimento->periodo_agendamento }}"
                                        data-hora="{{ $atendimento->data_inicio_agendamento?->format('H:i') }}"
                                        data-duracao="{{ $atendimento->duracao_agendamento_minutos ? max(1, (int) ($atendimento->duracao_agendamento_minutos / 60)) : '' }}"
                                        data-tecnicos-adicionais="{{ $atendimento->tecnicosAdicionais->pluck('id')->implode(',') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    {{-- Editar --}}
                                    <button type="button"
                                        class="inline-flex items-center justify-center w-8 h-8 border border-blue-500 text-blue-500 bg-white hover:bg-blue-50 rounded-full transition"
                                        title="Editar"
                                        onclick="window.location='{{ route('atendimentos.edit', $atendimento) }}'">
                                        <svg fill="currentColor" viewBox="0 0 20 20" style="width:15px;height:15px;">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </button>
                                    {{-- Excluir --}}
                                    <form action="{{ route('atendimentos.destroy', $atendimento) }}" method="POST" onsubmit="return confirm('Deseja excluir este atendimento?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 border border-red-500 text-red-500 bg-white hover:bg-red-50 rounded-full transition"
                                            title="Excluir">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width:15px;height:15px;">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="expandable-content-row preview-row" id="expand-content-{{ $atendimento->id }}" style="display:none;">
                            <td colspan="9">
                                <div class="preview-content">
                                    <div>
                                        <div class="preview-label">Assunto</div>
                                        <div class="preview-value">{{ $assuntoExibicao }}</div>
                                        <div class="preview-label mt-2">Descrição</div>
                                        <div class="preview-value truncate-text" style="max-width:280px;white-space:normal;line-height:1.4;">
                                            {{ \Illuminate\Support\Str::limit((string)$atendimento->descricao, 120) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="preview-label">Criado em</div>
                                        <div class="preview-value">{{ $atendimento->created_at->format('d/m/Y H:i') }}</div>
                                        <div class="preview-label mt-2">Data Agendamento</div>
                                        <div class="preview-value">{{ $atendimento->data_inicio_agendamento ? $atendimento->data_inicio_agendamento->format('d/m/Y') : 'Sem data' }}</div>
                                        @if($ultimaAcaoAt)
                                        <div class="preview-label mt-2">Última Ação</div>
                                        <div class="preview-value">{{ $ultimaAcaoAt->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="preview-label">SLA</div>
                                        <div class="preview-value">
                                            <span class="sla-badge sla-{{ $slaCor }}">{{ $slaTexto }}</span>
                                            @if($slaCor === 'vermelho')<div class="text-xs text-red-600 mt-1">⚠ SLA estourado</div>@endif
                                            @if($slaCor === 'amarelo')<div class="text-xs text-amber-600 mt-1">⚠ Próximo do limite</div>@endif
                                        </div>
                                        @if($atendimento->cliente)
                                        <div class="preview-label mt-2">Telefone</div>
                                        <div class="preview-value">{{ $atendimento->telefone_solicitante ?: '—' }}</div>
                                        @endif
                                        <div class="mt-2">
                                            <a href="{{ route('atendimentos.edit', $atendimento) }}" class="text-xs font-semibold" style="color:#3f9cae;">Ver detalhes completos →</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.expandable-row').forEach(function(row) {
                    row.addEventListener('click', function(e) {
                        // Evita conflito com selects e botões
                        if (e.target.tagName === 'SELECT' || e.target.tagName === 'OPTION' || e.target.closest('button')) return;
                        const id = row.getAttribute('data-id');
                        const contentRow = document.getElementById('expand-content-' + id);
                        if (contentRow.style.display === 'none') {
                            document.querySelectorAll('.expandable-content-row').forEach(r => r.style.display = 'none');
                            contentRow.style.display = '';
                        } else {
                            contentRow.style.display = 'none';
                        }
                    });
                });
            });
        </script>
        @endpush

        {{-- ================= MOBILE CARDS ================= --}}
        <div class="mobile-cards">
            @foreach($atendimentos as $atendimento)
                @php
                    $assuntoExibicao = $atendimento->assunto->nome
                        ?? (str_starts_with((string) $atendimento->descricao, 'Assunto:')
                            ? trim((string) \Illuminate\Support\Str::of($atendimento->descricao)->before("\n"))->replace('Assunto:', '')
                            : '—');
                    $semEmpresa = empty($atendimento->empresa_id);
                    $semTecnico = empty($atendimento->funcionario_id);
                @endphp
            <div class="mobile-card">
                <div class="mobile-card-header">
                    <div>
                        <div class="mobile-card-title">Atendimento #{{ $atendimento->numero_atendimento }}</div>
                        <div class="mobile-card-date">{{ $atendimento->data_atendimento->format('d/m/Y \à\s H:i') }}
                        </div>
                    </div>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Solicitante</span>
                    <span class="mobile-card-value">
                        @if($atendimento->cliente)
                        {{ $atendimento->cliente->nome }}
                        @else
                        {{ $atendimento->nome_solicitante }}
                        @endif
                    </span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Assunto</span>
                    <span class="mobile-card-value">{{ $assuntoExibicao }}</span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Empresa</span>
                    <span class="mobile-card-value">
                        @if($semEmpresa)
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Não definida</span>
                        @else
                            {{ optional($atendimento->empresa)->nome_fantasia ?? '—' }}
                        @endif
                    </span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Técnico</span>
                    <span class="mobile-card-value {{ $semTecnico ? 'text-amber-700 font-semibold' : '' }}">
                        {{ $semTecnico ? 'Não definido' : optional($atendimento->funcionario)->nome }}
                    </span>
                </div>

                <div class="mobile-card-badges">
                    <span class="table-badge badge-{{ $atendimento->prioridade }}">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                        {{ ucfirst($atendimento->prioridade) }}
                    </span>
                </div>

                <div class="mobile-card-actions">
                    <a href="{{ route('atendimentos.edit', $atendimento) }}" class="btn btn-sm btn-edit">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Editar
                    </a>

                    <form action="{{ route('atendimentos.destroy', $atendimento) }}" method="POST"
                        onsubmit="return confirm('Deseja excluir este atendimento?')" style="flex: 1;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-delete"
                            style="width: 100%; justify-content: center;">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ================= PAGINAÇÃO ================= --}}
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando <strong>{{ $atendimentos->count() }}</strong> de
                <strong>{{ $atendimentos->total() }}</strong>
                atendimentos
            </div>

            <div class="pagination-links">
                {{-- Link Anterior --}}
                @if($atendimentos->onFirstPage())
                <span class="pagination-link disabled">← Anterior</span>
                @else
                <a href="{{ $atendimentos->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                @endif

                {{-- Links de Página --}}
                @foreach($atendimentos->getUrlRange(1, $atendimentos->lastPage()) as $page => $url)
                @if($page == $atendimentos->currentPage())
                <span class="pagination-link active">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                @endif
                @endforeach

                {{-- Link Próximo --}}
                @if($atendimentos->hasMorePages())
                <a href="{{ $atendimentos->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                @else
                <span class="pagination-link disabled">Próximo →</span>
                @endif
            </div>
        </div>
            @else
            <div class="bg-white rounded-xl p-12 text-center border border-dashed border-gray-300 text-gray-500">Nenhum atendimento encontrado.</div>
            @endif
        </div>
    </div>

    <div id="modal-reagendar-fila" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:60;">
        <div style="max-width:920px; margin:3vh auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,.25);">
            <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-weight:700; color:#1f2937;">Agendar técnico</h3>
                <button type="button" id="fechar-modal-reagendar-fila" style="font-size:20px; color:#6b7280;">&times;</button>
            </div>
            <div style="padding:18px; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px;">
                <div>
                    <label class="text-sm font-medium text-gray-700">Técnico Principal</label>
                    <select id="fila_funcionario_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        @foreach($funcionarios as $funcionario)
                        <option value="{{ $funcionario->id }}">{{ $funcionario->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Data</label>
                    <input id="fila_data" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Período</label>
                    <select id="fila_periodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <option value="dia_todo">Dia todo (08:00-17:00, 9 horas)</option>
                        <option value="manha">Manhã (08:00-12:00)</option>
                        <option value="tarde">Tarde (13:00-18:00)</option>
                        <option value="noite">Noite (18:01-21:59)</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Hora início</label>
                    <input id="fila_hora_inicio" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Duração (horas)</label>
                    <select id="fila_duracao_horas" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        @for($h = 1; $h <= 9; $h++)
                        <option value="{{ $h }}">{{ $h }} hora{{ $h > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- Técnicos Adicionais --}}
            <div style="padding:0 18px 12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <label class="text-sm font-semibold text-gray-700">Técnicos Adicionais</label>
                    <button type="button" id="btn-add-tecnico-fila"
                        style="font-size:12px; background:#3f9cae; color:#fff; border:none; border-radius:9999px; padding:3px 14px; cursor:pointer; font-weight:600;">
                        + Adicionar Técnico
                    </button>
                </div>
                <div id="lista-tecnicos-adicionais-fila" style="display:flex; flex-direction:column; gap:6px;">
                    <p style="font-size:12px; color:#9ca3af; margin:0;">Nenhum técnico adicional adicionado.</p>
                </div>
            </div>
            <div style="padding:0 18px 12px;">
                <div class="text-sm font-semibold text-gray-700 mb-2">Agenda do dia (calendário por técnico)</div>
                <div id="agenda-calendario-fila" style="max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:8px; padding:10px;"></div>
            </div>
            <div style="padding:14px 18px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" id="cancelar-reagendar-fila" class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancelar</button>
                <button type="button" id="confirmar-reagendar-fila" class="px-4 py-2 rounded-lg bg-[#3f9cae] text-white text-sm">Salvar agendamento</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const modalReagendarFila = document.getElementById('modal-reagendar-fila');
        const filaFuncionarioInput = document.getElementById('fila_funcionario_id');
        const filaDataInput = document.getElementById('fila_data');
        const filaPeriodoInput = document.getElementById('fila_periodo');
        const filaHoraInicioInput = document.getElementById('fila_hora_inicio');
        const filaDuracaoInput = document.getElementById('fila_duracao_horas');
        const calendarioFilaWrapper = document.getElementById('agenda-calendario-fila');
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

        let contextoReagendarFila = null;

        // ===== TÉCNICOS ADICIONAIS =====
        let tecnicosAdicionaisFila = [];
        const funcionariosFilaOpts = [
            @foreach($funcionarios as $f)
            { id: '{{ $f->id }}', nome: '{{ addslashes($f->nome) }}' },
            @endforeach
        ];

        function renderTecnicosAdicionaisFila() {
            const container = document.getElementById('lista-tecnicos-adicionais-fila');
            if (!container) return;
            if (tecnicosAdicionaisFila.length === 0) {
                container.innerHTML = '<p style="font-size:12px;color:#9ca3af;margin:0;">Nenhum técnico adicional adicionado.</p>';
                return;
            }
            container.innerHTML = tecnicosAdicionaisFila.map((id, i) => {
                const opts = funcionariosFilaOpts
                    .map(f => `<option value="${f.id}" ${String(f.id) === String(id) ? 'selected' : ''}>${f.nome}</option>`)
                    .join('');
                return `<div style="display:flex;gap:6px;align-items:center;">
                    <select onchange="window._atFilaUpdate(${i},this.value)" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm" style="flex:1;font-size:13px;">
                        <option value="">Selecione o técnico</option>${opts}
                    </select>
                    <button type="button" onclick="window._atFilaRemove(${i})" style="background:#fee2e2;color:#dc2626;border:none;border-radius:9999px;width:26px;height:26px;cursor:pointer;font-size:14px;line-height:1;flex-shrink:0;">✕</button>
                </div>`;
            }).join('');
        }

        window._atFilaUpdate = (i, val) => { tecnicosAdicionaisFila[i] = val; };
        window._atFilaRemove = (i) => { tecnicosAdicionaisFila.splice(i, 1); renderTecnicosAdicionaisFila(); };

        document.getElementById('btn-add-tecnico-fila')?.addEventListener('click', function() {
            tecnicosAdicionaisFila.push('');
            renderTecnicosAdicionaisFila();
        });

        // ===== AÇÕES RÁPIDAS =====
        document.querySelectorAll('.btn-acao-rapida').forEach((btn) => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id     = this.dataset.id;
                const campo  = this.dataset.campo;
                const valor  = this.dataset.valor;
                const titulo = this.title;

                if (!confirm(`Confirmar: ${titulo}?`)) return;

                const btnEl = this;
                btnEl.disabled = true;
                btnEl.style.opacity = '0.5';

                fetch(`{{ url('atendimentos') }}/${id}/atualizar-campo`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ campo, valor })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btnEl.style.opacity = '1';
                        btnEl.style.background = '#16a34a';
                        btnEl.style.color = '#fff';
                        setTimeout(() => window.location.reload(), 600);
                        return;
                    }
                    btnEl.disabled = false;
                    btnEl.style.opacity = '1';
                    alert(data.message || 'Não foi possível atualizar.');
                })
                .catch(() => {
                    btnEl.disabled = false;
                    btnEl.style.opacity = '1';
                    alert('Erro ao processar a ação.');
                });
            });
        });

        document.querySelectorAll('.campo-editavel').forEach((campo) => {
            campo.addEventListener('change', function() {
                const id = this.dataset.id;
                const campo = this.dataset.campo;
                const valor = this.value;
                fetch(`{{ url('atendimentos') }}/${id}/atualizar-campo`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ campo, valor })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.add('border-green-500');
                        setTimeout(() => this.classList.remove('border-green-500'), 1000);
                        return;
                    }

                    alert(data.message || 'Não foi possível atualizar o campo.');
                    window.location.reload();
                })
                .catch(() => {
                    alert('Erro ao atualizar o campo.');
                    window.location.reload();
                });
            });
        });

        document.querySelectorAll('.btn-agendar-fila').forEach((button) => {
            button.addEventListener('click', function() {
                const atendimentoId = this.dataset.atendimentoId;
                const tecnicoSelect = document.getElementById(`tecnico_${atendimentoId}`);

                contextoReagendarFila = {
                    atendimentoId,
                    funcionarioId: (tecnicoSelect?.value || this.dataset.funcionarioId || ''),
                    data: this.dataset.data || '',
                    periodo: this.dataset.periodo || '',
                    hora: this.dataset.hora || '',
                    duracao: this.dataset.duracao || '1'
                };

                filaFuncionarioInput.value = contextoReagendarFila.funcionarioId;
                filaDataInput.value = contextoReagendarFila.data || new Date().toISOString().slice(0, 10);
                filaPeriodoInput.value = contextoReagendarFila.periodo;
                filaHoraInicioInput.value = contextoReagendarFila.hora;
                filaDuracaoInput.value = contextoReagendarFila.duracao;

                if (filaPeriodoInput.value === 'dia_todo') {
                    filaHoraInicioInput.value = '08:00';
                    filaDuracaoInput.value = '9';
                    filaHoraInicioInput.disabled = true;
                    filaDuracaoInput.disabled = true;
                } else {
                    filaHoraInicioInput.disabled = false;
                    filaDuracaoInput.disabled = false;
                }

                modalReagendarFila.style.display = 'block';

                // Carregar técnicos adicionais existentes
                tecnicosAdicionaisFila = (this.dataset.tecnicosAdicionais || '').split(',').filter(Boolean);
                renderTecnicosAdicionaisFila();

                carregarAgendaFila();
            });
        });

        function fecharModalFila() {
            modalReagendarFila.style.display = 'none';
            contextoReagendarFila = null;
            tecnicosAdicionaisFila = [];
            renderTecnicosAdicionaisFila();
        }

        document.getElementById('fechar-modal-reagendar-fila')?.addEventListener('click', fecharModalFila);
        document.getElementById('cancelar-reagendar-fila')?.addEventListener('click', fecharModalFila);

        filaPeriodoInput?.addEventListener('change', function() {
            if (this.value === 'dia_todo') {
                filaHoraInicioInput.value = '08:00';
                filaDuracaoInput.value = '9';
                filaHoraInicioInput.disabled = true;
                filaDuracaoInput.disabled = true;
            } else {
                filaHoraInicioInput.disabled = false;
                filaDuracaoInput.disabled = false;
            }

            carregarAgendaFila();
        });

        filaDataInput?.addEventListener('change', carregarAgendaFila);

        async function carregarAgendaFila() {
            const data = filaDataInput.value;
            const periodo = filaPeriodoInput.value;

            if (!data) {
                calendarioFilaWrapper.innerHTML = '<div class="text-sm text-gray-500">Selecione a data para visualizar a agenda.</div>';
                return;
            }

            calendarioFilaWrapper.innerHTML = '<div class="text-sm text-gray-500">Carregando agenda...</div>';

            try {
                const url = `{{ route('agenda-tecnica.disponibilidade') }}?data=${encodeURIComponent(data)}${periodo ? `&periodo=${encodeURIComponent(periodo)}` : ''}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const json = await response.json();

                const blocos = (json.tecnicos || []).map((tecnico) => {
                    const agendaTecnico = (json.agendamentos || []).filter((item) => String(item.funcionario_id) === String(tecnico.id));
                    const linhas = agendaTecnico.length
                        ? agendaTecnico.map((item) => `<div style="font-size:12px;color:#374151;margin-top:3px;">${item.inicio} - ${item.fim} • #${item.numero_atendimento} • ${item.cliente}</div>`).join('')
                        : '<div style="font-size:12px;color:#16a34a;margin-top:3px;">Livre</div>';

                    return `<div style="border-bottom:1px solid #f3f4f6;padding:8px 0;"><div style="font-weight:600;font-size:13px;">${tecnico.nome}</div>${linhas}</div>`;
                });

                calendarioFilaWrapper.innerHTML = blocos.length ? blocos.join('') : '<div class="text-sm text-gray-500">Nenhum técnico encontrado.</div>';
            } catch (_) {
                calendarioFilaWrapper.innerHTML = '<div class="text-sm text-red-600">Não foi possível carregar a agenda.</div>';
            }
        }

        document.getElementById('confirmar-reagendar-fila')?.addEventListener('click', function() {
            if (!contextoReagendarFila?.atendimentoId) {
                alert('Não foi possível identificar o atendimento para agendar.');
                return;
            }

            const funcionarioId = filaFuncionarioInput.value;
            const data = filaDataInput.value;
            const periodo = filaPeriodoInput.value;
            const horaInicio = filaHoraInicioInput.value;
            const duracaoHoras = filaDuracaoInput.value;

            if (!funcionarioId || !data || !periodo || !horaInicio || !duracaoHoras) {
                alert('Preencha todos os campos do agendamento.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('atendimentos') }}/${contextoReagendarFila.atendimentoId}/reagendar-agendamento`;

            const payload = {
                _token: token,
                funcionario_id: funcionarioId,
                data_agendamento: data,
                periodo_agendamento: periodo,
                hora_inicio: horaInicio,
                duracao_horas: duracaoHoras,
            };

            Object.entries(payload).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });

            // Técnicos adicionais
            tecnicosAdicionaisFila.filter(id => id && id !== funcionarioId).forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'tecnicos_adicionais[]'; inp.value = id;
                form.appendChild(inp);
            });

            document.body.appendChild(form);
            form.submit();
        });
    </script>
    @endpush
</x-app-layout>
