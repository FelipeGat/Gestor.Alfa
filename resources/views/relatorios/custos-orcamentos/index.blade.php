<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    <style>
        @media print {
            .print\:hidden { display: none !important; }
        }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            {{-- TÍTULO --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Custos x Orçamentos
                </h2>
            </div>

            {{-- BOTÃO VOLTAR --}}
            <a href="{{ route('financeiro.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm group print:hidden"
                title="Voltar para Dashboard Financeiro">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- FILTROS --}}
            <div class="bg-white shadow rounded-xl p-6 mb-6 print:hidden">
                <form method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                            <select name="cliente_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" onchange="this.form.submit()">
                                <option value="">Todos os Clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nome_fantasia ?? $cliente->razao_social ?? $cliente->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Orçamento</label>
                            <select name="orcamento_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" onchange="this.form.submit()">
                                <option value="">Selecione um Orçamento...</option>
                                @foreach($orcamentos as $orcamento)
                                    <option value="{{ $orcamento->id }}" {{ request('orcamento_id') == $orcamento->id ? 'selected' : '' }}>
                                        #{{ $orcamento->numero_orcamento }} - {{ $orcamento->cliente?->nome_fantasia ?? $orcamento->cliente?->razao_social ?? '—' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            @if($orcamentoSelecionado)
                {{-- DADOS DO ORÇAMENTO --}}
                <div class="bg-white shadow rounded-xl p-6 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Cliente</span>
                            <span class="font-bold text-gray-800 truncate block" title="{{ $dadosOrcamento['cliente'] }}">{{ $dadosOrcamento['cliente'] }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Orçamento</span>
                            <span class="font-bold text-indigo-600">#{{ $dadosOrcamento['numero'] }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Status</span>
                            <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-full bg-gray-200 text-gray-700">
                                {{ ucfirst(strtolower($dadosOrcamento['status'])) }}
                            </span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Valor Total</span>
                            <span class="font-bold text-emerald-600">R$ {{ number_format($dadosOrcamento['valor_total'], 2, ',', '.') }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Início</span>
                            <span class="font-bold text-gray-700">{{ $dadosOrcamento['data_inicio'] }}</span>
                        </div>
                        @if($dadosOrcamento['data_fim'])
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Fim</span>
                            <span class="font-bold text-gray-700">{{ $dadosOrcamento['data_fim'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- KPIs (Cards de Resumo) --}}
                <div class="flex justify-end mb-4">
                    <button onclick="window.print()" class="inline-flex items-center justify-center h-10 w-10 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-full shadow-lg transition-all transform hover:scale-105 active:scale-95 print:hidden" title="Imprimir Relatório">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    {{-- Valor Orçado --}}
                    <div class="bg-white shadow rounded-xl flex flex-col items-center justify-center p-6 border-l-4 border-l-indigo-500">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Valor Orçado</span>
                        <span class="text-2xl font-black text-gray-800">R$ {{ number_format($kpis['valor_orcado'] ?? 0, 2, ',', '.') }}</span>
                    </div>

                    {{-- Custo Total --}}
                    <div class="bg-white shadow rounded-xl flex flex-col items-center justify-center p-6 border-l-4 border-l-red-500">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Custo Total</span>
                        <span class="text-2xl font-black text-red-600">R$ {{ number_format($kpis['custo_total'] ?? 0, 2, ',', '.') }}</span>
                    </div>

                    {{-- Receita Recebida --}}
                    <div class="bg-white shadow rounded-xl flex flex-col items-center justify-center p-6 border-l-4 border-l-emerald-500">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Receita Recebida</span>
                        <span class="text-2xl font-black text-emerald-600">R$ {{ number_format($kpis['receita_recebida'] ?? 0, 2, ',', '.') }}</span>
                    </div>

                    {{-- Lucro / Prejuízo --}}
                    <div class="bg-white shadow rounded-xl flex flex-col items-center justify-center p-6 border-l-4 border-l-blue-500">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Lucro / Prejuízo</span>
                        <span class="text-2xl font-black text-blue-700">R$ {{ number_format($kpis['lucro'] ?? 0, 2, ',', '.') }}</span>
                    </div>

                    {{-- Margem --}}
                    <div class="bg-white shadow rounded-xl flex flex-col items-center justify-center p-6 border-l-4 border-l-yellow-500">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Margem</span>
                        <span class="text-2xl font-black text-yellow-600">{{ number_format($kpis['margem'] ?? 0, 2, ',', '.') }}%</span>
                    </div>
                </div>

                {{-- GRÁFICOS --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white shadow rounded-xl p-6">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                            <div class="w-2 h-6 bg-indigo-500 rounded-full"></div>
                            <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Evolução de Custos no Tempo</h3>
                        </div>
                        <div class="h-[300px]">
                            <canvas id="graficoEvolucaoCustos"></canvas>
                        </div>
                    </div>
                    <div class="bg-white shadow rounded-xl p-6">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                            <div class="w-2 h-6 bg-orange-500 rounded-full"></div>
                            <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider" id="titulo-custos-categoria">Custos por Categoria</h3>
                        </div>
                        <div class="h-[300px] flex flex-col items-center justify-center">
                            <canvas id="graficoCustosCategoria"></canvas>
                            <button id="btn-voltar-drilldown" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm group hidden cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>Voltar</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-xl p-6 mb-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Orçado x Realizado</h3>
                    </div>
                    <div class="h-[300px]">
                        <canvas id="graficoOrcadoRealizado"></canvas>
                    </div>
                </div>

                {{-- TABELA DE CUSTOS --}}
                <div class="bg-white shadow rounded-xl p-6 overflow-hidden">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-6 bg-gray-800 rounded-full"></div>
                            <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Detalhamento de Custos</h3>
                        </div>
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold">
                            {{ $qtdLancamentos ?? 0 }} lançamentos
                        </span>
                    </div>
                    
                    <div class="overflow-x-auto overflow-y-auto max-h-[500px]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th scope="col" class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Data</th>
                                    <th scope="col" class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Fornecedor</th>
                                    <th scope="col" class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Categoria</th>
                                    <th scope="col" class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Descrição</th>
                                    <th scope="col" class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Tipo</th>
                                    <th scope="col" class="px-4 py-3 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @php $totalTabela = 0; @endphp
                                @foreach($tabelaCustos as $item)
                                    @php $totalTabela += $item['valor']; @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 font-medium">{{ $item['data'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 font-bold">{{ $item['fornecedor'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $item['categoria'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 min-w-[200px]">{{ $item['descricao'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ strtolower($item['tipo']) == 'custo' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ ucfirst(strtolower($item['tipo'])) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-black text-gray-900">
                                            R$ {{ number_format($item['valor'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Total Geral</td>
                                    <td class="px-4 py-4 text-right text-base font-black text-gray-900">
                                        R$ {{ number_format($totalTabela, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- SCRIPTS DOS GRÁFICOS --}}
                @push('scripts')
                <script src="{{ asset('js/vendor/chart.js') }}"></script>

                <script>
                    Chart.defaults.font.family = "'Inter', 'ui-sans-serif', 'system-ui'";
                    Chart.defaults.color = '#64748b';

                    // Gráfico Evolução de Custos
                    const ctxEvolucao = document.getElementById('graficoEvolucaoCustos').getContext('2d');
                    new Chart(ctxEvolucao, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($custosEvolucao->pluck('mes')) !!},
                            datasets: [{
                                label: 'Custo Acumulado',
                                data: {!! json_encode($custosEvolucao->pluck('valor')) !!},
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99,102,241,0.1)',
                                borderWidth: 3,
                                pointBackgroundColor: '#6366f1',
                                pointBorderColor: '#fff',
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    callbacks: {
                                        label: function(context) {
                                            return ' R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        callback: value => 'R$ ' + value.toLocaleString('pt-BR')
                                    }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });

                    // Drilldown de Custos por Categoria
                    const custosPorCategoria = @json($custosPorCategoria);
                    let nivelAtual = 'categoria';
                    let categoriaSelecionada = null;
                    let subcategoriaSelecionada = null;
                    let chartCategoria = null;

                    function renderGraficoCategoria(dados, titulo, nivel) {
                        const ctx = document.getElementById('graficoCustosCategoria').getContext('2d');
                        if (chartCategoria) chartCategoria.destroy();
                        let labels = [];
                        let valores = [];
                        let cores = ['#6366f1','#f59e42','#10b981','#ef4444','#fbbf24','#a78bfa','#2dd4bf','#f43f5e'];
                        if (nivel === 'categoria') {
                            labels = dados.map(c => c.categoria_nome);
                            valores = dados.map(c => c.valor);
                        } else if (nivel === 'subcategoria') {
                            labels = dados.map(s => s.subcategoria_nome);
                            valores = dados.map(s => s.valor);
                        } else if (nivel === 'conta') {
                            labels = dados.map(c => c.conta_nome);
                            valores = dados.map(c => c.valor);
                        }
                        chartCategoria = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: valores,
                                    backgroundColor: cores,
                                    borderWidth: 2,
                                    borderColor: '#fff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '70%',
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 20,
                                            font: { size: 11, weight: '600' }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return ' R$ ' + context.parsed + '';
                                            }
                                        }
                                    }
                                },
                                onClick: (e, elements) => {
                                    if (elements.length > 0) {
                                        const idx = elements[0].index;
                                        if (nivel === 'categoria') {
                                            categoriaSelecionada = dados[idx];
                                            nivelAtual = 'subcategoria';
                                            renderGraficoCategoria(categoriaSelecionada.subcategorias, 'Custos por Subcategoria', 'subcategoria');
                                            document.getElementById('btn-voltar-drilldown').classList.remove('hidden');
                                            document.getElementById('titulo-custos-categoria').innerText = 'Custos por Subcategoria';
                                        } else if (nivel === 'subcategoria') {
                                            subcategoriaSelecionada = dados[idx];
                                            nivelAtual = 'conta';
                                            renderGraficoCategoria(subcategoriaSelecionada.contas, 'Custos por Conta', 'conta');
                                            document.getElementById('titulo-custos-categoria').innerText = 'Custos por Conta';
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Botão voltar
                    document.getElementById('btn-voltar-drilldown').addEventListener('click', function() {
                        if (nivelAtual === 'conta') {
                            renderGraficoCategoria(categoriaSelecionada.subcategorias, 'Custos por Subcategoria', 'subcategoria');
                            nivelAtual = 'subcategoria';
                            document.getElementById('titulo-custos-categoria').innerText = 'Custos por Subcategoria';
                        } else if (nivelAtual === 'subcategoria') {
                            renderGraficoCategoria(custosPorCategoria, 'Custos por Categoria', 'categoria');
                            nivelAtual = 'categoria';
                            document.getElementById('btn-voltar-drilldown').classList.add('hidden');
                            document.getElementById('titulo-custos-categoria').innerText = 'Custos por Categoria';
                        }
                    });

                    // Inicialização
                    renderGraficoCategoria(custosPorCategoria, 'Custos por Categoria', 'categoria');


                    // Gráfico Orçado x Realizado
                    const ctxOrcado = document.getElementById('graficoOrcadoRealizado').getContext('2d');
                    new Chart(ctxOrcado, {
                        type: 'bar',
                        data: {
                            labels: ['Orçado', 'Custo Total', 'Receita Recebida'],
                            datasets: [{
                                data: [
                                    {{ $kpis['valor_orcado'] ?? 0 }},
                                    {{ $kpis['custo_total'] ?? 0 }},
                                    {{ $kpis['receita_recebida'] ?? 0 }}
                                ],
                                backgroundColor: ['#6366f1','#ef4444','#10b981'],
                                borderRadius: 8,
                                barThickness: 40
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return ' R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        callback: value => 'R$ ' + value.toLocaleString('pt-BR')
                                    }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                </script>
                @endpush
            @endif

        </div>
    </div>
</x-app-layout>
