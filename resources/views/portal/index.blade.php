<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <div class="portal-wrapper">

        {{-- Sessão Bem Vindo (no topo) --}}
        <div class="portal-welcome-card">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="portal-header-title">
                        Bem-vindo, {{ auth()->user()->name }}!
                    </h1>
                    <p class="portal-header-subtitle">
                        Gerencie seu Financeiro, Atendimentos e Documentos de forma centralizada e segura.
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        Unidade:
                        <span class="font-semibold text-[#3f9cae]">
                            {{ $cliente->nome_exibicao }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    @if(auth()->user()->clientes()->count() > 1)
                    <form method="POST" action="{{ route('portal.trocar-unidade') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2
                                   bg-[#3f9cae] hover:bg-[#2d7a8a]
                                   text-white text-sm font-semibold
                                   rounded-lg border-0 shadow transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 7a2 2 0 100-4 2 2 0 000 4zm0 0H4m16 0a2 2 0 100 4 2 2 0 000-4zm0 0h4"></path>
                            </svg>
                            <span class="hidden sm:inline">Trocar Unidade</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Navigation Cards --}}
        <div class="portal-nav-grid">

            <!-- Card Financeiro -->
            <a href="{{ route('portal.financeiro') }}" class="portal-nav-card">
                <div class="portal-nav-card-icon-wrapper">
                    <div class="portal-nav-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2
                             3 .895 3 2-1.343 2-3 2m0-8
                             c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1
                             c-1.11 0-2.08-.402-2.599-1M21 12
                             a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="portal-nav-card-title">
                    Acessar Meu Financeiro
                </h3>
                <p class="portal-nav-card-description">
                    Visualize boletos, notas fiscais, pagamentos e acompanhe seu histórico financeiro.
                </p>
            </a>

            <!-- Card Atendimentos -->
            <a href="{{ route('portal.atendimentos') }}" class="portal-nav-card">
                <div class="portal-nav-card-icon-wrapper">
                    <div class="portal-nav-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="portal-nav-card-title">
                    Acessar Minhas OS
                </h3>
                <p class="portal-nav-card-description">
                    Acompanhe suas ordens de serviço, atendimentos e solicitações.
                </p>
            </a>

            <!-- Card Ativos Técnicos -->
            <a href="{{ route('portal.ativos.index') }}" class="portal-nav-card">
                <div class="portal-nav-card-icon-wrapper">
                    <div class="portal-nav-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                </div>
                <h3 class="portal-nav-card-title">
                    Meus Ativos Técnicos
                </h3>
                <p class="portal-nav-card-description">
                    Gerencie ativos técnicos, manutenções, limpezas e abra chamados via QRCode.
                </p>
            </a>

        </div>

        {{-- Visão Rápida de Ativos Técnicos --}}
        <div class="portal-section">
            <h2 class="portal-section-title">
                <svg class="w-6 h-6 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
                Visão Rápida de Ativos Técnicos
            </h2>

            <div class="portal-stats-grid">
                <a href="{{ route('portal.ativos.index') }}" class="portal-stat-card portal-stat-card--blue block hover:shadow-md transition-all">
                    <p class="portal-stat-label">Total de Ativos</p>
                    <p class="portal-stat-value">{{ $totalAtivosTecnicos }}</p>
                </a>
                <a href="{{ route('portal.ativos.index', ['status_ativo' => 'operando']) }}" class="portal-stat-card portal-stat-card--green block hover:shadow-md transition-all">
                    <p class="portal-stat-label">Operando</p>
                    <p class="portal-stat-value">{{ $ativosOperando }}</p>
                </a>
                <a href="{{ route('portal.ativos.index', ['status_ativo' => 'em_manutencao']) }}" class="portal-stat-card portal-stat-card--orange block hover:shadow-md transition-all">
                    <p class="portal-stat-label">Em Manutenção</p>
                    <p class="portal-stat-value">{{ $ativosEmManutencao }}</p>
                </a>
                <a href="{{ route('portal.ativos.index', ['manutencao_status' => 'vencida']) }}" class="portal-stat-card portal-stat-card--red block hover:shadow-md transition-all">
                    <p class="portal-stat-label">Manutenções Vencidas</p>
                    <p class="portal-stat-value">{{ $manutencoesVencidas }}</p>
                </a>
            </div>

            <div class="portal-charts-grid">
                <div class="portal-chart-card">
                    <h3 class="portal-chart-title">Status Atual dos Ativos</h3>
                    <div class="portal-chart-canvas-wrap">
                        <canvas id="chartStatusAtivosHome"></canvas>
                    </div>
                </div>

                <div class="portal-chart-card">
                    <h3 class="portal-chart-title">Saúde de Manutenção</h3>
                    <div class="portal-chart-canvas-wrap">
                        <canvas id="chartManutencaoAtivosHome"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dashboard Overview --}}
        <div class="portal-section">
            <h2 class="portal-section-title">
                <svg class="w-6 h-6 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Resumo de Atividades
            </h2>

            {{-- Stats Grid --}}
            <div class="portal-stats-grid">
                <!-- Total NF e Boletos -->
                <div class="portal-stat-card portal-stat-card--blue">
                    <p class="portal-stat-label">Total NF e Boletos</p>
                    <p class="portal-stat-value">
                        {{ $totalBoletos + $totalNotas }}
                    </p>
                </div>

                <!-- Atendimentos Abertos -->
                <div class="portal-stat-card portal-stat-card--yellow">
                    <p class="portal-stat-label">Atendimentos Abertos</p>
                    <p class="portal-stat-value">
                        {{ $totalAtendimentosAbertos }}
                    </p>
                </div>

                <!-- Atendimentos Em Execução -->
                <div class="portal-stat-card portal-stat-card--orange">
                    <p class="portal-stat-label">Em Execução</p>
                    <p class="portal-stat-value">
                        {{ $totalAtendimentosExecucao }}
                    </p>
                </div>

                <!-- Atendimentos Finalizados -->
                <div class="portal-stat-card portal-stat-card--green">
                    <p class="portal-stat-label">Finalizados</p>
                    <p class="portal-stat-value">
                        {{ $totalAtendimentosFinalizados }}
                    </p>
                </div>
            </div>

            {{-- Charts Section --}}
            <div class="portal-charts-grid">
                <!-- Chart 1: Atendimentos por Status -->
                <div class="portal-chart-card">
                    <h3 class="portal-chart-title">
                        Distribuição de Atendimentos
                    </h3>
                    <div class="portal-chart-canvas-wrap">
                        <canvas id="chartAtendimentos"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Documentos por Tipo -->
                <div class="portal-chart-card">
                    <h3 class="portal-chart-title">
                        Documentos Anexados
                    </h3>
                    <div class="portal-chart-canvas-wrap">
                        <canvas id="chartDocumentos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Boletos Table (if exists) --}}
        @if(isset($boletos) && $boletos->count() > 0)
        <div class="portal-section">
            <div class="portal-table-card">
                <div class="portal-table-header">
                    <h3 class="portal-table-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Últimos Boletos e Notas Fiscais
                    </h3>
                </div>

                {{-- Versão Desktop (Tabela) --}}
                <div class="portal-table-wrapper">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Referência</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th class="text-center">Boleto</th>
                                <th class="text-center">NF</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boletos->take(10) as $boleto)
                            <tr>
                                <td class="portal-font-semibold">
                                    {{ str_pad($boleto->mes, 2, '0', STR_PAD_LEFT) }}/{{ $boleto->ano }}
                                </td>
                                <td>
                                    {{ $boleto->data_vencimento->format('d/m/Y') }}
                                </td>
                                <td class="portal-font-semibold">
                                    R$ {{ number_format($boleto->valor, 2, ',', '.') }}
                                </td>
                                <td>
                                    @if($boleto->cobranca && $boleto->cobranca->status === 'pago')
                                    <span class="portal-badge portal-badge--success">Pago</span>
                                    @elseif($boleto->status === 'vencido')
                                    <span class="portal-badge portal-badge--danger">Vencido</span>
                                    @else
                                    <span class="portal-badge portal-badge--warning">Em aberto</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('portal.boletos.download', $boleto) }}"
                                        class="portal-btn portal-btn--primary portal-btn--sm portal-btn--icon"
                                        title="Baixar Boleto">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                </td>
                                <td class="text-center">
                                    @if($boleto->nota_fiscal)
                                    <a href="{{ route('portal.notas.download', $boleto->nota_fiscal) }}"
                                        class="portal-btn portal-btn--primary portal-btn--sm portal-btn--icon"
                                        title="Baixar NF">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($boleto->foiBaixado())
                                    <div class="text-gray-600 text-xs">
                                        <span class="portal-font-semibold">Baixado</span><br>
                                        {{ $boleto->baixado_em->format('d/m/Y H:i') }}
                                    </div>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Versão Mobile (Cards) --}}
                <div class="portal-mobile-cards px-4 pb-4">
                    @foreach($boletos->take(10) as $boleto)
                    <div class="portal-mobile-card">
                        <div class="portal-mobile-card-header">
                            <div>
                                <div class="portal-mobile-card-title">
                                    {{ str_pad($boleto->mes, 2, '0', STR_PAD_LEFT) }}/{{ $boleto->ano }}
                                </div>
                                <div class="portal-mobile-card-subtitle">
                                    Vencimento: {{ $boleto->data_vencimento->format('d/m/Y') }}
                                </div>
                            </div>
                            @if($boleto->cobranca && $boleto->cobranca->status === 'pago')
                            <span class="portal-badge portal-badge--success">Pago</span>
                            @elseif($boleto->status === 'vencido')
                            <span class="portal-badge portal-badge--danger">Vencido</span>
                            @else
                            <span class="portal-badge portal-badge--warning">Em aberto</span>
                            @endif
                        </div>
                        <div class="portal-mobile-card-row">
                            <span class="portal-mobile-card-label">Valor</span>
                            <span class="portal-mobile-card-value portal-font-bold">
                                R$ {{ number_format($boleto->valor, 2, ',', '.') }}
                            </span>
                        </div>
                        @if($boleto->foiBaixado())
                        <div class="portal-mobile-card-row">
                            <span class="portal-mobile-card-label">Baixado em</span>
                            <span class="portal-mobile-card-value">
                                {{ $boleto->baixado_em->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        @endif
                        <div class="portal-mobile-card-actions">
                            <a href="{{ route('portal.boletos.download', $boleto) }}"
                                class="portal-btn portal-btn--primary portal-btn--sm flex-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Boleto
                            </a>
                            @if($boleto->nota_fiscal)
                            <a href="{{ route('portal.notas.download', $boleto->nota_fiscal) }}"
                                class="portal-btn portal-btn--primary portal-btn--sm flex-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                NF
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <a href="https://gestor.alfa.solucoesgrupo.com/portal/financeiro" target="_blank" rel="noopener noreferrer"
                        class="portal-btn portal-btn--primary">
                        Ver Todos
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="portal-empty-state-title">Nenhum boleto disponível no momento.</p>
            <p class="portal-empty-state-text">Seus documentos aparecerão aqui quando forem anexados.</p>
        </div>
        @endif

    </div>

    {{-- Chart.js Library --}}
    <script src="{{ asset('js/vendor/chart.js') }}"></script>

    <script>
        // Dados para os gráficos
        const atendimentosAbertos = {{ (int) $totalAtendimentosAbertos }};
        const atendimentosEmExecucao = {{ (int) $totalAtendimentosExecucao }};
        const atendimentosFinalizados = {{ (int) $totalAtendimentosFinalizados }};

        const nfTotal = {{ (int) $totalNotas }};
        const boletosTotal = {{ (int) $totalBoletos }};

        const graficoStatusAtivos = @json($graficoStatusAtivos);
        const graficoManutencaoAtivos = @json($graficoManutencaoAtivos);

        const ctxStatusAtivosHome = document.getElementById('chartStatusAtivosHome').getContext('2d');
        new Chart(ctxStatusAtivosHome, {
            type: 'doughnut',
            data: {
                labels: graficoStatusAtivos.labels,
                datasets: [{
                    data: graficoStatusAtivos.values,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(249, 115, 22, 0.85)',
                        'rgba(107, 114, 128, 0.85)',
                        'rgba(245, 158, 11, 0.85)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(107, 114, 128, 1)',
                        'rgba(245, 158, 11, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            },
                            padding: 12,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        const ctxManutencaoAtivosHome = document.getElementById('chartManutencaoAtivosHome').getContext('2d');
        new Chart(ctxManutencaoAtivosHome, {
            type: 'bar',
            data: {
                labels: graficoManutencaoAtivos.labels,
                datasets: [{
                    label: 'Ativos',
                    data: graficoManutencaoAtivos.values,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(239, 68, 68, 0.85)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.7
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
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico 1: Distribuição de Atendimentos
        const ctxAtendimentos = document.getElementById('chartAtendimentos').getContext('2d');
        new Chart(ctxAtendimentos, {
            type: 'doughnut',
            data: {
                labels: ['Abertos', 'Em Execução', 'Finalizados'],
                datasets: [{
                    data: [atendimentosAbertos, atendimentosEmExecucao, atendimentosFinalizados],
                    backgroundColor: [
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(34, 197, 94, 0.8)'
                    ],
                    borderColor: [
                        'rgba(234, 179, 8, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(34, 197, 94, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            },
                            padding: 12,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Gráfico 2: Documentos por Tipo
        const ctxDocumentos = document.getElementById('chartDocumentos').getContext('2d');
        new Chart(ctxDocumentos, {
            type: 'bar',
            data: {
                labels: ['Notas Fiscais', 'Boletos'],
                datasets: [{
                    label: 'Quantidade',
                    data: [nfTotal, boletosTotal],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(139, 92, 246, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.7
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
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
