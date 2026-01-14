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
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1f2937;
    }

    .page-title .numero {
        color: #3b82f6;
        font-weight: 600;
    }

    .page-badges {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .badge-status {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-prioridade-alta {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-prioridade-media {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-prioridade-baixa {
        background: #dcfce7;
        color: #166534;
    }

    /* ========================= GRID LAYOUT ========================= */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
    }

    .main-content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    /* ========================= CARDS ========================= */
    .card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .card-header h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    .card-header svg {
        width: 20px;
        height: 20px;
        color: #3b82f6;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* ========================= SEÇÕES ========================= */
    .form-section {
        margin-bottom: 1.5rem;
    }

    .form-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-icon {
        width: 20px;
        height: 20px;
        color: #3b82f6;
    }

    /* ========================= CAMPOS ========================= */
    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .form-group label .required {
        color: #ef4444;
        margin-left: 0.25rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-family: inherit;
        transition: all 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #f0f9ff;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    /* ========================= GRID ========================= */
    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    /* ========================= BOTÕES ========================= */
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

    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-secondary:hover:not(:disabled) {
        background: #d1d5db;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
    }

    .btn svg {
        width: 18px;
        height: 18px;
    }

    .btn-group {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    /* ========================= TIMELINE ========================= */
    .timeline {
        position: relative;
    }

    .timeline-item {
        position: relative;
        padding-left: 3rem;
        padding-bottom: 2rem;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 0.625rem;
        top: 2.5rem;
        width: 1px;
        height: calc(100% + 1rem);
        background: #e5e7eb;
    }

    .timeline-dot {
        position: absolute;
        left: 0;
        top: 0;
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        background: #3b82f6;
        border: 3px solid white;
        box-shadow: 0 0 0 1px #e5e7eb;
    }

    .timeline-content {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
    }

    .timeline-header {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .timeline-time {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .timeline-text {
        margin-top: 0.75rem;
        color: #4b5563;
        font-size: 0.875rem;
        line-height: 1.5;
        white-space: pre-line;
    }

    .timeline-images {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .timeline-image {
        width: 80px;
        height: 80px;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.2s;
    }

    .timeline-image:hover {
        box-shadow: 0 0 0 2px #3b82f6;
    }

    .timeline-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* ========================= EMPTY STATE ========================= */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        background: #f9fafb;
        border: 2px dashed #e5e7eb;
        border-radius: 0.75rem;
        color: #6b7280;
    }

    .empty-state-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        color: #d1d5db;
    }

    /* ========================= ALERTA ========================= */
    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }

    /* ========================= UPLOAD ========================= */
    .upload-area {
        background: #f9fafb;
        border: 2px dashed #d1d5db;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.2s;
    }

    .upload-area:hover {
        border-color: #3b82f6;
        background: #f0f9ff;
    }

    .upload-area input[type="file"] {
        display: none;
    }

    .upload-label {
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .upload-label svg {
        width: 32px;
        height: 32px;
        color: #3b82f6;
    }

    .upload-label-text {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .upload-label-hint {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* ========================= RESPONSIVE ========================= */
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }

        .sidebar {
            grid-column: 1;
            grid-row: auto;
        }
    }

    @media (max-width: 768px) {
        .page-wrapper {
            padding: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .page-badges {
            flex-direction: column;
        }

        .badge {
            width: 100%;
            justify-content: center;
        }

        .form-grid-2 {
            grid-template-columns: 1fr;
        }

        .btn-group {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .card-header {
            padding: 1rem;
        }

        .card-body {
            padding: 1rem;
        }
    }
    </style>

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="page-header">
            <h1 class="page-title">
                Atendimento <span class="numero">#{{ $atendimento->numero_atendimento }}</span>
            </h1>

            <div class="page-badges">
                <span class="badge badge-status">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                </span>

                <span class="badge badge-prioridade-{{ $atendimento->prioridade }}">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    {{ ucfirst($atendimento->prioridade) }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="page-wrapper">
        <div class="content-grid">

            {{-- ================= COLUNA PRINCIPAL ================= --}}
            <div class="main-content">

                {{-- ===== NOVO ANDAMENTO ===== --}}
                @if($atendimento->status_atual !== 'concluido')
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            Adicionar Novo Andamento
                        </h3>
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('atendimentos.andamentos.store', $atendimento) }}"
                            class="form-section">
                            @csrf

                            <div class="form-group">
                                <label for="descricao">
                                    Descrição do Andamento
                                    <span class="required">*</span>
                                </label>
                                <textarea name="descricao" id="descricao" required
                                    placeholder="Descreva o que foi feito ou a próxima etapa..."
                                    class="@error('descricao') border-red-500 @enderror">{{ old('descricao') }}</textarea>
                                @error('descricao')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-primary">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Salvar Andamento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- ===== HISTÓRICO ===== --}}
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5 2a1 1 0 011-1h8a1 1 0 011 1v12a1 1 0 11-2 0V3H7v12a1 1 0 11-2 0V2z"
                                    clip-rule="evenodd" />
                            </svg>
                            Histórico do Atendimento
                        </h3>
                    </div>

                    <div class="card-body">
                        @if($atendimento->andamentos->count())

                        {{-- ===== UPLOAD DE FOTOS ===== --}}
                        @if($atendimento->status_atual !== 'finalizacao' && $atendimento->status_atual !== 'concluido')
                        <div class="form-section" style="margin-bottom: 2rem;">
                            <h4 class="form-section-title">
                                <svg class="form-section-icon" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
                                </svg>
                                Anexar Fotos ao Atendimento
                            </h4>

                            <form method="POST"
                                action="{{ route('andamentos.fotos.store', $atendimento->andamentos->first()) }}"
                                enctype="multipart/form-data" class="upload-area">
                                @csrf

                                <label class="upload-label">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <input type="file" name="fotos[]" multiple accept="image/*">
                                    <span class="upload-label-text">Clique ou arraste fotos aqui</span>
                                    <span class="upload-label-hint">PNG, JPG até 10MB</span>
                                </label>
                            </form>

                            <p class="text-xs text-gray-500 mt-2">
                                As fotos serão vinculadas ao andamento mais recente.
                            </p>
                        </div>
                        @endif

                        {{-- ===== TIMELINE ===== --}}
                        <div class="timeline">
                            @foreach($atendimento->andamentos as $andamento)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>

                                <div class="timeline-content">
                                    <div class="timeline-header">{{ $andamento->user->name ?? 'Sistema' }}</div>
                                    <div class="timeline-time">{{ $andamento->created_at->format('d/m/Y \à\s H:i') }}
                                    </div>
                                    <div class="timeline-text">{{ $andamento->descricao }}</div>

                                    {{-- FOTOS --}}
                                    @if($andamento->fotos->count())
                                    <div class="timeline-images">
                                        @foreach($andamento->fotos as $foto)
                                        <a href="{{ asset($foto->arquivo) }}" target="_blank" class="timeline-image">
                                            <img src="{{ asset($foto->arquivo) }}" alt="Anexo">
                                        </a>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @else
                        <div class="empty-state">
                            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p>Nenhum andamento registrado até o momento.</p>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ================= SIDEBAR ================= --}}
            <div class="sidebar">

                {{-- ===== AÇÕES ===== --}}
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" />
                            </svg>
                            Ações
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="btn-group" style="flex-direction: column;">
                            <a href="{{ route('atendimentos.index') }}" class="btn btn-secondary btn-sm"
                                style="width: 100%; justify-content: center;">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                Voltar
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ===== ATUALIZAR STATUS ===== --}}
                @if($atendimento->status_atual !== 'concluido')
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 1111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 105.199 7.09V4a1 1 0 01-1-1H4zm7 4a1 1 0 100 2 1 1 0 000-2z"
                                    clip-rule="evenodd" />
                            </svg>
                            Atualizar Status
                        </h3>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('atendimentos.status.update', $atendimento) }}"
                            class="form-section">
                            @csrf

                            <div class="form-group">
                                <label for="status">
                                    Novo Status
                                    <span class="required">*</span>
                                </label>
                                <select name="status" id="status" required
                                    class="@error('status') border-red-500 @enderror">
                                    <option value="">Selecione</option>
                                    @foreach(['orcamento' => 'Orçamento', 'aberto' => 'Aberto', 'em_atendimento' => 'Em
                                    Atendimento', 'pendente_cliente' => 'Pendente Cliente', 'pendente_fornecedor' =>
                                    'Pendente Fornecedor', 'finalizacao' => 'Finalização', 'garantia' => 'Garantia',
                                    'concluido' => 'Concluído'] as $v => $l)
                                    <option value="{{ $v }}" @selected($atendimento->status_atual === $v)>{{ $l }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="prioridade">
                                    Prioridade
                                    <span class="required">*</span>
                                </label>
                                <select name="prioridade" id="prioridade" required
                                    class="@error('prioridade') border-red-500 @enderror">
                                    <option value="baixa" @selected($atendimento->prioridade === 'baixa')>Baixa</option>
                                    <option value="media" @selected($atendimento->prioridade === 'media')>Média</option>
                                    <option value="alta" @selected($atendimento->prioridade === 'alta')>Alta</option>
                                </select>
                                @error('prioridade')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="justificativa">
                                    Justificativa
                                    <span class="required">*</span>
                                </label>
                                <textarea name="descricao" id="justificativa" required
                                    placeholder="Obrigatório para mudar status..."
                                    class="@error('descricao') border-red-500 @enderror"></textarea>
                                @error('descricao')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                Atualizar
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- ===== DETALHES ===== --}}
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 8a1 1 0 000 2h6a1 1 0 000-2H8zm0 3a1 1 0 000 2h3a1 1 0 000-2H8z"
                                    clip-rule="evenodd" />
                            </svg>
                            Detalhes
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="form-section">
                            <p class="text-xs uppercase font-bold text-gray-500 mb-1">Cliente</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $atendimento->cliente?->nome ?? $atendimento->nome_solicitante }}
                            </p>
                        </div>

                        <div class="form-section">
                            <p class="text-xs uppercase font-bold text-gray-500 mb-1">Assunto</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $atendimento->assunto->nome }}
                            </p>
                        </div>

                        <div class="form-section">
                            <p class="text-xs uppercase font-bold text-gray-500 mb-1">Técnico</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $atendimento->funcionario?->nome ?? 'Aguardando Atribuição' }}
                            </p>
                        </div>

                        <div class="form-section" style="margin-bottom: 0;">
                            <p class="text-xs uppercase font-bold text-gray-500 mb-1">Criado em</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $atendimento->created_at->format('d/m/Y \à\s H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

</x-app-layout>