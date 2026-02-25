<x-app-layout>
    {{-- ================= ESTILOS ================= --}}
    <style>
    /* ========================= CONTAINER ========================= */
    .page-wrapper {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
    }

    /* ========================= FILTROS ========================= */
    .filters-card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-top: 3px solid #3b82f6;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .filter-group input,
    .filter-group select {
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #f0f9ff;
    }

    /* ========================= TABELA ========================= */
    .table-card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: linear-gradient(to right, #f3f4f6, #e5e7eb);
        border-bottom: 2px solid #d1d5db;
    }

    .table thead th {
        padding: 1rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.875rem;
        color: #374151;
    }

    .table tbody tr {
        transition: background-color 0.2s;
        cursor: pointer;
    }

    .table tbody tr:hover {
        background-color: #f9fafb;
    }

    .table-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1e40af;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .table-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-alta {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-media {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-baixa {
        background: #dcfce7;
        color: #166534;
    }

    .badge-status {
        background: #dbeafe;
        color: #1e40af;
    }

    /* ========================= MOBILE CARDS ========================= */
    .mobile-cards {
        display: none;
        gap: 1rem;
    }

    .mobile-card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1rem;
        border-left: 4px solid #3b82f6;
        cursor: pointer;
        transition: all 0.2s;
    }

    .mobile-card:active {
        transform: scale(0.98);
    }

    .mobile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .mobile-card-title {
        font-weight: 700;
        color: #1f2937;
    }

    .mobile-card-date {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .mobile-card-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
    }

    .mobile-card-label {
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    .mobile-card-value {
        color: #1f2937;
        font-weight: 500;
        text-align: right;
    }

    .mobile-card-badges {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }

    /* ========================= EMPTY STATE ========================= */
    .empty-state {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 3rem 1.5rem;
        text-align: center;
    }

    .empty-state-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        color: #d1d5db;
    }

    .empty-state-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .empty-state-text {
        color: #6b7280;
    }

    /* ========================= RESPONSIVE ========================= */
    @media (max-width: 1024px) {
        .filters-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .page-wrapper {
            padding: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .table-wrapper {
            display: none;
        }

        .mobile-cards {
            display: flex;
            flex-direction: column;
        }
    }

    @media (max-width: 640px) {
        .page-wrapper {
            padding: 0.75rem;
        }

        .filters-card {
            padding: 1rem;
        }

        .mobile-card {
            padding: 0.75rem;
        }
    }
    </style>

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <h1 class="page-title">Meus Atendimentos</h1>
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="page-wrapper">

        {{-- ================= FILTROS ================= --}}
        <div class="filters-card">
            <form method="GET" id="filterForm">
                <div class="filters-grid">
                    {{-- BUSCA --}}
                    <div class="filter-group">
                        <label for="search">Buscar</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Cliente ou assunto">
                    </div>

                    {{-- STATUS --}}
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">Todos</option>
                            @foreach([
                            'orcamento' => 'Orçamento',
                            'aberto' => 'Aberto',
                            'em_atendimento' => 'Em Atendimento',
                            'pendente_cliente' => 'Pendente Cliente',
                            'pendente_fornecedor' => 'Pendente Fornecedor',
                            'garantia' => 'Garantia',
                            'finalizacao' => 'Finalização',
                            'concluido' => 'Concluído'
                            ] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status')===$value)>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PRIORIDADE --}}
                    <div class="filter-group">
                        <label for="prioridade">Prioridade</label>
                        <select id="prioridade" name="prioridade">
                            <option value="">Todas</option>
                            <option value="alta" @selected(request('prioridade')==='alta' )>Alta</option>
                            <option value="media" @selected(request('prioridade')==='media' )>Média</option>
                            <option value="baixa" @selected(request('prioridade')==='baixa' )>Baixa</option>
                        </select>
                    </div>

                    {{-- PERÍODO --}}
                    <div class="filter-group">
                        <label for="periodo">Período</label>
                        <select id="periodo" name="periodo">
                            <option value="dia" @selected(request('periodo', 'dia' )==='dia' )>Hoje</option>
                            <option value="semana" @selected(request('periodo')==='semana' )>Semana</option>
                            <option value="mes" @selected(request('periodo')==='mes' )>Mês</option>
                            <option value="ano" @selected(request('periodo')==='ano' )>Ano</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 1rem; flex-wrap: wrap;">
                    <button type="submit"
                        style="padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                        Filtrar
                    </button>

                    <a href="{{ route('portal-funcionario.atendimentos.index') }}"
                        style="padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; border: 1px solid #d1d5db; cursor: pointer; background: white; color: #374151; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                        <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        {{-- ================= TABELA (DESKTOP) ================= --}}
        @if($atendimentos->count() > 0)
        <div class="table-card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Nº</th>
                            <th>Cliente</th>
                            <th>Assunto</th>
                            <th style="width: 100px; text-align: center;">Prioridade</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th style="width: 100px;">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($atendimentos as $atendimento)
                        <tr
                            onclick="window.location.href='{{ route('portal-funcionario.atendimentos.show', ['atendimento' => $atendimento->id]) }}'">
                            {{-- Número --}}
                            <td>
                                <span class="table-number">{{ $atendimento->numero_atendimento }}</span>
                            </td>

                            {{-- Cliente --}}
                            <td>
                                <p style="font-weight: 600; color: #1f2937;">
                                    {{ $atendimento->cliente->nome ?? $atendimento->nome_solicitante }}
                                </p>
                            </td>

                            {{-- Assunto --}}
                            <td>{{ $atendimento->assunto->nome ?? '—' }}</td>

                            {{-- Prioridade --}}
                            <td style="text-align: center;">
                                <span class="table-badge badge-{{ $atendimento->prioridade }}">
                                    <span
                                        style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                    {{ ucfirst($atendimento->prioridade) }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td style="text-align: center;">
                                <span class="table-badge badge-status">
                                    <span
                                        style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                    {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                                </span>
                            </td>

                            {{-- Data --}}
                            <td>{{ $atendimento->data_atendimento->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= MOBILE CARDS ================= --}}
        <div class="mobile-cards">
            @foreach($atendimentos as $atendimento)
            <div class="mobile-card"
                onclick="window.location.href='{{ route('portal-funcionario.atendimentos.show', ['atendimento' => $atendimento->id]) }}'">
                <div class="mobile-card-header">
                    <div>
                        <div class="mobile-card-title">Atendimento #{{ $atendimento->numero_atendimento }}</div>
                        <div class="mobile-card-date">{{ $atendimento->data_atendimento->format('d/m/Y \à\s H:i') }}
                        </div>
                    </div>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Cliente</span>
                    <span class="mobile-card-value">
                        {{ $atendimento->cliente->nome ?? $atendimento->nome_solicitante }}
                    </span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Assunto</span>
                    <span class="mobile-card-value">{{ $atendimento->assunto->nome ?? '—' }}</span>
                </div>

                <div class="mobile-card-badges">
                    <span class="table-badge badge-{{ $atendimento->prioridade }}">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                        {{ ucfirst($atendimento->prioridade) }}
                    </span>

                    <span class="table-badge badge-status">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                        {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        @else
        {{-- ================= ESTADO VAZIO ================= --}}
        <div class="empty-state">
            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="empty-state-title">Nenhum atendimento</h3>
            <p class="empty-state-text">Você não possui atendimentos atribuídos no momento.</p>
        </div>
        @endif

    </div>

</x-app-layout>