<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Minhas Ordens de Serviço
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Unidade: <span class="font-semibold text-[#3f9cae]">{{ $cliente->nome_exibicao }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('portal.chamado.novo') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Novo Atendimento</span>
                </a>

                <a href="{{ route('portal.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg shadow transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="hidden sm:inline">Voltar</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="portal-wrapper">
        @if(session('atendimento_criado_numero'))
        <div id="modal-atendimento-criado" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
            <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-[#3f9cae]/20 overflow-hidden animate-pulse">
                <div class="px-6 py-5 bg-[#3f9cae]/10 border-b border-[#3f9cae]/20">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-emerald-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Atendimento criado com sucesso
                    </h3>
                </div>
                <div class="px-6 py-5 space-y-3 text-gray-700">
                    <p class="text-base">
                        Seu atendimento nº <span class="font-bold text-[#3f9cae]">{{ session('atendimento_criado_numero') }}</span> foi criado.
                    </p>
                    <p class="text-sm">
                        Em breve nossa equipe entrará em contato. Se precisar, ligue para nosso suporte:
                    </p>
                    <p class="text-sm font-semibold text-gray-900">27 4042-4157 ou 27 3109-3265</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button type="button" id="btn-ok-atendimento-criado" class="inline-flex items-center gap-2 px-5 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg shadow transition-all">
                        OK
                    </button>
                </div>
            </div>
        </div>
        @endif

        <div class="mb-4">
            <a href="{{ route('portal.chamado.novo') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Novo Atendimento</span>
            </a>
        </div>

        <div class="mb-4 rounded-lg border border-gray-200 bg-white p-4">
            <form method="GET" action="{{ route('portal.atendimentos') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div class="md:col-span-2">
                    <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ request('q') }}"
                        placeholder="Código, assunto ou status"
                        class="w-full rounded border-gray-300"
                    >
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded border-gray-300">
                        <option value="">Todos</option>
                        @foreach([
                            'orcamento' => 'Orçamento',
                            'aberto' => 'Aberto',
                            'em_atendimento' => 'Em atendimento',
                            'pendente_cliente' => 'Pendente cliente',
                            'pendente_fornecedor' => 'Pendente fornecedor',
                            'garantia' => 'Garantia',
                            'finalizacao' => 'Finalização',
                            'concluido' => 'Concluído',
                        ] as $valor => $label)
                            <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="prioridade" class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                    <select id="prioridade" name="prioridade" class="w-full rounded border-gray-300">
                        <option value="">Todas</option>
                        @foreach(['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'] as $valor => $label)
                            <option value="{{ $valor }}" @selected(request('prioridade') === $valor)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-2 justify-end">
                    <a href="{{ route('portal.atendimentos') }}" class="inline-flex items-center px-3 py-2 rounded border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Limpar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        @if($atendimentos->count() > 0)
        <div class="rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Meus Atendimentos
                </h3>
            </div>

            {{-- Versão Desktop (Tabela) --}}
            <div class="overflow-x-auto">
                <table id="tabelaAtendimentos" class="portal-table w-full">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Prioridade</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($atendimentos as $atendimento)
                        @php
                            $assuntoExibicao = $atendimento->assunto->nome
                                ?? (str_starts_with((string) $atendimento->descricao, 'Assunto:')
                                    ? trim((string) \Illuminate\Support\Str::of($atendimento->descricao)->before("\n"))->replace('Assunto:', '')
                                    : '—');
                            $tecnicoNome = $atendimento->funcionario?->nome;
                            $dataAgendada = $atendimento->data_inicio_agendamento;
                            $temTecnicoEAgenda = !empty($tecnicoNome) && !empty($dataAgendada);
                        @endphp
                        <tr class="cursor-pointer hover:bg-gray-50 transition" onclick="toggleExpand({{ $atendimento->id }})">
                            <td class="portal-font-bold text-[#3f9cae]">
                                #{{ $atendimento->numero_atendimento ?? $atendimento->id }}
                            </td>
                            <td>{{ strtoupper($atendimento->prioridade ?? '—') }}</td>
                            <td>
                                <div class="font-medium text-gray-800">{{ $assuntoExibicao }}</div>
                                <div class="mt-1 flex flex-col gap-1 text-xs text-gray-600">
                                    @if($temTecnicoEAgenda)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700 font-semibold w-fit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Técnico e horário definidos
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $status = $atendimento->status_atual;
                                    $badgeClass = 'portal-badge--gray';
                                    if(in_array($status, ['aberto', 'em_atendimento'])) {
                                        $badgeClass = 'portal-badge--info';
                                    } elseif(in_array($status, ['pendente_cliente', 'pendente_fornecedor', 'garantia', 'finalizacao'])) {
                                        $badgeClass = 'portal-badge--warning';
                                    } elseif($status === 'concluido') {
                                        $badgeClass = 'portal-badge--success';
                                    }
                                @endphp
                                <span class="portal-badge {{ $badgeClass }}">
                                    {{ strtoupper(str_replace('_', ' ', $status ?? 'Indefinido')) }}
                                </span>
                            </td>
                            <td>{{ $atendimento->created_at->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap text-center align-middle">
                                <div class="grid grid-cols-4 gap-2 place-items-center w-[176px] mx-auto">
                                    <a href="{{ route('portal.chamado.edit', $atendimento) }}"
                                        class="w-8 h-8 rounded-full inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition"
                                        title="Editar Atendimento">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M17.414 2.586a2 2 0 010 2.828l-9.5 9.5a1 1 0 01-.39.242l-4 1.5a1 1 0 01-1.286-1.286l1.5-4a1 1 0 01.242-.39l9.5-9.5a2 2 0 012.828 0z"/>
                                        </svg>
                                    </a>
                                    <button type="button"
                                        onclick="openModal('showHistorico{{ $atendimento->id }}')"
                                        class="w-8 h-8 rounded-full inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 transition"
                                        title="Ver Detalhes">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    <span class="w-8 h-8 rounded-full inline-flex items-center justify-center {{ $tecnicoNome ? 'text-[#3f9cae] bg-[#3f9cae]/10' : 'opacity-0' }}" title="Técnico vinculado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.878 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>
                                    <span class="w-8 h-8 rounded-full inline-flex items-center justify-center {{ $dataAgendada ? 'text-emerald-600 bg-emerald-100' : 'opacity-0' }}" title="Horário agendado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                </div>
                                <dialog id="showHistorico{{ $atendimento->id }}"
                                    class="rounded-xl shadow-xl p-0 w-full max-w-2xl backdrop:bg-gray-900/50">
                                    ...existing code...
                                </dialog>
                            </td>
                        </tr>
                        <tr id="expandRow{{ $atendimento->id }}" style="display: none;">
                            <td colspan="6" class="bg-gray-50 border-t-0">
                                <div class="p-4">
                                    <strong>Detalhes do atendimento #{{ $atendimento->numero_atendimento ?? $atendimento->id }}</strong>
                                    <div class="mt-2 text-sm text-gray-700">
                                        Assunto: {{ $assuntoExibicao }}<br>
                                        Prioridade: {{ strtoupper($atendimento->prioridade ?? '—') }}<br>
                                        Status: {{ strtoupper(str_replace('_', ' ', $atendimento->status_atual ?? 'Indefinido')) }}<br>
                                        Criado em: {{ $atendimento->created_at->format('d/m/Y H:i') }}<br>
                                        <div class="flex flex-col md:flex-row gap-4 mt-2">
                                            <div><span class="font-semibold text-gray-600">Equipamento:</span> {{ $atendimento->equipamento?->nome ?? '—' }}</div>
                                            <div><span class="font-semibold text-gray-600">Setor:</span> {{ $atendimento->equipamento?->setor?->nome ?? '—' }}</div>
                                            <div><span class="font-semibold text-gray-600">Responsável:</span> {{ $atendimento->equipamento?->responsavel?->nome ?? '—' }}</div>
                                            <div><span class="font-semibold text-gray-600">Usuário que abriu:</span> {{ $atendimento->iniciadoPor?->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                            <td>{{ strtoupper($atendimento->prioridade ?? '—') }}</td>
                            <td>
                                <div class="font-medium text-gray-800">{{ $assuntoExibicao }}</div>
                                <div class="mt-1 flex flex-col gap-1 text-xs text-gray-600">
                                    @if($temTecnicoEAgenda)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700 font-semibold w-fit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Técnico e horário definidos
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $status = $atendimento->status_atual;
                                    $badgeClass = 'portal-badge--gray';

                                    if(in_array($status, ['aberto', 'em_atendimento'])) {
                                        $badgeClass = 'portal-badge--info';
                                    } elseif(in_array($status, ['pendente_cliente', 'pendente_fornecedor', 'garantia', 'finalizacao'])) {
                                        $badgeClass = 'portal-badge--warning';
                                    } elseif($status === 'concluido') {
                                        $badgeClass = 'portal-badge--success';
                                    }
                                @endphp
                                <span class="portal-badge {{ $badgeClass }}">
                                    {{ strtoupper(str_replace('_', ' ', $status ?? 'Indefinido')) }}
                                </span>
                            </td>
                            <td>{{ $atendimento->created_at->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap text-center align-middle">
                                <div class="grid grid-cols-4 gap-2 place-items-center w-[176px] mx-auto">
                                    <a href="{{ route('portal.chamado.edit', $atendimento) }}"
                                        class="w-8 h-8 rounded-full inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition"
                                        title="Editar Atendimento">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M17.414 2.586a2 2 0 010 2.828l-9.5 9.5a1 1 0 01-.39.242l-4 1.5a1 1 0 01-1.286-1.286l1.5-4a1 1 0 01.242-.39l9.5-9.5a2 2 0 012.828 0z"/>
                                        </svg>
                                    </a>

                                    <button type="button"
                                        onclick="openModal('showHistorico{{ $atendimento->id }}')"
                                        class="w-8 h-8 rounded-full inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 transition"
                                        title="Ver Detalhes">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>

                                    <span class="w-8 h-8 rounded-full inline-flex items-center justify-center {{ $tecnicoNome ? 'text-[#3f9cae] bg-[#3f9cae]/10' : 'opacity-0' }}" title="Técnico vinculado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.878 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>

                                    <span class="w-8 h-8 rounded-full inline-flex items-center justify-center {{ $dataAgendada ? 'text-emerald-600 bg-emerald-100' : 'opacity-0' }}" title="Horário agendado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                </div>

                                <dialog id="showHistorico{{ $atendimento->id }}"
                                    class="rounded-xl shadow-xl p-0 w-full max-w-2xl backdrop:bg-gray-900/50">
                                    <form method="dialog" class="p-0">
                                        {{-- Header --}}
                                        <div class="px-6 py-4 border-b border-gray-200" style="background-color: rgba(63, 156, 174, 0.05);">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                Histórico do Atendimento #{{ $atendimento->id }}
                                            </h3>
                                        </div>

                                        {{-- Corpo --}}
                                        <div class="p-6">
                                            <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3 space-y-2">
                                                <div class="text-sm font-semibold text-gray-800">Situação do atendimento</div>

                                                <div class="text-sm text-gray-700 flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.878 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <span>Técnico: <strong>{{ $tecnicoNome ?: 'Ainda não atribuído' }}</strong></span>
                                                </div>

                                                <div class="text-sm text-gray-700 flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                                    </svg>
                                                    <span>Agendamento: <strong>{{ $dataAgendada ? $dataAgendada->format('d/m/Y H:i') : 'Ainda não agendado' }}</strong></span>
                                                </div>
                                            </div>

                                            @if($atendimento->andamentos && $atendimento->andamentos->count())
                                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                                @foreach($atendimento->andamentos as $andamento)
                                                <div class="border-b border-gray-200 pb-3 last:border-0">
                                                    <div class="text-sm text-gray-800">
                                                        {{ $andamento->descricao ?? '—' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ $andamento->created_at->format('d/m/Y H:i') }}
                                                    </div>

                                                    @if($andamento->fotos && $andamento->fotos->count())
                                                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                        @foreach($andamento->fotos as $foto)
                                                        <a href="{{ $foto->arquivo_url }}" target="_blank" rel="noopener noreferrer" class="block rounded-md overflow-hidden border border-gray-200 hover:border-[#3f9cae] transition-colors">
                                                            <img src="{{ $foto->arquivo_url }}" alt="Anexo do atendimento" class="w-full h-24 object-cover">
                                                        </a>
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                            @else
                                            <div class="text-gray-500 text-center py-8">
                                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm font-medium">Nenhum andamento registrado.</p>
                                            </div>
                                            @endif
                                        </div>

                                        {{-- Footer com Botões --}}
                                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                                            <x-button variant="danger" size="sm" class="min-w-[130px]" onclick="closeModal('showHistorico{{ $atendimento->id }}')">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                                Fechar
                                            </x-button>
                                        </div>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Versão Mobile (Cards) --}}
            <div class="portal-mobile-cards px-4 pb-4">
                @foreach($atendimentos as $atendimento)
                @php
                    $assuntoExibicao = $atendimento->assunto->nome
                        ?? (str_starts_with((string) $atendimento->descricao, 'Assunto:')
                            ? trim((string) \Illuminate\Support\Str::of($atendimento->descricao)->before("\n"))->replace('Assunto:', '')
                            : '—');
                    $tecnicoNome = $atendimento->funcionario?->nome;
                    $dataAgendada = $atendimento->data_inicio_agendamento;
                    $temTecnicoEAgenda = !empty($tecnicoNome) && !empty($dataAgendada);
                @endphp
                <div class="portal-mobile-card">
                    <div class="portal-mobile-card-header">
                        <div>
                            <div class="portal-mobile-card-title">
                                Atendimento #{{ $atendimento->id }}
                            </div>
                            <div class="portal-mobile-card-subtitle">
                                Prioridade: {{ strtoupper($atendimento->prioridade ?? '—') }}
                            </div>
                        </div>
                        @php
                            $status = $atendimento->status_atual;
                            $badgeClass = 'portal-badge--gray';

                            if(in_array($status, ['aberto', 'em_atendimento'])) {
                                $badgeClass = 'portal-badge--info';
                            } elseif(in_array($status, ['pendente_cliente', 'pendente_fornecedor', 'garantia', 'finalizacao'])) {
                                $badgeClass = 'portal-badge--warning';
                            } elseif($status === 'concluido') {
                                $badgeClass = 'portal-badge--success';
                            }
                        @endphp
                        <span class="portal-badge {{ $badgeClass }}">
                            {{ strtoupper(str_replace('_', ' ', $status ?? 'Indefinido')) }}
                        </span>
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Assunto</span>
                        <span class="portal-mobile-card-value">
                            {{ $assuntoExibicao }}
                        </span>
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Criado em</span>
                        <span class="portal-mobile-card-value">
                            {{ $atendimento->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Técnico</span>
                        <span class="portal-mobile-card-value inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.878 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $tecnicoNome ?: 'Ainda não atribuído' }}
                        </span>
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Agendamento</span>
                        <span class="portal-mobile-card-value inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                            </svg>
                            {{ $dataAgendada ? $dataAgendada->format('d/m/Y H:i') : 'Ainda não agendado' }}
                        </span>
                    </div>
                    @if($temTecnicoEAgenda)
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Situação</span>
                        <span class="portal-mobile-card-value inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700 font-semibold w-fit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Técnico e horário definidos
                        </span>
                    </div>
                    @endif
                    <div class="portal-mobile-card-actions">
                        <a href="{{ route('portal.chamado.edit', $atendimento) }}"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2
                                   bg-amber-500 hover:bg-amber-600
                                   text-white text-sm font-semibold
                                   rounded-lg border-0 shadow transition-all flex-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.414 2.586a2 2 0 010 2.828l-9.5 9.5a1 1 0 01-.39.242l-4 1.5a1 1 0 01-1.286-1.286l1.5-4a1 1 0 01.242-.39l9.5-9.5a2 2 0 012.828 0z"/>
                            </svg>
                            Editar
                        </a>

                        <button type="button"
                            onclick="window.showHistorico{{ $atendimento->id }}.showModal()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2
                                   bg-[#3f9cae] hover:bg-[#2d7a8a]
                                   text-white text-sm font-semibold
                                   rounded-lg border-0 shadow transition-all flex-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            Ver Detalhes
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @if($atendimentos->hasPages())
        <div class="mt-4 bg-white rounded-lg border border-gray-200 p-3">
            {{ $atendimentos->links() }}
        </div>
        @endif
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum atendimento encontrado.</p>
            <p class="portal-empty-state-text">
                Suas ordens de serviço aparecerão aqui quando forem cadastradas.
            </p>
            <a href="{{ route('portal.chamado.novo') }}"
                class="inline-flex items-center gap-2 px-4 py-2 mt-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Abrir Novo Atendimento</span>
            </a>
        </div>
        @endif

    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.showModal();
            // Remove foco do botão para evitar borda dupla
            setTimeout(function() {
                const button = modal.querySelector('button');
                if (button) button.blur();
            }, 0);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.close();
        }

        const btnOkAtendimentoCriado = document.getElementById('btn-ok-atendimento-criado');
        const modalAtendimentoCriado = document.getElementById('modal-atendimento-criado');

        btnOkAtendimentoCriado?.addEventListener('click', function() {
            modalAtendimentoCriado?.classList.add('hidden');
        });
    </script>
</x-app-layout>
