<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <style>
        @media print {
            .print\:hidden, .filters-card, .pagination-container,
            header, footer, nav, .btn, a[href] {
                display: none !important;
            }
        }
        .filters-card {
            border: 1px solid #3f9cae !important;
            border-top-width: 4px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            border-radius: 0.5rem !important;
        }
        .section-card {
            border: 1px solid #3f9cae !important;
            border-top-width: 4px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            border-radius: 0.5rem !important;
        }
        .filter-select:focus, .filter-input:focus {
            border-color: #3f9cae !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
    </style>

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Relatórios', 'url' => route('relatorios.index')],
            ['label' => 'Contas a Pagar']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="section-card filters-card mb-6 print:hidden">
                <form method="GET" class="filter-form">

                    <div class="bg-white rounded-lg p-4 border border-gray-200 mb-4">
                        <div class="filter-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Navegação Rápida</label>
                            <div class="w-full" style="max-width: 700px;">
                                <div class="flex items-center gap-2 flex-wrap">
                            @php
                                $hoje = \Carbon\Carbon::today();
                                $ontem = \Carbon\Carbon::yesterday();
                            @endphp
                            <a href="{{ route('relatorios.contas-pagar', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $ontem->format('Y-m-d'), 'data_fim' => $ontem->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('data_inicio') == $ontem->format('Y-m-d') && request('data_fim') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}"
                                >
                                Ontem
                            </a>
                            <a href="{{ route('relatorios.contas-pagar', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $hoje->format('Y-m-d'), 'data_fim' => $hoje->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('data_inicio') == $hoje->format('Y-m-d') && request('data_fim') == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}"
                                >
                                Hoje
                            </a>
                            @php
                            $dataAtual = request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio')) : \Carbon\Carbon::now();
                            $mesAnterior = $dataAtual->copy()->subMonth();
                            $proximoMes = $dataAtual->copy()->addMonth();
                            $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                            $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                            @endphp

                            <a href="{{ route('relatorios.contas-pagar', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'), 'data_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>

                            <div class="flex-1 text-center font-bold text-gray-700 bg-white px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                                {{ $mesAtualNome }}
                            </div>

                            <a href="{{ route('relatorios.contas-pagar', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'), 'data_fim' => $proximoMes->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            </div>
                            <details class="mt-2" id="periodoPersonalizadoDetails">
                                <summary id="periodoPersonalizadoSummary" class="cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                    Período Personalizado
                                </summary>
                                @php
                                    $dataAtual = request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio')) : \Carbon\Carbon::now();
                                    $inicioPadrao = $dataAtual->copy()->startOfMonth()->format('Y-m-d');
                                    $fimPadrao = $dataAtual->copy()->endOfMonth()->format('Y-m-d');
                                    $dataInicio = request('data_inicio') ?? $inicioPadrao;
                                    $dataFim = request('data_fim') ?? $fimPadrao;
                                @endphp
                                <div id="periodoPersonalizadoContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Data Inicial</label>
                                        <input type="date" name="data_inicio" id="data_inicio_relatorio" value="{{ $dataInicio }}"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Data Final</label>
                                        <input type="date" name="data_fim" id="data_fim_relatorio" value="{{ $dataFim }}"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    </div>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const details = document.getElementById('periodoPersonalizadoDetails');
                                        const summary = document.getElementById('periodoPersonalizadoSummary');
                                        if (details && summary) {
                                            summary.addEventListener('click', function (e) {
                                                if (!details.open) {
                                                    e.preventDefault();
                                                    details.open = true;
                                                }
                                            });
                                        }
                                        const dataInicio = document.getElementById('data_inicio_relatorio');
                                        const dataFim = document.getElementById('data_fim_relatorio');
                                        if (dataInicio && dataFim) {
                                            dataInicio.addEventListener('change', function () {
                                                if (dataInicio.value) {
                                                    const data = new Date(dataInicio.value);
                                                    data.setDate(data.getDate() + 1);
                                                    const nextDay = data.toISOString().slice(0, 10);
                                                    dataFim.value = nextDay;
                                                }
                                            });
                                        }
                                    });
                                </script>
                            </details>
                        </div>
                    </div>

                    <div class="mb-6"></div>

                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filtros</label>
                        <div class="filter-grid mb-0">
                            <div class="filter-group">
                                <label class="filter-label">Empresa</label>
                            <select name="empresa_id" class="filter-select">
                                <option value="">Todas as Empresas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome_fantasia ?? $empresa->razao_social ?? $empresa->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Centro de Custo</label>
                            <select name="centro_custo_id" class="filter-select">
                                <option value="">Todos os Centros</option>
                                @foreach($centrosCusto as $centro)
                                    <option value="{{ $centro->id }}" {{ request('centro_custo_id') == $centro->id ? 'selected' : '' }}>
                                        {{ $centro->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Fornecedor</label>
                            <select name="fornecedor_id" class="filter-select">
                                <option value="">Todos os Fornecedores</option>
                                @foreach($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}" {{ request('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>
                                        {{ $fornecedor->nome_fantasia ?? $fornecedor->razao_social ?? $fornecedor->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select name="status" class="filter-select">
                                <option value="">Todos os Status</option>
                                <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                                <option value="vence_hoje" {{ request('status') == 'vence_hoje' ? 'selected' : '' }}>Vence Hoje</option>
                                <option value="a_vencer" {{ request('status') == 'a_vencer' ? 'selected' : '' }}>A Vencer</option>
                                <option value="vencido" {{ request('status') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                                <option value="em_aberto" {{ request('status') == 'em_aberto' ? 'selected' : '' }}>Em Aberto</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-actions justify-end mt-4">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #3f9cae; border-radius: 9999px;">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>
                        <a href="{{ route('relatorios.contas-pagar') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #9ca3af; border-radius: 9999px; box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3); text-decoration: none;" onmouseover="this.style.boxShadow='0 4px 6px rgba(156, 163, 175, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(156, 163, 175, 0.3)'">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Limpar
                        </a>
                    </div>
                </form>
            </div>

            <div class="section-card mb-6 print-section mt-10">
                <div class="Print-title mb-6 p-4 border-b-2 border-gray-300 flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Relatório - Contas a Pagar</h1>
                        <p class="text-sm text-gray-600 mt-2">Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</p>
                    </div>
                    <button onclick="imprimirRelatorio(event)" class="inline-flex items-center justify-center h-10 w-10 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-full shadow-lg transition-all transform hover:scale-105 active:scale-95 print:hidden" title="Imprimir Relatório">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                        </svg>
                    </button>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-left">Empresa</th>
                                <th class="text-left">Centro</th>
                                <th class="text-left">Fornecedor</th>
                                <th class="text-left">Vencimento</th>
                                <th class="text-left">Valor</th>
                                <th class="text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contasPagar as $conta)
                                <tr>
                                    <td class="text-left">
                                        {{ $conta->empresaRelacionada?->nome_fantasia ?? $conta->empresaRelacionada?->razao_social ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->centroCusto?->nome ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->fornecedor?->nome_fantasia ?? $conta->fornecedor?->razao_social ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->data_vencimento?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td class="text-left font-black text-red-600">
                                        R$ {{ number_format($conta->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="text-left">
                                        <x-status-badge-contas-receber :status="$conta->status" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-sm text-gray-500 py-6">
                                        Nenhuma conta a pagar encontrada com os filtros selecionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            @foreach($totalizadoresPorCentro as $totalizador)
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-right">
                                    <span class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ $totalizador['nome'] }}</span>
                                    <span class="text-base font-black text-blue-600 ml-4">R$ {{ number_format($totalizador['total'], 2, ',', '.') }}</span>
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-right">
                                    <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Total a Pagar</span>
                                    <span class="text-base font-black text-red-600 ml-4">R$ {{ number_format($totalPagar, 2, ',', '.') }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="pagination-container p-4">
                    {{ $contasPagar->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
    function imprimirRelatorio(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        var btn = event ? event.currentTarget : null;
        var btnOriginalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Carregando...';
        }
        
        var urlParams = new URLSearchParams(window.location.search);
        var params = {};
        urlParams.forEach(function(value, key) {
            params[key] = value;
        });
        
        var url = '{{ route("relatorios.contas-pagar.json") }}' + '?' + urlParams.toString();
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            var titulo = 'Relatório - Contas a Pagar';
            var dataInicio = data.data_inicio_formatada;
            var dataFim = data.data_fim_formatada;
            
            var contas = data.contas_pagar;
            var totalLabel = 'Total a Pagar';
            var totalValor = data.total_pagar_formatado;
            var corTotal = '#dc2626';
            
            var totalizadoresPorCentro = data.totalizadores_por_centro || [];
            var totalizadoresHtml = '';
            if (totalizadoresPorCentro.length > 0) {
                totalizadoresPorCentro.forEach(function(centro) {
                    totalizadoresHtml += '<div style="text-align: right; padding: 6px 0;">';
                    totalizadoresHtml += '<span style="font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">' + centro.nome + '</span>';
                    totalizadoresHtml += '<span style="font-size: 13px; font-weight: bold; color: #2563eb; margin-left: 10px;">' + centro.total_formatado + '</span>';
                    totalizadoresHtml += '</div>';
                });
            }
            
            var tableRows = '';
            if (contas.length === 0) {
                tableRows = '<tr><td colspan="6" style="padding: 20px; text-align: center; color: #666;">Nenhuma conta encontrada com os filtros selecionados.</td></tr>';
            } else {
                var statusConfig = {
                    'pago': { label: 'Pago', bg: '#dcfce7', color: '#166534', icon: 'check' },
                    'vencido': { label: 'Vencido', bg: '#fee2e2', color: '#991b1b', icon: 'x' },
                    'vence_hoje': { label: 'Vence Hoje', bg: '#ffedd5', color: '#9a3412', icon: 'clock' },
                    'a_vencer': { label: 'A Vencer', bg: '#dbeafe', color: '#1e40af', icon: 'clock' },
                    'em_aberto': { label: 'Em Aberto', bg: '#f3f4f6', color: '#374151', icon: 'clock' }
                };
                var iconSvg = {
                    'check': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; margin-right: 6px;"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>',
                    'x': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; margin-right: 6px;"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>',
                    'clock': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; margin-right: 6px;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>'
                };
                contas.forEach(function(conta) {
                    var status = conta.status || 'em_aberto';
                    var config = statusConfig[status] || statusConfig['em_aberto'];
                    tableRows += '<tr>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (conta.empresa || '-') + '</td>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (conta.centro_custo || '-') + '</td>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (conta.fornecedor || '-') + '</td>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (conta.data_vencimento || '-') + '</td>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; text-align: right; font-weight: bold; color: #dc2626; white-space: nowrap;">' + conta.valor_formatado + '</td>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;"><span style="display: inline-flex; align-items: center; justify-content: center; width: 130px; padding: 6px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; background-color: ' + config.bg + '; color: ' + config.color + ';">' + iconSvg[config.icon] + config.label + '</span></td>';
                    tableRows += '</tr>';
                });
            }
            
            var tableHeader = '<thead><tr>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Empresa</th>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Centro</th>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Fornecedor</th>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Vencimento</th>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: right; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Valor</th>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Status</th>';
            tableHeader += '</tr></thead>';
            
            var tableHtml = '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">' + tableHeader + '<tbody>' + tableRows + '</tbody></table>';
            
            var totalsHtml = '';
            if (totalizadoresHtml) {
                totalsHtml += totalizadoresHtml;
                totalsHtml += '<div style="border-top: 1px solid #e5e7eb; margin: 8px 0;"></div>';
            }
            totalsHtml += '<div style="text-align: right;">';
            totalsHtml += '<span style="font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">' + totalLabel + '</span>';
            totalsHtml += '<span style="font-size: 13px; font-weight: bold; color: ' + corTotal + '; margin-left: 10px;">' + totalValor + '</span>';
            totalsHtml += '</div>';
            
            var printWindow = window.open('', '_blank', 'width=800,height=600');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${titulo}</title>
                    <meta charset="UTF-8">
                    <style>
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                        @media print {
                            body { margin: 0; padding: 15mm; }
                            .no-print { display: none; }
                            table { page-break-inside: auto; }
                            tr { page-break-inside: avoid; page-break-after: auto; }
                            thead { display: table-header-group; }
                            tfoot { display: table-footer-group; }
                        }
                    </style>
                </head>
                <body style="font-family: Arial, sans-serif; margin: 0; padding: 15mm; color: #333; font-size: 11px;">
                    <h1 style="font-size: 18px; margin-bottom: 5px; color: #000;">${titulo}</h1>
                    <p style="font-size: 11px; color: #666; margin-bottom: 15px; border-bottom: 2px solid #ccc; padding-bottom: 10px;">
                        Período: ${dataInicio} a ${dataFim}
                    </p>
                    <div>
                        ${tableHtml}
                    </div>
                    <div style="margin-top: 20px; padding: 10px; background-color: #f9fafb; border-top: 2px solid #e5e7eb;">
                        ${totalsHtml}
                    </div>
                    <div class="no-print" style="margin-top: 30px; text-align: center;">
                        <button onclick="window.print()" style="padding: 10px 20px; width: 120px; font-size: 14px; cursor: pointer; background: #1f2937; color: white; border: none; border-radius: 9999px; font-weight: bold;">Imprimir</button>
                        <button onclick="window.close()" style="padding: 10px 20px; width: 120px; font-size: 14px; cursor: pointer; margin-left: 10px; background: #e5e7eb; color: #374151; border: none; border-radius: 9999px; font-weight: bold;">Fechar</button>
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = btnOriginalHtml;
            }
        })
        .catch(function(error) {
            console.error('Erro ao carregar dados para impressão:', error);
            alert('Erro ao carregar dados para impressão. Tente novamente.');
            
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = btnOriginalHtml;
            }
        });
    }
    </script>
</x-app-layout>
