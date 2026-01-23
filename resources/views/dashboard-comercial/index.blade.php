<x-app-layout>
    @push('styles')
    @vite('resources/css/dashboard/dashboard.css')
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
        data-status='@json($orcamentosPorStatus)'
        data-empresas='@json($orcamentosPorEmpresa)'
        style="display: none;">
    </div>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= CARDS DE RESUMO ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-blue-600">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Orçamentos</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalOrcamentos }}</p>
                </div>

                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-amber-500">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Financeiro</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $qtdFinanceiro }}</p>
                </div>

                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-emerald-500">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Aprovado</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $qtdAprovado }}</p>
                </div>

                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-indigo-400">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Aguardando Aprovação</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $qtdAguardando }}</p>
                </div>

            </div>

            {{-- ================= MÉTRICAS FILTRADAS (QTD E VALOR) ================= --}}
            <div class="bg-indigo-700 rounded-xl shadow-lg p-8 mb-10 text-white">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <h3 class="text-indigo-100 text-lg font-medium">Resultado do Filtro Atual</h3>
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