<x-app-layout>
    @push('styles')
    @vite('resources/css/dashboard/dashboard_operacional.css')
    @endpush

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

                {{-- Filtro de Data --}}
                <input type="date" name="data_inicio" value="{{ $dataInicio->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm text-sm">
                <input type="date" name="data_fim" value="{{ $dataFim->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm text-sm">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Filtrar</button>

                {{-- Botão Limpar --}}
                @if($empresaId || $statusFiltro)
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition text-sm">
                    Limpar
                </a>
                @endif
            </form>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= CARDS DE RESUMO GERAL ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-blue-500">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Abertos</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosAbertos }}</p>
                </div>
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-orange-500">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Em Atendimento</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosEmAtendimento }}</p>
                </div>
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-yellow-500">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Aguardando Cliente</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosAguardando }}</p>
                </div>
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-green-500">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Concluídos</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $chamadosConcluidos }}</p>
                </div>
            </div>

            {{-- ================= MÉTRICAS FILTRADAS ================= --}}
            <div class="bg-indigo-700 rounded-xl shadow-lg p-8 mb-10 text-white">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <h3 class="text-indigo-100 text-lg font-medium">Resultado do Filtro Atual</h3>
                        <p class="text-sm text-indigo-200">
                            Período: <strong>{{ $dataInicio->format('d/m') }} a {{ $dataFim->format('d/m') }}</strong> |
                            Status: <strong>{{ $statusFiltro ? ucfirst(str_replace('_', ' ', $statusFiltro)) : 'Todos' }}</strong>
                        </p>
                    </div>
                    <div class="text-center">
                        <span class="block text-indigo-200 text-xs uppercase font-bold">Total de Chamados</span>
                        <span class="text-5xl font-black">{{ $metricasFiltradas->qtd ?? 0 }}</span>
                    </div>
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
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        }
                    }
                });
            } else {
                mostrarVazio('graficoAtendimentosTecnico');
            }

            // 3. Chamados por Status
            const labelsStatus = @json($labelsAtendimentosStatus ?? []);
            if (validarDados(labelsStatus)) {
                new Chart(document.getElementById('graficoChamadosStatus'), {
                    type: 'doughnut',
                    data: {
                        labels: labelsStatus,
                        datasets: [{
                            data: @json($valoresAtendimentosStatus ?? []),
                            backgroundColor: ['#f97316', '#22c55e', '#3b82f6', '#6b7280', '#ef4444']
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

            // 4. Tabela Top 5 Clientes
            const topClientes = @json($topClientes ?? []);
            const containerClientes = document.getElementById('tabelaTopClientesContainer');

            if (validarDados(topClientes)) {
                let tableHtml = '<table class="w-full text-sm"><tbody>';

                topClientes.forEach(c => {
                    // CORREÇÃO: Acessa o nome_fantasia do objeto cliente relacionado
                    const nomeCliente = c.cliente ? c.cliente.nome_fantasia : 'Cliente não identificado';

                    tableHtml += `
            <tr class="border-b last:border-b-0">
                <td class="py-3 px-2">${nomeCliente}</td>
                <td class="py-3 px-2 text-right font-bold text-gray-600">${c.total}</td>
            </tr>
        `;
                });

                containerClientes.innerHTML = tableHtml + '</tbody></table>';
            } else {
                mostrarVazio('tabelaTopClientesContainer');
            }

            // 5. Top 5 Assuntos
            const labelsAssunto = @json($labelsTopAssuntos ?? []);
            if (validarDados(labelsAssunto)) {
                new Chart(document.getElementById('graficoTopAssuntos'), {
                    type: 'bar',
                    data: {
                        labels: labelsAssunto,
                        datasets: [{
                            label: 'Quantidade',
                            data: @json($valoresTopAssuntos ?? []),
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
            } else {
                mostrarVazio('graficoTopAssuntos');
            }

            // 6. Chamados por Prioridade
            const labelsPrio = @json($labelsChamadosPrioridade ?? []);
            if (validarDados(labelsPrio)) {
                new Chart(document.getElementById('graficoChamadosPrioridade'), {
                    type: 'pie',
                    data: {
                        labels: labelsPrio,
                        datasets: [{
                            data: @json($valoresChamadosPrioridade ?? []),
                            backgroundColor: ['#ef4444', '#f97316', '#22c55e', '#3b82f6']
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

            // 7. Chamados por Empresa (condicional)
            @if(!$empresaId)
            const labelsEmpresa = @json($labelsChamadosEmpresa ?? []);
            if (validarDados(labelsEmpresa)) {
                new Chart(document.getElementById('graficoChamadosEmpresa'), {
                    type: 'pie',
                    data: {
                        labels: labelsEmpresa,
                        datasets: [{
                            data: @json($valoresChamadosEmpresa ?? []),
                            backgroundColor: ['#3b82f6', '#16a34a', '#facc15', '#db2777', '#6d28d9']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            } else {
                mostrarVazio('graficoChamadosEmpresa');
            }
            @endif
        });
    </script>
    @endpush
</x-app-layout>