<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Administrativo
        </h2>
    </x-slot>

    <!-- Link do CSS customizado -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <div class="dashboard-container">
        <div class="dashboard-grid">

            {{-- CARD 1: CLIENTES CADASTRADOS --}}
            <div class="dashboard-card blue">
                <div class="card-content">
                    <div class="card-info">
                        <p class="card-label">Clientes Cadastrados</p>
                        <p class="card-value blue">{{ $totalClientes }}</p>
                    </div>
                    <div class="card-icon blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM12 14a8 8 0 00-8 8v2h16v-2a8 8 0 00-8-8z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- CARD 2: CLIENTES ATIVOS --}}
            <div class="dashboard-card green">
                <div class="card-content">
                    <div class="card-info">
                        <p class="card-label">Clientes Ativos</p>
                        <p class="card-value green">{{ $clientesAtivos }}</p>
                    </div>
                    <div class="card-icon green">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- CARD 3: CLIENTES INATIVOS --}}
            <div class="dashboard-card red">
                <div class="card-content">
                    <div class="card-info">
                        <p class="card-label">Clientes Inativos</p>
                        <p class="card-value red">{{ $clientesInativos }}</p>
                    </div>
                    <div class="card-icon red">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- CARD 4: RECEITA PREVISTA --}}
            <div class="dashboard-card yellow">
                <div class="card-content">
                    <div class="card-info">
                        <p class="card-label">Receita Prevista</p>
                        <p class="card-date">{{ str_pad(now()->month,2,'0',STR_PAD_LEFT) }}/{{ now()->year }}</p>
                        <p class="card-value yellow">
                            R$ {{ number_format($receitaPrevista, 2, ',', '.') }}
                        </p>
                    </div>
                    <div class="card-icon yellow">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- CARD 5: RECEITA REALIZADA --}}
            <div class="dashboard-card green">
                <div class="card-content">
                    <div class="card-info">
                        <p class="card-label">Receita Realizada</p>
                        <p class="card-date">{{ str_pad(now()->month,2,'0',STR_PAD_LEFT) }}/{{ now()->year }}</p>
                        <p class="card-value green">
                            R$ {{ number_format($receitaRealizada, 2, ',', '.') }}
                        </p>
                    </div>
                    <div class="card-icon green">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- CARD 6: COBRANÇAS GERADAS --}}
            <div class="dashboard-card blue">
                <div class="card-content">
                    <div class="card-info">
                        <p class="card-label">Cobranças Geradas</p>
                        <p class="card-value blue">{{ $clientesComCobranca }}</p>
                    </div>
                    <div class="card-icon blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- CARD 7: BOLETOS NÃO BAIXADOS --}}
            <div class="dashboard-card red">
                <div class="card-content">
                    <div class="card-info">
                        <p class="card-label">Boletos Não Baixados</p>
                        <p class="card-value red">{{ $clientesComBoletoNaoBaixado }}</p>
                    </div>
                    <div class="card-icon red">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4v2m0 4v2M6.343 3.665c.886-.887 2.318-.887 3.203 0l6.364 6.364c.884.884.884 2.317 0 3.2l-6.364 6.365c-.885.885-2.317.885-3.203 0L3.14 13.23c-.884-.883-.884-2.316 0-3.2L6.343 3.665z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>