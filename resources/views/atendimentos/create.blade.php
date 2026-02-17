<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/create.css')
    @vite('resources/js/cliente-busca.js')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Novo Atendimento
            </h2>

            <a href="{{ route('atendimentos.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm group"
                title="Voltar para Atendimentos">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
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
                        </div>
                    </div>
                </section>

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
        });
    </script>
</x-app-layout>