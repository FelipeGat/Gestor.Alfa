<x-app-layout>
    @push('styles')
    @vite('resources/css/dashboard/dashboard_comercial.css')
    <style>
        [x-cloak] {
            display: none !important;
        }
        /* Status Badges - Igual página de orçamentos */
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: uppercase;
            min-width: 160px;
            white-space: nowrap;
        }
        .status-badge-em_elaboracao { background-color: #f3f4f6; color: #374151; }
        .status-badge-aguardando_aprovacao { background-color: #fef3c7; color: #92400e; }
        .status-badge-aprovado { background-color: #dcfce7; color: #166534; }
        .status-badge-aguardando_pagamento { background-color: #fef9c3; color: #854d0e; }
        .status-badge-concluido { background-color: #dcfce7; color: #15803d; }
        .status-badge-recusado { background-color: #fee2e2; color: #991b1b; }
        .status-badge-agendado { background-color: #ede9fe; color: #5b21b6; }
        .status-badge-em_andamento { background-color: #e0f2fe; color: #075985; }
        .status-badge-garantia { background-color: #ffedd5; color: #9a3412; }
        .status-badge-financeiro { background-color: #fee2e2; color: #dc2626; }
        .status-badge-cancelado { background-color: #f3f4f6; color: #6b7280; text-decoration: line-through; }
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
        .kpi-card-indigo {
            border-color: #6366f1;
        }
        .kpi-card-emerald {
            border-color: #10b981;
        }
        .kpi-card-amber {
            border-color: #f59e0b;
        }
        .kpi-card-blue {
            border-color: #2563eb;
        }
        .kpi-card-green {
            border-color: #16a34a;
        }
        /* Botões de Filtro Rápido */
        .btn-filtro-rapido {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.2s;
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
        .btn-filtro-rapido.inativo:hover {
            background: #e5e7eb;
        }
        /* Cards dos Gráficos */
        .card-grafico {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        /* Métricas Filtro Atual */
        .metricas-filtro-atual {
            background: #3f9cae;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2rem;
            margin-bottom: 2.5rem;
        }
        .metricas-filtro-atual:hover {
            background: #358a96;
        }
    </style>
    @endpush
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Comercial', 'url' => route('comercial.index')],
            ['label' => 'Dashboard Comercial']
        ]" />
    </x-slot>

    <!-- FILTROS -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <form action="{{ url()->current() }}" method="GET" class="filters-card p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                <div class="flex flex-col lg:col-span-4">
                    <label class="text-sm font-medium text-gray-700 mb-2">
                        Empresa
                    </label>
                    <select name="empresa_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                        <option value="">Todas as Empresas</option>
                        @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ $empresaId == $empresa->id ? 'selected' : '' }}>
                            {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col lg:col-span-4">
                    <label class="text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]">
                        <option value="">Todos os Status</option>
                        @foreach($todosStatus as $st)
                        <option value="{{ $st }}" {{ $statusFiltro == $st ? 'selected' : '' }}>
                            {{ ucfirst($st) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 lg:col-span-4">
                    <button type="submit" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #3f9cae; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.5rem; color: white; font-weight: 500; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(63, 156, 174, 0.3);" onmouseover="this.style.background='#358a96'; this.style.boxShadow='0 4px 6px rgba(63, 156, 174, 0.4)'" onmouseout="this.style.background='#3f9cae'; this.style.boxShadow='0 2px 4px rgba(63, 156, 174, 0.3)'">
                        <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                        </svg>
                        Filtrar
                    </button>
                    <a href="{{ route('dashboard.comercial') }}" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #9ca3af; border-radius: 9999px; box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3); display: inline-flex; align-items: center; gap: 0.5rem; color: white; font-weight: 500; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px rgba(156, 163, 175, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(156, 163, 175, 0.3)'">
                        <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- DADOS PARA O JS (Evita erro de sintaxe no editor) --}}
    <div id="dashboard-json-data"
        data-status='@json($statusCount)'
        data-empresas='@json($orcamentosPorEmpresa)'
        style="display: none;">
    </div>

    <div class="pb-8 pt-4" x-data="{
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
            }" class="mb-6 filters-card p-6">
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
                                    :class="filtroRapido === 'mes_anterior' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
                                    Mês Anterior
                                </button>
                                <button type="button"
                                    @click="aplicarFiltro('dia')"
                                    :class="filtroRapido === 'dia' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
                                    Dia
                                </button>
                                <button type="button"
                                    @click="aplicarFiltro('semana')"
                                    :class="filtroRapido === 'semana' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
                                    Semana
                                </button>
                                <button type="button"
                                    @click="aplicarFiltro('mes')"
                                    :class="filtroRapido === 'mes' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
                                    Mês
                                </button>
                                <button type="button"
                                    @click="aplicarFiltro('ano')"
                                    :class="filtroRapido === 'ano' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
                                    Ano
                                </button>
                                <button type="button"
                                    @click="aplicarFiltro('custom')"
                                    :class="filtroRapido === 'custom' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
                                    Outro período
                                </button>
                            </div>
                            <div class="flex gap-2 items-center">
                                <input type="hidden" name="origem" :value="origemFiltro">
                                <button type="button"
                                    @click="aplicarOrigem('todos')"
                                    :class="origemFiltro === 'todos' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
                                    TODOS
                                </button>
                                <button type="button"
                                    @click="aplicarOrigem('contrato')"
                                    :class="origemFiltro === 'contrato' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
                                    CONTRATOS
                                </button>
                                <button type="button"
                                    @click="aplicarOrigem('avulso')"
                                    :class="origemFiltro === 'avulso' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="btn-filtro-rapido">
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
                class="metricas-filtro-atual cursor-pointer transition-all transform hover:scale-[1.02]">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <h3 class="text-white text-lg font-medium flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Resultado do Filtro Atual (clique para ver detalhes)
                        </h3>
                        <p class="text-sm text-white">
                            Status: <strong>{{ $statusFiltro ?: 'Todos' }}</strong> |
                            Empresa: <strong>{{ $empresaId ? ($empresas->find($empresaId)->nome_fantasia ?? 'Selecionada') : 'Todas' }}</strong>
                        </p>
                    </div>
                    <div class="flex gap-12">
                        <div class="text-center">
                            <span class="block text-white text-xs uppercase font-bold">Quantidade</span>
                            <span class="text-4xl font-black text-white">{{ $metricasFiltradas->qtd ?? 0 }}</span>
                        </div>
                        <div class="text-center border-l border-white/50 pl-12">
                            <span class="block text-white text-xs uppercase font-bold">Valor Total</span>
                            <span class="text-4xl font-black text-white">R$ {{ number_format($metricasFiltradas->valor_total ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= CARDS DE RESUMO ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">

                <div @click="abrirModal('aguardando_aprovacao', 'Aguardando Aprovação')"
                    class="kpi-card kpi-card-indigo cursor-pointer hover:shadow-xl transition-all transform hover:scale-105 p-6">
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
                    class="kpi-card kpi-card-emerald cursor-pointer hover:shadow-xl transition-all transform hover:scale-105 p-6">
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
                    class="kpi-card kpi-card-amber cursor-pointer hover:shadow-xl transition-all transform hover:scale-105 p-6">
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
                    class="kpi-card kpi-card-blue cursor-pointer hover:shadow-xl transition-all transform hover:scale-105 p-6">
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
                    class="kpi-card kpi-card-green cursor-pointer hover:shadow-xl transition-all transform hover:scale-105 p-6">
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

                <div class="card-grafico p-6">
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

                <div class="card-grafico p-6">
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

            <div class="card-grafico p-6 mb-10">
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
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full"
                    style="background: white; border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-radius: 0.5rem;">

                    <!-- Header -->
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900" style="font-family: 'Inter', sans-serif; font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;" x-text="tituloModal"></h3>
                                <p class="text-sm text-gray-600" style="font-size: 0.875rem;">
                                    <span x-text="totalOrcamentos"></span> orçamento(s) |
                                    Valor Total: R$ <span x-text="valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                                </p>
                            </div>
                            <button @click="fecharModal()" class="text-gray-400 hover:text-red-600 transition">
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
                        <div x-show="!carregando && orcamentos.length > 0" class="rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                            <div class="overflow-x-auto">
                                <table class="w-full table-auto">
                                    <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                                        <tr>
                                            <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);">Número</th>
                                            <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);">Cliente</th>
                                            <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);">Empresa</th>
                                            <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);">Vendedor</th>
                                            <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);">Valor</th>
                                            <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);">Status</th>
                                            <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);">Data</th>
                                            <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <template x-for="orc in orcamentos" :key="orc.id">
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-4 py-3 text-sm" style="font-weight: 400; color: rgb(17, 24, 39);" x-text="orc.numero"></td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span style="font-weight: 400; color: rgb(17, 24, 39);" x-text="orc.cliente"></span>
                                                </td>
                                                <td class="px-4 py-3 text-sm" style="font-weight: 400; color: rgb(17, 24, 39);" x-text="orc.empresa"></td>
                                                <td class="px-4 py-3 text-sm" style="font-weight: 400; color: rgb(17, 24, 39);" x-text="orc.vendedor"></td>
                                                <td class="px-4 py-3 text-sm font-semibold whitespace-nowrap" style="font-weight: 400; color: rgb(17, 24, 39);">
                                                    R$ <span x-text="orc.valor_total"></span>
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="status-badge"
                                                        :class="'status-badge-' + orc.status">
                                                        <span x-text="orc.status_label"></span>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm" style="font-weight: 400; color: rgb(17, 24, 39);" x-text="orc.data"></td>
                                                <td class="px-4 py-3 text-sm">
                                                    <div class="flex items-center gap-1">
                                                        <button @click="abrirModalHistorico(orc)"
                                                            class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition"
                                                            title="Histórico">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </button>
                                                        <a :href="orc.url"
                                                            target="_blank"
                                                            class="inline-flex items-center justify-center w-8 h-8 bg-gray-800 hover:bg-gray-900 text-white rounded-full transition"
                                                            title="Imprimir">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z"></path>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
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
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3" style="border-top: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <button @click="exportarModal()"
                            class="px-4 py-2 text-white rounded-lg transition font-medium flex items-center gap-2"
                            style="background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3); font-size: 0.875rem; min-width: 140px; justify-content: center;"
                            onmouseover="this.style.boxShadow='0 4px 6px rgba(34, 197, 94, 0.4)'"
                            onmouseout="this.style.boxShadow='0 2px 4px rgba(34, 197, 94, 0.3)'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar PDF
                        </button>
                        <button @click="fecharModal()"
                            class="px-4 py-2 text-white rounded-lg transition font-medium flex items-center gap-2"
                            style="background: #ef4444; border-radius: 9999px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3); font-size: 0.875rem; min-width: 140px; justify-content: center;"
                            onmouseover="this.style.boxShadow='0 4px 6px rgba(239, 68, 68, 0.4)'"
                            onmouseout="this.style.boxShadow='0 2px 4px rgba(239, 68, 68, 0.3)'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
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
                    style="border: 1px solid #3f9cae; border-top-width: 4px;"
                    @click.stop>

                    <!-- Header -->
                    <div class="px-6 py-4" style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #e5e7eb;">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold" style="font-family: 'Inter', sans-serif; font-size: 1.125rem; font-weight: 600; color: #111827;">
                                <span class="flex items-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Histórico do Orçamento <span x-text="orcamentoAtual?.numero"></span>
                                </span>
                            </h3>
                            <button @click="fecharModalHistorico()" class="text-gray-400 hover:text-red-600 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        <!-- Formulário para nova observação -->
                        <div class="mb-6 p-4 rounded-lg" style="background-color: #f9fafb;">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nova Observação
                            </label>
                            <textarea x-model="novaObservacao"
                                rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#3f9cae] focus:ring focus:ring-[#3f9cae] focus:ring-opacity-20"
                                placeholder="Digite a observação sobre o contato com o cliente..."></textarea>
                            <div class="mt-2 flex justify-end">
                                <button @click="salvarHistorico()"
                                    :disabled="salvandoHistorico"
                                    class="px-4 py-2 text-white rounded-lg transition font-medium flex items-center gap-2"
                                    style="background: #3f9cae; border-radius: 9999px; box-shadow: 0 2px 4px rgba(63, 156, 174, 0.3); font-size: 0.875rem; min-width: 140px; justify-content: center;"
                                    onmouseover="this.style.background='#358a96'; this.style.boxShadow='0 4px 6px rgba(63, 156, 174, 0.4)'"
                                    onmouseout="this.style.background='#3f9cae'; this.style.boxShadow='0 2px 4px rgba(63, 156, 174, 0.3)'"
                                    :class="salvandoHistorico ? 'opacity-50 cursor-not-allowed' : ''">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span x-show="!salvandoHistorico">Salvar</span>
                                    <span x-show="salvandoHistorico">Salvando...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Loading -->
                        <div x-show="carregandoHistoricos" class="text-center py-8">
                            <svg class="animate-spin h-8 w-8 text-[#3f9cae] mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-500 mt-2">Carregando históricos...</p>
                        </div>

                        <!-- Lista de históricos -->
                        <div x-show="!carregandoHistoricos && historicos.length > 0">
                            <h4 class="text-lg font-semibold" style="font-family: 'Inter', sans-serif; font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 1rem;">Histórico de Contatos</h4>
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
                    <div class="bg-gray-50 px-6 py-4 flex justify-end" style="border-top: 1px solid #e5e7eb;">
                        <button @click="fecharModalHistorico()"
                            class="px-4 py-2 text-white rounded-lg transition font-medium flex items-center gap-2"
                            style="background: #ef4444; border-radius: 9999px; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3); font-size: 0.875rem; min-width: 140px; justify-content: center;"
                            onmouseover="this.style.boxShadow='0 4px 6px rgba(239, 68, 68, 0.4)'"
                            onmouseout="this.style.boxShadow='0 2px 4px rgba(239, 68, 68, 0.3)'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/vendor/chart.js') }}"></script>
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