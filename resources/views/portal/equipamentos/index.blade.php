<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Controle de Equipamentos
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gerencie seus equipamentos, manutenções e limpezas
                </p>
            </div>
        </div>
    </x-slot>

    <div class="portal-wrapper">

        <!-- Navigation Cards -->
        <div class="portal-nav-grid">
            <!-- Card Lista de Equipamentos -->
            <a href="{{ route('portal.equipamentos.lista') }}" class="portal-nav-card">
                <div class="portal-nav-card-icon-wrapper">
                    <div class="portal-nav-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                </div>
                <h3 class="portal-nav-card-title">
                    Lista de Equipamentos
                </h3>
                <p class="portal-nav-card-description">
                    Visualize todos os equipamentos cadastrados
                </p>
            </a>

            <!-- Card Setores -->
            <a href="{{ route('portal.equipamentos.setores') }}" class="portal-nav-card">
                <div class="portal-nav-card-icon-wrapper">
                    <div class="portal-nav-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="portal-nav-card-title">
                    Setores
                </h3>
                <p class="portal-nav-card-description">
                    Organize equipamentos por localização
                </p>
            </a>

            <!-- Card Responsáveis -->
            <a href="{{ route('portal.equipamentos.responsaveis') }}" class="portal-nav-card">
                <div class="portal-nav-card-icon-wrapper">
                    <div class="portal-nav-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <h3 class="portal-nav-card-title">
                    Responsáveis
                </h3>
                <p class="portal-nav-card-description">
                    Gerencie responsáveis pelos equipamentos
                </p>
            </a>

            {{-- Card PMOC - Oculto temporariamente (plano de manutenção não será utilizado no momento) --}}
            {{--<a href="{{ route('portal.equipamentos.pmoc') }}" class="portal-nav-card">
                <div class="portal-nav-card-icon-wrapper">
                    <div class="portal-nav-card-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <h3 class="portal-nav-card-title">
                    Plano de Manutenção (PMOC)
                </h3>
                <p class="portal-nav-card-description">
                    Relatório de manutenção preventiva
                </p>
            </a>--}}
        </div>

        <!-- Dashboard Resumo -->
        <div class="portal-section">
            <h2 class="portal-section-title">
                <svg class="w-6 h-6 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Resumo de Equipamentos
            </h2>

            <div class="portal-stats-grid">
                <!-- Total Equipamentos -->
                <div class="portal-stat-card portal-stat-card--blue">
                    <p class="portal-stat-label">Total de Equipamentos</p>
                    <p class="portal-stat-value">{{ $totalEquipamentos }}</p>
                </div>

                <!-- Manutenções em Dia -->
                <div class="portal-stat-card portal-stat-card--green">
                    <p class="portal-stat-label">Em Dia</p>
                    <p class="portal-stat-value">{{ $manutencoesEmDia }}</p>
                </div>

                <!-- Próximas do Vencimento -->
                <div class="portal-stat-card portal-stat-card--yellow">
                    <p class="portal-stat-label">Atenção</p>
                    <p class="portal-stat-value">{{ $manutencoesProximo }}</p>
                </div>

                <!-- Vencidas -->
                <div class="portal-stat-card portal-stat-card--red">
                    <p class="portal-stat-label">Vencidas</p>
                    <p class="portal-stat-value">{{ $manutencoesVencidas }}</p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
