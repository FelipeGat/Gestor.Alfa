<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                    Controle de Equipamentos
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gerencie seus equipamentos, manutenções e limpezas
                </p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-green-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-8">

            <!-- Navigation Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card Lista de Equipamentos -->
                <a href="{{ route('portal.equipamentos.lista') }}"
                    class="group bg-white rounded-2xl shadow-lg p-6 border-2 border-transparent
                      hover:border-blue-500 hover:shadow-2xl transition-all duration-300
                      transform hover:scale-105 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-blue-100 to-blue-50 p-3 rounded-xl">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                        Lista de Equipamentos
                    </h3>

                    <p class="text-gray-600 text-sm">
                        Visualize todos os equipamentos cadastrados
                    </p>
                </a>

                <!-- Card Setores -->
                <a href="{{ route('portal.equipamentos.setores') }}"
                    class="group bg-white rounded-2xl shadow-lg p-6 border-2 border-transparent
                      hover:border-green-500 hover:shadow-2xl transition-all duration-300
                      transform hover:scale-105 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-green-100 to-green-50 p-3 rounded-xl">
                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-green-600 transition-colors">
                        Setores
                    </h3>

                    <p class="text-gray-600 text-sm">
                        Organize equipamentos por localização
                    </p>
                </a>

                <!-- Card Responsáveis -->
                <a href="{{ route('portal.equipamentos.responsaveis') }}"
                    class="group bg-white rounded-2xl shadow-lg p-6 border-2 border-transparent
                      hover:border-purple-500 hover:shadow-2xl transition-all duration-300
                      transform hover:scale-105 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-purple-100 to-purple-50 p-3 rounded-xl">
                            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors">
                        Responsáveis
                    </h3>

                    <p class="text-gray-600 text-sm">
                        Gerencie responsáveis pelos equipamentos
                    </p>
                </a>

                <!-- Card PMOC -->
                <a href="{{ route('portal.equipamentos.pmoc') }}"
                    class="group bg-white rounded-2xl shadow-lg p-6 border-2 border-transparent
                      hover:border-orange-500 hover:shadow-2xl transition-all duration-300
                      transform hover:scale-105 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-orange-100 to-orange-50 p-3 rounded-xl">
                            <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors">
                        Plano de Manutenção (PMOC)
                    </h3>

                    <p class="text-gray-600 text-sm">
                        Relatório de manutenção preventiva
                    </p>
                </a>
            </div>

            <!-- Dashboard Resumo -->
            <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center">
                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Resumo de Equipamentos
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Equipamentos -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border-l-4 border-blue-600 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">Total de Equipamentos</p>
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-blue-600 mb-1">
                            {{ $totalEquipamentos }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Equipamentos cadastrados
                        </p>
                    </div>

                    <!-- Manutenções em Dia -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border-l-4 border-green-600 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">Em Dia</p>
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-green-600 mb-1">
                            {{ $manutencoesEmDia }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Manutenções em dia
                        </p>
                    </div>

                    <!-- Próximas do Vencimento -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border-l-4 border-yellow-600 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">Atenção</p>
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-yellow-600 mb-1">
                            {{ $manutencoesProximo }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Próximas do vencimento
                        </p>
                    </div>

                    <!-- Vencidas -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border-l-4 border-red-600 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">Vencidas</p>
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-red-600 mb-1">
                            {{ $manutencoesVencidas }}
                        </p>
                        <p class="text-xs text-gray-600">
                            Manutenções vencidas
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
