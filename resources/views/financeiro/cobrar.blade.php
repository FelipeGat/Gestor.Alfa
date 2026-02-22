<x-app-layout>

    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @vite('resources/css/financeiro/contasareceber.css')
    @vite('resources/css/financeiro/index.css')
    <style>
        .filter-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
            display: block;
        }
        .filter-select:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Filtros */
        .filters-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            margin-bottom: 0;
        }
        /* Inputs e Selects */
        .filters-card input:focus,
        .filters-card select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Tabela */
        .tabela-contas thead th {
            color: rgb(17, 24, 39) !important;
            font-size: 14px;
            font-weight: 600;
        }
        .tabela-contas tbody td {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-contas tbody td.font-medium {
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
        .kpi-card-red { border-color: #dc2626; }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Cobrar']
        ]" />
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" action="{{ route('financeiro.cobrar') }}" class="filters-card p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                    <div class="flex flex-col lg:col-span-4">
                        <label class="text-sm font-medium text-gray-700 mb-2">Buscar por Cliente ou Descrição</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ex: Invest, Manutenção..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">Empresa</label>
                        <select name="empresa_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todas as Empresas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome_fantasia }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">Vencimento (Início)</label>
                        <input type="date" name="vencimento_inicio" value="{{ request('vencimento_inicio') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                    </div>

                    <div class="flex flex-col lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-2">Vencimento (Fim)</label>
                        <input type="date" name="vencimento_fim" value="{{ request('vencimento_fim') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-3">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #3f9cae; border-radius: 9999px; box-shadow: 0 2px 4px rgba(63, 156, 174, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(63, 156, 174, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(63, 156, 174, 0.3)'">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>
                        <a href="{{ route('financeiro.cobrar') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #9ca3af; border-radius: 9999px; box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(156, 163, 175, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(156, 163, 175, 0.3)'">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Limpar
                        </a>
                    </div>
                </div>
            </form>

            {{-- Mensagens de Feedback --}}
            @if(session('error'))
            <div class="mb-4">
                <div class="alert alert-error">{{ session('error') }}</div>
            </div>
            @endif
            @if(session('success'))
            <div class="mb-4">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
            @endif

            {{-- ================= TABELA ================= --}}
            @if($orcamentos->count())
            <div class="bg-white rounded-lg overflow-hidden mt-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto tabela-contas">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">ORÇAMENTO</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">CLIENTE</th>
                                <th class="px-4 py-3 text-center uppercase" style="font-size: 14px; font-weight: 600;">STATUS</th>
                                <th class="px-4 py-3 text-right uppercase" style="font-size: 14px; font-weight: 600;">VALOR</th>
                                <th class="px-4 py-3 text-center uppercase" style="font-size: 14px; font-weight: 600;">AGENDAR</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php $totalPagina = 0; @endphp
                            @forelse($orcamentos as $orcamento)
                            @php $totalPagina += $orcamento->valor_total; @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    {{ $orcamento->numero_orcamento }}
                                    <div class="text-xs text-gray-500">{{ $orcamento->empresa->nome_fantasia ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $orcamento->cliente?->nome_fantasia ?? $orcamento->cliente?->razao_social ?? $orcamento->preCliente?->nome_fantasia ?? $orcamento->preCliente?->razao_social ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center h-8 px-3 rounded-full text-sm font-semibold w-28 uppercase
                                        @if($orcamento->status === 'financeiro') bg-yellow-100 text-yellow-800
                                        @elseif($orcamento->status === 'aprovado') bg-green-100 text-green-800
                                        @elseif($orcamento->status === 'reprovado') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $orcamento->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                                    R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-normal">
                                    <div class="flex flex-col items-center gap-1">
                                        @if(empty($orcamento->data_agendamento))
                                            <button type="button" class="btn btn-xs btn-outline-primary font-normal" onclick="abrirCalendarioAgendamento({{ $orcamento->id }})">Agendar</button>
                                            <form method="POST" action="{{ route('financeiro.agendar-cobranca', $orcamento->id) }}" id="form-agendar-{{ $orcamento->id }}" style="display:none; margin-top:4px;" class="flex items-center gap-2">
                                                @csrf
                                                <input type="date" name="data_agendamento" min="{{ now()->toDateString() }}" class="rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-2 py-1 text-sm w-36" required>
                                                <button type="submit" class="btn btn-xs btn-success font-normal">Salvar</button>
                                                    <button type="button" class="btn btn-xs btn-secondary font-normal" onclick="fecharCalendarioAgendamento({{ $orcamento->id }})">Cancelar</button>
                                            </form>
                                        @else
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs" style="font-weight: 400;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                    Agendado para {{ \Carbon\Carbon::parse($orcamento->data_agendamento)->format('d/m/Y') }}
                                                </span>
                                                <form method="POST" action="{{ route('financeiro.cancelar-agendamento', $orcamento->id) }}" onsubmit="return confirm('Deseja cancelar o agendamento?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger font-normal" title="Cancelar agendamento">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                <script>
                                function abrirCalendarioAgendamento(id) {
                                    document.getElementById('form-agendar-' + id).style.display = 'flex';
                                    event.target.style.display = 'none';
                                }
                                function fecharCalendarioAgendamento(id) {
                                    document.getElementById('form-agendar-' + id).style.display = 'none';
                                    const btns = document.querySelectorAll('[onclick^="abrirCalendarioAgendamento"]');
                                    btns.forEach(btn => {
                                        if (btn.getAttribute('onclick').includes(id)) {
                                            btn.style.display = '';
                                        }
                                    });
                                }
                                </script>
                                </td>
                                <td class="px-4 py-3 text-left">
                                    <div class="flex gap-1 items-center justify-start">
                                        @if($orcamento->status === 'financeiro')
                                        @php
                                        $__orcData = [
                                            'id' => $orcamento->id,
                                            'numero_orcamento' => $orcamento->numero_orcamento,
                                            'valor_total' => $orcamento->valor_total,
                                            'cliente' => [
                                                'nome_fantasia' => $orcamento->cliente?->nome_fantasia ?? $orcamento->cliente?->razao_social ?? null
                                            ],
                                            'pre_cliente_id' => $orcamento->pre_cliente_id ?? null,
                                            'forma_pagamento' => $orcamento->forma_pagamento,
                                        ];
                                        @endphp
                                        <button type="button" class="inline-flex items-center justify-center w-8 h-8 bg-green-600 hover:bg-green-700 text-white rounded-full transition" data-role="gerar-cobranca" data-orc='@json($__orcData)' title="Gerar Cobrança">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                        @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-400 rounded-full" title="Cobrança Gerada">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                        @endif
                                        <a href="{{ route('orcamentos.imprimir', $orcamento->id) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 bg-gray-800 hover:bg-gray-900 text-white rounded-full transition" title="Imprimir">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <span class="text-3xl mb-2">📂</span>
                                        Nenhum orçamento encontrado.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= TOTAL ================= --}}
            <div class="bg-white rounded-lg overflow-hidden mt-4" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="p-4 flex justify-end">
                    <div class="text-right">
                        <span class="text-sm text-gray-600 uppercase tracking-wide">Total da Página:</span>
                        <span class="ml-2 text-xl font-bold text-gray-900">R$ {{ number_format($totalPagina, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- ================= PAGINAÇÃO ================= --}}
            <div class="pagination-container mt-4">
                {{ $orcamentos->links() }}
            </div>
            @else
            <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <h3 class="text-lg font-medium text-gray-900">Nenhum orçamento encontrado</h3>
            </div>
            @endif

        </div>
    </div>
    @include('financeiro.partials.modal-gerar-cobranca')
</x-app-layout>