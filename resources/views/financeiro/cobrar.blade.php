<x-app-layout>

    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @vite('resources/css/financeiro/contasareceber.css')
    @vite('resources/css/financeiro/index.css')
    <style>
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
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
            <x-filter :action="route('financeiro.cobrar')" :show-clear-button="true" class="mb-4">
                <x-filter-field name="search" label="Buscar por Cliente ou Descrição" placeholder="Ex: Invest, Manutenção..." colSpan="lg:col-span-4" />
                <x-filter-field name="empresa_id" label="Empresa" type="select" placeholder="Todas as Empresas" colSpan="lg:col-span-3">
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" @selected(request('empresa_id')==$empresa->id)>
                            {{ $empresa->nome_fantasia }}
                        </option>
                    @endforeach
                </x-filter-field>
                <x-filter-field name="vencimento_inicio" label="Vencimento (Início)" type="date" colSpan="lg:col-span-3" />
                <x-filter-field name="vencimento_fim" label="Vencimento (Fim)" type="date" colSpan="lg:col-span-2" />
            </x-filter>

            {{-- ================= MENSAGENS ================= --}}
            @if(session('error'))
                <x-alert type="danger" class="mb-4">{{ session('error') }}</x-alert>
            @endif
            @if(session('success'))
                <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
            @endif

            {{-- ================= TABELA ================= --}}
            @php
                $columns = [
                    ['label' => 'Orçamento'],
                    ['label' => 'Cliente'],
                    ['label' => 'Status'],
                    ['label' => 'Valor'],
                    ['label' => 'Agendar'],
                    ['label' => 'Ações'],
                ];
            @endphp

            @if($orcamentos->count())
            <x-table :columns="$columns" :data="$orcamentos" :actions="false" emptyMessage="Nenhum orçamento encontrado" class="mb-4">
                @php $totalPagina = 0; @endphp
                @foreach($orcamentos as $orcamento)
                @php $totalPagina += $orcamento->valor_total; @endphp
                <tr class="hover:bg-gray-50 transition">
                    <x-table-cell :nowrap="true">
                        {{ $orcamento->numero_orcamento }}
                        <div class="text-xs text-gray-500">{{ $orcamento->empresa->nome_fantasia ?? '—' }}</div>
                    </x-table-cell>
                    <x-table-cell type="muted">
                        {{ $orcamento->cliente?->nome_fantasia ?? $orcamento->cliente?->razao_social ?? $orcamento->preCliente?->nome_fantasia ?? $orcamento->preCliente?->razao_social ?? '—' }}
                    </x-table-cell>
                    <x-table-cell align="left">
                            @if($orcamento->status === 'financeiro')
                                <x-badge type="warning">Pendente</x-badge>
                            @elseif($orcamento->status === 'aprovado')
                                <x-badge type="success">Aprovado</x-badge>
                            @elseif($orcamento->status === 'reprovado')
                                <x-badge type="danger">Reprovado</x-badge>
                            @else
                                <x-badge type="default">{{ ucfirst(str_replace('_', ' ', $orcamento->status)) }}</x-badge>
                            @endif
                    </x-table-cell>
                    <x-table-cell align="right">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</x-table-cell>
                    <x-table-cell align="left">
                        <div class="flex flex-col items-start gap-1">
                            @if(empty($orcamento->data_agendamento))
                                <x-button size="sm" onclick="abrirCalendarioAgendamento({{ $orcamento->id }})">
                                    Agendar
                                </x-button>
                                <form method="POST" action="{{ route('financeiro.agendar-cobranca', $orcamento->id) }}" id="form-agendar-{{ $orcamento->id }}" style="display:none; margin-top:4px;" class="flex items-center gap-2">
                                    @csrf
                                    <input type="date" name="data_agendamento" min="{{ now()->toDateString() }}" class="rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-2 py-1 text-sm w-36" required>
                                    <x-button size="sm" type="submit">Salvar</x-button>
                                    <x-button size="sm" variant="secondary" type="button" onclick="fecharCalendarioAgendamento({{ $orcamento->id }})">Cancelar</x-button>
                                </form>
                            @else
                                <div class="flex items-center gap-2 mt-1">
                                    <x-badge type="success">Agendado para {{ \Carbon\Carbon::parse($orcamento->data_agendamento)->format('d/m/Y') }}</x-badge>
                                    <form method="POST" action="{{ route('financeiro.cancelar-agendamento', $orcamento->id) }}" onsubmit="return confirm('Deseja cancelar o agendamento?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button size="sm" variant="secondary" type="submit" title="Cancelar agendamento">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </x-button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </x-table-cell>
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
                @endforeach
            </x-table>

            {{-- ================= TOTAL ================= --}}
            <x-card class="mt-4 mb-6">
                <div class="p-4 flex justify-end">
                    <div class="text-right">
                        <span class="text-sm text-gray-600 uppercase tracking-wide">Total da Página:</span>
                        <span class="ml-2 text-xl font-bold text-gray-900">R$ {{ number_format($totalPagina, 2, ',', '.') }}</span>
                    </div>
                </div>
            </x-card>

            {{-- ================= PAGINAÇÃO ================= --}}
            <x-pagination :paginator="$orcamentos" label="orçamentos" />
            @else
            <x-card class="mt-6">
                <div class="p-12 text-center">
                    <h3 class="text-lg font-medium text-gray-900">Nenhum orçamento encontrado</h3>
                </div>
            </x-card>
            @endif

        </div>
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

    @include('financeiro.partials.modal-gerar-cobranca')
</x-app-layout>
