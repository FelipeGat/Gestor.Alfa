<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    <style>
        .filters-card, .section-card {
            border: 1px solid #3f9cae !important;
            border-top-width: 4px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            border-radius: 0.5rem !important;
            background: #fff;
        }
        .filter-select:focus,
        .filter-input:focus {
            border-color: #3f9cae !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        .pagination-link {
            border-radius: 9999px !important;
        }
        .card-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .card-title-bar {
            width: 0.5rem;
            height: 1.5rem;
            border-radius: 9999px;
            background: #3f9cae;
        }
        .pager {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        .pager a,
        .pager span {
            min-width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #374151;
            background: #fff;
            padding: 0 0.5rem;
        }
        .pager a:hover {
            border-color: #3f9cae;
            color: #3f9cae;
        }
        .pager .active {
            background: #3f9cae;
            color: #fff;
            border-color: #3f9cae;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Relatórios', 'url' => route('relatorios.index')],
            ['label' => 'Comercial']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @php
                $perPage = 15;

                $pipelineRows = collect($dados['pipeline']['por_status']);
                $pipelineTotalPages = max(1, (int) ceil($pipelineRows->count() / $perPage));
                $pipelinePage = min(max(1, (int) request('pipeline_page', 1)), $pipelineTotalPages);
                $pipelineRowsPage = $pipelineRows->forPage($pipelinePage, $perPage);

                $origemRows = collect($dados['origem_leads']);
                $origemTotalPages = max(1, (int) ceil($origemRows->count() / $perPage));
                $origemPage = min(max(1, (int) request('origem_page', 1)), $origemTotalPages);
                $origemRowsPage = $origemRows->forPage($origemPage, $perPage);

                $ticketRows = collect($dados['ticket_por_tipo']);
                $ticketTotalPages = max(1, (int) ceil($ticketRows->count() / $perPage));
                $ticketPage = min(max(1, (int) request('ticket_page', 1)), $ticketTotalPages);
                $ticketRowsPage = $ticketRows->forPage($ticketPage, $perPage);

                $performanceRows = collect($dados['performance_vendedor']);
                $performanceTotalPages = max(1, (int) ceil($performanceRows->count() / $perPage));
                $performancePage = min(max(1, (int) request('performance_page', 1)), $performanceTotalPages);
                $performanceRowsPage = $performanceRows->forPage($performancePage, $perPage);

                $clientesRows = collect($dados['clientes_ativos_inativos']['receita_anual_por_cliente']);
                $clientesTotalPages = max(1, (int) ceil($clientesRows->count() / $perPage));
                $clientesPage = min(max(1, (int) request('clientes_page', 1)), $clientesTotalPages);
                $clientesRowsPage = $clientesRows->forPage($clientesPage, $perPage);

                $lucratividadeRows = collect($dados['lucratividade_servico']);
                $lucratividadeTotalPages = max(1, (int) ceil($lucratividadeRows->count() / $perPage));
                $lucratividadePage = min(max(1, (int) request('lucratividade_page', 1)), $lucratividadeTotalPages);
                $lucratividadeRowsPage = $lucratividadeRows->forPage($lucratividadePage, $perPage);
            @endphp

            <form method="GET" class="filters-card p-6 grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Início</label>
                    <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] }}" class="filter-input w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Fim</label>
                    <input type="date" name="data_fim" value="{{ $filtros['data_fim'] }}" class="filter-input w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Empresa</label>
                    <select name="empresa_id" class="filter-select w-full rounded border-gray-300">
                        <option value="">Todas</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @selected((string)$filtros['empresa_id'] === (string)$empresa->id)>
                                {{ $empresa->nome_fantasia }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Vendedor</label>
                    <select name="vendedor_id" class="filter-select w-full rounded border-gray-300">
                        <option value="">Todos</option>
                        @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->id }}" @selected((string)$filtros['vendedor_id'] === (string)$vendedor->id)>
                                {{ $vendedor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Tipo</label>
                    <select name="tipo_servico" class="filter-select w-full rounded border-gray-300">
                        <option value="">Todos</option>
                        <option value="PRODUTO" @selected($filtros['tipo_servico'] === 'PRODUTO')>Produto</option>
                        <option value="SERVICO" @selected($filtros['tipo_servico'] === 'SERVICO')>Serviço</option>
                    </select>
                </div>
                <div class="md:col-span-5 flex gap-2 justify-end">
                    <a href="{{ route('relatorios.comercial') }}" class="px-4 py-2 rounded bg-gray-200 text-gray-800">Limpar</a>
                    <button class="px-4 py-2 rounded bg-[#3f9cae] text-white">Aplicar filtros</button>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="section-card p-4">
                    <div class="text-sm text-gray-500">Taxa de Conversão</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($dados['kpis']['taxa_conversao'], 2, ',', '.') }}%</div>
                </div>
                <div class="section-card p-4">
                    <div class="text-sm text-gray-500">Ticket Médio</div>
                    <div class="text-2xl font-bold text-gray-800">R$ {{ number_format($dados['kpis']['ticket_medio'], 2, ',', '.') }}</div>
                </div>
                <div class="section-card p-4">
                    <div class="text-sm text-gray-500">Tempo Médio de Fechamento</div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($dados['kpis']['tempo_medio_fechamento_dias'], 2, ',', '.') }} dias</div>
                </div>
            </div>

            <div class="section-card p-4 overflow-x-auto">
                <div class="card-title">
                    <span class="card-title-bar"></span>
                    <h3 class="font-semibold text-gray-800">Pipeline Comercial</h3>
                </div>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Status</th>
                            <th class="py-2">Qtd</th>
                            <th class="py-2">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pipelineRowsPage as $item)
                            <tr class="border-b">
                                <td class="py-2">{{ $item->status }}</td>
                                <td class="py-2">{{ $item->quantidade }}</td>
                                <td class="py-2">R$ {{ number_format((float)$item->valor_total, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td class="py-3 text-gray-500" colspan="3">Sem dados no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($pipelineRows->count() > $perPage)
                    <div class="pager">
                        @for($i = 1; $i <= $pipelineTotalPages; $i++)
                            @if($i === $pipelinePage)
                                <span class="active">{{ $i }}</span>
                            @else
                                <a href="{{ request()->fullUrlWithQuery(['pipeline_page' => $i]) }}">{{ $i }}</a>
                            @endif
                        @endfor
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="section-card p-4">
                    <div class="card-title">
                        <span class="card-title-bar"></span>
                        <h3 class="font-semibold text-gray-800">Orçamentos Enviados x Fechados</h3>
                    </div>
                    <ul class="text-sm text-gray-700 space-y-2">
                        <li>Enviados: <strong>{{ $dados['enviados_fechados']['quantidade_enviada'] }}</strong></li>
                        <li>Fechados: <strong>{{ $dados['enviados_fechados']['quantidade_fechada'] }}</strong></li>
                        <li>Conversão: <strong>{{ number_format($dados['enviados_fechados']['percentual_conversao'], 2, ',', '.') }}%</strong></li>
                        <li>Valor enviado: <strong>R$ {{ number_format($dados['enviados_fechados']['valor_total_enviado'], 2, ',', '.') }}</strong></li>
                        <li>Valor fechado: <strong>R$ {{ number_format($dados['enviados_fechados']['valor_total_fechado'], 2, ',', '.') }}</strong></li>
                    </ul>
                </div>

                <div class="section-card p-4">
                    <div class="card-title">
                        <span class="card-title-bar"></span>
                        <h3 class="font-semibold text-gray-800">Receita Prevista x Receita Real</h3>
                    </div>
                    <ul class="text-sm text-gray-700 space-y-2">
                        <li>Contratos aprovados: <strong>R$ {{ number_format($dados['receita_prevista_real']['contratos_aprovados'], 2, ',', '.') }}</strong></li>
                        <li>Negociação ponderada: <strong>R$ {{ number_format($dados['receita_prevista_real']['negociacao_ponderada'], 2, ',', '.') }}</strong></li>
                        <li>Receita prevista: <strong>R$ {{ number_format($dados['receita_prevista_real']['receita_prevista'], 2, ',', '.') }}</strong></li>
                        <li>Receita real: <strong>R$ {{ number_format($dados['receita_prevista_real']['receita_real'], 2, ',', '.') }}</strong></li>
                        <li>Diferença: <strong>{{ number_format($dados['receita_prevista_real']['diferenca_percentual'], 2, ',', '.') }}%</strong></li>
                    </ul>
                </div>
            </div>

            <div class="section-card p-4 overflow-x-auto">
                <div class="card-title">
                    <span class="card-title-bar"></span>
                    <h3 class="font-semibold text-gray-800">Follow-up Comercial</h3>
                </div>
                <div class="text-sm text-gray-700 mb-3">
                    +3 dias: <strong>{{ $dados['follow_up']['mais_3_dias'] }}</strong> |
                    +7 dias: <strong>{{ $dados['follow_up']['mais_7_dias'] }}</strong> |
                    +15 dias: <strong>{{ $dados['follow_up']['mais_15_dias'] }}</strong>
                </div>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Orçamento</th>
                            <th class="py-2">Cliente</th>
                            <th class="py-2">Empresa</th>
                            <th class="py-2">Vendedor</th>
                            <th class="py-2">Última atualização</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dados['follow_up']['lista'] as $orcamento)
                            <tr class="border-b">
                                <td class="py-2">{{ $orcamento->numero_orcamento }}</td>
                                <td class="py-2">{{ $orcamento->nome_cliente }}</td>
                                <td class="py-2">{{ $orcamento->empresa?->nome_fantasia ?? '-' }}</td>
                                <td class="py-2">{{ $orcamento->vendedor?->name ?? '-' }}</td>
                                <td class="py-2">{{ optional($orcamento->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td class="py-3 text-gray-500" colspan="5">Sem orçamentos pendentes de follow-up.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">{{ $dados['follow_up']['lista']->links() }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="section-card p-4 overflow-x-auto">
                    <div class="card-title">
                        <span class="card-title-bar"></span>
                        <h3 class="font-semibold text-gray-800">Origem dos Leads</h3>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2">Origem</th>
                                <th class="py-2">Qtd</th>
                                <th class="py-2">Valor</th>
                                <th class="py-2">Conversão</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($origemRowsPage as $origem)
                                <tr class="border-b">
                                    <td class="py-2">{{ $origem->origem }}</td>
                                    <td class="py-2">{{ $origem->quantidade }}</td>
                                    <td class="py-2">R$ {{ number_format((float)$origem->valor_gerado, 2, ',', '.') }}</td>
                                    <td class="py-2">{{ number_format((float)$origem->taxa_conversao, 2, ',', '.') }}%</td>
                                </tr>
                            @empty
                                <tr><td class="py-3 text-gray-500" colspan="4">Sem dados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($origemRows->count() > $perPage)
                        <div class="pager">
                            @for($i = 1; $i <= $origemTotalPages; $i++)
                                @if($i === $origemPage)
                                    <span class="active">{{ $i }}</span>
                                @else
                                    <a href="{{ request()->fullUrlWithQuery(['origem_page' => $i]) }}">{{ $i }}</a>
                                @endif
                            @endfor
                        </div>
                    @endif
                </div>

                <div class="section-card p-4 overflow-x-auto">
                    <div class="card-title">
                        <span class="card-title-bar"></span>
                        <h3 class="font-semibold text-gray-800">Ticket Médio por Tipo de Serviço</h3>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2">Tipo</th>
                                <th class="py-2">Ticket médio</th>
                                <th class="py-2">Qtd vendas</th>
                                <th class="py-2">Receita total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ticketRowsPage as $tipo)
                                <tr class="border-b">
                                    <td class="py-2">{{ $tipo->tipo }}</td>
                                    <td class="py-2">R$ {{ number_format((float)$tipo->ticket_medio, 2, ',', '.') }}</td>
                                    <td class="py-2">{{ $tipo->quantidade_vendas }}</td>
                                    <td class="py-2">R$ {{ number_format((float)$tipo->receita_total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td class="py-3 text-gray-500" colspan="4">Sem dados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($ticketRows->count() > $perPage)
                        <div class="pager">
                            @for($i = 1; $i <= $ticketTotalPages; $i++)
                                @if($i === $ticketPage)
                                    <span class="active">{{ $i }}</span>
                                @else
                                    <a href="{{ request()->fullUrlWithQuery(['ticket_page' => $i]) }}">{{ $i }}</a>
                                @endif
                            @endfor
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="section-card p-4 overflow-x-auto">
                    <div class="card-title">
                        <span class="card-title-bar"></span>
                        <h3 class="font-semibold text-gray-800">Performance por Vendedor</h3>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2">Vendedor</th>
                                <th class="py-2">Valor vendido</th>
                                <th class="py-2">Conversão</th>
                                <th class="py-2">Ticket médio</th>
                                <th class="py-2">Tempo médio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($performanceRowsPage as $item)
                                <tr class="border-b">
                                    <td class="py-2">{{ $item->vendedor }}</td>
                                    <td class="py-2">R$ {{ number_format((float)$item->valor_vendido, 2, ',', '.') }}</td>
                                    <td class="py-2">{{ number_format((float)$item->conversao, 2, ',', '.') }}%</td>
                                    <td class="py-2">R$ {{ number_format((float)$item->ticket_medio, 2, ',', '.') }}</td>
                                    <td class="py-2">{{ number_format((float)$item->tempo_medio_fechamento, 2, ',', '.') }} dias</td>
                                </tr>
                            @empty
                                <tr><td class="py-3 text-gray-500" colspan="5">Sem dados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($performanceRows->count() > $perPage)
                        <div class="pager">
                            @for($i = 1; $i <= $performanceTotalPages; $i++)
                                @if($i === $performancePage)
                                    <span class="active">{{ $i }}</span>
                                @else
                                    <a href="{{ request()->fullUrlWithQuery(['performance_page' => $i]) }}">{{ $i }}</a>
                                @endif
                            @endfor
                        </div>
                    @endif
                </div>

                <div class="section-card p-4 overflow-x-auto">
                    <div class="card-title">
                        <span class="card-title-bar"></span>
                        <h3 class="font-semibold text-gray-800">Clientes Ativos x Inativos</h3>
                    </div>
                    <div class="text-sm text-gray-700 mb-3">
                        Ativos (12 meses): <strong>{{ $dados['clientes_ativos_inativos']['ativos'] }}</strong> |
                        Inativos: <strong>{{ $dados['clientes_ativos_inativos']['inativos'] }}</strong>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2">Cliente</th>
                                <th class="py-2">Receita anual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clientesRowsPage as $cliente)
                                <tr class="border-b">
                                    <td class="py-2">{{ $cliente->cliente }}</td>
                                    <td class="py-2">R$ {{ number_format((float)$cliente->receita_anual, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td class="py-3 text-gray-500" colspan="2">Sem dados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($clientesRows->count() > $perPage)
                        <div class="pager">
                            @for($i = 1; $i <= $clientesTotalPages; $i++)
                                @if($i === $clientesPage)
                                    <span class="active">{{ $i }}</span>
                                @else
                                    <a href="{{ request()->fullUrlWithQuery(['clientes_page' => $i]) }}">{{ $i }}</a>
                                @endif
                            @endfor
                        </div>
                    @endif
                </div>
            </div>

            <div class="section-card p-4 overflow-x-auto">
                <div class="card-title">
                    <span class="card-title-bar"></span>
                    <h3 class="font-semibold text-gray-800">Lucratividade por Serviço</h3>
                </div>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2">Serviço</th>
                            <th class="py-2">Valor vendido</th>
                            <th class="py-2">Custo estimado</th>
                            <th class="py-2">Margem bruta</th>
                            <th class="py-2">Margem %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lucratividadeRowsPage as $item)
                            <tr class="border-b">
                                <td class="py-2">{{ $item->servico }}</td>
                                <td class="py-2">R$ {{ number_format((float)$item->valor_vendido, 2, ',', '.') }}</td>
                                <td class="py-2">R$ {{ number_format((float)$item->custo_estimado, 2, ',', '.') }}</td>
                                <td class="py-2">R$ {{ number_format((float)$item->margem_bruta, 2, ',', '.') }}</td>
                                <td class="py-2">{{ number_format((float)$item->margem_percentual, 2, ',', '.') }}%</td>
                            </tr>
                        @empty
                            <tr><td class="py-3 text-gray-500" colspan="5">Sem dados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($lucratividadeRows->count() > $perPage)
                    <div class="pager">
                        @for($i = 1; $i <= $lucratividadeTotalPages; $i++)
                            @if($i === $lucratividadePage)
                                <span class="active">{{ $i }}</span>
                            @else
                                <a href="{{ request()->fullUrlWithQuery(['lucratividade_page' => $i]) }}">{{ $i }}</a>
                            @endif
                        @endfor
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
