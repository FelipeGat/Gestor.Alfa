<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    @endpush

        @php
            $statusList = [
                'em_elaboracao' => 'Em elaboração',
                'aguardando_aprovacao' => 'Aguardando aprovação',
                'enviado' => 'Enviado',
                'aprovado' => 'Aprovado',
                'aguardando_pagamento'  => 'Aguardando Pagamento',
                'concluido' => 'Concluído',
                'recusado' => 'Recusado',
                'agendado' => 'Agendado',
                'em_andamento' => 'Em Andamento', 
                'garantia' => 'Garantia',
                'cancelado' => 'Cancelado',
            ];
        @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📄 Orçamentos
        </h2>
    </x-slot>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                {{-- ================= FILTROS ================= --}}
                <form method="GET" class="bg-white shadow rounded-lg p-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

        {{-- 🔍 PESQUISA --}}
        <div class="flex flex-col lg:col-span-6">
            <label class="text-sm font-medium text-gray-700 mb-2">
                🔍 Pesquisar Orçamentos
            </label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cliente, Empresa, Status"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- 📌 STATUS --}}
        <div class="flex flex-col lg:col-span-3">
            <label class="text-sm font-medium text-gray-700 mb-2">
                📌 Status
            </label>
            <select name="status[]" multiple
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500 h-32">
                @foreach($statusList as $key => $label)
                    <option value="{{ $key }}"
                        @selected(collect(request('status'))->contains($key))>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 🏢 EMPRESA --}}
        <div class="flex flex-col lg:col-span-3">
            <label class="text-sm font-medium text-gray-700 mb-2">
                🏢 Empresa
            </label>
            <select name="empresa_id[]" multiple
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500 h-32">
                @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}"
                        @selected(collect(request('empresa_id'))->contains($empresa->id))>
                        {{ $empresa->nome_fantasia }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 📅 PERÍODO --}}
        <div class="flex flex-col lg:col-span-3">
            <label class="text-sm font-medium text-gray-700 mb-2">
                📅 Período
            </label>
            <select name="periodo"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="ano" @selected(request('periodo') === 'ano')>Ano Atual</option>
                <option value="mes" @selected(request('periodo') === 'mes')>Mês Atual</option>
                <option value="semana" @selected(request('periodo') === 'semana')>Semana Atual</option>
                <option value="dia" @selected(request('periodo') === 'dia')>Hoje</option>
                <option value="intervalo" @selected(request('periodo') === 'intervalo')>
                    Intervalo de Datas
                </option>
            </select>
        </div>

        {{-- 📆 DATA INICIAL --}}
        <div class="flex flex-col lg:col-span-2">
            <label class="text-sm text-gray-600">Data Inicial</label>
            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>

        {{-- 📆 DATA FINAL --}}
        <div class="flex flex-col lg:col-span-2">
            <label class="text-sm text-gray-600">Data Final</label>
            <input type="date" name="data_fim" value="{{ request('data_fim') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>

        {{-- 🔘 BOTÕES --}}
        <div class="flex gap-3 items-end lg:col-span-5 justify-end">
            <button type="submit" class="btn btn-primary">
                🔍 Filtrar
            </button>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
                <a href="{{ route('orcamentos.create') }}" class="btn btn-success">
                    ➕ Orçamento
                </a>
            @endif
            
            {{-- 🧹 LIMPAR FILTROS --}}
                <a href="{{ route('orcamentos.index') }}"
                class="btn btn-secondary">
                    🧹 Limpar
                </a>
        </div>

    </div>
</form>


            {{-- ================= ATENDIMENTOS AGUARDANDO ORÇAMENTO ================= --}}
            @if(isset($atendimentosParaOrcamento) && $atendimentosParaOrcamento->count())
            <div class="table-card mb-8 border-l-4 border-yellow-500 bg-yellow-50/30">
                <div class="p-4 border-b border-yellow-100">
                    <h3 class="font-bold text-yellow-800 flex items-center gap-2">
                        <span class="text-xl">🛠️</span> Atendimentos aguardando orçamento
                    </h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
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
                                <td>{{ $atendimento->cliente->nome ?? '—' }}</td>
                                <td>{{ $atendimento->empresa?->nome_fantasia ?? '—' }}</td>
                                <td>{{ $atendimento->created_at->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('orcamentos.create', ['atendimento_id' => $atendimento->id]) }}" class="btn btn-pre btn-sm">
                                        Criar Orçamento
                                    </a>
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
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{!! sortLink('Nº', 'numero_orcamento') !!}</th>
                                <th>{!! sortLink('Cliente', 'nome_cliente') !!}</th>
                                <th>Empresa</th>
                                <th class="text-center">{!! sortLink('Status', 'status') !!}</th>
                                <th>{!! sortLink('Valor Total', 'valor_total') !!}</th>
                                <th>{!! sortLink('Data', 'created_at') !!}</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orcamentos as $orcamento)
                            <tr>
                                <td class="font-bold text-gray-900">{{ $orcamento->numero_orcamento }}</td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $orcamento->nome_cliente }}</span>
                                        @if($orcamento->pre_cliente_id)
                                        <span class="text-[10px] uppercase font-bold text-orange-500">Pré-Cliente</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-gray-500 text-xs">{{ $orcamento->empresa?->nome_fantasia ?? '—' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('orcamentos.updateStatus', $orcamento) }}" method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <select name="status" onchange="this.form.submit()" class="status-select status-{{ $orcamento->status }}">
                                            @foreach($statusList as $key => $label)
                                                <option value="{{ $key }}" @selected($orcamento->status === $key)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="font-semibold text-gray-900">
                                    {{ $orcamento->valor_total ? 'R$ ' . number_format($orcamento->valor_total, 2, ',', '.') : '—' }}
                                </td>
                                <td class="text-gray-500">{{ $orcamento->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('orcamentos.imprimir', $orcamento->id) }}" target="_blank" class="btn btn-sm btn-edit" title="Imprimir">
                                            🖨️
                                        </a>
                                        <a href="{{ route('orcamentos.edit', $orcamento) }}" class="btn btn-sm btn-edit" title="Editar">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('orcamentos.destroy', $orcamento) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este orçamento?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-delete" title="Excluir">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
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
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <strong>{{ $orcamentos->count() }}</strong> de
                    <strong>{{ $orcamentos->total() }}</strong>
                    Orcamentos
                </div>

                <div class="pagination-links">
                    {{-- Link Anterior --}}
                    @if($orcamentos->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $orcamentos->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif

                    {{-- Links de Página --}}
                    @foreach($orcamentos->getUrlRange(1, $orcamentos->lastPage()) as $page => $url)
                    @if($page == $orcamentos->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach

                    {{-- Link Próximo --}}
                    @if($orcamentos->hasMorePages())
                    <a href="{{ $orcamentos->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>

            @else
            <div class="empty-state">
                <div class="text-5xl mb-4">📁</div>
                <h3>Nenhum orçamento encontrado</h3>
                <p>Ainda não existem orçamentos cadastrados no sistema.</p>
                <a href="{{ route('orcamentos.create') }}" class="btn btn-success mt-4">Criar Primeiro Orçamento</a>
            </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
