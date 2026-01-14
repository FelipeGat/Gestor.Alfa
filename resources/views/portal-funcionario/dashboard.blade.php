<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📊 Dashboard do Técnico
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

    .dashboard-card.orange {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
    }

    .dashboard-card.purple {
        background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
        color: white;
    }

    /* ========================= DISTRIBUIÇÃO CARDS ========================= */
    .distribution-card {
        background: white;
        padding: 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.2s;
    }

    .distribution-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    }

    .distribution-card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .distribution-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
    }

    .distribution-item:last-child {
        border-bottom: none;
    }

    .distribution-label {
        color: #4b5563;
        font-weight: 500;
    }

    .distribution-value {
        background: #f3f4f6;
        color: #1f2937;
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 600;
        min-width: 50px;
        text-align: center;
    }

    .distribution-empty {
        padding: 1rem;
        text-align: center;
        color: #9ca3af;
        font-size: 0.875rem;
        background: #f9fafb;
        border-radius: 0.375rem;
    }

    /* ========================= STATS GRID ========================= */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-item {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        border-left: 3px solid #3b82f6;
    }

    .stat-item.success {
        border-left-color: #22c55e;
    }

    .stat-item.warning {
        border-left-color: #facc15;
    }

    .stat-item.danger {
        border-left-color: #ef4444;
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
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

        .dashboard-section-title {
            font-size: 1.25rem;
        }

        .distribution-card {
            padding: 1rem;
        }

        .distribution-card-title {
            font-size: 1rem;
        }

        .distribution-item {
            font-size: 0.8rem;
            padding: 0.5rem 0;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ========================= EMPTY STATE ========================= */
    .empty-state {
        text-align: center;
        padding: 2rem;
        background: #f9fafb;
        border-radius: 0.75rem;
        border: 1px dashed #d1d5db;
    }

    .empty-state-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        color: #d1d5db;
    }

    .empty-state-text {
        color: #6b7280;
        font-size: 0.875rem;
    }
    </style>

    {{-- ================= DASHBOARD CONTAINER ================= --}}
    <div class="dashboard-wrapper">

        {{-- ================= SEÇÃO: ATENDIMENTOS ================= --}}
        <section class="dashboard-section">
            <h3 class="dashboard-section-title">📞 Atendimentos</h3>

            <div class="dashboard-cards">
                <div class="dashboard-card blue" role="region" aria-label="Atendimentos em Aberto">
                    <span>Atendimentos em Aberto</span>
                    <strong>{{ $totalEmAberto ?? 0 }}</strong>
                </div>

                <div class="dashboard-card green" role="region" aria-label="Atendimentos Finalizados">
                    <span>Atendimentos Finalizados</span>
                    <strong>{{ $totalFinalizados ?? 0 }}</strong>
                </div>
            </div>
        </section>

        {{-- ================= SEÇÃO: DISTRIBUIÇÕES ================= --}}
        <section class="dashboard-section">
            <h3 class="dashboard-section-title">📊 Distribuições</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Por Status --}}
                <div class="distribution-card">
                    <h4 class="distribution-card-title">
                        <svg class="inline-block w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 1 1 0 000-2A4 4 0 000 5v10a4 4 0 004 4h12a4 4 0 004-4V5a4 4 0 00-4-4 1 1 0 000 2 2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Por Status
                    </h4>

                    @forelse($porStatus ?? [] as $status => $total)
                    <div class="distribution-item">
                        <span class="distribution-label">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                        <span class="distribution-value">{{ $total }}</span>
                    </div>
                    @empty
                    <div class="distribution-empty">
                        <svg class="inline-block w-6 h-6 mb-2 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                            </path>
                        </svg>
                        <p>Nenhum dado disponível</p>
                    </div>
                    @endforelse
                </div><br>

                {{-- Por Assunto --}}
                <div class="distribution-card">
                    <h4 class="distribution-card-title">
                        <svg class="inline-block w-5 h-5 mr-2 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5z"></path>
                            <path fill-rule="evenodd"
                                d="M2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Por Assunto
                    </h4>

                    @forelse($porAssunto ?? [] as $assunto => $total)
                    <div class="distribution-item">
                        <span class="distribution-label">{{ $assunto }}</span>
                        <span class="distribution-value">{{ $total }}</span>
                    </div>
                    @empty
                    <div class="distribution-empty">
                        <svg class="inline-block w-6 h-6 mb-2 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                            </path>
                        </svg>
                        <p>Nenhum dado disponível</p>
                    </div>
                    @endforelse
                </div><br>

                {{-- Por Prioridade --}}
                <div class="distribution-card">
                    <h4 class="distribution-card-title">
                        <svg class="inline-block w-5 h-5 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg>
                        Por Prioridade
                    </h4>

                    @forelse($porPrioridade ?? [] as $prioridade => $total)
                    <div class="distribution-item">
                        <span class="distribution-label">{{ ucfirst($prioridade) }}</span>
                        <span class="distribution-value">{{ $total }}</span>
                    </div>
                    @empty
                    <div class="distribution-empty">
                        <svg class="inline-block w-6 h-6 mb-2 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                            </path>
                        </svg>
                        <p>Nenhum dado disponível</p>
                    </div>
                    @endforelse
                </div>

            </div>
        </section>

    </div>

</x-app-layout>