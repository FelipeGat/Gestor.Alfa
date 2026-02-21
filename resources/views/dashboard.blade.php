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
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Dashboard Operacional']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 Dashboard Técnico {{ $empresaId ? '- ' . ($empresas->find($empresaId)->nome_fantasia ?? 'Empresa') : '(Global)' }}
            </h2>

            <!-- FILTROS -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center justify-center gap-3">
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
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition text-sm">
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
                
                const response = await fetch(`/dashboard/atendimentos?${params}`);
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
        
        exportarModal() {
            const params = new URLSearchParams();
            if (this.empresaIdFiltro) params.append('empresa_id', this.empresaIdFiltro);
            if (this.statusFiltro) params.append('status_atual', this.statusFiltro);
            params.append('filtro_rapido', this.filtroRapido || 'mes');
            if (this.filtroRapido === 'custom' && this.inicioCustom && this.fimCustom) {
                params.append('inicio_custom', this.inicioCustom);
                params.append('fim_custom', this.fimCustom);
            }
            
            const url = `/dashboard/exportar?${params}`;
            window.open(url, '_blank');
        },
        
        fecharModal() {
            this.modalAberto = false;
            this.atendimentos = [];
        },
        
        getStatusColor(status) {
            const colors = {
                'aberto': 'bg-blue-100 text-blue-800',
                'em_atendimento': 'bg-orange-100 text-orange-800',
                'aguardando_cliente': 'bg-yellow-100 text-yellow-800',
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
                <form method="GET" x-ref="formFiltro" action="{{ route('dashboard') }}">
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

            {{-- ================= CARDS DE RESUMO POR STATUS (CLICÁVEIS) ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
                <div @click="abrirModal('aberto', 'Atendimentos Abertos')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-blue-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Abertos</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosAbertos }}</p>
                </div>
                <div @click="abrirModal('em_atendimento', 'Atendimentos em Atendimento')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-orange-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Em Atendimento</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosEmAtendimento }}</p>
                </div>
                <div @click="abrirModal('aguardando_cliente', 'Aguardando Cliente')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-yellow-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Aguardando Cliente</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosAguardando }}</p>
                </div>
                <div @click="abrirModal('concluido', 'Atendimentos Concluídos')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-green-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Concluídos</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosConcluidos }}</p>
                </div>
                <div @click="abrirModal('cancelado', 'Atendimentos Cancelados')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-gray-500 cursor-pointer hover:shadow-xl hover:scale-105 transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Cancelados</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosCancelados }}</p>
                </div>
            </div>

            {{-- ================= GRÁFICOS ================= --}}
            <div class="space-y-8">
                {{-- Linha 1: Atendimentos por Dia --}}
                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="text-base font-semibold text-gray-700 mb-4">Evolução de Atendimentos por Dia</h3>
                    <div class="h-80"><canvas id="graficoAtendimentosDia"></canvas></div>
                </div>

                {{-- Linha 2: Performance e Status --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white shadow rounded-xl p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Atendimentos por Técnico</h3>
                        <div class="h-80"><canvas id="graficoAtendimentosTecnico"></canvas></div>
                    </div>
                    <div class="bg-white shadow rounded-xl p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Distribuição por Status</h3>
                        <div class="h-80"><canvas id="graficoChamadosStatus"></canvas></div>
                    </div>
                </div>

                {{-- Linha 3: Clientes e Assuntos --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white shadow rounded-xl p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Top 5 Clientes com Mais Chamados</h3>
                        <div id="tabelaTopClientesContainer" class="h-80 overflow-y-auto"></div>
                    </div>
                    <div class="bg-white shadow rounded-xl p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Top 5 Assuntos Recorrentes</h3>
                        <div class="h-80"><canvas id="graficoTopAssuntos"></canvas></div>
                    </div>
                </div>

                {{-- Linha 4: Prioridade e Empresas --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white shadow rounded-xl p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Chamados por Prioridade</h3>
                        <div class="h-80"><canvas id="graficoChamadosPrioridade"></canvas></div>
                    </div>
                    @if(!$empresaId)
                    <div class="bg-white shadow rounded-xl p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Chamados por Empresa</h3>
                        <div class="h-80"><canvas id="graficoChamadosEmpresa"></canvas></div>
                    </div>
                    @endif
                </div>
            </div>
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
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Descrição</th>
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
                                            <td class="px-4 py-3 text-sm text-gray-700" x-text="atendimento.descricao"></td>
                                            <td class="px-4 py-3 text-sm">
                                                <span :class="getStatusColor(atendimento.status_atual)"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    x-text="atendimento.status_atual.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())">
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700" x-text="atendimento.data_atendimento"></td>
                                            <td class="px-4 py-3 text-sm">
                                                <a :href="atendimento.url" target="_blank"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-md transition">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
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
                        <div class="text-sm text-gray-600">
                            Total: <span class="font-bold text-gray-900" x-text="totalAtendimentos"></span> atendimento(s)
                        </div>
                        <div class="flex gap-3">
                            <button @click="exportarModal()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Exportar PDF
                            </button>
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

    @push('scripts')
    <script src="{{ asset('js/vendor/chart.js') }}"></script>
    <script>
        // Funções auxiliares para mostrar estados de erro/vazio
        const mostrarVazio = (containerId, mensagem = 'Sem dados para o filtro atual.') => {
            const container = document.getElementById(containerId)?.parentElement;
            if (container) container.innerHTML = `<div class="flex items-center justify-center h-full text-gray-500">${mensagem}</div>`;
        };
        const validarDados = (dados) => dados && Array.isArray(dados) && dados.length > 0;

        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
            Chart.defaults.plugins.legend.position = 'bottom';

            // 1. Atendimentos por Dia
            const labelsDia = @json($labelsAtendimentosDia ?? []);
            if (validarDados(labelsDia)) {
                new Chart(document.getElementById('graficoAtendimentosDia'), {
                    type: 'line',
                    data: {
                        labels: labelsDia,
                        datasets: [{
                            label: 'Nº de Atendimentos',
                            data: @json($valoresAtendimentosDia ?? []),
                            fill: true,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                mostrarVazio('graficoAtendimentosDia');
            }

            // 2. Atendimentos por Técnico
            const labelsTecnico = @json($labelsAtendimentosTecnico ?? []);
            if (validarDados(labelsTecnico)) {
                new Chart(document.getElementById('graficoAtendimentosTecnico'), {
                    type: 'bar',
                    data: {
                        labels: labelsTecnico,
                        datasets: [{
                            label: 'Atendimentos',
                            data: @json($valoresAtendimentosTecnico ?? []),
                            backgroundColor: '#8b5cf6'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                mostrarVazio('graficoAtendimentosTecnico');
            }

            // 3. Distribuição por Status
            const labelsStatus = @json($labelsAtendimentosStatus ?? []);
            if (validarDados(labelsStatus)) {
                new Chart(document.getElementById('graficoChamadosStatus'), {
                    type: 'doughnut',
                    data: {
                        labels: labelsStatus,
                        datasets: [{
                            data: @json($valoresAtendimentosStatus ?? []),
                            backgroundColor: ['#3b82f6', '#f97316', '#eab308', '#22c55e', '#6b7280']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            } else {
                mostrarVazio('graficoChamadosStatus');
            }

            // 4. Top Clientes (tabela)
            const topClientes = @json($topClientes ?? []);
            const container = document.getElementById('tabelaTopClientesContainer');
            if (topClientes && topClientes.length > 0) {
                let html = '<table class="w-full"><thead><tr class="border-b"><th class="text-left py-2 text-sm font-semibold text-gray-700">Cliente</th><th class="text-right py-2 text-sm font-semibold text-gray-700">Chamados</th></tr></thead><tbody>';
                topClientes.forEach((item, idx) => {
                    html += `<tr class="${idx % 2 === 0 ? 'bg-gray-50' : ''}"><td class="py-2 text-sm text-gray-800">${item.cliente?.nome_fantasia ?? 'N/A'}</td><td class="text-right text-sm font-bold text-indigo-600">${item.total}</td></tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Sem dados</div>';
            }

            // 5. Top Assuntos
            const labelsAssuntos = @json($labelsTopAssuntos ?? []);
            if (validarDados(labelsAssuntos)) {
                new Chart(document.getElementById('graficoTopAssuntos'), {
                    type: 'bar',
                    data: {
                        labels: labelsAssuntos,
                        datasets: [{
                            label: 'Chamados',
                            data: @json($valoresTopAssuntos ?? []),
                            backgroundColor: '#06b6d4'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                mostrarVazio('graficoTopAssuntos');
            }

            // 6. Prioridade
            const labelsPrioridade = @json($labelsChamadosPrioridade ?? []);
            if (validarDados(labelsPrioridade)) {
                new Chart(document.getElementById('graficoChamadosPrioridade'), {
                    type: 'pie',
                    data: {
                        labels: labelsPrioridade,
                        datasets: [{
                            data: @json($valoresChamadosPrioridade ?? []),
                            backgroundColor: ['#ef4444', '#f59e0b', '#22c55e', '#3b82f6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            } else {
                mostrarVazio('graficoChamadosPrioridade');
            }

            // 7. Empresas
            const labelsEmpresas = @json($labelsChamadosEmpresa ?? []);
            if (validarDados(labelsEmpresas)) {
                new Chart(document.getElementById('graficoChamadosEmpresa'), {
                    type: 'bar',
                    data: {
                        labels: labelsEmpresas,
                        datasets: [{
                            label: 'Chamados',
                            data: @json($valoresChamadosEmpresa ?? []),
                            backgroundColor: '#a855f7'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>