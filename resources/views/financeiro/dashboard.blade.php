<x-app-layout>

    @push('styles')
    @vite('resources/css/financeiro/dashboard.css')
    @endpush

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                💰 Dashboard Financeiro
                {{ $empresaId ? '- ' . ($empresas->find($empresaId)->nome_fantasia ?? 'Empresa') : '(Global)' }}
            </h2>

            {{-- FILTROS --}}
            <form action="{{ url()->current() }}" method="GET" class="flex flex-wrap gap-3">

                <select name="empresa_id"
                    onchange="this.form.submit()"
                    class="rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200">
                    <option value="">Todas as Empresas</option>
                    @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}" @selected($empresaId==$empresa->id)>
                        {{ $empresa->nome_fantasia }}
                    </option>
                    @endforeach
                </select>

                <select name="status"
                    onchange="this.form.submit()"
                    class="rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200">
                    <option value="">Todos os Status</option>
                    <option value="aprovado" @selected($statusFiltro==='aprovado' )>Aprovado</option>
                    <option value="financeiro" @selected($statusFiltro==='financeiro' )>Financeiro</option>
                    <option value="aguardando_pagamento" @selected($statusFiltro==='aguardando_pagamento' )>
                        Aguardando Pagamento
                    </option>
                </select>

                @if($empresaId || $statusFiltro)
                <a href="{{ url()->current() }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Limpar
                </a>
                @endif
            </form>
        </div>
    </x-slot>

    {{-- DADOS PARA JS --}}
    <div id="financeiro-json-data"
        data-status='@json($orcamentosPorStatus)'
        data-empresas='@json($orcamentosPorEmpresa)'
        style="display:none;"></div>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= CARDS ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                <div class="dashboard-card card-receber">
                    <h3>Aprovados</h3>
                    <p class="valor">{{ $qtdAprovado }}</p>
                </div>

                <div class="dashboard-card card-hoje">
                    <h3>Financeiro</h3>
                    <p class="valor">{{ $qtdFinanceiro }}</p>
                </div>

                <div class="dashboard-card card-vencido">
                    <h3>Aguardando Pagamento</h3>
                    <p class="valor">{{ $qtdAguardandoPagamento }}</p>
                </div>

                <div class="dashboard-card card-pago">
                    <h3>Total em Aberto</h3>
                    <p class="valor">
                        R$ {{ number_format($valorTotalAberto, 2, ',', '.') }}
                    </p>
                </div>

            </div>

            {{-- ================= MÉTRICAS ================= --}}
            <div class="bg-indigo-700 rounded-xl shadow-lg p-8 mb-10 text-white">
                <div class="flex flex-col md:flex-row justify-between gap-6">

                    <div>
                        <h3 class="text-indigo-100 text-lg font-medium">
                            Resultado do Filtro Atual
                        </h3>
                        <p class="text-sm text-indigo-200">
                            Status:
                            <strong>{{ $statusFiltro ?: 'Todos' }}</strong> |
                            Empresa:
                            <strong>
                                {{ $empresaId
                                    ? ($empresas->find($empresaId)->nome_fantasia ?? 'Selecionada')
                                    : 'Todas' }}
                            </strong>
                        </p>
                    </div>

                    <div class="flex gap-12">
                        <div class="text-center">
                            <span class="block text-indigo-200 text-xs uppercase font-bold">
                                Quantidade
                            </span>
                            <span class="text-4xl font-black">
                                {{ $metricasFiltradas->qtd ?? 0 }}
                            </span>
                        </div>

                        <div class="text-center border-l border-indigo-500 pl-12">
                            <span class="block text-indigo-200 text-xs uppercase font-bold">
                                Valor Total
                            </span>
                            <span class="text-4xl font-black">
                                R$ {{ number_format($metricasFiltradas->valor_total ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ================= GRÁFICOS ================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">

                <div class="dashboard-chart">
                    <h3>Distribuição Financeira por Status</h3>
                    <div class="h-72">
                        <canvas id="chartFinanceiroStatus"></canvas>
                    </div>
                </div>

                <div class="dashboard-chart">
                    <h3>Valor Financeiro por Empresa</h3>
                    <div class="h-72">
                        <canvas id="chartFinanceiroEmpresa"></canvas>
                    </div>
                </div>

            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const el = document.getElementById('financeiro-json-data');
        const statusData = JSON.parse(el.dataset.status || '{}');
        const empresaData = JSON.parse(el.dataset.empresas || '[]');

        new Chart(document.getElementById('chartFinanceiroStatus'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: ['#6366f1', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        new Chart(document.getElementById('chartFinanceiroEmpresa'), {
            type: 'bar',
            data: {
                labels: empresaData.map(e => e.empresa.nome_fantasia),
                datasets: [{
                    label: 'Valor (R$)',
                    data: empresaData.map(e => e.total_valor),
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            callback: v => 'R$ ' + v.toLocaleString('pt-BR')
                        }
                    }
                }
            }
        });
    </script>
    @endpush

</x-app-layout>