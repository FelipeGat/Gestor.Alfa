<x-app-layout>
    @push('styles')
    @vite('resources/css/dashboard/dashboard_operacional.css')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Dashboard Técnico']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 Dashboard Técnico {{ $empresaId ? '- ' . ($empresas->find($empresaId)->nome_fantasia ?? 'Empresa') : '(Global)' }}
            </h2>

            <!-- FILTROS -->
            <form action="{{ route('dashboard.tecnico') }}" method="GET" class="flex flex-wrap items-center justify-center gap-3">
                {{-- Filtro de Empresa --}}
                <select name="empresa_id" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="">Todas as Empresas</option>
                    @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}" @selected($empresaId==$empresa->id)>
                        {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                    </option>
                    @endforeach
                </select>

                {{-- Filtro de Status --}}
                <select name="status_atual" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    <option value="">Todos os Status</option>
                    @foreach($todosStatus as $st)
                    <option value="{{ $st }}" @selected($statusFiltro==$st)>
                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                    </option>
                    @endforeach
                </select>

                {{-- Botão Limpar --}}
                @if($empresaId || $statusFiltro)
                <a href="{{ route('dashboard.tecnico') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition text-sm">
                    Limpar
                </a>
                @endif
            </form>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="{
        modalAberto: false,
        carregando: false,
        tituloModal: '',
        atendimentos: [],
        totalAtendimentos: 0,
        empresaIdFiltro: '{{ $empresaId }}',
        statusFiltro: '',
        filtroRapido: '{{ $filtroRapido }}',
        inicioCustom: '{{ $inicio?->format('Y-m-d') ?? '' }}',
        fimCustom: '{{ $fim?->format('Y-m-d') ?? '' }}',
        
        async abrirModal(status, titulo) {
            this.statusFiltro = status;
            this.tituloModal = titulo;
            this.modalAberto = true;
            this.carregando = true;
            
            try {
                const params = new URLSearchParams();
                if (this.empresaIdFiltro) params.append('empresa_id', this.empresaIdFiltro);
                if (status) params.append('status_atual', status);
                
                // Adicionar filtros de data
                params.append('filtro_rapido', this.filtroRapido || 'mes');
                if (this.filtroRapido === 'custom' && this.inicioCustom && this.fimCustom) {
                    params.append('inicio_custom', this.inicioCustom);
                    params.append('fim_custom', this.fimCustom);
                }
                
                const response = await fetch(`/dashboard-tecnico/atendimentos?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.atendimentos = data.atendimentos;
                    this.totalAtendimentos = data.total;
                }
            } catch (error) {
                console.error('Erro ao buscar atendimentos:', error);
                alert('Erro ao carregar atendimentos. Tente novamente.');
            } finally {
                this.carregando = false;
            }
        },
        
        fecharModal() {
            this.modalAberto = false;
            this.atendimentos = [];
        },
        
        getStatusColor(status) {
            const colors = {
                'aberto': 'bg-blue-100 text-blue-800',
                'em_atendimento': 'bg-orange-100 text-orange-800',
                'finalizacao': 'bg-yellow-100 text-yellow-800',
                'concluido': 'bg-green-100 text-green-800',
                'cancelado': 'bg-gray-100 text-gray-800',
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= FILTRO RÁPIDO DE PERÍODO ================= --}}
            <div x-data="{
                filtroRapido: '{{ $filtroRapido }}',
                mostrarCustom: {{ $filtroRapido === 'custom' ? 'true' : 'false' }},
                aplicarFiltro(tipo) {
                    this.filtroRapido = tipo;
                    if (tipo !== 'custom') {
                        this.mostrarCustom = false;
                        const form = this.$refs.formFiltro;
                        const inputInicio = form.querySelector('input[name=inicio]');
                        const inputFim = form.querySelector('input[name=fim]');
                        if (inputInicio) inputInicio.disabled = true;
                        if (inputFim) inputFim.disabled = true;
                        setTimeout(() => form.submit(), 10);
                    } else {
                        this.mostrarCustom = true;
                    }
                }
            }" class="mb-6 bg-white rounded-xl shadow-sm p-6">
                <form method="GET" x-ref="formFiltro" action="{{ route('dashboard.tecnico') }}">
                    @if($empresaId)
                    <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                    @endif
                    @if($statusFiltro)
                    <input type="hidden" name="status_atual" value="{{ $statusFiltro }}">
                    @endif
                    <input type="hidden" name="filtro_rapido" :value="filtroRapido">

                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-gray-700 font-semibold text-sm">Filtrar por período:</span>

                        {{-- Botões de filtro rápido --}}
                        <button type="button"
                            @click="aplicarFiltro('mes_anterior')"
                            :class="filtroRapido === 'mes_anterior' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-2 rounded-md text-xs font-medium transition">
                            Mês Anterior
                        </button>

                        <button type="button"
                            @click="aplicarFiltro('dia')"
                            :class="filtroRapido === 'dia' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-2 rounded-md text-xs font-medium transition">
                            Dia
                        </button>

                        <button type="button"
                            @click="aplicarFiltro('semana')"
                            :class="filtroRapido === 'semana' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-2 rounded-md text-xs font-medium transition">
                            Semana
                        </button>

                        <button type="button"
                            @click="aplicarFiltro('mes')"
                            :class="filtroRapido === 'mes' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-2 rounded-md text-xs font-medium transition">
                            Mês
                        </button>

                        <button type="button"
                            @click="aplicarFiltro('ano')"
                            :class="filtroRapido === 'ano' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-2 rounded-md text-xs font-medium transition">
                            Ano
                        </button>

                        <button type="button"
                            @click="aplicarFiltro('custom')"
                            :class="filtroRapido === 'custom' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-2 rounded-md text-xs font-medium transition">
                            Outro período
                        </button>
                    </div>

                    {{-- Campos de data customizada --}}
                    <div x-show="mostrarCustom" x-cloak x-transition class="mt-4 flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                            <input type="date" name="inicio" value="{{ request('inicio', $inicio?->format('Y-m-d')) }}"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                            <input type="date" name="fim" value="{{ request('fim', $fim?->format('Y-m-d')) }}"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 text-sm">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm font-medium">
                            Aplicar
                        </button>
                    </div>
                </form>
            </div>

            {{-- ================= MÉTRICAS FILTRADAS ================= --}}
            <div @click="abrirModal('{{ $statusFiltro }}', 'Filtro Atual: {{ $statusFiltro ? ucfirst(str_replace('_', ' ', $statusFiltro)) : 'Todos os Status' }}')"
                class="bg-indigo-700 rounded-xl shadow-lg p-8 mb-10 text-white cursor-pointer hover:bg-indigo-800 transition-all transform hover:scale-[1.02]">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <h3 class="text-indigo-100 text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Resultado do Filtro Atual (clique para ver detalhes)
                        </h3>
                        <p class="text-sm text-indigo-200">
                            Status: <strong>{{ $statusFiltro ? ucfirst(str_replace('_', ' ', $statusFiltro)) : 'Todos' }}</strong> |
                            Empresa: <strong>{{ $empresaId ? ($empresas->find($empresaId)->nome_fantasia ?? 'Selecionada') : 'Todas' }}</strong>
                        </p>
                    </div>
                    <div class="text-center">
                        <span class="block text-indigo-200 text-xs uppercase font-bold">Total de Atendimentos</span>
                        <span class="text-5xl font-black">{{ $metricasFiltradas->qtd ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- ================= CARDS DE INDICADORES (CLICÁVEIS) ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 mb-10">
                <!-- Agendados -->
                <div @click="abrirModal('aberto', 'Atendimentos Agendados')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-blue-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Agendados</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-agendados">{{ $indicadores['agendados'] }}</p>
                </div>

                <!-- Em Execução -->
                <div @click="abrirModal('em_atendimento', 'Atendimentos em Execução')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-green-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Em Execução</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-execucao">{{ $indicadores['em_execucao'] }}</p>
                </div>

                <!-- Em Pausa -->
                <div @click="abrirModal('em_atendimento', 'Atendimentos em Pausa')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-orange-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Em Pausa</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-pausa">{{ $indicadores['em_pausa'] }}</p>
                </div>

                <!-- Finalizados -->
                <div @click="abrirModal('concluido', 'Atendimentos Finalizados')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-purple-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Finalizados</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-finalizados">{{ $indicadores['finalizados'] }}</p>
                </div>


                <!-- Aguardando Finalização (clicável) -->
                <div @click="abrirModal('finalizacao', 'Aguardando Finalização')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-pink-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Aguardando Finalização</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-aguardando-finalizacao">{{ $indicadores['aguardando_finalizacao'] ?? 0 }}</p>
                </div>

                <!-- Técnicos em Pausa -->
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-gray-500 hover:shadow-xl transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Técnicos em Pausa</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-tecnicos-pausados">{{ $indicadores['tecnicos_pausados'] }}</p>
                </div>
            </div>

            <!-- Painel de Acompanhamento -->
            <div class="bg-white shadow rounded-xl p-6 mb-8">
                <h3 class="text-base font-semibold text-gray-700 mb-6">Acompanhamento em Tempo Real</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" id="tecnicosGrid">
            @forelse($tecnicos as $tecnicoData)
                @php
                    $funcionario = $tecnicoData['funcionario'];
                    $atendimentoAtual = $tecnicoData['atendimento_atual'];
                    $pausaAtiva = $tecnicoData['pausa_ativa'];
                @endphp

                <div class="tecnico-card {{ $atendimentoAtual ? ($atendimentoAtual->em_pausa ? 'status-pausa' : 'status-execucao') : 'status-livre' }}" 
                     data-tecnico-id="{{ $funcionario->id }}">
                    
                    <!-- Header do Card -->
                    <div class="tecnico-header">
                        <div class="tecnico-info">
                            <div class="tecnico-avatar" style="width: 36px; height: 36px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="36" height="36">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="tecnico-nome">{{ $funcionario->user->name }}</h3>
                                <p class="tecnico-stats">
                                    {{ $tecnicoData['atendimentos_finalizados'] }}/{{ $tecnicoData['atendimentos_total'] }} finalizados
                                </p>
                            </div>
                        </div>

                        @if($atendimentoAtual)
                            @if($atendimentoAtual->em_pausa)
                                <span class="status-badge badge-pausa">
                                    <svg class="badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    EM PAUSA
                                </span>
                            @else
                                <span class="status-badge badge-execucao">
                                    <svg class="badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                    </svg>
                                    EM EXECUÇÃO
                                </span>
                            @endif
                        @else
                            <span class="status-badge badge-livre">
                                <svg class="badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                LIVRE
                            </span>
                        @endif
                    </div>

                    <!-- Conteúdo do Card -->
                    <div class="tecnico-body">
                        @if($atendimentoAtual)
                            <!-- Cliente Atual -->
                            <div class="info-row">
                                <span class="info-label">
                                    <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Cliente:
                                </span>
                                <span class="info-value">{{ $atendimentoAtual->cliente->nome_fantasia ?? 'N/D' }}</span>
                            </div>

                            <!-- Prioridade -->
                            <div class="info-row">
                                <span class="info-label">
                                    <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
                                    </svg>
                                    Prioridade:
                                </span>
                                <span class="prioridade-badge prioridade-{{ $atendimentoAtual->prioridade }}">
                                    {{ strtoupper($atendimentoAtual->prioridade) }}
                                </span>
                            </div>

                            @if($pausaAtiva)
                                <!-- Tipo de Pausa -->
                                <div class="info-row">
                                    <span class="info-label">
                                        <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Tipo de Pausa:
                                    </span>
                                    <span class="pausa-tipo">{{ $pausaAtiva->tipo_pausa_label }}</span>
                                </div>
                            @endif

                            <!-- Horário de Início -->
                            <div class="info-row">
                                <span class="info-label">
                                    <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Início:
                                </span>
                                <span class="info-value">{{ $atendimentoAtual->iniciado_em ? $atendimentoAtual->iniciado_em->format('H:i:s') : 'N/D' }}</span>
                            </div>

                            <!-- Tempo Trabalhado -->
                            <div class="tempo-row">
                                <div class="tempo-item">
                                    <p class="tempo-label">Tempo Trabalhado</p>
                                    <p class="tempo-valor tempo-trabalhado"
                                       data-segundos="{{ $tecnicoData['total_tempo_trabalhado'] }}"
                                       data-iniciado-em="{{ $atendimentoAtual && $atendimentoAtual->em_execucao && $atendimentoAtual->iniciado_em ? $atendimentoAtual->iniciado_em->format('Y-m-d H:i:s') : '' }}"
                                       data-em-execucao="{{ $atendimentoAtual && $atendimentoAtual->em_execucao ? 'true' : 'false' }}">
                                        {{ gmdate('H:i:s', $tecnicoData['total_tempo_trabalhado']) }}
                                    </p>
                                </div>
                                <div class="tempo-item">
                                    <p class="tempo-label">Tempo Pausas</p>
                                    <p class="tempo-valor tempo-pausas"
                                       data-segundos="{{ $tecnicoData['total_tempo_pausas'] }}"
                                       data-em-pausa="{{ $pausaAtiva ? 'true' : 'false' }}">
                                        {{ gmdate('H:i:s', $tecnicoData['total_tempo_pausas'] + $tecnicoData['tempo_pausa_atual']) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Botão Ver Detalhes -->
                            <button class="btn-ver-detalhes" onclick="abrirDetalhes({{ $atendimentoAtual->id }})">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver Detalhes Completos
                            </button>
                        @else
                            <div class="tecnico-livre-msg">
                                <svg class="libre-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <p>Sem atendimento em andamento</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p>Nenhum técnico com atendimento hoje</p>
                </div>
            @endforelse
        </div>
    </div>

            <!-- Atendimentos Não Iniciados -->
            @if($atendimentosNaoIniciados->count() > 0)
            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Atendimentos Agendados Não Iniciados ({{ $atendimentosNaoIniciados->count() }})
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3">Técnico</th>
                                <th class="px-4 py-3">Cliente</th>
                                <th class="px-4 py-3">Assunto</th>
                                <th class="px-4 py-3">Prioridade</th>
                                <th class="px-4 py-3">Agendado Para</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atendimentosNaoIniciados as $atendimento)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $atendimento->funcionario->user->name ?? 'N/D' }}</td>
                                <td class="px-4 py-3">{{ $atendimento->cliente->nome_fantasia ?? 'N/D' }}</td>
                                <td class="px-4 py-3">{{ $atendimento->assunto->nome ?? 'N/D' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $atendimento->prioridade === 'alta' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $atendimento->prioridade === 'media' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $atendimento->prioridade === 'baixa' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ strtoupper($atendimento->prioridade) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $atendimento->data_atendimento ? \Carbon\Carbon::parse($atendimento->data_atendimento)->format('d/m/Y H:i') : 'N/D' }}</td>
                                <td class="px-4 py-3">
                                    @if($atendimento->data_atendimento < now())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ATRASADO</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">AGUARDANDO</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- ================= MODAL DE ATENDIMENTOS ================= --}}
        <div x-show="modalAberto"
            x-cloak
            @keydown.escape.window="fecharModal()"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;">

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

            {{-- Modal --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalAberto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.stop
                    class="relative bg-white rounded-xl shadow-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden">

                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span x-text="tituloModal"></span>
                        </h3>
                        <button @click="fecharModal()" class="text-white hover:text-gray-200 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Loading State --}}
                    <div x-show="carregando" class="flex items-center justify-center p-12">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-indigo-600 mx-auto mb-4"></div>
                            <p class="text-gray-600 font-medium">Carregando atendimentos...</p>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div x-show="!carregando" class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
                        {{-- Empty State --}}
                        <div x-show="atendimentos.length === 0" class="text-center py-12">
                            <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-500 text-lg">Nenhum atendimento encontrado</p>
                        </div>

                        {{-- Table --}}
                        <div x-show="atendimentos.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Número</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Empresa</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Técnico</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Assunto</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Data</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(atendimento, index) in atendimentos" :key="index">
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900" x-text="atendimento.numero"></td>
                                            <td class="px-4 py-3 text-sm text-gray-700" x-text="atendimento.cliente"></td>
                                            <td class="px-4 py-3 text-sm text-gray-700" x-text="atendimento.empresa"></td>
                                            <td class="px-4 py-3 text-sm text-gray-700" x-text="atendimento.tecnico"></td>
                                            <td class="px-4 py-3 text-sm text-gray-700" x-text="atendimento.assunto"></td>
                                            <td class="px-4 py-3 text-sm">
                                                <span :class="getStatusColor(atendimento.status)"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    x-text="atendimento.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())">
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700" x-text="atendimento.data_atendimento"></td>
                                            <td class="px-4 py-3 text-sm">
                                                <button
                                                    type="button"
                                                    @click="abrirDetalhes(atendimento.id)"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-md transition">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                    Abrir
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div x-show="!carregando" class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-200">
                        <div class="text-sm text-gray-600">
                            Total: <span class="font-bold text-gray-900" x-text="totalAtendimentos"></span> atendimento(s)
                        </div>
                        <div class="flex gap-3">
                            <button @click="fecharModal()"
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Detalhes do Atendimento</h3>
                <button onclick="fecharDetalhes()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 overflow-y-auto" id="modalDetalhesConteudo">
                <!-- Conteúdo carregado via AJAX -->
            </div>
        </div>
    </div>

    <style>
    .tecnico-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid #cbd5e1;
        transition: all 0.3s;
    }

    .tecnico-card.status-execucao {
        border-left-color: #059669;
        background: linear-gradient(to right, rgba(5, 150, 105, 0.05) 0%, white 100%);
    }

    .tecnico-card.status-pausa {
        border-left-color: #f59e0b;
        background: linear-gradient(to right, rgba(245, 158, 11, 0.05) 0%, white 100%);
    }

    .tecnico-card.status-livre {
        border-left-color: #cbd5e1;
    }


    .tecnico-avatar {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        min-height: 36px !important;
        max-width: 36px !important;
        max-height: 36px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .tecnico-avatar svg {
        width: 28px !important;
        height: 28px !important;
        display: block;
    }

    /* Ícones de status dentro do card do técnico */
    .tecnico-card svg {
        width: 32px !important;
        height: 32px !important;
        max-width: 32px !important;
        max-height: 32px !important;
        margin: 0 auto;
        display: block;
    }

    .tempo-valor {
        font-family: 'Courier New', monospace;
    }
    </style>

    <script>
    // Atualizar cronômetros a cada segundo
    setInterval(function() {
        // Atualizar tempos trabalhados
        document.querySelectorAll('.tempo-trabalhado[data-em-execucao="true"]').forEach(el => {
            let baseSegundos = parseInt(el.dataset.segundos) || 0;
            let iniciadoEm = el.dataset.iniciadoEm;
            if (iniciadoEm) {
                let iniciado = new Date(iniciadoEm.replace(/-/g, '/'));
                let agora = new Date();
                let diff = Math.floor((agora - iniciado) / 1000);
                let total = baseSegundos + diff;
                el.textContent = formatarTempo(total);
            } else {
                el.textContent = formatarTempo(baseSegundos);
            }
        });
        
        // Atualizar tempos de pausa
        document.querySelectorAll('.tempo-pausas[data-em-pausa="true"]').forEach(el => {
            let segundos = parseInt(el.dataset.segundos) + 1;
            el.dataset.segundos = segundos;
            el.textContent = formatarTempo(segundos);
        });
    }, 1000);

    // Atualizar dados via AJAX a cada 30 segundos
    setInterval(function() {
        fetch('{{ route('dashboard.tecnico.atualizar') }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar timestamp
                    document.getElementById('timestampAtualizacao').textContent = data.timestamp;
                    
                    // Atualizar indicadores
                    document.getElementById('ind-agendados').textContent = data.indicadores.agendados_hoje;
                    document.getElementById('ind-execucao').textContent = data.indicadores.em_execucao;
                    document.getElementById('ind-pausa').textContent = data.indicadores.em_pausa;
                    document.getElementById('ind-finalizados').textContent = data.indicadores.finalizados_hoje;
                    document.getElementById('ind-tecnicos-ativos').textContent = data.indicadores.tecnicos_ativos;
                    document.getElementById('ind-tecnicos-pausados').textContent = data.indicadores.tecnicos_pausados;
                    
                    // Atualizar status dos técnicos
                    data.tecnicos.forEach(tecnico => {
                        let card = document.querySelector(`.tecnico-card[data-tecnico-id="${tecnico.funcionario_id}"]`);
                        if (card) {
                            // Atualizar classes de status
                            card.classList.remove('status-execucao', 'status-pausa', 'status-livre');
                            if (tecnico.status === 'execucao') {
                                card.classList.add('status-execucao');
                            } else if (tecnico.status === 'pausa') {
                                card.classList.add('status-pausa');
                            } else {
                                card.classList.add('status-livre');
                            }
                        }
                    });
                }
            })
            .catch(error => console.error('Erro ao atualizar:', error));
    }, 30000); // 30 segundos

    // Abrir modal de detalhes
    function abrirDetalhes(atendimentoId) {
        const modal = document.getElementById('modalDetalhes');
        const conteudo = document.getElementById('modalDetalhesConteudo');
        
        conteudo.innerHTML = '<div class="text-center py-10"><p>Carregando...</p></div>';
        modal.style.display = 'flex';
        
        fetch(`/dashboard-tecnico/atendimento/${atendimentoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    conteudo.innerHTML = data.html;
                } else {
                    conteudo.innerHTML = '<p class="text-center text-red-500">Erro ao carregar detalhes</p>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                conteudo.innerHTML = '<p class="text-center text-red-500">Erro ao carregar detalhes</p>';
            });
    }

    // Fechar modal
    function fecharDetalhes() {
        document.getElementById('modalDetalhes').style.display = 'none';
    }

    // Fechar modal ao clicar fora
    document.getElementById('modalDetalhes')?.addEventListener('click', function(e) {
        if (e.target === this) {
            fecharDetalhes();
        }
    });

    // Função auxiliar para formatar tempo
    function formatarTempo(segundos) {
        const h = Math.floor(segundos / 3600);
        const m = Math.floor((segundos % 3600) / 60);
        const s = segundos % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }
    </script>
</x-app-layout>
