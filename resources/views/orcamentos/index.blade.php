<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    <style>
        /* Filtros */
        .filters-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        /* Inputs e Selects */
        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Tabela */
        .tabela-orcamentos thead th {
            color: rgb(17, 24, 39) !important;
            font-size: 14px;
            font-weight: 600;
        }
        .tabela-orcamentos tbody td {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-orcamentos tbody td.font-medium {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-orcamentos tbody td:nth-child(1),
        .tabela-orcamentos tbody td:nth-child(2),
        .tabela-orcamentos tbody td:nth-child(3),
        .tabela-orcamentos tbody td:nth-child(5),
        .tabela-orcamentos tbody td:nth-child(6) {
            font-family: 'Inter', sans-serif !important;
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        /* Paginação */
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
        /* Cards KPI */
        .kpi-card {
            background: white;
            border: 1px solid;
            border-left-width: 4px;
            border-top-width: 1px;
            border-right-width: 1px;
            border-bottom-width: 1px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .kpi-card-blue { border-color: #2563eb; }
        .kpi-card-green { border-color: #16a34a; }
        .kpi-card-yellow { border-color: #eab308; }
        .kpi-card-purple { border-color: #7c3aed; }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Comercial', 'url' => route('comercial.index')],
            ['label' => 'Orçamentos']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Mensagens de erro/sucesso --}}
            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="filters-card p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                    <div class="flex flex-col lg:col-span-4">
                        <label class="text-sm font-medium text-gray-700 mb-2">Pesquisar</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cliente, Empresa, Status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todos</option>
                            @foreach($statusList as $key => $label)
                            <option value="{{ $key }}" @selected(collect(request('status'))->contains($key))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">Empresa</label>
                        <select name="empresa_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @selected(collect(request('empresa_id'))->contains($empresa->id))>{{ $empresa->nome_fantasia }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-2">Período</label>
                        <select name="periodo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todos</option>
                            <option value="ano" @selected(request('periodo')==='ano')>Ano Atual</option>
                            <option value="mes" @selected(request('periodo')==='mes')>Mês Atual</option>
                            <option value="semana" @selected(request('periodo')==='semana')>Semana Atual</option>
                            <option value="dia" @selected(request('periodo')==='dia')>Hoje</option>
                            <option value="intervalo" @selected(request('periodo')==='intervalo')>Intervalo</option>
                        </select>
                    </div>

                    <div class="flex flex-col lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
                        <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                    </div>

                    <div class="flex flex-col lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-2">Data Final</label>
                        <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-3">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #3f9cae; border-radius: 9999px;">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>

                        <a href="{{ route('orcamentos.index') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #9ca3af; border-radius: 9999px; box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(156, 163, 175, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(156, 163, 175, 0.3)'">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Limpar
                        </a>
                    </div>
                </div>
            </form>

            {{-- ================= RESUMO ================= --}}
            @php
            $totalOrcamentos = $orcamentos->total();
            $aprovados = $orcamentos->filter(fn($o) => $o->status === 'aprovado')->count();
            $pendentes = $orcamentos->filter(fn($o) => in_array($o->status, ['em_elaboracao', 'aguardando_aprovacao']))->count();
            $valorTotal = $orcamentos->sum('valor_total');
            @endphp

            <style>
            @media (min-width: 1024px) {
                .resumo-grid {
                    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                }
            }
            </style>

            <div class="resumo-grid gap-4 mb-6" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));">
                <div class="kpi-card kpi-card-blue w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Orçamentos</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $totalOrcamentos }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-green w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Aprovados</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $aprovados }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-yellow w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Pendentes</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        {{ $pendentes }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-purple w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Valor Total</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">
                        R$ {{ number_format($valorTotal, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- ================= ATENDIMENTOS AGUARDANDO ORÇAMENTO ================= --}}
            @if(isset($atendimentosParaOrcamento) && $atendimentosParaOrcamento->count())
            <div class="section-card mb-6">
                <div class="card-header">
                    <h3 class="font-bold text-gray-800">Atendimentos aguardando orçamento</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        @if($errors->has('delete'))
                        <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                            {{ $errors->first('delete') }}
                        </div>
                        @endif
                        <thead>
                            <tr>
                                <th>Nº Atendimento</th>
                                <th>Cliente</th>
                                <th>Empresa</th>
                                <th>Data</th>
                                <th class="text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atendimentosParaOrcamento as $atendimento)
                            <tr>
                                <td class="font-mono font-bold text-blue-600">#{{ $atendimento->numero_atendimento }}</td>
                                <td>
                                    @if($atendimento->cliente)
                                        {{ $atendimento->cliente->nome }}
                                    @elseif(!empty($atendimento->nome_solicitante))
                                        {{ $atendimento->nome_solicitante }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $atendimento->empresa?->nome_fantasia ?? '—' }}</td>
                                <td>{{ $atendimento->created_at->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('orcamentos.create', ['atendimento_id' => $atendimento->id]) }}" class="btn btn-pre btn-sm">
                                            Criar Orçamento
                                        </a>
                                        <a href="{{ route('atendimentos.edit', $atendimento->id) }}" target="_blank" class="btn btn-sm btn-secondary" title="Imprimir Atendimento">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ================= LISTA DE ORÇAMENTOS ================= --}}
            @if($orcamentos->count() > 0)
            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <a href="{{ route('orcamentos.create') }}" class="btn btn-success inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @php
            function sortLink($label, $column) {
            $direction = request('direction') === 'asc' ? 'desc' : 'asc';

            return '<a href="' . request()->fullUrlWithQuery([
                        'sort' => $column,
                        'direction' => $direction
                    ]) . '" class="flex items-center gap-1 hover:text-blue-600">'
                . $label .
                (request('sort') === $column
                ? (request('direction') === 'asc' ? ' ▲' : ' ▼')
                : '') .
                '</a>';
            }
            @endphp
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto tabela-orcamentos">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Nº', 'numero_orcamento') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Cliente', 'nome_cliente') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Empresa</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Status', 'status') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Valor Total', 'valor_total') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Data', 'created_at') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($orcamentos as $orcamento)
                            <tr class="hover:bg-gray-50 transition" data-orcamento-id="{{ $orcamento->id }}">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    {{ $orcamento->numero_orcamento }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ $orcamento->nome_cliente }}</span>
                                        @if($orcamento->pre_cliente_id)
                                        <span class="text-[10px] uppercase font-bold text-orange-500">Pré-Cliente</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $orcamento->empresa?->nome_fantasia ?? '—' }}</td>
                                <td class="px-4 py-3 text-left" onclick="event.stopPropagation();">
                                    <form action="{{ route('orcamentos.updateStatus', $orcamento) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="orcamento_status" class="status-select status-{{ $orcamento->status }} text-xs" 
                                            style="border: none !important; text-align: center; @switch($orcamento->status)
                                                @case('em_elaboracao') background-color: #f3f4f6; color: #374151; @break
                                                @case('aguardando_aprovacao') background-color: #fef3c7; color: #92400e; @break
                                                @case('aprovado') background-color: #dcfce7; color: #166534; @break
                                                @case('aguardando_pagamento') background-color: #fef9c3; color: #854d0e; @break
                                                @case('agendado') background-color: #ede9fe; color: #5b21b6; @break
                                                @case('em_andamento') background-color: #e0f2fe; color: #075985; @break
                                                @case('financeiro') background-color: #fee2e2; color: #dc2626; @break
                                                @case('concluido') background-color: #dcfce7; color: #15803d; @break
                                                @case('recusado') background-color: #fee2e2; color: #991b1b; @break
                                                @case('garantia') background-color: #ffedd5; color: #9a3412; @break
                                                @case('cancelado') background-color: #f3f4f6; color: #6b7280; @break
                                            @endswitch">
                                            @foreach($statusList as $key => $label)
                                            <option value="{{ $key }}" @selected($orcamento->status === $key)>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    {{ $orcamento->valor_total ? 'R$ ' . number_format($orcamento->valor_total, 2, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $orcamento->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-left">
                                    <div class="flex gap-1 items-center justify-start">
                                        <a href="{{ route('orcamentos.imprimir', $orcamento->id) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-gray-800 hover:bg-gray-900 text-white rounded-full transition" title="Imprimir">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('orcamentos.duplicate', $orcamento) }}" method="POST" onsubmit="return confirm('Deseja duplicar este orçamento?')" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition" title="Duplicar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('orcamentos.edit', $orcamento) }}" class="btn btn-sm btn-edit" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Editar">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('orcamentos.destroy', $orcamento) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este orçamento?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-delete" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Excluir">
                                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
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

            {{-- ================= PAGINAÇÃO ================= --}}
            <div class="pagination-container">
                {{ $orcamentos->links() }}
            </div>

            @else
            <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <h3 class="text-lg font-medium text-gray-900">Nenhum orçamento encontrado</h3>
                <a href="{{ route('orcamentos.create') }}" class="btn btn-success mt-4 inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(34, 197, 94, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(34, 197, 94, 0.3)'">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT' && e.target.name === 'orcamento_status') {
                e.preventDefault();
                e.stopPropagation();
                
                // Navegar manualmente para encontrar o formulário
                var element = e.target;
                var form = null;
                
                // Subir na árvore do DOM até encontrar o FORM
                while (element && element.tagName !== 'BODY') {
                    if (element.tagName === 'FORM') {
                        form = element;
                        break;
                    }
                    element = element.parentElement;
                }
                
                if (form) {
                    form.submit();
                } else {
                    // Fallback: criar form dinamicamente
                    var status = e.target.value;
                    var row = e.target.closest('tr');
                    var orcamentoId = row ? row.getAttribute('data-orcamento-id') : null;
                    
                    if (!orcamentoId) {
                        console.error('ID do orçamento não encontrado');
                        return;
                    }
                    
                    // Criar form dinamicamente
                    var tempForm = document.createElement('form');
                    tempForm.method = 'POST';
                    tempForm.action = '/orcamentos/' + orcamentoId + '/status';
                    
                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    tempForm.appendChild(csrfInput);
                    
                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PATCH';
                    tempForm.appendChild(methodInput);
                    
                    var statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'orcamento_status';
                    statusInput.value = status;
                    tempForm.appendChild(statusInput);
                    
                    document.body.appendChild(tempForm);
                    tempForm.submit();
                }
            }
        });
    });
</script>