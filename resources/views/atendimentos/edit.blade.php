<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/edit.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Atendimentos', 'url' => route('atendimentos.index')],
            ['label' => 'Editar #' . $atendimento->numero_atendimento]
        ]" />
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="page-wrapper">
        <div class="content-grid">

            {{-- ================= COLUNA PRINCIPAL ================= --}}
            <div class="main-content">

                {{-- ===== NOVO ANDAMENTO ===== --}}
                @if(strtolower($atendimento->status_atual) !== 'concluido')
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
                            class="form-section" enctype="multipart/form-data">
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

                            <div class="form-group">
                                <label for="fotos">
                                    Fotos do Andamento
                                </label>
                                <input type="file" name="fotos[]" accept="image/*" class="@error('fotos') border-red-500 @enderror">
                                @error('fotos')
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

                {{-- ===== DESCRIÇÃO DO ATENDIMENTO ===== --}}
                @if(!empty($atendimento->descricao))
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Descrição do Atendimento
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="form-section">
                            <p class="text-sm text-gray-900 whitespace-pre-line">
                                {{ $atendimento->descricao }}
                            </p>
                        </div>
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
                        @if($atendimento->status_atual !== 'finalizacao' && $atendimento->status_atual !==
                        'concluido')
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
                                    <div class="timeline-time">
                                        {{ $andamento->created_at->format('d/m/Y \à\s H:i') }}
                                    </div>
                                    <div class="timeline-text">{{ $andamento->descricao }}</div>

                                    {{-- FOTOS --}}
                                    @if($andamento->fotos->count())
                                    <div class="timeline-images">
                                        @foreach($andamento->fotos as $foto)
                                        <a href="{{ asset('storage/' . $foto->arquivo) }}" target="_blank" class="timeline-image">
                                            <img src="{{ asset('storage/' . $foto->arquivo) }}" alt="Anexo">
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
                                    @foreach(['orcamento' => 'Orçamento', 'aberto' => 'Aberto', 'em_atendimento' =>
                                    'Em
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
                                    <option value="baixa" @selected($atendimento->prioridade === 'baixa')>Baixa
                                    </option>
                                    <option value="media" @selected($atendimento->prioridade === 'media')>Média
                                    </option>
                                    <option value="alta" @selected($atendimento->prioridade === 'alta')>Alta
                                    </option>
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

                {{-- ===== AGENDAMENTO ===== --}}
                @if($atendimento->data_inicio_agendamento)
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                            </svg>
                            Agendamento
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="form-section">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs uppercase font-bold text-gray-500">Data do Agendamento</p>
                                <button type="button" class="text-xs font-medium text-blue-600 hover:text-blue-800 btn-reagendar"
                                    data-atendimento-id="{{ $atendimento->id }}"
                                    data-funcionario-id="{{ $atendimento->funcionario_id }}"
                                    data-data="{{ $atendimento->data_inicio_agendamento->format('Y-m-d') }}"
                                    data-periodo="{{ $atendimento->periodo_agendamento }}"
                                    data-hora="{{ $atendimento->data_inicio_agendamento->format('H:i') }}"
                                    data-duracao="{{ $atendimento->duracao_agendamento_minutos ? intdiv($atendimento->duracao_agendamento_minutos, 60) : 1 }}">
                                    Reagendar
                                </button>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $atendimento->data_inicio_agendamento->format('d/m/Y') }}
                                @if($atendimento->data_inicio_agendamento->format('H:i') !== '00:00')
                                    <span class="text-gray-500 font-normal">às {{ $atendimento->data_inicio_agendamento->format('H:i') }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="form-section">
                            <p class="text-xs uppercase font-bold text-gray-500 mb-1">Período</p>
                            <p class="text-sm font-semibold text-gray-900">
                                @php
                                    $periodos = ['manha' => 'Manhã', 'tarde' => 'Tarde', 'noite' => 'Noite', 'dia_todo' => 'Dia Todo'];
                                @endphp
                                {{ $periodos[$atendimento->periodo_agendamento] ?? '—' }}
                            </p>
                        </div>

                        <div class="form-section" style="margin-bottom: 0;">
                            <p class="text-xs uppercase font-bold text-gray-500 mb-1">Técnico Responsável</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $atendimento->funcionario?->nome ?? 'Não atribuído' }}
                            </p>
                        </div>
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

