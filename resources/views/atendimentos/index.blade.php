<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

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
                            <option value="dia" @selected(request('periodo')==='dia' )>Hoje</option>
                            <option value="semana" @selected(request('periodo')==='semana' )>Semana</option>
                            <option value="mes" @selected(request('periodo', 'mes' )==='mes' )>Mês</option>
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
                                    <option value="baixa" @selected($atendimento->prioridade === 'baixa')>Baixa
                                    </option>
                                    <option value="media" @selected($atendimento->prioridade === 'media')>Média
                                    </option>
                                    <option value="alta" @selected($atendimento->prioridade === 'alta')>Alta
                                    </option>
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
                Mostrando <strong>{{ $atendimentos->count() }}</strong> de
                <strong>{{ $atendimentos->total() }}</strong>
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
                        'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]')
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