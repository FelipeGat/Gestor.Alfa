<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/create.css')
    @vite('resources/js/cliente-busca.js')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Atendimentos', 'url' => route('atendimentos.index')],
            ['label' => 'Novo']
        ]" />
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="form-wrapper">
        <div class="form-container">

            {{-- ================= ERROS ================= --}}
            @if ($errors->any())
            <div class="error-alert" role="alert">
                <div class="error-alert-title">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    Verifique os erros abaixo:
                </div>
                <ul class="error-alert-list">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- ================= FORMULÁRIO ================= --}}
            <form action="{{ route('atendimentos.store') }}" method="POST" class="form" id="formAtendimento">
                @csrf

                {{-- ================= SEÇÃO: CLIENTE ================= --}}
                <section class="form-section">
                    <h3 class="form-section-title">
                        <svg class="form-section-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                        </svg>
                        Cliente
                    </h3>

                    <div class="form-group">
                        <div class="form-group relative">
                            <label>Cliente (opcional)</label>

                            <input type="text"
                                id="cliente_nome"
                                name="cliente_nome"
                                placeholder="Buscar cliente ou pré-cliente..."
                                autocomplete="off">

                            <input type="hidden" name="cliente_id" id="cliente_id">
                            <input type="hidden" name="pre_cliente_id" id="pre_cliente_id">
                            <input type="hidden" name="cliente_tipo" id="cliente_tipo">

                            <div id="cliente-resultados" class="search-results-container hidden"></div>
                        </div>
                        @error('cliente_id')
                        <p class="form-help text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- ================= SEÇÃO: SOLICITANTE ================= --}}
                <section class="form-section">
                    <h3 class="form-section-title">
                        <svg class="form-section-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z">
                            </path>
                        </svg>
                        Dados do Solicitante
                    </h3>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label for="nome_solicitante">
                                Nome do Solicitante
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="nome_solicitante" id="nome_solicitante" required
                                value="{{ old('nome_solicitante') }}" placeholder="Digite o nome completo"
                                class="@error('nome_solicitante') border-red-500 @enderror">
                            @error('nome_solicitante')
                            <p class="form-help text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="telefone_solicitante">
                                Telefone
                            </label>
                            <input type="text" name="telefone_solicitante" id="telefone_solicitante"
                                value="{{ old('telefone_solicitante') }}" placeholder="(00) 00000-0000"
                                class="telefone @error('telefone_solicitante') border-red-500 @enderror">
                            @error('telefone_solicitante')
                            <p class="form-help text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email_solicitante">
                                E-mail
                            </label>
                            <input type="email" name="email_solicitante" id="email_solicitante"
                                value="{{ old('email_solicitante') }}" placeholder="seu.email@exemplo.com"
                                class="@error('email_solicitante') border-red-500 @enderror">
                            @error('email_solicitante')
                            <p class="form-help text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ================= SEÇÃO: EMPRESA E ASSUNTO ================= --}}
                <section class="form-section">
                    <h3 class="form-section-title">
                        <svg class="form-section-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z">
                            </path>
                        </svg>
                        Empresa e Assunto
                    </h3>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="empresa_id">
                                Empresa
                                <span class="required">*</span>
                            </label>
                            <select name="empresa_id" id="empresa_id" required
                                class="@error('empresa_id') border-red-500 @enderror">
                                <option value="">Selecione a empresa</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id')==$empresa->id)>
                                    {{ $empresa->nome_fantasia }}
                                </option>
                                @endforeach
                            </select>
                            @error('empresa_id')
                            <p class="form-help text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="assunto_id">
                                Assunto
                                <span class="required">*</span>
                            </label>
                            <select name="assunto_id" id="assunto_id" required
                                class="@error('assunto_id') border-red-500 @enderror">
                                <option value="">Selecione a empresa primeiro</option>
                            </select>
                            @error('assunto_id')
                            <p class="form-help text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ================= SEÇÃO: DESCRIÇÃO ================= --}}
                <section class="form-section">
                    <h3 class="form-section-title">
                        <svg class="form-section-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Descrição
                    </h3>

                    <div class="form-group">
                        <label for="descricao">
                            Descrição do Atendimento
                            <span class="required">*</span>
                        </label>
                        <textarea name="descricao" id="descricao" required
                            placeholder="Descreva detalhadamente o problema ou solicitação"
                            class="@error('descricao') border-red-500 @enderror">{{ old('descricao') }}</textarea>
                        @error('descricao')
                        <p class="form-help text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                {{-- ================= SEÇÃO: CONFIGURAÇÃO ================= --}}
                <section class="form-section">
                    <h3 class="form-section-title">
                        <svg class="form-section-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Configuração
                    </h3>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label for="prioridade">
                                Prioridade
                                <span class="required">*</span>
                            </label>
                            <select name="prioridade" id="prioridade" required
                                class="@error('prioridade') border-red-500 @enderror">
                                <option value="baixa" @selected(old('prioridade')=='baixa' )>Baixa</option>
                                <option value="media" @selected(old('prioridade', 'media' )=='media' )>Média
                                </option>
                                <option value="alta" @selected(old('prioridade')=='alta' )>Alta</option>
                            </select>
                            @error('prioridade')
                            <p class="form-help text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status_inicial">
                                Status Inicial
                                <span class="required">*</span>
                            </label>
                            <select name="status_inicial" id="status_inicial" required
                                class="@error('status_inicial') border-red-500 @enderror">
                                <option value="aberto" @selected(old('status_inicial', 'aberto' )=='aberto' )>Aberto
                                </option>
                                <option value="orcamento" @selected(old('status_inicial')=='orcamento' )>Orçamento
                                </option>
                                <option value="garantia" @selected(old('status_inicial')=='garantia' )>Garantia
                                </option>
                            </select>
                            @error('status_inicial')
                            <p class="form-help text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="funcionario_id">
                                Técnico
                            </label>
                            <select name="funcionario_id" id="funcionario_id"
                                class="@error('funcionario_id') border-red-500 @enderror">
                                <option value="">— Não atribuído —</option>
                                @foreach($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}" @selected(old('funcionario_id')==$funcionario->
                                    id)>
                                    {{ $funcionario->nome }}
                                </option>
                                @endforeach
                            </select>
                            @error('funcionario_id')
                            <p class="form-help text-red-500">{{ $message }}</p>
                            @enderror

                            <div style="margin-top: 0.75rem; display:flex; gap:.5rem; align-items:center;">
                                <button type="button" id="btnAbrirAgendamento" class="btn btn-secondary" style="padding:.5rem .75rem;">
                                    Agendar técnico
                                </button>
                                <span id="agendamentoResumo" style="font-size:12px; color:#6b7280;"></span>
                            </div>
                        </div>
                    </div>
                </section>

                <input type="hidden" name="agendar_tecnico" id="agendar_tecnico" value="0">
                <input type="hidden" name="data_agendamento" id="data_agendamento_hidden" value="{{ old('data_agendamento') }}">
                <input type="hidden" name="periodo_agendamento" id="periodo_agendamento_hidden" value="{{ old('periodo_agendamento') }}">
                <input type="hidden" name="hora_inicio" id="hora_inicio_hidden" value="{{ old('hora_inicio') }}">
                <input type="hidden" name="duracao_horas" id="duracao_horas_hidden" value="{{ old('duracao_horas') }}">

                {{-- ================= BOTÕES ================= --}}
                <div class="form-actions">
                    <a href="{{ route('atendimentos.index') }}" class="btn btn-secondary">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Salvar Atendimento
                    </button>
                </div>

            </form>

        </div>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    <script>
        /* =========================
       MÁSCARA DE TELEFONE
    ========================= */
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('telefone')) {
                let v = e.target.value.replace(/\D/g, '');
                e.target.value = v.length <= 10 ?
                    v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3') :
                    v.replace(/(\d{2})(\d{1})(\d{4})(\d{0,4})/, '($1) $2.$3-$4');
            }
        });

        /* =========================
           CARREGAMENTO DE ASSUNTOS
        ========================= */
        document.addEventListener('DOMContentLoaded', function() {
            const empresaSelect = document.getElementById('empresa_id');
            const assuntoSelect = document.getElementById('assunto_id');
            const formAtendimento = document.getElementById('formAtendimento');
            const btnSubmit = document.getElementById('btnSubmit');

            if (!empresaSelect || !assuntoSelect) return;

            /* =========================
               FUNÇÃO: CARREGAR ASSUNTOS
            ========================= */
            async function carregarAssuntos(empresaId) {
                if (!empresaId) {
                    assuntoSelect.innerHTML = '<option value="">Selecione a empresa primeiro</option>';
                    assuntoSelect.disabled = false;
                    return;
                }

                assuntoSelect.disabled = true;
                assuntoSelect.innerHTML = '<option value="">Carregando assuntos...</option>';

                try {
                    const response = await fetch(`/empresas/${empresaId}/assuntos`);

                    if (!response.ok) {
                        throw new Error(`Erro HTTP: ${response.status}`);
                    }

                    const data = await response.json();

                    if (!Array.isArray(data) || data.length === 0) {
                        assuntoSelect.innerHTML = '<option value="">Nenhum assunto disponível</option>';
                        assuntoSelect.disabled = false;
                        return;
                    }

                    assuntoSelect.innerHTML = '<option value="">Selecione o assunto</option>';

                    // Limpa e adiciona opção inicial
                    assuntoSelect.innerHTML = '<option value="">Selecione o assunto</option>';

                    // Agrupar por categoria
                    const grupos = {};

                    data.forEach(assunto => {
                        const categoria = assunto.categoria || 'Outros';

                        if (!grupos[categoria]) {
                            const optgroup = document.createElement('optgroup');
                            optgroup.label = categoria;
                            grupos[categoria] = optgroup;
                        }

                        const option = document.createElement('option');
                        option.value = assunto.id;

                        if (assunto.subcategoria) {
                            option.textContent = `${assunto.subcategoria} › ${assunto.nome}`;
                        } else {
                            option.textContent = assunto.nome;
                        }

                        grupos[categoria].appendChild(option);
                    });

                    // Anexa os grupos ao select
                    Object.values(grupos).forEach(grupo => {
                        assuntoSelect.appendChild(grupo);
                    });



                    assuntoSelect.disabled = false;

                } catch (error) {
                    console.error('Erro ao carregar assuntos:', error);
                    assuntoSelect.innerHTML = '<option value="">Erro ao carregar assuntos</option>';
                    assuntoSelect.disabled = false;
                }
            }

            /* =========================
               EVENT LISTENER: MUDANÇA DE EMPRESA
            ========================= */
            empresaSelect.addEventListener('change', function() {
                carregarAssuntos(this.value);
            });

            /* =========================
               EVENT LISTENER: SUBMIT DO FORMULÁRIO
            ========================= */
            formAtendimento.addEventListener('submit', function(e) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="loading-spinner"></span>Salvando...';
            });

            /* =========================
               CARREGAR ASSUNTOS SE EMPRESA JÁ SELECIONADA
            ========================= */
            if (empresaSelect.value) {
                carregarAssuntos(empresaSelect.value);
            }

            const modal = document.createElement('div');
            modal.id = 'modalAgendamentoAtendimento';
            modal.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:60;';
            modal.innerHTML = `
                <div style="max-width:920px;margin:3vh auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,.25);">
                    <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="font-weight:700;color:#1f2937;">Agendar Técnico</h3>
                        <button type="button" id="fecharModalAgendamentoAtendimento" style="font-size:20px;color:#6b7280;">&times;</button>
                    </div>
                    <div style="padding:18px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Data</label>
                            <input id="agendamentoData" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Período</label>
                            <select id="agendamentoPeriodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">Selecione</option>
                                <option value="manha">Manhã (08:00-12:00)</option>
                                <option value="tarde">Tarde (13:00-18:00)</option>
                                <option value="noite">Noite (18:01-21:59)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Hora início</label>
                            <input id="agendamentoHoraInicio" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Duração (horas)</label>
                            <select id="agendamentoDuracao" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">Selecione</option>
                                <option value="1">1 hora</option>
                                <option value="2">2 horas</option>
                                <option value="3">3 horas</option>
                                <option value="4">4 horas</option>
                            </select>
                        </div>
                    </div>
                    <div style="padding:0 18px 12px;">
                        <div class="text-sm font-semibold text-gray-700 mb-2">Agenda do dia (calendário por técnico)</div>
                        <div id="calendarioAgendamentoAtendimento" style="max-height:260px;overflow:auto;border:1px solid #e5e7eb;border-radius:8px;padding:10px;"></div>
                    </div>
                    <div style="padding:14px 18px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:8px;">
                        <button type="button" id="cancelarModalAgendamentoAtendimento" class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancelar</button>
                        <button type="button" id="confirmarModalAgendamentoAtendimento" class="px-4 py-2 rounded-lg bg-[#3f9cae] text-white text-sm">Salvar agendamento</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            const btnAbrir = document.getElementById('btnAbrirAgendamento');
            const resumo = document.getElementById('agendamentoResumo');
            const campoTecnico = document.getElementById('funcionario_id');
            const campoDataHidden = document.getElementById('data_agendamento_hidden');
            const campoPeriodoHidden = document.getElementById('periodo_agendamento_hidden');
            const campoHoraHidden = document.getElementById('hora_inicio_hidden');
            const campoDuracaoHidden = document.getElementById('duracao_horas_hidden');
            const campoAgendar = document.getElementById('agendar_tecnico');

            const modalData = document.getElementById('agendamentoData');
            const modalPeriodo = document.getElementById('agendamentoPeriodo');
            const modalHoraInicio = document.getElementById('agendamentoHoraInicio');
            const modalDuracao = document.getElementById('agendamentoDuracao');
            const calendario = document.getElementById('calendarioAgendamentoAtendimento');

            function atualizarResumo() {
                if (campoAgendar.value !== '1') {
                    resumo.textContent = '';
                    return;
                }

                const tecnicoNome = campoTecnico.options[campoTecnico.selectedIndex]?.text || 'Técnico não definido';
                resumo.textContent = `${campoDataHidden.value} • ${campoPeriodoHidden.value} • ${campoHoraHidden.value} • ${campoDuracaoHidden.value}h • ${tecnicoNome}`;
            }

            async function carregarCalendario() {
                if (!modalData.value) {
                    calendario.innerHTML = '<div style="font-size:12px;color:#6b7280;">Selecione a data.</div>';
                    return;
                }

                calendario.innerHTML = '<div style="font-size:12px;color:#6b7280;">Carregando...</div>';

                try {
                    const url = `{{ route('agenda-tecnica.disponibilidade') }}?data=${encodeURIComponent(modalData.value)}${modalPeriodo.value ? `&periodo=${encodeURIComponent(modalPeriodo.value)}` : ''}`;
                    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const json = await response.json();

                    const blocos = (json.tecnicos || []).map(tecnico => {
                        const agendaTecnico = (json.agendamentos || []).filter(item => String(item.funcionario_id) === String(tecnico.id));
                        const linhas = agendaTecnico.length
                            ? agendaTecnico.map(item => `<div style="font-size:12px;color:#374151;margin-top:3px;">${item.inicio} - ${item.fim} • #${item.numero_atendimento} • ${item.cliente}</div>`).join('')
                            : '<div style="font-size:12px;color:#16a34a;margin-top:3px;">Livre</div>';

                        return `<div style="border-bottom:1px solid #f3f4f6;padding:8px 0;"><div style="font-weight:600;font-size:13px;">${tecnico.nome}</div>${linhas}</div>`;
                    });

                    calendario.innerHTML = blocos.length ? blocos.join('') : '<div style="font-size:12px;color:#6b7280;">Nenhum técnico encontrado.</div>';
                } catch (error) {
                    calendario.innerHTML = '<div style="font-size:12px;color:#dc2626;">Falha ao carregar a agenda.</div>';
                }
            }

            btnAbrir.addEventListener('click', function() {
                if (!campoTecnico.value) {
                    alert('Selecione o técnico antes de agendar.');
                    return;
                }

                modalData.value = campoDataHidden.value || new Date().toISOString().slice(0, 10);
                modalPeriodo.value = campoPeriodoHidden.value || '';
                modalHoraInicio.value = campoHoraHidden.value || '';
                modalDuracao.value = campoDuracaoHidden.value || '';

                modal.style.display = 'block';
                carregarCalendario();
            });

            document.getElementById('fecharModalAgendamentoAtendimento').addEventListener('click', () => modal.style.display = 'none');
            document.getElementById('cancelarModalAgendamentoAtendimento').addEventListener('click', () => modal.style.display = 'none');

            document.getElementById('confirmarModalAgendamentoAtendimento').addEventListener('click', function() {
                if (!modalData.value || !modalPeriodo.value || !modalHoraInicio.value || !modalDuracao.value) {
                    alert('Preencha todos os campos do agendamento.');
                    return;
                }

                campoDataHidden.value = modalData.value;
                campoPeriodoHidden.value = modalPeriodo.value;
                campoHoraHidden.value = modalHoraInicio.value;
                campoDuracaoHidden.value = modalDuracao.value;
                campoAgendar.value = '1';

                atualizarResumo();
                modal.style.display = 'none';
            });

            modalData.addEventListener('change', carregarCalendario);
            modalPeriodo.addEventListener('change', carregarCalendario);

            atualizarResumo();
        });
    </script>
</x-app-layout>
