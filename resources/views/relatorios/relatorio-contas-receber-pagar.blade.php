<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <style>
        @media print {
            /* Ocultar elementos que não devem aparecer na impressão da página principal */
            .print\:hidden, .filters-card, .pagination-container, 
            header, footer, nav, .btn, a[href] {
                display: none !important;
            }
        }
    </style>

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v2a3 3 0 006 0v-2c0-1.657-1.343-3-3-3zm0 0V6a2 2 0 10-4 0" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 20h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Relatorio - Contas a Receber e a Pagar
                </h2>
            </div>

            <a href="{{ route('relatorios.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-emerald-600 transition-all shadow-sm group print:hidden"
                title="Voltar para Relatorios">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
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
                            <a href="{{ route('relatorios.contas-receber-pagar', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $ontem->format('Y-m-d'), 'data_fim' => $ontem->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('data_inicio') == $ontem->format('Y-m-d') && request('data_fim') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}"
                                >
                                Ontem
                            </a>
                            <a href="{{ route('relatorios.contas-receber-pagar', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $hoje->format('Y-m-d'), 'data_fim' => $hoje->format('Y-m-d')])) }}"
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

                            <a href="{{ route('relatorios.contas-receber-pagar', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'), 'data_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>

                            <div class="flex-1 text-center font-bold text-gray-700 bg-white px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                                {{ $mesAtualNome }}
                            </div>

                            <a href="{{ route('relatorios.contas-receber-pagar', array_merge(request()->except(['data_inicio', 'data_fim']), ['data_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'), 'data_fim' => $proximoMes->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            </div>
                            <details class="mt-2" id="periodoPersonalizadoDetails">
                                <summary id="periodoPersonalizadoSummary" class="cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                                    🗓️ Período Personalizado
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
                            <label class="filter-label">Cliente</label>
                            <select name="cliente_id" class="filter-select">
                                <option value="">Todos os Clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nome_fantasia ?? $cliente->razao_social ?? $cliente->nome }}
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
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtrar Relatorio
                        </button>
                    </div>
                </form>
            </div>

            <div id="section-contas-receber" class="section-card mb-6 print-section-receber">
                <div class="print-title-receber mb-6 pb-4 border-b-2 border-gray-300">
                    <h1 class="text-2xl font-bold text-gray-800">Relatório - Contas a Receber</h1>
                    <p class="text-sm text-gray-600 mt-2">Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</p>
                </div>
                <div class="flex justify-between items-center pt-2 pr-4 mb-4 pb-3 border-b border-gray-100 print:hidden">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Contas a Receber</h3>
                    </div>
                    <button type="button" onclick="imprimirSecao(event, 'receber')" class="ml-2 px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-semibold print:hidden" title="Imprimir">
                        🖨️
                    </button>
                </div>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-left">Empresa</th>
                                <th class="text-left">Cliente</th>
                                <th class="text-left">Vencimento</th>
                                <th class="text-right">Valor</th>
                                <th class="text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contasReceber as $conta)
                                <tr>
                                    <td class="text-left">
                                        {{ $conta->empresaRelacionada?->nome_fantasia ?? $conta->empresaRelacionada?->razao_social ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->cliente?->nome_fantasia ?? $conta->cliente?->razao_social ?? $conta->cliente?->nome ?? '-' }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->data_vencimento?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td class="text-right font-black text-emerald-700">
                                        R$ {{ number_format($conta->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->status ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-sm text-gray-500 py-6">
                                        Nenhuma conta a receber encontrada com os filtros selecionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Total a Receber</td>
                                <td class="px-4 py-4 text-right text-base font-black text-emerald-700">
                                    R$ {{ number_format($totalReceber, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="pagination-container p-4">
                    {{ $contasReceber->links() }}
                </div>
            </div>

            <div id="section-contas-pagar" class="section-card print-section-pagar">
                <div class="print-title-pagar mb-6 pb-4 border-b-2 border-gray-300">
                    <h1 class="text-2xl font-bold text-gray-800">Relatório - Contas a Pagar</h1>
                    <p class="text-sm text-gray-600 mt-2">Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</p>
                </div>
                <div class="flex justify-between items-center pt-2 pr-4 mb-4 pb-3 border-b border-gray-100 print:hidden">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-6 bg-red-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Contas a Pagar</h3>
                    </div>
                    <button type="button" onclick="imprimirSecao(event, 'pagar')" class="ml-2 px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-semibold print:hidden" title="Imprimir">
                        🖨️
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
                                <th class="text-right">Valor</th>
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
                                    <td class="text-right font-black text-red-600">
                                        R$ {{ number_format($conta->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="text-left">
                                        {{ $conta->status ?? '-' }}
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
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Total a Pagar</td>
                                <td class="px-4 py-4 text-right text-base font-black text-red-600">
                                    R$ {{ number_format($totalPagar, 2, ',', '.') }}
                                </td>
                                <td></td>
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
    function imprimirSecao(event, tipo) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Pegar o conteúdo da seção desejada
        var secaoId = tipo === 'receber' ? 'section-contas-receber' : 'section-contas-pagar';
        var secao = document.getElementById(secaoId);
        var titulo = tipo === 'receber' ? 'Relatório - Contas a Receber' : 'Relatório - Contas a Pagar';
        var dataInicio = '{{ \Carbon\Carbon::parse($dataInicio)->format("d/m/Y") }}';
        var dataFim = '{{ \Carbon\Carbon::parse($dataFim)->format("d/m/Y") }}';
        
        // Criar uma nova janela para impressão
        var printWindow = window.open('', '_blank', 'width=800,height=600');
        
        // Clonar a tabela para não afetar a original
        var tabelaOriginal = secao.querySelector('table');
        var tabelaClone = tabelaOriginal.cloneNode(true);
        
        // Remover o tfoot (total mal formatado e resultado)
        var tfoot = tabelaClone.querySelector('tfoot');
        if (tfoot) {
            tfoot.remove();
        }
        
        // Remover classes Tailwind que podem causar problemas na impressão
        tabelaClone.className = '';
        tabelaClone.style.width = '100%';
        tabelaClone.style.borderCollapse = 'collapse';
        
        // Aplicar estilos inline nas células
        var ths = tabelaClone.querySelectorAll('th');
        ths.forEach(function(th) {
            th.style.backgroundColor = '#f5f5f5';
            th.style.padding = '6px 8px';
            th.style.textAlign = 'left';
            th.style.borderBottom = '1px solid #ddd';
            th.style.fontWeight = 'bold';
            th.style.fontSize = '11px';
        });
        
        var tds = tabelaClone.querySelectorAll('td');
        tds.forEach(function(td) {
            td.style.padding = '5px 8px';
            td.style.borderBottom = '1px solid #eee';
            td.style.fontSize = '11px';
        });
        
        // Pegar o valor total e definir cor
        var totalValor = tipo === 'receber' ? '{{ number_format($totalReceber, 2, ",", ".") }}' : '{{ number_format($totalPagar, 2, ",", ".") }}';
        var totalLabel = tipo === 'receber' ? 'Total a Receber' : 'Total a Pagar';
        var corTotal = tipo === 'receber' ? '#059669' : '#dc2626';
        
        // Escrever o HTML da nova janela
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
                    ${tabelaClone.outerHTML}
                </div>
                <div style="margin-top: 20px; padding: 10px; background-color: #f9fafb; border-top: 2px solid #e5e7eb; text-align: right;">
                    <span style="font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">${totalLabel}</span>
                    <span style="font-size: 13px; font-weight: bold; color: ${corTotal}; margin-left: 10px;">R$&nbsp;${totalValor}</span>
                </div>
                <div class="no-print" style="margin-top: 30px; text-align: center;">
                    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Imprimir</button>
                    <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; margin-left: 10px;">Fechar</button>
                </div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.focus();
    }
    </script>
</x-app-layout>
