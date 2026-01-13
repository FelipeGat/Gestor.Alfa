<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📊 Dashboard Administrativo
        </h2>
    </x-slot>

    {{-- ================= ESTILOS ================= --}}
    <style>
    /* ========================= CONTAINERS ========================= */
    .dashboard-wrapper {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-section {
        margin-bottom: 3rem;
    }

    .dashboard-section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e5e7eb;
    }

    /* ========================= CARDS ========================= */
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .dashboard-card {
        padding: 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 140px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    }

    .dashboard-card span {
        font-size: 0.875rem;
        font-weight: 500;
        opacity: 0.85;
        margin-bottom: 0.5rem;
    }

    .dashboard-card strong {
        font-size: 2rem;
        font-weight: 700;
    }

    /* ========================= CARD COLORS ========================= */
    .dashboard-card.blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
    }

    .dashboard-card.green {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: white;
    }

    .dashboard-card.red {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .dashboard-card.indigo {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .dashboard-card.yellow {
        background: linear-gradient(135deg, #facc15 0%, #eab308 100%);
        color: #1f2937;
    }

    /* ========================= CHARTS ========================= */
    .dashboard-charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
    }

    .dashboard-chart {
        background: white;
        padding: 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .dashboard-chart h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
    }

    .dashboard-chart canvas {
        max-height: 300px;
    }

    /* ========================= LOADING STATE ========================= */
    .chart-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 300px;
        background: #f9fafb;
        border-radius: 0.75rem;
        color: #6b7280;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #e5e7eb;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* ========================= ERROR STATE ========================= */
    .chart-error {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 300px;
        background: #fef2f2;
        border-radius: 0.75rem;
        border: 1px solid #fecaca;
        color: #dc2626;
        font-size: 0.875rem;
    }

    .chart-error svg {
        width: 24px;
        height: 24px;
        margin-right: 0.5rem;
    }

    /* ========================= EMPTY STATE ========================= */
    .chart-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 300px;
        background: #f0fdf4;
        border-radius: 0.75rem;
        border: 1px solid #bbf7d0;
        color: #16a34a;
        font-size: 0.875rem;
    }

    .chart-empty svg {
        width: 24px;
        height: 24px;
        margin-right: 0.5rem;
    }

    /* ========================= RESPONSIVE ========================= */
    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 1rem;
        }

        .dashboard-cards {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .dashboard-card {
            min-height: 120px;
            padding: 1rem;
        }

        .dashboard-card strong {
            font-size: 1.5rem;
        }

        .dashboard-charts-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-chart {
            padding: 1rem;
        }

        .dashboard-chart canvas {
            max-height: 250px;
        }
    }
    </style>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- ================= DASHBOARD CONTAINER ================= --}}
    <div class="dashboard-wrapper">

        {{-- ================= ADMINISTRATIVO ================= --}}
        <section class="dashboard-section">
            <h3 class="dashboard-section-title">📁 Administrativo</h3>

            <div class="dashboard-cards">
                <div class="dashboard-card blue" role="region" aria-label="Clientes Cadastrados">
                    <span>Clientes Cadastrados</span>
                    <strong>{{ $totalClientes ?? 0 }}</strong>
                </div>

                <div class="dashboard-card green" role="region" aria-label="Clientes Ativos">
                    <span>Clientes Ativos</span>
                    <strong>{{ $clientesAtivos ?? 0 }}</strong>
                </div>

                <div class="dashboard-card red" role="region" aria-label="Clientes Inativos">
                    <span>Clientes Inativos</span>
                    <strong>{{ $clientesInativos ?? 0 }}</strong>
                </div>

                <div class="dashboard-card indigo" role="region" aria-label="Clientes com Contrato">
                    <span>Clientes com Contrato</span>
                    <strong>{{ $clientesContrato ?? 0 }}</strong>
                </div>
            </div>
        </section>

        {{-- ================= FINANCEIRO ================= --}}
        <section class="dashboard-section">
            <h3 class="dashboard-section-title">💰 Financeiro</h3>

            <div class="dashboard-cards">
                <div class="dashboard-card yellow" role="region" aria-label="Receita Prevista">
                    <span>Receita Prevista</span>
                    <strong>R$ {{ number_format($receitaPrevista ?? 0, 2, ',', '.') }}</strong>
                </div>

                <div class="dashboard-card green" role="region" aria-label="Receita Realizada">
                    <span>Receita Realizada</span>
                    <strong>R$ {{ number_format($receitaRealizada ?? 0, 2, ',', '.') }}</strong>
                </div>
            </div>

            <div class="dashboard-chart">
                <h4>Comparativo de Receita</h4>
                <div id="graficoReceitaContainer">
                    <div class="chart-loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= TÉCNICO ================= --}}
        <section class="dashboard-section">
            <h3 class="dashboard-section-title">🛠️ Técnico</h3>

            <div class="dashboard-charts-grid">
                <div class="dashboard-chart">
                    <h4>Assuntos por Empresa</h4>
                    <div id="graficoEmpresaContainer">
                        <div class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-chart">
                    <h4>Serviço x Venda</h4>
                    <div id="graficoTipoContainer">
                        <div class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-chart">
                    <h4>Top 5 Categorias</h4>
                    <div id="graficoCategoriaContainer">
                        <div class="chart-loading">
                            <div class="spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script>
    /* =========================
       CONFIGURAÇÃO GLOBAL CHART.JS
    ========================= */
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.plugins.legend.position = 'bottom';

    /* =========================
       HELPER: CRIAR CANVAS
    ========================= */
    function criarCanvas(containerId, chartId) {
        const container = document.getElementById(containerId);
        if (!container) return null;

        container.innerHTML = `<canvas id="${chartId}"></canvas>`;
        return document.getElementById(chartId);
    }

    /* =========================
       HELPER: MOSTRAR ERRO
    ========================= */
    function mostrarErro(containerId, mensagem = 'Erro ao carregar gráfico') {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="chart-error" role="alert">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span>${mensagem}</span>
            </div>
        `;
    }

    /* =========================
       HELPER: MOSTRAR VAZIO
    ========================= */
    function mostrarVazio(containerId, mensagem = 'Sem dados disponíveis') {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="chart-empty" role="status">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2A4 4 0 000 5v10a4 4 0 004 4h12a4 4 0 004-4V5a4 4 0 00-4-4 1 1 0 000 2 2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" clip-rule="evenodd"></path>
                </svg>
                <span>${mensagem}</span>
            </div>
        `;
    }

    /* =========================
       VALIDAR DADOS
    ========================= */
    function validarDados(dados) {
        if (!dados) return false;
        if (Array.isArray(dados) && dados.length === 0) return false;
        return true;
    }

    /* =========================
       GRÁFICO: RECEITA
    ========================= */
    try {
        const receitaPrevista = @json($receitaPrevista ?? 0);
        const receitaRealizada = @json($receitaRealizada ?? 0);

        if (receitaPrevista > 0 || receitaRealizada > 0) {
            const canvas = criarCanvas('graficoReceitaContainer', 'graficoReceita');
            if (canvas) {
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: ['Prevista', 'Realizada'],
                        datasets: [{
                            label: 'Receita (R$)',
                            data: [receitaPrevista, receitaRealizada],
                            backgroundColor: ['#facc15', '#22c55e'],
                            borderColor: ['#eab308', '#16a34a'],
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        } else {
            mostrarVazio('graficoReceitaContainer', 'Nenhum dado de receita disponível');
        }
    } catch (error) {
        console.error('Erro ao renderizar gráfico de receita:', error);
        mostrarErro('graficoReceitaContainer', 'Erro ao carregar gráfico de receita');
    }

    /* =========================
       GRÁFICO: ASSUNTOS POR EMPRESA
    ========================= */
    try {
        const labelsEmpresa = @json($labelsEmpresa ?? []);
        const valoresEmpresa = @json($valoresEmpresa ?? []);

        if (validarDados(labelsEmpresa) && validarDados(valoresEmpresa)) {
            const canvas = criarCanvas('graficoEmpresaContainer', 'graficoEmpresa');
            if (canvas) {
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: labelsEmpresa,
                        datasets: [{
                            data: valoresEmpresa,
                            backgroundColor: ['#3b82f6', '#22c55e', '#facc15', '#ef4444', '#6366f1'],
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        } else {
            mostrarVazio('graficoEmpresaContainer', 'Nenhum assunto por empresa');
        }
    } catch (error) {
        console.error('Erro ao renderizar gráfico de empresa:', error);
        mostrarErro('graficoEmpresaContainer', 'Erro ao carregar gráfico de empresa');
    }

    /* =========================
       GRÁFICO: SERVIÇO X VENDA
    ========================= */
    try {
        const labelsTipo = @json($labelsTipo ?? []);
        const valoresTipo = @json($valoresTipo ?? []);

        if (validarDados(labelsTipo) && validarDados(valoresTipo)) {
            const canvas = criarCanvas('graficoTipoContainer', 'graficoTipo');
            if (canvas) {
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: labelsTipo,
                        datasets: [{
                            data: valoresTipo,
                            backgroundColor: ['#0ea5e9', '#f97316'],
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        } else {
            mostrarVazio('graficoTipoContainer', 'Nenhum dado de serviço/venda');
        }
    } catch (error) {
        console.error('Erro ao renderizar gráfico de tipo:', error);
        mostrarErro('graficoTipoContainer', 'Erro ao carregar gráfico de tipo');
    }

    /* =========================
       GRÁFICO: TOP 5 CATEGORIAS
    ========================= */
    try {
        const labelsCategoria = @json($labelsCategoria ?? []);
        const valoresCategoria = @json($valoresCategoria ?? []);

        if (validarDados(labelsCategoria) && validarDados(valoresCategoria)) {
            const canvas = criarCanvas('graficoCategoriaContainer', 'graficoCategoria');
            if (canvas) {
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labelsCategoria,
                        datasets: [{
                            label: 'Quantidade',
                            data: valoresCategoria,
                            backgroundColor: '#6366f1',
                            borderColor: '#4f46e5',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        } else {
            mostrarVazio('graficoCategoriaContainer', 'Nenhuma categoria disponível');
        }
    } catch (error) {
        console.error('Erro ao renderizar gráfico de categoria:', error);
        mostrarErro('graficoCategoriaContainer', 'Erro ao carregar gráfico de categoria');
    }
    </script>
</x-app-layout>