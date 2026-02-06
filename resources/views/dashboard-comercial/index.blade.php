<x-app-layout>
    @push('styles')
    @vite('resources/css/dashboard/dashboard_comercial.css')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @endpush
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📈 Dashboard Comercial {{ $empresaId ? '- ' . ($empresas->find($empresaId)->nome_fantasia ?? 'Empresa') : '(Global)' }}
            </h2>

            <!-- FILTROS -->
            <form action="{{ url()->current() }}" method="GET" class="flex flex-wrap gap-3">
                <select name="empresa_id" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todas as Empresas</option>
                    @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}" {{ $empresaId == $empresa->id ? 'selected' : '' }}>
                        {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                    </option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todos os Status</option>
                    @foreach($todosStatus as $st)
                    <option value="{{ $st }}" {{ $statusFiltro == $st ? 'selected' : '' }}>
                        {{ ucfirst($st) }}
                    </option>
                    @endforeach
                </select>

                @if($empresaId || $statusFiltro)
                <a href="{{ url()->current() }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    Limpar
                </a>
                @endif
            </form>
        </div>
    </x-slot>

    {{-- DADOS PARA O JS (Evita erro de sintaxe no editor) --}}
    <div id="dashboard-json-data"
        data-status='@json($statusCount)'
        data-empresas='@json($orcamentosPorEmpresa)'
        style="display: none;">
    </div>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="{
        modalAberto: false,
        carregando: false,
        tituloModal: '',
        orcamentos: [],
        totalOrcamentos: 0,
        valorTotal: 0,
        empresaIdFiltro: '{{ $empresaId ?? '' }}',
        statusFiltro: '{{ $statusFiltro ?? '' }}',
        filtroRapido: '{{ $filtroRapido ?? '' }}',
        origemFiltro: '{{ request('origem', 'todos') }}',
        inicioCustom: '{{ request('inicio', $inicio?->format('Y-m-d')) ?? '' }}',
        fimCustom: '{{ request('fim', $fim?->format('Y-m-d')) ?? '' }}',
        modalHistoricoAberto: false,
        historicos: [],
        carregandoHistoricos: false,
        orcamentoAtual: null,
        novaObservacao: '',
        salvandoHistorico: false,
        
        async abrirModal(status, titulo) {
            this.statusFiltro = status;
            this.tituloModal = titulo;
            this.modalAberto = true;
            this.carregando = true;
            
            try {
                const params = new URLSearchParams();
                if (this.empresaIdFiltro) params.append('empresa_id', this.empresaIdFiltro);
                if (status) params.append('status', status);
                // Adicionar filtros de data
                params.append('filtro_rapido', this.filtroRapido || 'mes');
                if (this.filtroRapido === 'custom' && this.inicioCustom && this.fimCustom) {
                    params.append('inicio', this.inicioCustom);
                    params.append('fim', this.fimCustom);
                }
                // Adicionar filtro de origem
                params.append('origem', this.origemFiltro || 'todos');
                const response = await fetch(`{{ route('dashboard.comercial.orcamentos') }}?${params}`);
                const data = await response.json();
                if (data.success) {
                    this.orcamentos = data.orcamentos;
                    this.totalOrcamentos = data.total;
                    this.valorTotal = data.valor_total;
                }
            } catch (error) {
                console.error('Erro ao buscar orçamentos:', error);
                alert('Erro ao carregar orçamentos. Tente novamente.');
            } finally {
                this.carregando = false;
            }
        },
        
        exportarModal() {
            const params = new URLSearchParams();
            if (this.empresaIdFiltro) params.append('empresa_id', this.empresaIdFiltro);
            if (this.statusFiltro) params.append('status', this.statusFiltro);
            params.append('filtro_rapido', this.filtroRapido || 'mes');
            if (this.filtroRapido === 'custom' && this.inicioCustom && this.fimCustom) {
                params.append('inicio', this.inicioCustom);
                params.append('fim', this.fimCustom);
            }
            
            const url = `{{ route('dashboard.comercial.exportar') }}?${params}`;
            console.log('URL de exportação:', url);
            window.open(url, '_blank');
        },
        
        fecharModal() {
            this.modalAberto = false;
            this.orcamentos = [];
        },
        
        getStatusColor(status) {
            const colors = {
                'aguardando_aprovacao': 'bg-indigo-100 text-indigo-800',
                'financeiro': 'bg-amber-100 text-amber-800',
                'aprovado': 'bg-emerald-100 text-emerald-800',
                'aguardando_pagamento': 'bg-blue-100 text-blue-800',
                'concluido': 'bg-green-100 text-green-800',
                'reprovado': 'bg-red-100 text-red-800',
                'cancelado': 'bg-gray-100 text-gray-800',
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        },
        
        async abrirModalHistorico(orcamento) {
            this.orcamentoAtual = orcamento;
            this.modalHistoricoAberto = true;
            this.carregandoHistoricos = true;
            this.novaObservacao = '';
            
            try {
                const response = await fetch(`/dashboard-comercial/orcamentos/${orcamento.id}/historicos`);
                const data = await response.json();
                
                if (data.success) {
                    this.historicos = data.historicos;
                }
            } catch (error) {
                console.error('Erro ao buscar históricos:', error);
                alert('Erro ao carregar históricos. Tente novamente.');
            } finally {
                this.carregandoHistoricos = false;
            }
        },
        
        async salvarHistorico() {
            if (!this.novaObservacao.trim()) {
                alert('Por favor, preencha a observação.');
                return;
            }
            
            this.salvandoHistorico = true;
            
            try {
                const response = await fetch(`/dashboard-comercial/orcamentos/${this.orcamentoAtual.id}/historicos`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        observacao: this.novaObservacao
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.historicos.unshift(data.historico);
                    this.novaObservacao = '';
                    alert('Histórico adicionado com sucesso!');
                } else {
                    alert(data.message || 'Erro ao salvar histórico.');
                }
            } catch (error) {
                console.error('Erro ao salvar histórico:', error);
                alert('Erro ao salvar histórico. Tente novamente.');
            } finally {
                this.salvandoHistorico = false;
            }
        },
        
        fecharModalHistorico() {
            this.modalHistoricoAberto = false;
            this.historicos = [];
            this.orcamentoAtual = null;
            this.novaObservacao = '';
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= FILTRO RÁPIDO DE PERÍODO ================= --}}
            <div x-data="{
                filtroRapido: '{{ $filtroRapido }}',
                mostrarCustom: {{ $filtroRapido === 'custom' ? 'true' : 'false' }},
                origemFiltro: '{{ request('origem', 'todos') }}',
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
                },
                aplicarOrigem(origem) {
                    this.origemFiltro = origem;
                    const form = this.$refs.formFiltro;
                    form.querySelector('input[name=origem]').value = origem;
                    setTimeout(() => form.submit(), 10);
                }
            }" class="mb-6 bg-white rounded-xl shadow-sm p-6">
                <form method="GET" x-ref="formFiltro" action="{{ route('dashboard.comercial') }}">
                    @if($empresaId)
                    <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                    @endif
                    @if($statusFiltro)
                    <input type="hidden" name="status" value="{{ $statusFiltro }}">
                    @endif
                    <input type="hidden" name="filtro_rapido" :value="filtroRapido">

                    <div class="flex flex-wrap items-center gap-3">

                        <div class="flex w-full justify-between items-center gap-3">
                            <div class="flex gap-3 items-center">
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
                            <div class="flex gap-2 items-center">
                                <input type="hidden" name="origem" :value="origemFiltro">
                                <button type="button"
                                    @click="aplicarOrigem('todos')"
                                    :class="origemFiltro === 'todos' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-2 rounded-md text-xs font-medium transition">
                                    TODOS
                                </button>
                                <button type="button"
                                    @click="aplicarOrigem('contrato')"
                                    :class="origemFiltro === 'contrato' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-2 rounded-md text-xs font-medium transition">
                                    CONTRATOS
                                </button>
                                <button type="button"
                                    @click="aplicarOrigem('avulso')"
                                    :class="origemFiltro === 'avulso' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-2 rounded-md text-xs font-medium transition">
                                    AVULSO
                                </button>
                            </div>
                        </div>
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

            {{-- ================= MÉTRICAS FILTRADAS (QTD E VALOR) ================= --}}
            <div @click="abrirModal('{{ $statusFiltro }}', 'Filtro Atual: {{ $statusFiltro ? ucfirst($statusFiltro) : "Todos os Status" }}')"
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
                            Status: <strong>{{ $statusFiltro ?: 'Todos' }}</strong> |
                            Empresa: <strong>{{ $empresaId ? ($empresas->find($empresaId)->nome_fantasia ?? 'Selecionada') : 'Todas' }}</strong>
                        </p>
                    </div>
                    <div class="flex gap-12">
                        <div class="text-center">
                            <span class="block text-indigo-200 text-xs uppercase font-bold">Quantidade</span>
                            <span class="text-4xl font-black">{{ $metricasFiltradas->qtd ?? 0 }}</span>
                        </div>
                        <div class="text-center border-l border-indigo-500 pl-12">
                            <span class="block text-indigo-200 text-xs uppercase font-bold">Valor Total</span>
                            <span class="text-4xl font-black">R$ {{ number_format($metricasFiltradas->valor_total ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= CARDS DE RESUMO ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">

                <div @click="abrirModal('aguardando_aprovacao', 'Aguardando Aprovação')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-indigo-400 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider flex items-center justify-between">
                        Aguardando Aprovação
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $qtdAguardando }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>

                <div @click="abrirModal('aprovado', 'Aprovado')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-emerald-500 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider flex items-center justify-between">
                        Aprovado
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $qtdAprovado }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>

                <div @click="abrirModal('financeiro', 'Financeiro')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-amber-500 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider flex items-center justify-between">
                        Financeiro
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $qtdFinanceiro }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>

                <div @click="abrirModal('aguardando_pagamento', 'Aguardando Pagamento')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-blue-600 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider flex items-center justify-between">
                        Aguardando Pagamento
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $qtdAguardandoPagamento }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>

                <div @click="abrirModal('concluido', 'Concluído')"
                    class="bg-white shadow rounded-xl p-6 border-l-4 border-green-600 cursor-pointer hover:shadow-xl transition-all transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider flex items-center justify-between">
                        Concluído
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $qtdConcluido }}</p>
                    <p class="text-xs text-gray-400 mt-2">Clique para ver detalhes</p>
                </div>

            </div>

            {{-- ================= GRÁFICOS ================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">

                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                        </svg>
                        Distribuição por Status
                    </h3>
                    <div class="h-72">
                        <canvas id="chartStatus"></canvas>
                    </div>
                </div>

                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4m-5 10h.01M15 7h.01M15 11h.01M15 15h.01M15 19h.01M9 15h.01M9 19h.01"></path>
                        </svg>
                        Orçamentos por Empresa (Quantidade)
                    </h3>
                    <div class="h-72">
                        <canvas id="chartQtdaEmpresa"></canvas>
                    </div>
                </div>

            </div>

            <div class="bg-white shadow rounded-xl p-6 mb-10">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Volume Financeiro por Empresa (Valor Total)
                </h3>
                <div class="h-96">
                    <canvas id="chartEmpresaValor"></canvas>
                </div>
            </div>

        </div>

        {{-- ================= MODAL DE ORÇAMENTOS ================= --}}
        <div x-show="modalAberto"
            x-cloak
            @click.away="fecharModal()"
            @keydown.escape.window="fecharModal()"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div x-show="modalAberto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Content -->
                <div x-show="modalAberto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">

                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white" x-text="tituloModal"></h3>
                                <p class="text-sm text-indigo-100 mt-1">
                                    <span x-text="totalOrcamentos"></span> orçamento(s) |
                                    Valor Total: R$ <span x-text="valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                                </p>
                            </div>
                            <button @click="fecharModal()" class="text-white hover:text-indigo-200 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                        <!-- Loading -->
                        <div x-show="carregando" class="text-center py-12">
                            <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-500 mt-4">Carregando orçamentos...</p>
                        </div>

                        <!-- Tabela de Orçamentos -->
                        <div x-show="!carregando && orcamentos.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendedor</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="orc in orcamentos" :key="orc.id">
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="orc.numero"></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700" x-text="orc.cliente"></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700" x-text="orc.empresa"></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700" x-text="orc.vendedor"></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                R$ <span x-text="orc.valor_total"></span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                                    :class="getStatusColor(orc.status)"
                                                    x-text="orc.status_label"></span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500" x-text="orc.data"></td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <div class="flex items-center gap-2">
                                                    <button @click="abrirModalHistorico(orc)"
                                                        class="text-blue-600 hover:text-blue-900 font-medium flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        Histórico
                                                    </button>
                                                    <a :href="orc.url"
                                                        target="_blank"
                                                        class="text-indigo-600 hover:text-indigo-900 font-medium flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                        </svg>
                                                        Imprimir
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mensagem vazia -->
                        <div x-show="!carregando && orcamentos.length === 0" class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-500">Nenhum orçamento encontrado para este filtro.</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button @click="exportarModal()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar PDF
                        </button>
                        <button @click="fecharModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Histórico -->
        <div x-show="modalHistoricoAberto"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            @click.self="fecharModalHistorico()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="modalHistoricoAberto"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-90"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-90"
                    class="relative bg-white rounded-lg shadow-xl w-full max-w-3xl"
                    @click.stop>

                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-lg">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Histórico do Orçamento <span x-text="orcamentoAtual?.numero"></span></span>
                            </h3>
                            <button @click="fecharModalHistorico()" class="text-white hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        <!-- Formulário para nova observação -->
                        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nova Observação
                            </label>
                            <textarea x-model="novaObservacao"
                                rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                placeholder="Digite a observação sobre o contato com o cliente..."></textarea>
                            <div class="mt-2 flex justify-end">
                                <button @click="salvarHistorico()"
                                    :disabled="salvandoHistorico"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition">
                                    <span x-show="!salvandoHistorico">Salvar</span>
                                    <span x-show="salvandoHistorico">Salvando...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Loading -->
                        <div x-show="carregandoHistoricos" class="text-center py-8">
                            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-500 mt-2">Carregando históricos...</p>
                        </div>

                        <!-- Lista de históricos -->
                        <div x-show="!carregandoHistoricos && historicos.length > 0">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Histórico de Contatos</h4>
                            <div class="space-y-3">
                                <template x-for="hist in historicos" :key="hist.id">
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-sm font-medium text-gray-900" x-text="hist.usuario"></span>
                                            <span class="text-xs text-gray-500" x-text="hist.data"></span>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="hist.observacao"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Mensagem vazia -->
                        <div x-show="!carregandoHistoricos && historicos.length === 0" class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-500">Nenhum histórico encontrado.</p>
                            <p class="text-sm text-gray-400 mt-1">Adicione a primeira observação acima.</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-end rounded-b-lg">
                        <button @click="fecharModalHistorico()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Recupera os dados do elemento HTML (Evita erro de sintaxe no editor)
            const dataElement = document.getElementById('dashboard-json-data');
            const statusData = JSON.parse(dataElement.getAttribute('data-status') || '{}');
            const empresaData = JSON.parse(dataElement.getAttribute('data-empresas') || '[]');

            // 1. Gráfico de Status
            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#f59e0b', '#10b981', '#6366f1', '#3b82f6', '#ef4444', '#6b7280'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // 2. Gráfico de Quantidade por Empresa
            new Chart(document.getElementById('chartQtdaEmpresa'), {
                type: 'bar',
                data: {
                    labels: empresaData.map(e => e.empresa ? (e.empresa.nome_fantasia || e.empresa.razao_social) : 'N/A'),
                    datasets: [{
                        label: 'Quantidade',
                        data: empresaData.map(e => e.total_qtd),
                        backgroundColor: '#10b981'
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
                    }
                }
            });

            // 3. Gráfico de Valor por Empresa
            new Chart(document.getElementById('chartEmpresaValor'), {
                type: 'bar',
                data: {
                    labels: empresaData.map(e => e.empresa ? (e.empresa.nome_fantasia || e.empresa.razao_social) : 'N/A'),
                    datasets: [{
                        label: 'Valor Total (R$)',
                        data: empresaData.map(e => e.total_valor),
                        backgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Valor: R$ ' + context.raw.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>