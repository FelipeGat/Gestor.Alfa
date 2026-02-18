<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <nav class="flex items-center gap-2 text-base font-semibold leading-tight rounded-full py-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-800 font-medium">Orçamentos</span>
        </nav>
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
            <div class="filters-card" style="border: none;">
                <form method="GET" class="filter-form">
                    <div class="bg-white rounded-lg p-4 mb-4">
                        <div class="filter-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pesquisar</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cliente, Empresa, Status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            {{-- 📌 STATUS --}}
                            <div class="filter-group">
                                <label class="filter-label">Status</label>
                                <select name="status" multiple
                                    class="filter-select h-24">
                                    @foreach($statusList as $key => $label)
                                    <option value="{{ $key }}"
                                        @selected(collect(request('status'))->contains($key))>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 🏢 EMPRESA --}}
                            <div class="filter-group">
                                <label class="filter-label">Empresa</label>
                                <select name="empresa_id" multiple
                                    class="filter-select h-24">
                                    @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}"
                                        @selected(collect(request('empresa_id'))->contains($empresa->id))>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 📅 PERÍODO --}}
                            <div class="filter-group">
                                <label class="filter-label">Período</label>
                                <select name="periodo"
                                    class="filter-select">
                                    <option value="">Todos</option>
                                    <option value="ano" @selected(request('periodo')==='ano' )>Ano Atual</option>
                                    <option value="mes" @selected(request('periodo')==='mes' )>Mês Atual</option>
                                    <option value="semana" @selected(request('periodo')==='semana' )>Semana Atual</option>
                                    <option value="dia" @selected(request('periodo')==='dia' )>Hoje</option>
                                    <option value="intervalo" @selected(request('periodo')==='intervalo' )>
                                        Intervalo de Datas
                                    </option>
                                </select>
                            </div>

                            <div></div>

                            {{-- 📆 DATA INICIAL --}}
                            <div class="filter-group">
                                <label class="filter-label">Data Inicial</label>
                                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                                    class="filter-select">
                            </div>

                            {{-- 📆 DATA FINAL --}}
                            <div class="filter-group">
                                <label class="filter-label">Data Final</label>
                                <input type="date" name="data_fim" value="{{ request('data_fim') }}"
                                    class="filter-select">
                            </div>
                        </div>
                    </div>

                    <div class="filter-actions justify-end">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #3b82f6; border-radius: 9999px;">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>

                        <a href="{{ route('orcamentos.index') }}"
                            class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; border-radius: 9999px;">
                            Limpar
                        </a>
                    </div>
            </div>

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
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Orçamentos</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $totalOrcamentos }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Aprovados</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $aprovados }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-yellow-500 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Pendentes</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        {{ $pendentes }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-purple-600 w-full max-w-none">
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
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">{!! sortLink('Nº', 'numero_orcamento') !!}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">{!! sortLink('Cliente', 'nome_cliente') !!}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Empresa</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">{!! sortLink('Status', 'status') !!}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">{!! sortLink('Valor Total', 'valor_total') !!}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">{!! sortLink('Data', 'created_at') !!}</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Ações</th>
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
                                <td class="px-4 py-3 text-center">
                                    <div class="flex gap-1 items-center justify-center">
                                        <a href="{{ route('orcamentos.imprimir', $orcamento->id) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-gray-800 hover:bg-gray-900 text-white rounded-full transition" title="Imprimir">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                                            </svg>
                                        </a>
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
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <strong>{{ $orcamentos->count() }}</strong> de
                    <strong>{{ $orcamentos->total() }}</strong>
                    Orçamentos
                </div>

                <div class="pagination-links">
                    {{ $orcamentos->links() }}
                </div>
            </div>

            @else
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <h3 class="text-lg font-medium text-gray-900">Nenhum orçamento encontrado</h3>
                <a href="{{ route('orcamentos.create') }}" class="btn btn-success mt-4 inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
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