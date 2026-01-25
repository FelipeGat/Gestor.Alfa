<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                    Portal do Cliente
                </h2>

                <p class="text-sm text-gray-600 mt-2">
                    Unidade:
                    <span class="font-semibold text-blue-600">
                        {{ $cliente->nome_exibicao }}
                    </span>
                </p>
            </div>

            @if(auth()->user()->clientes()->count() > 1)
            <form method="POST" action="{{ route('portal.trocar-unidade') }}">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2
                           bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800
                           text-white text-sm font-semibold
                           rounded-lg border-0 shadow-md hover:shadow-lg
                           transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 7a2 2 0 100-4 2 2 0 000 4zm0 0H4m16 0a2 2 0 100 4 2 2 0 000-4zm0 0h4"></path>
                    </svg>
                    Trocar Unidade
                </button>
            </form>
            @endif
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-green-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-8">

            {{-- Sessão Bem Vindo --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            Bem-vindo, {{ auth()->user()->name }}! 👋
                        </h1>
                        <p class="text-gray-600">
                            Gerencie seu Financeiro, Atendimentos e Documentos de forma centralizada e segura.
                        </p>
                    </div>
                    <div class="hidden md:block text-6xl opacity-20">
                        📊
                    </div>
                </div>
            </div>

            {{-- Navigation Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Card Financeiro -->
                <a href="{{ route('portal.financeiro') }}"
                    class="group bg-white rounded-2xl shadow-lg p-8 border-2 border-transparent
              hover:border-blue-500 hover:shadow-2xl transition-all duration-300
              transform hover:scale-105 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-blue-100 to-blue-50 p-4 rounded-xl">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2
                             3 .895 3 2-1.343 2-3 2m0-8
                             c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1
                             c-1.11 0-2.08-.402-2.599-1M21 12
                             a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-600
                        group-hover:translate-x-1 transition-all"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                        Acessar Meu Financeiro
                    </h3>

                    <p class="text-gray-600 text-sm mb-4">
                        Visualize boletos, notas fiscais, pagamentos e acompanhe seu histórico financeiro.
                    </p>

                    <div class="flex items-center text-blue-600 font-semibold text-sm">
                        Ir para Financeiro
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Card Atendimentos -->
                <a href="{{ route('portal.index') }}"
                    class="group bg-white rounded-2xl shadow-lg p-8 border-2 border-transparent
              hover:border-green-500 hover:shadow-2xl transition-all duration-300
              transform hover:scale-105 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-green-100 to-green-50 p-4 rounded-xl">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <svg class="w-6 h-6 text-gray-400 group-hover:text-green-600
                        group-hover:translate-x-1 transition-all"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-green-600 transition-colors">
                        Acessar Minhas OS
                    </h3>

                    <p class="text-gray-600 text-sm mb-4">
                        Acompanhe suas ordens de serviço, atendimentos e solicitações.
                    </p>

                    <div class="flex items-center text-green-600 font-semibold text-sm">
                        Ir para Atendimentos
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

            </div>


            {{-- Dashboard Overview --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center">
                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Resumo de Atividades
                </h2>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total NF e Boletos -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border-l-4 border-blue-600 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">Total NF e Boletos</p>
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-blue-600 mb-1">
                            {{ rand(15, 45) }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Documentos anexados no portal
                        </p>
                    </div>

                    <!-- Atendimentos Abertos -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border-l-4 border-yellow-600 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">Atendimentos Abertos</p>
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-yellow-600 mb-1">
                            {{ rand(2, 8) }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Aguardando ação
                        </p>
                    </div>

                    <!-- Atendimentos Em Execução -->
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border-l-4 border-orange-600 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">Em Execução</p>
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-orange-600 mb-1">
                            {{ rand(3, 12) }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Sendo processados
                        </p>
                    </div>

                    <!-- Atendimentos Finalizados -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border-l-4 border-green-600 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">Finalizados</p>
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-green-600 mb-1">
                            {{ rand(25, 60) }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Concluídos com sucesso
                        </p>
                    </div>
                </div>

                {{-- Charts Section --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Chart 1: Atendimentos por Status -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">
                            Distribuição de Atendimentos
                        </h3>
                        <canvas id="chartAtendimentos" height="300"></canvas>
                    </div>

                    <!-- Chart 2: Documentos por Tipo -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">
                            Documentos Anexados
                        </h3>
                        <canvas id="chartDocumentos" height="300"></canvas>
                    </div>
                </div>
            </div>

            {{-- Boletos Table (if exists) --}}
            @if(isset($boletos) && $boletos->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                <div class="p-8 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Últimos Boletos e Notas Fiscais
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Referência
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Vencimento
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Valor
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Boleto
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    NF
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Ação
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($boletos->take(10) as $boleto)
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ str_pad($boleto->mes, 2, '0', STR_PAD_LEFT) }}/{{ $boleto->ano }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $boleto->data_vencimento->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    R$ {{ number_format($boleto->valor, 2, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    @if($boleto->cobranca && $boleto->cobranca->status === 'pago')
                                    <span class="inline-flex px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                                        ✓ Pago
                                    </span>
                                    @elseif($boleto->status === 'vencido')
                                    <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">
                                        ⚠ Vencido
                                    </span>
                                    @else
                                    <span class="inline-flex px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">
                                        ⏳ Em aberto
                                    </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="{{ route('portal.boletos.download', $boleto) }}"
                                        class="inline-flex items-center justify-center px-3 py-2
                                        bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg
                                        shadow-sm hover:shadow-md transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                </td>

                                <td class="px-6 py-4 text-sm text-center">
                                    @if($boleto->nota_fiscal)
                                    <a href="{{ route('portal.notas.download', $boleto->nota_fiscal) }}"
                                        class="inline-flex items-center justify-center px-3 py-2
                                        bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg
                                        shadow-sm hover:shadow-md transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    @if($boleto->foiBaixado())
                                    <div class="text-gray-600 text-xs">
                                        <span class="font-semibold">Baixado</span><br>
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

                <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
                    <a href="https://gestor.alfa.solucoesgrupo.com/portal/financeiro" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all">
                        Ver Todos
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
            @else
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center border border-gray-100">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-500 text-lg font-medium">Nenhum boleto disponível no momento.</p>
                <p class="text-gray-400 text-sm mt-2">Seus documentos aparecerão aqui quando forem anexados.</p>
            </div>
            @endif

        </div>
    </div>

    {{-- Chart.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <script>
        // Dados para os gráficos
        const atendimentosAbertos = {
            {
                rand(2, 8)
            }
        };
        const atendimentosEmExecucao = {
            {
                rand(3, 12)
            }
        };
        const atendimentosFinalizados = {
            {
                rand(25, 60)
            }
        };

        const nfTotal = {
            {
                rand(10, 25)
            }
        };
        const boletosTotal = {
            {
                rand(15, 35)
            }
        };

        // Gráfico 1: Distribuição de Atendimentos
        const ctxAtendimentos = document.getElementById('chartAtendimentos').getContext('2d');
        new Chart(ctxAtendimentos, {
            type: 'doughnut',
            data: {
                labels: ['Abertos', 'Em Execução', 'Finalizados'],
                datasets: [{
                    data: [atendimentosAbertos, atendimentosEmExecucao, atendimentosFinalizados],
                    backgroundColor: [
                        'rgba(234, 179, 8, 0.8)', // Amarelo
                        'rgba(249, 115, 22, 0.8)', // Laranja
                        'rgba(34, 197, 94, 0.8)' // Verde
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
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            padding: 15,
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
                        'rgba(59, 130, 246, 0.8)', // Azul
                        'rgba(139, 92, 246, 0.8)' // Roxo
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

    <style>
        /* Smooth transitions */
        * {
            @apply transition-all duration-200;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</x-app-layout>