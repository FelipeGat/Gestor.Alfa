<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @vite('resources/css/financeiro/contasareceber.css')
    @vite('resources/css/financeiro/index.css')
    <style>
        .filters-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .quick-filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            transition: all 0.2s;
            cursor: pointer;
            text-transform: uppercase;
        }
        /* Estilo para quando NENHUMA prioridade específica está selecionada (Padrão: Todas) */
        .btn-prioridade-alta { border-color: #fee2e2; color: #dc2626; }
        .btn-prioridade-alta.active { background: #dc2626; color: #fff; border-color: #dc2626; }

        .btn-prioridade-media { border-color: #ffedd5; color: #f59e42; }
        .btn-prioridade-media.active { background: #f59e42; color: #fff; border-color: #f59e42; }

        .btn-prioridade-baixa { border-color: #dcfce7; color: #16a34a; }
        .btn-prioridade-baixa.active { background: #16a34a; color: #fff; border-color: #16a34a; }

        /* Ajuste fino da tabela para não vazar */
        .table-tight th, .table-tight td {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            white-space: nowrap;
        }
        .truncate-text {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            white-space: nowrap;
        }
        .table-wrapper {
            overflow-x: auto;
            padding-right: 0.75rem;
            padding-bottom: 0.25rem;
        }
        .table-tight th:last-child,
        .table-tight td:last-child {
            padding-right: 1.25rem !important;
        }
    </style>
    @endpush

    {{-- ================= HEADER ================= --}}
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Atendimentos']
        ]" />
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= FILTROS ================= --}}
            <div class="filters-card">
                <form method="GET" id="filterForm">
                    @if(request('triagem_pendente'))
                        <input type="hidden" name="triagem_pendente" value="1">
                    @endif

                    {{-- PRIMEIRA LINHA: BUSCA, EMPRESA, STATUS, TÉCNICO --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="filter-group relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" name="search" id="busca-geral" value="{{ request('search') }}"
                                placeholder="Cliente, solicitante..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="flex gap-2">
                            <div class="filter-group" style="max-width: 170px; min-width: 120px;">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                                <select name="empresa_id" onchange="this.form.submit()" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full" style="min-width: 100px; max-width: 160px;">
                                    <option value="">Todas</option>
                                    @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-group" style="max-width: 170px; min-width: 120px;">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" onchange="this.form.submit()" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full" style="min-width: 100px; max-width: 160px;">
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
                                        <option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Técnico</label>
                            <select name="tecnico_id" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Todos os Técnicos</option>
                                @foreach($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}" {{ request('tecnico_id') == $funcionario->id ? 'selected' : '' }}>
                                    {{ Str::limit($funcionario->nome, 14, '.') }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- SEGUNDA LINHA: NAVEGAÇÃO RÁPIDA E PRIORIDADE --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-4 items-end">
                        <div class="filter-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Navegação Rápida</label>
                            <div class="flex items-center gap-2 flex-wrap">
                                @php
                                    $hoje = \Carbon\Carbon::today();
                                    $ontem = \Carbon\Carbon::yesterday();
                                    $dataAtual = request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio')) : \Carbon\Carbon::now();
                                    $mesAnterior = $dataAtual->copy()->subMonth();
                                    $proximoMes = $dataAtual->copy()->addMonth();
                                    $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                                    $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                                @endphp

                                <a href="{{ route('atendimentos.index', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $ontem->format('Y-m-d'), 'data_fim' => $ontem->format('Y-m-d')])) }}"
                                    class="quick-filter-btn {{ request('data_inicio') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : '' }}">Ontem</a>
                                <a href="{{ route('atendimentos.index', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $hoje->format('Y-m-d'), 'data_fim' => $hoje->format('Y-m-d')])) }}"
                                    class="quick-filter-btn {{ request('data_inicio') == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : '' }}">Hoje</a>

                                <div class="flex items-center gap-1 ml-1">
                                    <a href="{{ route('atendimentos.index', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'), 'data_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')])) }}"
                                        class="p-2 bg-white hover:bg-gray-50 rounded-lg border border-gray-300 shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></a>
                                    <div class="text-base font-bold text-gray-700 bg-white px-8 py-2 rounded-lg border border-gray-300 shadow-sm" style="min-width:180px;text-align:center;">{{ $mesAtualNome }}</div>
                                    <a href="{{ route('atendimentos.index', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'), 'data_fim' => $proximoMes->endOfMonth()->format('Y-m-d')])) }}"
                                        class="p-2 bg-white hover:bg-gray-50 rounded-lg border border-gray-300 shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg></a>
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="block text-sm font-medium text-gray-700 mb-2 lg:text-right">Prioridade</label>
                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <a href="{{ route('atendimentos.index', array_merge(request()->except('triagem_pendente'), ['triagem_pendente' => 1])) }}"
                                    class="quick-filter-btn {{ request('triagem_pendente') ? 'bg-amber-600 text-white border-amber-600' : 'border-amber-200 text-amber-700' }}">Pendentes Triagem</a>
                                <a href="{{ route('atendimentos.index', array_merge(request()->except('prioridade'), ['prioridade' => 'alta'])) }}"
                                    class="quick-filter-btn btn-prioridade-alta {{ request('prioridade') === 'alta' ? 'active' : '' }}">Alta</a>
                                <a href="{{ route('atendimentos.index', array_merge(request()->except('prioridade'), ['prioridade' => 'media'])) }}"
                                    class="quick-filter-btn btn-prioridade-media {{ request('prioridade') === 'media' ? 'active' : '' }}">Média</a>
                                <a href="{{ route('atendimentos.index', array_merge(request()->except('prioridade'), ['prioridade' => 'baixa'])) }}"
                                    class="quick-filter-btn btn-prioridade-baixa {{ request('prioridade') === 'baixa' ? 'active' : '' }}">Baixa</a>
                                @if(request('triagem_pendente'))
                                    <a href="{{ route('atendimentos.index', request()->except('triagem_pendente')) }}" class="inline-flex items-center px-2 py-1 border border-gray-300 text-gray-400 rounded-lg text-xs ml-2 bg-white hover:bg-gray-50 transition">Limpar Triagem</a>
                                @endif
                                @if(request('prioridade'))
                                    <a href="{{ route('atendimentos.index', request()->except('prioridade')) }}" class="inline-flex items-center px-2 py-1 border border-gray-300 text-gray-400 rounded-lg text-xs ml-2 bg-white hover:bg-gray-50 transition">Limpar</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-8 pt-4 border-t border-gray-100">
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition shadow-sm text-sm">
                                Filtrar
                            </button>
                            <a href="{{ route('atendimentos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition text-sm">
                                Limpar
                            </a>
                        </div>

                        <a href="{{ route('atendimentos.create') }}" class="inline-flex items-center gap-2 px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-md text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                            Novo Atendimento
                        </a>
                    </div>
                </form>
            </div>

            {{-- ================= TABELA (DESKTOP) ================= --}}
        @if($atendimentos->count() > 0)
        @php
            $pendentesEmpresa = $atendimentos->getCollection()->whereNull('empresa_id')->count();
            $pendentesTecnico = $atendimentos->getCollection()->whereNull('funcionario_id')->count();
        @endphp

        @if($pendentesEmpresa > 0 || $pendentesTecnico > 0)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span class="font-semibold">Triagem pendente nesta página:</span>
            <span class="ml-2">Sem empresa: <strong>{{ $pendentesEmpresa }}</strong></span>
            <span class="ml-4">Sem técnico: <strong>{{ $pendentesTecnico }}</strong></span>
        </div>
        @endif

        <div class="table-card">
            <div class="table-wrapper">
                <table class="table table-tight" style="min-width: 1320px;">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Nº</th>
                            <th>Solicitante</th>
                            <th>Assunto</th>
                            <th style="width: 190px;">Empresa</th>
                            <th style="width: 190px;">Técnico</th>
                            <th style="width: 100px; text-align: center;">Prioridade</th>
                            <th style="width: 120px; text-align: center;">Status</th>
                            <th style="width: 100px;">Data Criação</th>
                            <th style="width: 160px;">Data Agendamento</th>
                            <th style="width: 190px; text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($atendimentos as $atendimento)
                        @php
                            $assuntoExibicao = $atendimento->assunto->nome
                                ?? (str_starts_with((string) $atendimento->descricao, 'Assunto:')
                                    ? trim((string) \Illuminate\Support\Str::of($atendimento->descricao)->before("\n"))->replace('Assunto:', '')
                                    : '—');
                            $semEmpresa = empty($atendimento->empresa_id);
                            $semTecnico = empty($atendimento->funcionario_id);
                        @endphp
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
                            <td>{{ $assuntoExibicao }}</td>

                            {{-- Empresa --}}
                            <td>
                                <select data-id="{{ $atendimento->id }}" data-campo="empresa_id"
                                    class="campo-editavel table-select {{ $semEmpresa ? 'border-amber-300 bg-amber-50' : '' }}">
                                    <option value="">—</option>
                                    @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" @selected($atendimento->empresa_id == $empresa->id)>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Técnico (Editável) --}}
                            <td>
                                <select data-id="{{ $atendimento->id }}" data-campo="funcionario_id"
                                    id="tecnico_{{ $atendimento->id }}"
                                    class="campo-editavel table-select {{ $semTecnico ? 'border-amber-300 bg-amber-50' : '' }}">
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

                            {{-- Data Criação --}}
                            <td>{{ $atendimento->created_at->format('d/m/Y') }}</td>

                            {{-- Data Agendamento --}}
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($atendimento->data_inicio_agendamento)
                                        <span class="text-xs font-semibold text-gray-700">{{ $atendimento->data_inicio_agendamento->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">Sem data</span>
                                    @endif
                                    <button
                                        type="button"
                                        class="btn-agendar-fila inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50"
                                        data-atendimento-id="{{ $atendimento->id }}"
                                        data-funcionario-id="{{ $atendimento->funcionario_id }}"
                                        data-data="{{ $atendimento->data_inicio_agendamento?->format('Y-m-d') }}"
                                        data-periodo="{{ $atendimento->periodo_agendamento }}"
                                        data-hora="{{ $atendimento->data_inicio_agendamento?->format('H:i') }}"
                                        data-duracao="{{ $atendimento->duracao_agendamento_minutos ? max(1, (int) ($atendimento->duracao_agendamento_minutos / 60)) : '' }}"
                                    >
                                        {{ $atendimento->data_inicio_agendamento ? 'Reagendar' : 'Agendar' }}
                                    </button>
                                </div>
                            </td>

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
                @php
                    $assuntoExibicao = $atendimento->assunto->nome
                        ?? (str_starts_with((string) $atendimento->descricao, 'Assunto:')
                            ? trim((string) \Illuminate\Support\Str::of($atendimento->descricao)->before("\n"))->replace('Assunto:', '')
                            : '—');
                    $semEmpresa = empty($atendimento->empresa_id);
                    $semTecnico = empty($atendimento->funcionario_id);
                @endphp
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
                    <span class="mobile-card-value">{{ $assuntoExibicao }}</span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Empresa</span>
                    <span class="mobile-card-value">
                        @if($semEmpresa)
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Não definida</span>
                        @else
                            {{ optional($atendimento->empresa)->nome_fantasia ?? '—' }}
                        @endif
                    </span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Técnico</span>
                    <span class="mobile-card-value {{ $semTecnico ? 'text-amber-700 font-semibold' : '' }}">
                        {{ $semTecnico ? 'Não definido' : optional($atendimento->funcionario)->nome }}
                    </span>
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
            <div class="mt-4">{{ $atendimentos->links() }}</div>
            @else
            <div class="bg-white rounded-xl p-12 text-center border border-dashed border-gray-300 text-gray-500">Nenhum atendimento encontrado.</div>
            @endif
        </div>
    </div>

    <div id="modal-reagendar-fila" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:60;">
        <div style="max-width:920px; margin:3vh auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,.25);">
            <div style="padding:14px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-weight:700; color:#1f2937;">Agendar técnico</h3>
                <button type="button" id="fechar-modal-reagendar-fila" style="font-size:20px; color:#6b7280;">&times;</button>
            </div>
            <div style="padding:18px; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px;">
                <div>
                    <label class="text-sm font-medium text-gray-700">Técnico</label>
                    <select id="fila_funcionario_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        @foreach($funcionarios as $funcionario)
                        <option value="{{ $funcionario->id }}">{{ $funcionario->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Data</label>
                    <input id="fila_data" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Período</label>
                    <select id="fila_periodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        <option value="dia_todo">Dia todo (08:00-17:00, 9 horas)</option>
                        <option value="manha">Manhã (08:00-12:00)</option>
                        <option value="tarde">Tarde (13:00-18:00)</option>
                        <option value="noite">Noite (18:01-21:59)</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Hora início</label>
                    <input id="fila_hora_inicio" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Duração (horas)</label>
                    <select id="fila_duracao_horas" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Selecione</option>
                        @for($h = 1; $h <= 9; $h++)
                        <option value="{{ $h }}">{{ $h }} hora{{ $h > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div style="padding:0 18px 12px;">
                <div class="text-sm font-semibold text-gray-700 mb-2">Agenda do dia (calendário por técnico)</div>
                <div id="agenda-calendario-fila" style="max-height:260px; overflow:auto; border:1px solid #e5e7eb; border-radius:8px; padding:10px;"></div>
            </div>
            <div style="padding:14px 18px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" id="cancelar-reagendar-fila" class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancelar</button>
                <button type="button" id="confirmar-reagendar-fila" class="px-4 py-2 rounded-lg bg-[#3f9cae] text-white text-sm">Salvar agendamento</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const modalReagendarFila = document.getElementById('modal-reagendar-fila');
        const filaFuncionarioInput = document.getElementById('fila_funcionario_id');
        const filaDataInput = document.getElementById('fila_data');
        const filaPeriodoInput = document.getElementById('fila_periodo');
        const filaHoraInicioInput = document.getElementById('fila_hora_inicio');
        const filaDuracaoInput = document.getElementById('fila_duracao_horas');
        const calendarioFilaWrapper = document.getElementById('agenda-calendario-fila');
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

        let contextoReagendarFila = null;

        document.querySelectorAll('.campo-editavel').forEach((campo) => {
            campo.addEventListener('change', function() {
                const id = this.dataset.id;
                const campo = this.dataset.campo;
                const valor = this.value;
                fetch(`{{ url('atendimentos') }}/${id}/atualizar-campo`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ campo, valor })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.add('border-green-500');
                        setTimeout(() => this.classList.remove('border-green-500'), 1000);
                        return;
                    }

                    alert(data.message || 'Não foi possível atualizar o campo.');
                    window.location.reload();
                })
                .catch(() => {
                    alert('Erro ao atualizar o campo.');
                    window.location.reload();
                });
            });
        });

        document.querySelectorAll('.btn-agendar-fila').forEach((button) => {
            button.addEventListener('click', function() {
                const atendimentoId = this.dataset.atendimentoId;
                const tecnicoSelect = document.getElementById(`tecnico_${atendimentoId}`);

                contextoReagendarFila = {
                    atendimentoId,
                    funcionarioId: (tecnicoSelect?.value || this.dataset.funcionarioId || ''),
                    data: this.dataset.data || '',
                    periodo: this.dataset.periodo || '',
                    hora: this.dataset.hora || '',
                    duracao: this.dataset.duracao || '1'
                };

                filaFuncionarioInput.value = contextoReagendarFila.funcionarioId;
                filaDataInput.value = contextoReagendarFila.data || new Date().toISOString().slice(0, 10);
                filaPeriodoInput.value = contextoReagendarFila.periodo;
                filaHoraInicioInput.value = contextoReagendarFila.hora;
                filaDuracaoInput.value = contextoReagendarFila.duracao;

                if (filaPeriodoInput.value === 'dia_todo') {
                    filaHoraInicioInput.value = '08:00';
                    filaDuracaoInput.value = '9';
                    filaHoraInicioInput.disabled = true;
                    filaDuracaoInput.disabled = true;
                } else {
                    filaHoraInicioInput.disabled = false;
                    filaDuracaoInput.disabled = false;
                }

                modalReagendarFila.style.display = 'block';
                carregarAgendaFila();
            });
        });

        function fecharModalFila() {
            modalReagendarFila.style.display = 'none';
            contextoReagendarFila = null;
        }

        document.getElementById('fechar-modal-reagendar-fila')?.addEventListener('click', fecharModalFila);
        document.getElementById('cancelar-reagendar-fila')?.addEventListener('click', fecharModalFila);

        filaPeriodoInput?.addEventListener('change', function() {
            if (this.value === 'dia_todo') {
                filaHoraInicioInput.value = '08:00';
                filaDuracaoInput.value = '9';
                filaHoraInicioInput.disabled = true;
                filaDuracaoInput.disabled = true;
            } else {
                filaHoraInicioInput.disabled = false;
                filaDuracaoInput.disabled = false;
            }

            carregarAgendaFila();
        });

        filaDataInput?.addEventListener('change', carregarAgendaFila);

        async function carregarAgendaFila() {
            const data = filaDataInput.value;
            const periodo = filaPeriodoInput.value;

            if (!data) {
                calendarioFilaWrapper.innerHTML = '<div class="text-sm text-gray-500">Selecione a data para visualizar a agenda.</div>';
                return;
            }

            calendarioFilaWrapper.innerHTML = '<div class="text-sm text-gray-500">Carregando agenda...</div>';

            try {
                const url = `{{ route('agenda-tecnica.disponibilidade') }}?data=${encodeURIComponent(data)}${periodo ? `&periodo=${encodeURIComponent(periodo)}` : ''}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const json = await response.json();

                const blocos = (json.tecnicos || []).map((tecnico) => {
                    const agendaTecnico = (json.agendamentos || []).filter((item) => String(item.funcionario_id) === String(tecnico.id));
                    const linhas = agendaTecnico.length
                        ? agendaTecnico.map((item) => `<div style="font-size:12px;color:#374151;margin-top:3px;">${item.inicio} - ${item.fim} • #${item.numero_atendimento} • ${item.cliente}</div>`).join('')
                        : '<div style="font-size:12px;color:#16a34a;margin-top:3px;">Livre</div>';

                    return `<div style="border-bottom:1px solid #f3f4f6;padding:8px 0;"><div style="font-weight:600;font-size:13px;">${tecnico.nome}</div>${linhas}</div>`;
                });

                calendarioFilaWrapper.innerHTML = blocos.length ? blocos.join('') : '<div class="text-sm text-gray-500">Nenhum técnico encontrado.</div>';
            } catch (_) {
                calendarioFilaWrapper.innerHTML = '<div class="text-sm text-red-600">Não foi possível carregar a agenda.</div>';
            }
        }

        document.getElementById('confirmar-reagendar-fila')?.addEventListener('click', function() {
            if (!contextoReagendarFila?.atendimentoId) {
                alert('Não foi possível identificar o atendimento para agendar.');
                return;
            }

            const funcionarioId = filaFuncionarioInput.value;
            const data = filaDataInput.value;
            const periodo = filaPeriodoInput.value;
            const horaInicio = filaHoraInicioInput.value;
            const duracaoHoras = filaDuracaoInput.value;

            if (!funcionarioId || !data || !periodo || !horaInicio || !duracaoHoras) {
                alert('Preencha todos os campos do agendamento.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('atendimentos') }}/${contextoReagendarFila.atendimentoId}/reagendar-agendamento`;

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
    </script>
    @endpush
</x-app-layout>
