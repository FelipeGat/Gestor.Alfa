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
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
    }

    .page-title span {
        color: #3b82f6;
    }

    /* ========================= CARDS ========================= */
    .content-card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* ========================= BADGES ========================= */
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

    /* ========================= TIMELINE ========================= */
    .timeline {
        position: relative;
        padding-left: 3rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2.5rem;
    }

    .timeline-icon {
        position: absolute;
        left: -3rem;
        width: 2.5rem;
        height: 2.5rem;
        background: #f0f9ff;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3b82f6;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        z-index: 1;
    }

    .timeline-content {
        background: #f9fafb;
        border-radius: 0.75rem;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .timeline-user {
        font-weight: 700;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .timeline-date {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .timeline-text {
        font-size: 0.875rem;
        color: #374151;
        line-height: 1.6;
        white-space: pre-line;
    }

    /* ========================= GRID LAYOUT ========================= */
    .main-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
    }

    /* ========================= INFO BOXES ========================= */
    .info-box {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .info-box-blue {
        background: #eff6ff;
        border: 1px solid #dbeafe;
    }

    .info-box-gray {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
    }

    .info-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
    }

    /* ========================= BUTTONS ========================= */
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
        cursor: pointer;
        width: 100%;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
        border: none;
    }

    .btn-primary:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-outline {
        background: white;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-outline:hover {
        background: #f9fafb;
    }

    .btn-warning {
        background: #f97316;
        color: white;
        border: none;
    }

    .btn-warning:hover {
        background: #ea580c;
    }

    /* ========================= IMAGES ========================= */
    .photo-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .photo-item {
        position: relative;
        width: 80px;
        height: 80px;
    }

    .photo-item img {
        width: 100%;
        height: 100%;
        object-cover: cover;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }

    .btn-remove-photo {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        border: none;
        cursor: pointer;
    }

    /* ========================= RESPONSIVE ========================= */
    @media (max-width: 1024px) {
        .main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-wrapper {
            padding: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
        }
    }
    </style>

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="page-header">
            <h1 class="page-title">Atendimento <span>#{{ $atendimento->numero_atendimento }}</span></h1>

            <div style="display: flex; gap: 0.75rem;">
                <span class="table-badge badge-status">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                    {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                </span>
                <span class="table-badge badge-{{ $atendimento->prioridade }}">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                    {{ ucfirst($atendimento->prioridade) }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="page-wrapper">
        <div class="main-grid">

            {{-- COLUNA ESQUERDA: HISTÓRICO --}}
            <div class="left-column">
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Histórico do Atendimento</h3>
                    </div>
                    <div class="card-body">
                        @if($atendimento->andamentos->count())
                        <div class="timeline">
                            @foreach($atendimento->andamentos as $andamento)
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-user">{{ $andamento->user->name ?? 'Sistema' }}</span>
                                        <span
                                            class="timeline-date">{{ $andamento->created_at->format('d/m/Y \à\s H:i') }}</span>
                                    </div>
                                    <div class="timeline-text">
                                        {{ $andamento->descricao }}
                                    </div>

                                    {{-- ANEXAR FOTOS --}}
                                    @if($atendimento->status_atual !== 'finalizacao' && $atendimento->status_atual !==
                                    'concluido')
                                    <form method="POST"
                                        action="{{ route('portal-funcionario.andamentos.fotos.store', $andamento) }}"
                                        enctype="multipart/form-data"
                                        id="fotos-inicio-form"
                                        style="margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem; align-items: flex-start;">
                                        @csrf
                                        <input type="file" name="fotos[]" id="foto-captura" accept="image/*" capture="environment" style="font-size: 0.75rem; color: #6b7280;" required>
                                        <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.8rem; width: auto; font-size: 0.75rem;">Iniciar Atendimento</button>
                                    </form>
                                    @endif

                                    {{-- GALERIA DE FOTOS --}}
                                    @if($andamento->fotos->count())
                                    <div class="photo-grid">
                                        @foreach($andamento->fotos as $foto)
                                        <div class="photo-item">
                                            <a href="{{ $foto->arquivo_url }}" target="_blank">
                                                <img src="{{ $foto->arquivo_url }}" alt="Anexo">
                                            </a>
                                            @if($atendimento->status_atual !== 'finalizacao' &&
                                            $atendimento->status_atual !== 'concluido')
                                            <form method="POST"
                                                action="{{ route('portal-funcionario.andamentos.fotos.destroy', $foto->id) }}"
                                                onsubmit="return confirm('Remover esta foto?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-remove-photo">✕</button>
                                            </form>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div style="text-align: center; padding: 3rem 0; color: #9ca3af;">
                            <svg style="width: 3rem; height: 3rem; margin: 0 auto 1rem; opacity: 0.5;" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p>Nenhum andamento registrado até o momento.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- COLUNA DIREITA: AÇÕES E DETALHES --}}
            <div class="right-column">

                {{-- CARD DE AÇÕES --}}
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Ações</h3>
                    </div>
                    <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="{{ route('portal-funcionario.index') }}" class="btn btn-outline">
                            <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Voltar ao Painel
                        </a>

                        @if($atendimento->status_atual !== 'finalizacao' && $atendimento->status_atual !== 'concluido')
                        <form method="POST"
                            action="{{ route('portal-funcionario.atendimentos.finalizacao', $atendimento) }}">
                            @csrf
                            <input type="hidden" name="status" value="finalizacao">
                            <button type="submit" class="btn btn-warning">
                                <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Enviar para Finalização
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- CARD DE DETALHES --}}
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Detalhes do Chamado</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box info-box-blue">
                            <div class="info-label">Cliente</div>
                            <div class="info-value">{{ $atendimento->cliente?->nome ?? $atendimento->nome_solicitante }}
                            </div>
                        </div>

                        <div class="info-box info-box-gray">
                            <div class="info-label">Assunto</div>
                            <div class="info-value">{{ $atendimento->assunto->nome }}</div>
                        </div>

                        <div class="info-box info-box-gray">
                            <div class="info-label">Técnico Responsável</div>
                            <div class="info-value">{{ $atendimento->funcionario?->nome ?? 'Aguardando Atribuição' }}
                            </div>
                        </div>

                        <div class="info-box info-box-gray">
                            <div class="info-label">Data de Abertura</div>
                            <div class="info-value">{{ $atendimento->data_atendimento->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
