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
        margin-bottom: 1rem;
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

    .filters-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
        transform: translateY(-1px);
    }

    .btn-success {
        background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
        color: white;
    }

    .btn-success:hover:not(:disabled) {
        box-shadow: 0 4px 6px rgba(34, 197, 94, 0.3);
        transform: translateY(-1px);
    }

    .btn svg {
        width: 18px;
        height: 18px;
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

    .table-select {
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        transition: all 0.2s;
    }

    .table-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    .table-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-edit {
        border: 1px solid #3b82f6;
        color: #3b82f6;
        background: white;
    }

    .btn-edit:hover {
        background: #eff6ff;
    }

    .btn-delete {
        border: 1px solid #ef4444;
        color: #ef4444;
        background: white;
    }

    .btn-delete:hover {
        background: #fef2f2;
    }

    /* ========================= PAGINAÇÃO ========================= */
    .pagination-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pagination-info {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .pagination-links {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .pagination-link {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .pagination-link:hover {
        border-color: #3b82f6;
        color: #3b82f6;
        background: #f0f9ff;
    }

    .pagination-link.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .pagination-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
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
        margin-bottom: 1.5rem;
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
    }

    .mobile-card-badges {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .mobile-card-actions {
        display: flex;
        gap: 0.5rem;
    }

    .mobile-card-actions .btn {
        flex: 1;
        justify-content: center;
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

        .filters-actions {
            flex-direction: column;
        }

        .filters-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .table-wrapper {
            display: none;
        }

        .mobile-cards {
            display: flex;
            flex-direction: column;
        }

        .pagination-wrapper {
            flex-direction: column;
            text-align: center;
        }

        .pagination-links {
            justify-content: center;
            width: 100%;
        }

        .table-actions {
            flex-direction: column;
        }

        .table-actions .btn {
            width: 100%;
        }
    }

    @media (max-width: 640px) {
        .page-wrapper {
            padding: 0.75rem;
        }

        .filters-card {
            padding: 1rem;
        }

        .pagination-wrapper {
            padding: 1rem;
        }

        .mobile-card {
            padding: 0.75rem;
        }
    }
    </style>

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <h1 class="page-title">📋 Atendimentos</h1>
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
                            placeholder="Cliente ou solicitante">
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

                <div class="filters-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                        Filtrar
                    </button>

                    <a href="{{ route('atendimentos.create') }}" class="btn btn-success">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Novo Atendimento
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
                            <th>Solicitante</th>
                            <th>Assunto</th>
                            <th>Empresa</th>
                            <th>Técnico</th>
                            <th style="width: 100px; text-align: center;">Prioridade</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th style="width: 100px;">Data</th>
                            <th style="width: 100px; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($atendimentos as $atendimento)
                        <tr>
                            {{-- Número --}}
                            <td>
                                <span class="table-number">{{ $atendimento->numero_atendimento }}</span>
                            </td>

                            {{-- Solicitante --}}
                            <td>
                                <div>
                                    <p style="font-weight: 600; color: #1f2937;">
                                        @if($atendimento->cliente)
                                        {{ $atendimento->cliente->nome }}
                                        @else
                                        {{ $atendimento->nome_solicitante }}
                                        @endif
                                    </p>
                                    @if($atendimento->telefone_solicitante)
                                    <p style="font-size: 0.8125rem; color: #6b7280;">
                                        {{ $atendimento->telefone_solicitante }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Assunto --}}
                            <td>{{ optional($atendimento->assunto)->nome ?? '—' }}</td>

                            {{-- Empresa --}}
                            <td>{{ optional($atendimento->empresa)->nome_fantasia ?? '—' }}</td>

                            {{-- Técnico (Editável) --}}
                            <td>
                                <select data-id="{{ $atendimento->id }}" data-campo="funcionario_id"
                                    class="campo-editavel table-select">
                                    <option value="">—</option>
                                    @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}" @selected($atendimento->funcionario_id ==
                                        $funcionario->id)>
                                        {{ $funcionario->nome }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Prioridade (Editável) --}}
                            <td style="text-align: center;">
                                <select data-id="{{ $atendimento->id }}" data-campo="prioridade"
                                    class="campo-editavel table-select">
                                    <option value="baixa" @selected($atendimento->prioridade === 'baixa')>Baixa</option>
                                    <option value="media" @selected($atendimento->prioridade === 'media')>Média</option>
                                    <option value="alta" @selected($atendimento->prioridade === 'alta')>Alta</option>
                                </select>
                            </td>

                            {{-- Status (Editável) --}}
                            <td style="text-align: center;">
                                <select data-id="{{ $atendimento->id }}" data-campo="status"
                                    class="campo-editavel table-select">
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
                                    <option value="{{ $value }}" @selected($atendimento->status_atual === $value)>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Data --}}
                            <td>{{ $atendimento->data_atendimento->format('d/m/Y') }}</td>

                            {{-- Ações --}}
                            <td style="text-align: center;">
                                <div class="table-actions">
                                    <a href="{{ route('atendimentos.edit', $atendimento) }}"
                                        class="btn btn-sm btn-edit">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                        Editar
                                    </a>

                                    <form action="{{ route('atendimentos.destroy', $atendimento) }}" method="POST"
                                        onsubmit="return confirm('Deseja excluir este atendimento?')"
                                        style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-delete">
                                            <svg fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= MOBILE CARDS ================= --}}
        <div class="mobile-cards">
            @foreach($atendimentos as $atendimento)
            <div class="mobile-card">
                <div class="mobile-card-header">
                    <div>
                        <div class="mobile-card-title">Atendimento #{{ $atendimento->numero_atendimento }}</div>
                        <div class="mobile-card-date">{{ $atendimento->data_atendimento->format('d/m/Y \à\s H:i') }}
                        </div>
                    </div>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Solicitante</span>
                    <span class="mobile-card-value">
                        @if($atendimento->cliente)
                        {{ $atendimento->cliente->nome }}
                        @else
                        {{ $atendimento->nome_solicitante }}
                        @endif
                    </span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Assunto</span>
                    <span class="mobile-card-value">{{ optional($atendimento->assunto)->nome ?? '—' }}</span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Empresa</span>
                    <span class="mobile-card-value">{{ optional($atendimento->empresa)->nome_fantasia ?? '—' }}</span>
                </div>

                <div class="mobile-card-badges">
                    <span class="table-badge badge-{{ $atendimento->prioridade }}">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                        {{ ucfirst($atendimento->prioridade) }}
                    </span>
                </div>

                <div class="mobile-card-actions">
                    <a href="{{ route('atendimentos.edit', $atendimento) }}" class="btn btn-sm btn-edit">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Editar
                    </a>

                    <form action="{{ route('atendimentos.destroy', $atendimento) }}" method="POST"
                        onsubmit="return confirm('Deseja excluir este atendimento?')" style="flex: 1;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-delete"
                            style="width: 100%; justify-content: center;">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ================= PAGINAÇÃO ================= --}}
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Mostrando <strong>{{ $atendimentos->count() }}</strong> de <strong>{{ $atendimentos->total() }}</strong>
                atendimentos
            </div>

            <div class="pagination-links">
                {{-- Link Anterior --}}
                @if($atendimentos->onFirstPage())
                <span class="pagination-link disabled">← Anterior</span>
                @else
                <a href="{{ $atendimentos->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                @endif

                {{-- Links de Página --}}
                @foreach($atendimentos->getUrlRange(1, $atendimentos->lastPage()) as $page => $url)
                @if($page == $atendimentos->currentPage())
                <span class="pagination-link active">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                @endif
                @endforeach

                {{-- Link Próximo --}}
                @if($atendimentos->hasMorePages())
                <a href="{{ $atendimentos->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                @else
                <span class="pagination-link disabled">Próximo →</span>
                @endif
            </div>
        </div>

        @else
        {{-- ================= ESTADO VAZIO ================= --}}
        <div class="empty-state">
            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="empty-state-title">Nenhum atendimento registrado</h3>
            <p class="empty-state-text">Nenhum atendimento foi encontrado com os filtros aplicados.</p>
            <a href="{{ route('atendimentos.create') }}" class="btn btn-success">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Criar Novo Atendimento
            </a>
        </div>
        @endif

    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script>
    /* ========================= EDIÇÃO INLINE ========================= */
    document.querySelectorAll('.campo-editavel').forEach(el => {
        el.addEventListener('change', async function() {
            const id = this.dataset.id;
            const campo = this.dataset.campo;
            const valor = this.value;

            try {
                const response = await fetch(`/atendimentos/${id}/atualizar-campo`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        campo: campo,
                        valor: valor
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    console.error('Erro:', data.message);
                    alert(data.message || 'Erro ao atualizar');
                    location.reload();
                }
            } catch (error) {
                console.error('Erro de comunicação:', error);
                alert('Erro de comunicação');
                location.reload();
            }
        });
    });
    </script>

</x-app-layout>