{{-- Modal de Reagendamento --}}
<div id="modal-reagendar-agendamento" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:60;">
    <div style="max-width:920px; margin:3vh auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,.25);">
        <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-weight:700; color:#1f2937;" id="modal-titulo-reagendar">Reagendar Agendamento</h3>
            <button type="button" id="fechar-modal-reagendar" style="font-size:20px; color:#6b7280;">&times;</button>
        </div>
        <div style="padding:18px; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px;">
            <div>
                <label class="text-sm font-medium text-gray-700">Técnico</label>
                <select id="reagendar_funcionario_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Selecione</option>
                    @foreach($funcionarios as $funcionario)
                    <option value="{{ $funcionario->id }}">{{ $funcionario->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Data</label>
                <input id="reagendar_data" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Período</label>
                <select id="reagendar_periodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Selecione</option>
                    <option value="dia_todo">Dia todo (08:00-17:00, 9 horas)</option>
                    <option value="manha">Manhã (08:00-12:00)</option>
                    <option value="tarde">Tarde (13:00-18:00)</option>
                    <option value="noite">Noite (18:01-21:59)</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Hora início</label>
                <input id="reagendar_hora_inicio" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Duração (horas)</label>
                <select id="reagendar_duracao_horas" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Selecione</option>
                    <option value="1">1 hora</option>
                    <option value="2">2 horas</option>
                    <option value="3">3 horas</option>
                    <option value="4">4 horas</option>
                    <option value="5">5 horas</option>
                    <option value="6">6 horas</option>
                    <option value="7">7 horas</option>
                    <option value="8">8 horas</option>
                    <option value="9">9 horas</option>
                </select>
            </div>
        </div>
        <div style="padding:0 18px 12px;">
            <div class="text-sm font-semibold text-gray-700 mb-2">Agenda do dia (calendário por técnico)</div>
            <div id="agenda-calendario-reagendar" style="max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:8px; padding:10px;"></div>
        </div>
        <div style="padding:14px 18px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:8px;">
            <button type="button" id="cancelar-reagendar" class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancelar</button>
            <button type="button" id="confirmar-reagendar" class="px-4 py-2 rounded-lg bg-[#3f9cae] text-white text-sm">Salvar reagendamento</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalReagendar = document.getElementById('modal-reagendar-agendamento');
        const reagendarDataInput = document.getElementById('reagendar_data');
        const reagendarPeriodoInput = document.getElementById('reagendar_periodo');
        const reagendarFuncionarioInput = document.getElementById('reagendar_funcionario_id');
        const reagendarHoraInicioInput = document.getElementById('reagendar_hora_inicio');
        const reagendarDuracaoInput = document.getElementById('reagendar_duracao_horas');
        const calendarioReagendarWrapper = document.getElementById('agenda-calendario-reagendar');
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

        let contextoReagendar = null;

        // Evento para selecionar "Dia todo" automaticamente
        reagendarPeriodoInput.addEventListener('change', function() {
            if (this.value === 'dia_todo') {
                reagendarHoraInicioInput.value = '08:00';
                reagendarDuracaoInput.value = '9';
                reagendarHoraInicioInput.disabled = true;
                reagendarDuracaoInput.disabled = true;
            } else {
                reagendarHoraInicioInput.disabled = false;
                reagendarDuracaoInput.disabled = false;
            }
        });

        // Abrir modal de reagendamento
        document.querySelectorAll('.btn-reagendar').forEach(button => {
            button.addEventListener('click', function() {
                contextoReagendar = {
                    atendimentoId: this.dataset.atendimentoId,
                    funcionarioId: this.dataset.funcionarioId,
                    data: this.dataset.data,
                    periodo: this.dataset.periodo,
                    hora: this.dataset.hora,
                    duracao: this.dataset.duracao
                };

                reagendarFuncionarioInput.value = contextoReagendar.funcionarioId || '';
                reagendarDataInput.value = contextoReagendar.data || new Date().toISOString().slice(0, 10);
                reagendarPeriodoInput.value = contextoReagendar.periodo || '';
                reagendarHoraInicioInput.value = contextoReagendar.hora || '';
                reagendarDuracaoInput.value = contextoReagendar.duracao || '1';

                if (reagendarPeriodoInput.value === 'dia_todo') {
                    reagendarHoraInicioInput.disabled = true;
                    reagendarDuracaoInput.disabled = true;
                }

                modalReagendar.style.display = 'block';
                carregarAgendaReagendar();
            });
        });

        // Fechar modal
        document.getElementById('fechar-modal-reagendar').addEventListener('click', function() {
            modalReagendar.style.display = 'none';
            contextoReagendar = null;
        });

        document.getElementById('cancelar-reagendar').addEventListener('click', function() {
            modalReagendar.style.display = 'none';
            contextoReagendar = null;
        });

        // Carregar agenda
        async function carregarAgendaReagendar() {
            const data = reagendarDataInput.value;
            const periodo = reagendarPeriodoInput.value;

            if (!data) {
                calendarioReagendarWrapper.innerHTML = '<div class="text-sm text-gray-500">Selecione a data para visualizar a agenda.</div>';
                return;
            }

            calendarioReagendarWrapper.innerHTML = '<div class="text-sm text-gray-500">Carregando agenda...</div>';

            try {
                const url = `{{ route('agenda-tecnica.disponibilidade') }}?data=${encodeURIComponent(data)}${periodo ? `&periodo=${encodeURIComponent(periodo)}` : ''}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const json = await response.json();

                const blocos = (json.tecnicos || []).map(tecnico => {
                    const agendaTecnico = (json.agendamentos || []).filter(item => String(item.funcionario_id) === String(tecnico.id));
                    const linhas = agendaTecnico.length
                        ? agendaTecnico.map(item => `<div style="font-size:12px;color:#374151;margin-top:3px;">${item.inicio} - ${item.fim} • #${item.numero_atendimento} • ${item.cliente}</div>`).join('')
                        : '<div style="font-size:12px;color:#16a34a;margin-top:3px;">Livre</div>';

                    return `<div style="border-bottom:1px solid #f3f4f6;padding:8px 0;"><div style="font-weight:600;font-size:13px;">${tecnico.nome}</div>${linhas}</div>`;
                });

                calendarioReagendarWrapper.innerHTML = blocos.length ? blocos.join('') : '<div class="text-sm text-gray-500">Nenhum técnico encontrado.</div>';
            } catch (error) {
                calendarioReagendarWrapper.innerHTML = '<div class="text-sm text-red-600">Não foi possível carregar a agenda.</div>';
            }
        }

        // Confirmar reagendamento
        document.getElementById('confirmar-reagendar').addEventListener('click', function() {
            if (!contextoReagendar) {
                return;
            }

            const funcionarioId = reagendarFuncionarioInput.value;
            const data = reagendarDataInput.value;
            const periodo = reagendarPeriodoInput.value;
            const horaInicio = reagendarHoraInicioInput.value;
            const duracaoHoras = reagendarDuracaoInput.value;

            if (!funcionarioId || !data || !periodo || !horaInicio || !duracaoHoras) {
                alert('Preencha todos os campos de reagendamento.');
                return;
            }

            if (!contextoReagendar.atendimentoId) {
                alert('Não foi possível identificar o atendimento para reagendar.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/atendimentos/${contextoReagendar.atendimentoId}/reagendar-agendamento`;

            const payload = {
                _token: token,
                funcionario_id: funcionarioId,
                data_agendamento: data,
                periodo_agendamento: periodo,
                hora_inicio: horaInicio,
                duracao_horas: duracaoHoras,
            };

            Object.entries(payload).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });

        // Carregar agenda ao mudar data/período
        reagendarDataInput.addEventListener('change', carregarAgendaReagendar);
        reagendarPeriodoInput.addEventListener('change', carregarAgendaReagendar);
    });
</script>
