@if(isset($categorias))
    <div id="detalhamento-categoria-modal-html" style="display:none">
        <table class="min-w-full text-xs">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Subcategoria</th>
                    <th>Conta</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
            @foreach($categorias as $cat)
                @foreach($cat->subcategorias ?? [] as $sub)
                    @foreach($sub->contas ?? [] as $conta)
                        <tr>
                            <td>{{ $cat->nome }}</td>
                            <td>{{ $sub->nome }}</td>
                            <td>{{ $conta->nome }}</td>
                            <td>R$ {{ number_format(
                                $custos->filter(fn($c) => $c->conta && $c->conta->id == $conta->id)->sum('valor'),
                                2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
@endif

<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    <style>
        @media print {
            .print\:hidden { display: none !important; }
        }
        .card-zoom { transition: box-shadow 0.2s; }
        .card-zoom:hover { box-shadow: 0 0 0 4px #3b82f633; }
        .modal-card {
            position: fixed; z-index: 50; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;
        }
        .modal-card-content {
            background: #fff; border-radius: 1rem; padding: 2rem; min-width: 350px; min-height: 200px; box-shadow: 0 8px 32px #0002;
            position: relative; max-width: 90vw; max-height: 90vh; overflow-y: auto;
        }
        .modal-card-close {
            position: absolute; top: 1rem; right: 1rem; background: #eee; border: none; border-radius: 50%; width: 2rem; height: 2rem; font-size: 1.2rem; cursor: pointer;
        }
        .modal-extra-info { margin-top: 2rem; background: #f9fafb; border-radius: 0.5rem; padding: 1rem; font-size: 0.95rem; color: #374151; }
    </style>
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
            <a href="{{ route('relatorios.index') }}" class="text-gray-500 hover:text-gray-700 transition">Relatórios</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-800 font-medium">Custos Gerenciais</span>
        </nav>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- FILTROS --}}
            <div class="section-card filters-card mb-6 print:hidden">
                <form method="GET" class="filter-form">
                    <div class="filter-grid mb-4">
                        <div class="filter-group">
                            <label class="filter-label">Cliente</label>
                            <select name="cliente_id" class="filter-select" onchange="this.form.submit()">
                                <option value="">Todos os Clientes</option>
                                @foreach($clientes ?? [] as $cliente)
                                    <option value="{{ $cliente->id }}" {{ (request('cliente_id') == $cliente->id) ? 'selected' : '' }}>{{ $cliente->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Orçamento</label>
                            <select name="orcamento_id" class="filter-select" onchange="this.form.submit()">
                                <option value="">Selecione um Orçamento...</option>
                                @foreach($orcamentos ?? [] as $orc)
                                    <option value="{{ $orc->id }}" {{ (request('orcamento_id') == $orc->id) ? 'selected' : '' }}>
                                        #{{ $orc->id }} - {{ $orc->cliente->nome ?? 'Cliente' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Início</label>
                            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="filter-input" />
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Fim</label>
                            <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="filter-input" />
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
                    </div>
                </form>
            </div>

            @if(isset($mensagem))
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-xl shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-700 font-bold">{{ $mensagem }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- DADOS DO ORÇAMENTO --}}
            @if(isset($orcamento) && $orcamento)
                <div class="section-card mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Cliente</span>
                            <span class="font-bold text-gray-800 truncate block">{{ $orcamento->cliente->nome ?? '-' }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Nº Orçamento</span>
                            <span class="font-bold text-indigo-600">#{{ $orcamento->id }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Status</span>
                            <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-full bg-gray-200 text-gray-700">
                                {{ $orcamento->status ?? '-' }}
                            </span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Total Orçado</span>
                            <span class="font-bold text-emerald-600">R$ {{ number_format($valorOrcado,2,',','.') }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Período</span>
                            <span class="font-bold text-gray-700 text-xs">{{ $inicio->format('d/m/Y') }} - {{ $fim->format('d/m/Y') }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Duração</span>
                            <span class="font-bold text-gray-700">{{ (int) $dias }} {{ $dias == 1 ? 'dia' : 'dias' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- KPIs (Cards de Resumo) --}}
            <div class="flex justify-end mb-4">
                <button onclick="window.print()" class="inline-flex items-center justify-center h-10 w-10 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-full shadow-lg transition-all transform hover:scale-105 active:scale-95 print:hidden" title="Imprimir Relatório">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h14z" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="section-card flex flex-col items-center justify-center p-6 border-l-4 border-l-blue-500 kpi-card" onclick="showKpiFormula('valorOrcado')" style="cursor:pointer">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 text-center">Valor Orçado</span>
                    <span class="text-2xl font-black text-gray-800">R$ {{ number_format($valorOrcado,2,',','.') }}</span>
                </div>
                <div class="section-card flex flex-col items-center justify-center p-6 border-l-4 border-l-red-500 kpi-card" onclick="showKpiFormula('custoTotal')" style="cursor:pointer">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 text-center">Custo Total Real</span>
                    <span class="text-2xl font-black text-red-600">R$ {{ number_format($custoTotal,2,',','.') }}</span>
                </div>
                <div class="section-card flex flex-col items-center justify-center p-6 border-l-4 border-l-emerald-500 kpi-card" onclick="showKpiFormula('receitaRecebida')" style="cursor:pointer">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 text-center">Receita Recebida</span>
                    <span class="text-2xl font-black text-emerald-600">R$ {{ number_format($receitaRecebida,2,',','.') }}</span>
                </div>
                <div class="section-card flex flex-col items-center justify-center p-6 border-l-4 border-l-amber-500 kpi-card" onclick="showKpiFormula('lucro')" style="cursor:pointer">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 text-center">Lucro / Prejuízo</span>
                    <span class="text-2xl font-black text-amber-700">R$ {{ number_format($lucro,2,',','.') }}</span>
                </div>
                <div class="section-card flex flex-col items-center justify-center p-6 border-l-4 border-l-purple-500 kpi-card" onclick="showKpiFormula('margem')" style="cursor:pointer">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 text-center">Margem (%)</span>
                    <span class="text-2xl font-black text-purple-700">{{ number_format($margem,2,',','.') }}%</span>
                </div>
            </div>

            <script>
            function showKpiFormula(tipo) {
                let title = '', formula = '', explicacao = '';
                switch(tipo) {
                    case 'valorOrcado':
                        title = 'Valor Orçado';
                        formula = 'Valor Orçado = Soma dos valores previstos no orçamento';
                        explicacao = 'Este valor representa o total planejado para o serviço, conforme definido no orçamento.';
                        break;
                    case 'custoTotal':
                        title = 'Custo Total Real';
                        formula = 'Custo Total = Soma de todos os custos pagos vinculados ao orçamento';
                        explicacao = 'É a soma de todos os pagamentos realizados para execução do serviço.';
                        break;
                    case 'receitaRecebida':
                        title = 'Receita Recebida';
                        formula = 'Receita Recebida = Soma de todas as cobranças pagas do orçamento';
                        explicacao = 'Total efetivamente recebido do cliente para este orçamento.';
                        break;
                    case 'lucro':
                        title = 'Lucro / Prejuízo';
                        formula = 'Lucro = Receita Recebida - Custo Total';
                        explicacao = 'Diferença entre o que foi recebido e o que foi gasto.';
                        break;
                    case 'margem':
                        title = 'Margem (%)';
                        formula = 'Margem = (Lucro / Valor Orçado) x 100';
                        explicacao = 'Percentual do lucro em relação ao valor orçado.';
                        break;
                }
                let valores = {
                    valorOrcado: 'R$ {{ number_format($valorOrcado,2,',','.') }}',
                    custoTotal: 'R$ {{ number_format($custoTotal,2,',','.') }}',
                    receitaRecebida: 'R$ {{ number_format($receitaRecebida,2,',','.') }}',
                    lucro: 'R$ {{ number_format($lucro,2,',','.') }}',
                    margem: '{{ number_format($margem,2,',','.') }}%'
                };
                let detalhes = '';
                if(tipo === 'lucro') {
                    detalhes = `<b>Receita Recebida:</b> ${valores.receitaRecebida}<br><b>Custo Total:</b> ${valores.custoTotal}`;
                } else if(tipo === 'margem') {
                    detalhes = `<b>Lucro:</b> ${valores.lucro}<br><b>Valor Orçado:</b> ${valores.valorOrcado}`;
                }
                let html = `<div style='font-size:1.1em'><b>${title}</b><br><br><b>Fórmula:</b><br>${formula}<br><br>${explicacao}`;
                if(detalhes) html += `<br><br><b>Valores usados:</b><br>${detalhes}`;
                html += '</div>';
                let modal = document.createElement('div');
                modal.className = 'modal-card';
                let content = document.createElement('div');
                content.className = 'modal-card-content';
                let closeBtn = document.createElement('button');
                closeBtn.className = 'modal-card-close';
                closeBtn.innerHTML = '&times;';
                closeBtn.onclick = () => modal.remove();
                content.appendChild(closeBtn);
                let info = document.createElement('div');
                info.innerHTML = html;
                content.appendChild(info);
                modal.appendChild(content);
                document.body.appendChild(modal);
            }
            </script>

            {{-- Alertas Automáticos --}}
            @if(count($alertas) > 0)
                <div class="mb-6 space-y-2">
                    @foreach($alertas as $alerta)
                        <div class="section-card flex items-center gap-3 border border-gray-100">
                            @if(Str::contains($alerta, '🔴'))
                                <div class="w-2 h-10 bg-red-500 rounded-full"></div>
                                <span class="text-red-700 font-bold text-lg">{{ str_replace('🔴', '', $alerta) }}</span>
                            @elseif(Str::contains($alerta, '⚠️'))
                                <div class="w-2 h-10 bg-amber-500 rounded-full"></div>
                                <span class="text-amber-700 font-bold text-lg">{{ str_replace('⚠️', '', $alerta) }}</span>
                            @else
                                <div class="w-2 h-10 bg-emerald-500 rounded-full"></div>
                                <span class="text-emerald-700 font-bold text-lg">{{ $alerta }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Indicadores Avançados --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="section-card card-zoom cursor-pointer" onclick="openCardModal(this, 'saude')">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-2 h-6 bg-red-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Saúde do Orçamento</h3>
                    </div>
                    <div class="h-[200px]">
                            <x-line-chart :labels="collect($custoAcumuladoLinha)->keys()->toArray()" :datasets="[
                                ['label' => 'Custo Acumulado', 'data' => array_values($custoAcumuladoLinha), 'borderColor' => '#ef4444'],
                                ['label' => 'Custo Máximo', 'data' => array_fill(0, count($custoAcumuladoLinha), $custoMaximo), 'borderColor' => '#3b82f6']
                            ]" />
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 text-center">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Margem mínima: {{ $margemMinima * 100 }}%</span>
                    </div>
                </div>

                <div class="section-card flex flex-col justify-between card-zoom cursor-pointer" onclick="openCardModal(this, 'ieo')">
                    <div>
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                            <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                            <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Eficiência Operacional (IEO)</h3>
                        </div>
                        <div class="flex flex-col items-center py-4">
                            @if(is_null($ieo))
                                <span class="text-base font-bold text-gray-400">IEO não disponível</span>
                            @else
                                <span class="text-5xl font-black {{ $ieoStatus == 'Alerta' ? 'text-red-600' : ($ieoStatus == 'Atenção' ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ number_format($ieo,2,',','.') }}%
                                </span>
                                <span class="mt-2 px-4 py-1 rounded-full font-black text-xs uppercase tracking-widest {{ $ieoStatus == 'Alerta' ? 'bg-red-100 text-red-700' : ($ieoStatus == 'Atenção' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    Status: {{ $ieoStatus }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 text-center italic">Baseado no tempo decorrido vs custo consumido</p>
                </div>

                <div class="section-card flex flex-col justify-between card-zoom cursor-pointer" onclick="openCardModal(this, 'burnrate')">
                    <div>
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                            <div class="w-2 h-6 bg-blue-500 rounded-full"></div>
                            <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Burn Rate do Serviço</h3>
                        </div>
                        <div class="space-y-4 py-2">
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-bold text-gray-400 uppercase">Atual</span>
                                <span class="text-xl font-black text-red-600">R$ {{ number_format($burnRate,2,',','.') }} <small class="text-[10px] text-gray-400">/ dia</small></span>
                            </div>
                            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-red-500 h-full" style="width: {{ min(100, ($burnRate / max(1, $burnRatePlanejado)) * 100) }}%"></div>
                            </div>
                            <div class="flex justify-between items-end pt-2">
                                <span class="text-xs font-bold text-gray-400 uppercase">Planejado</span>
                                @if(is_null($burnRatePlanejado))
                                    <span class="text-xs font-bold text-amber-600">Não disponível <span title="Necessário detalhamento de custos previstos">&#9432;</span></span>
                                @else
                                    <span class="text-xl font-black text-gray-800">R$ {{ number_format($burnRatePlanejado,2,',','.') }} <small class="text-[10px] text-gray-400">/ dia</small></span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráficos Gerenciais --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="section-card card-zoom cursor-pointer" onclick="openCardModal(this, 'evolucao')">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-2 h-6 bg-indigo-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Evolução no Tempo</h3>
                    </div>
                    <div class="h-[250px]">
                        <x-line-chart :labels="json_encode(array_keys($custoAcumuladoLinha))" :datasets="json_encode([
                            ['label' => 'Custo Acumulado', 'data' => array_values($custoAcumuladoLinha), 'borderColor' => '#ef4444']
                        ])" />
                    </div>
                </div>
                <div class="section-card card-zoom cursor-pointer" onclick="openCardModal(this, 'categoria')">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-2 h-6 bg-orange-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Por Categoria</h3>
                    </div>
                    <div class="h-[250px]">
                            <x-pie-chart :labels="$custosPorCategoria->keys()" :data="$custosPorCategoria->values()" />
                    </div>
                </div>
                <div class="section-card card-zoom cursor-pointer" onclick="openCardModal(this, 'orcado')">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                        <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Orçado x Realizado</h3>
                    </div>
                    <div class="h-[250px]">
                            <x-bar-chart :labels="array_keys($orcadoXRealizado)" :data="array_values($orcadoXRealizado)" />
                    </div>
                </div>
            </div>

            {{-- Desvios e Análises --}}
            <div class="section-card mb-6 overflow-hidden card-zoom cursor-pointer" onclick="openCardModal(this, 'desvios')">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                    <div class="w-2 h-6 bg-amber-500 rounded-full"></div>
                    <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Análise de Desvios por Categoria</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Conta</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Planejado</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Real</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Desvio (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($desvios as $d)
                                <tr class="hover:bg-gray-50 {{ $d['alerta'] ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ $d['categoria'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-600">R$ {{ number_format($d['planejado'],2,',','.') }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-600 font-bold">R$ {{ number_format($d['real'],2,',','.') }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-black {{ $d['alerta'] ? 'text-red-600' : 'text-emerald-600' }}">
                                        {{ number_format($d['percentual'],2,',','.') }}%
                                        @if($d['alerta']) <span class="ml-1">⚠️</span> @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ranking de Custos --}}
            <div class="section-card mb-6 overflow-hidden card-zoom cursor-pointer" onclick="openCardModal(this, 'top5')">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                    <div class="w-2 h-6 bg-gray-800 rounded-full"></div>
                    <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Top 5 Maiores Lançamentos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Fornecedor</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Tipo</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($topCustos as $c)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ $c['fornecedor'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $c['tipo'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-black text-red-600">R$ {{ number_format($c['valor'],2,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabela Detalhada --}}
            <div class="section-card overflow-hidden card-zoom cursor-pointer" onclick="openCardModal(this, 'base')">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-6 bg-gray-800 rounded-full"></div>
                        <h3 class="font-bold text-gray-800 uppercase text-sm tracking-wider">Base Operacional de Custos</h3>
                    </div>
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold">
                        {{ $quantidadeLancamentos }} lançamentos
                    </span>
                </div>
                <div class="table-wrapper max-h-[500px]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Data</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Fornecedor</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Categoria</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Descrição</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-500 uppercase tracking-widest">Tipo</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-gray-500 uppercase tracking-widest">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tabela as $linha)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ \Illuminate\Support\Carbon::parse($linha['data'])->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ $linha['fornecedor'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $linha['conta'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $linha['descricao'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700">
                                            {{ $linha['tipo'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-black text-gray-900">R$ {{ number_format($linha['valor'],2,',','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Total Acumulado</td>
                                <td class="px-4 py-4 text-right text-base font-black text-emerald-600">R$ {{ number_format($totalTabela,2,',','.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-zoom { transition: box-shadow 0.2s; }
        .card-zoom:hover { box-shadow: 0 0 0 4px #3b82f633; }
        .modal-card {
            position: fixed; z-index: 50; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;
        }
        .modal-card-content {
            background: #fff; border-radius: 1rem; padding: 2rem; min-width: 350px; min-height: 200px; box-shadow: 0 8px 32px #0002;
            position: relative; max-width: 90vw; max-height: 90vh; overflow-y: auto;
        }
        .modal-card-close {
            position: absolute; top: 1rem; right: 1rem; background: #eee; border: none; border-radius: 50%; width: 2rem; height: 2rem; font-size: 1.2rem; cursor: pointer;
        }
        .modal-extra-info { margin-top: 2rem; background: #f9fafb; border-radius: 0.5rem; padding: 1rem; font-size: 0.95rem; color: #374151; }
    </style>

    <script>
        function openCardModal(card, tipo) {
            document.querySelectorAll('.modal-card').forEach(e => e.remove());
            const modal = document.createElement('div');
            modal.className = 'modal-card';
            const content = document.createElement('div');
            content.className = 'modal-card-content';
            const closeBtn = document.createElement('button');
            closeBtn.className = 'modal-card-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = () => modal.remove();
            content.appendChild(closeBtn);
            const clone = card.cloneNode(true);
            clone.classList.remove('card-zoom');
            clone.style.cursor = 'default';
            clone.removeAttribute('onclick');
            content.appendChild(clone);
            // Informações adicionais por tipo de card
            let extra = document.createElement('div');
            extra.className = 'modal-extra-info';
            if(tipo === 'categoria') {
                const detalhamento = document.getElementById('detalhamento-categoria-modal-html');
                if (detalhamento) {
                    extra.innerHTML = `<b>Detalhamento por Categoria, Subcategoria e Conta:</b><br><br>` + detalhamento.innerHTML;
                } else {
                    extra.innerHTML = `<b>Detalhamento por Categoria, Subcategoria e Conta:</b><br><br><i>Não há dados detalhados disponíveis para este orçamento.</i>`;
                }
            } else if(tipo === 'ieo') {
                extra.innerHTML = `<b>Eficiência Operacional (IEO):</b><br>
                - Mede a eficiência do uso dos recursos ao longo do tempo.<br>
                - Compara o tempo decorrido com o custo consumido.<br>
                - Ajuda a identificar desvios operacionais e oportunidades de melhoria.`;
            } else {
                switch(tipo) {
                    case 'saude':
                        extra.innerHTML = `<b>Análise detalhada:</b><br>
                        - Evolução do custo acumulado ao longo do tempo.<br>
                        - Margem mínima e máxima atingidas.<br>
                        - Alertas de estouro de orçamento.<br>
                        - Histórico de alterações relevantes.`;
                        break;
                    case 'burnrate':
                        extra.innerHTML = `<b>Comparativo Burn Rate:</b><br>
                        - Burn rate atual vs planejado.<br>
                        - Tendência de consumo diário.<br>
                        - Dicas para controle de custos.<br>
                        - Comparação com outros orçamentos.`;
                        break;
                    case 'evolucao':
                        extra.innerHTML = `<b>Evolução no Tempo:</b><br>
                        - Identificação de picos de custo.<br>
                        - Datas críticas e eventos relevantes.<br>
                        - Comentários sobre variações inesperadas.`;
                        break;
                    case 'desvios':
                        extra.innerHTML = `<b>Desvios por Categoria:</b><br>
                        - Ranking de maiores desvios.<br>
                        - Categorias críticas para o orçamento.<br>
                        - Recomendações para ajuste de planejamento.`;
                        break;
                    case 'orcado':
                        extra.innerHTML = `<b>Orçado x Realizado:</b><br>
                        - Percentual de atingimento das metas.<br>
                        - Histórico de revisões do orçamento.<br>
                        - Impacto dos desvios no resultado final.`;
                        break;
                    case 'top5':
                        extra.innerHTML = `<b>Top 5 Maiores Lançamentos:</b><br>
                        - Detalhamento dos fornecedores e tipos.<br>
                        - Datas e justificativas dos maiores custos.<br>
                        - Oportunidades de renegociação ou revisão.`;
                        break;
                    case 'base':
                        extra.innerHTML = `<b>Base Operacional de Custos:</b><br>
                        - Resumo das principais contas e centros de custo.<br>
                        - Oportunidades de otimização.<br>
                        - Análise de recorrências e sazonalidades.`;
                        break;
                    default:
                        extra.innerHTML = '';
                }
            }
            content.appendChild(extra);
            modal.appendChild(content);
            document.body.appendChild(modal);
        }
    </script>
</x-app-layout>
