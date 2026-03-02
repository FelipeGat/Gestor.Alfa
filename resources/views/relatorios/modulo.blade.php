<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Relatórios', 'url' => route('relatorios.index')],
            ['label' => 'Módulo de Relatórios']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Módulo de Relatórios</h2>

                @php
                    $hoje = \Carbon\Carbon::today();
                    $ontem = \Carbon\Carbon::yesterday();
                    $amanha = \Carbon\Carbon::tomorrow();
                    $dataAtual = $filtros['data_inicio'] ? \Carbon\Carbon::parse($filtros['data_inicio']) : \Carbon\Carbon::now();
                    $mesAnterior = $dataAtual->copy()->subMonth();
                    $proximoMes = $dataAtual->copy()->addMonth();
                    $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                    $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                    $vencimentoInicio = $filtros['data_inicio'];
                    $vencimentoFim = $filtros['data_fim'];
                @endphp

                <form id="filtros-relatorios" method="GET" action="{{ route('relatorios.modulo') }}" class="space-y-4">
                    <input type="hidden" name="tipo" value="{{ $filtros['tipo'] }}">

                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Empresa</label>
                            <select name="empresa_id" onchange="this.form.submit()" class="w-full border rounded-lg px-3 py-2">
                                <option value="">Todas as empresas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" @selected((int)($filtros['empresa_id'] ?? 0) === (int)$empresa->id)>
                                        {{ $empresa->nome_fantasia ?: $empresa->razao_social }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Centro de custo</label>
                            <select name="centro_custo_id" onchange="this.form.submit()" class="w-full border rounded-lg px-3 py-2">
                                <option value="">Todos</option>
                                @foreach($centrosCusto as $centro)
                                    <option value="{{ $centro->id }}" @selected((int)($filtros['centro_custo_id'] ?? 0) === (int)$centro->id)>
                                        {{ $centro->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Navegação</label>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('relatorios.modulo', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim', 'data_inicio', 'data_fim']), ['vencimento_inicio' => $ontem->format('Y-m-d'), 'vencimento_fim' => $ontem->format('Y-m-d'), 'tipo' => $filtros['tipo']])) }}"
                                    class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px] {{ $vencimentoInicio == $ontem->format('Y-m-d') && $vencimentoFim == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                    Ontem
                                </a>
                                <a href="{{ route('relatorios.modulo', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim', 'data_inicio', 'data_fim']), ['vencimento_inicio' => $hoje->format('Y-m-d'), 'vencimento_fim' => $hoje->format('Y-m-d'), 'tipo' => $filtros['tipo']])) }}"
                                    class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px] {{ $vencimentoInicio == $hoje->format('Y-m-d') && $vencimentoFim == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                    Hoje
                                </a>
                                <a href="{{ route('relatorios.modulo', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim', 'data_inicio', 'data_fim']), ['vencimento_inicio' => $amanha->format('Y-m-d'), 'vencimento_fim' => $amanha->format('Y-m-d'), 'tipo' => $filtros['tipo']])) }}"
                                    class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px] {{ $vencimentoInicio == $amanha->format('Y-m-d') && $vencimentoFim == $amanha->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                    Amanhã
                                </a>
                                <a href="{{ route('relatorios.modulo', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim', 'data_inicio', 'data_fim']), ['vencimento_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'), 'vencimento_fim' => $mesAnterior->endOfMonth()->format('Y-m-d'), 'tipo' => $filtros['tipo']])) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <div class="flex-1 text-center font-bold text-gray-700 bg-white px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                                    {{ $mesAtualNome }}
                                </div>
                                <a href="{{ route('relatorios.modulo', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim', 'data_inicio', 'data_fim']), ['vencimento_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'), 'vencimento_fim' => $proximoMes->endOfMonth()->format('Y-m-d'), 'tipo' => $filtros['tipo']])) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <details class="mt-2" open>
                        <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                            Período Personalizado
                        </summary>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Data Inicial</label>
                                <input type="date" name="vencimento_inicio" value="{{ $vencimentoInicio }}" onchange="this.form.submit()" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Data Final</label>
                                <input type="date" name="vencimento_fim" value="{{ $vencimentoFim }}" onchange="this.form.submit()" class="w-full border rounded-lg px-3 py-2">
                            </div>
                        </div>
                    </details>

                    <div class="flex gap-2">
                        <a href="{{ route('relatorios.modulo', ['tipo' => $filtros['tipo']]) }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700">Limpar filtros</a>
                        <a href="{{ route('relatorios.modulo.imprimir', [
                            'empresa_id' => $filtros['empresa_id'],
                            'data_inicio' => $filtros['data_inicio'],
                            'data_fim' => $filtros['data_fim'],
                            'centro_custo_id' => $filtros['centro_custo_id'],
                            'tipo' => $filtros['tipo'],
                        ]) }}"
                           target="_blank"
                           class="px-4 py-2 rounded-lg border border-[#3f9cae] text-[#3f9cae]">
                            Imprimir
                        </a>
                    </div>
                </form>
            </div>

            @if($dados !== null)
                @php
                    $moeda = fn($valor) => 'R$ ' . number_format((float) $valor, 2, ',', '.');
                    $numero = fn($valor, $casas = 2) => number_format((float) $valor, $casas, ',', '.');
                @endphp
                <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);">
                    <h3 class="text-base font-bold text-gray-800 mb-3">Resultado: {{ strtoupper($filtros['tipo']) }}</h3>

                    @switch($filtros['tipo'])
                        @case('financeiro')
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Receita</div><div class="text-lg font-bold">{{ $moeda($dados['receita_total'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Despesa</div><div class="text-lg font-bold">{{ $moeda($dados['despesa_total'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Lucro líquido</div><div class="text-lg font-bold">{{ $moeda($dados['lucro_liquido'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Margem</div><div class="text-lg font-bold">{{ $numero($dados['margem_percentual'] ?? 0) }}%</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Saldo bancário</div><div class="text-lg font-bold">{{ $moeda($dados['saldo_bancario_total'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Contas em aberto (líquido)</div><div class="text-lg font-bold">{{ $moeda(($dados['contas_receber_em_aberto'] ?? 0) - ($dados['contas_pagar_em_aberto'] ?? 0)) }}</div></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-semibold text-sm mb-2">Receita por centro de custo</h4>
                                    <div class="overflow-auto border rounded-lg">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Centro</th><th class="px-3 py-2 text-right">Total</th></tr></thead>
                                            <tbody>
                                                @forelse(($dados['receita_por_centro_custo'] ?? []) as $item)
                                                    <tr class="border-t"><td class="px-3 py-2">{{ $item->centro_custo_nome ?? '-' }}</td><td class="px-3 py-2 text-right">{{ $moeda($item->total ?? 0) }}</td></tr>
                                                @empty
                                                    <tr class="border-t"><td colspan="2" class="px-3 py-2 text-gray-500">Sem dados no período.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm mb-2">Despesa por centro de custo</h4>
                                    <div class="overflow-auto border rounded-lg">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Centro</th><th class="px-3 py-2 text-right">Total</th></tr></thead>
                                            <tbody>
                                                @forelse(($dados['despesa_por_centro_custo'] ?? []) as $item)
                                                    <tr class="border-t"><td class="px-3 py-2">{{ $item->centro_custo_nome ?? '-' }}</td><td class="px-3 py-2 text-right">{{ $moeda($item->total ?? 0) }}</td></tr>
                                                @empty
                                                    <tr class="border-t"><td colspan="2" class="px-3 py-2 text-gray-500">Sem dados no período.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @break

                        @case('comercial')
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Orçamentos</div><div class="text-lg font-bold">{{ $numero($dados['total_orcamentos_criados'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Fechados</div><div class="text-lg font-bold">{{ $numero($dados['fechados'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Perdidos</div><div class="text-lg font-bold">{{ $numero($dados['perdidos'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Em aberto</div><div class="text-lg font-bold">{{ $numero($dados['em_aberto'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Taxa de conversão</div><div class="text-lg font-bold">{{ $numero($dados['taxa_conversao'] ?? 0) }}%</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Receita fechada</div><div class="text-lg font-bold">{{ $moeda($dados['receita_fechada'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Ticket médio</div><div class="text-lg font-bold">{{ $moeda($dados['ticket_medio'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Tempo médio fechamento</div><div class="text-lg font-bold">{{ $numero($dados['tempo_medio_ate_fechamento_dias'] ?? 0) }} dias</div></div>
                            </div>

                            <h4 class="font-semibold text-sm mb-2">Performance por vendedor</h4>
                            <div class="overflow-auto border rounded-lg">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Vendedor</th><th class="px-3 py-2 text-right">Orçamentos</th><th class="px-3 py-2 text-right">Fechados</th><th class="px-3 py-2 text-right">Conversão</th><th class="px-3 py-2 text-right">Receita</th></tr></thead>
                                    <tbody>
                                        @forelse(($dados['performance_vendedores'] ?? []) as $item)
                                            <tr class="border-t">
                                                <td class="px-3 py-2">{{ $item->vendedor_nome ?? '-' }}</td>
                                                <td class="px-3 py-2 text-right">{{ $numero($item->total_orcamentos ?? 0, 0) }}</td>
                                                <td class="px-3 py-2 text-right">{{ $numero($item->fechados ?? 0, 0) }}</td>
                                                <td class="px-3 py-2 text-right">{{ $numero($item->taxa_conversao ?? 0) }}%</td>
                                                <td class="px-3 py-2 text-right">{{ $moeda($item->receita_fechada ?? 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr class="border-t"><td colspan="5" class="px-3 py-2 text-gray-500">Sem dados no período.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @break

                        @case('tecnico')
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Chamados</div><div class="text-lg font-bold">{{ $numero($dados['total_chamados'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Finalizados</div><div class="text-lg font-bold">{{ $numero($dados['finalizados'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Abertos</div><div class="text-lg font-bold">{{ $numero($dados['abertos'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Cancelados</div><div class="text-lg font-bold">{{ $numero($dados['cancelados'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Tempo médio</div><div class="text-lg font-bold">{{ $numero($dados['tempo_medio_atendimento_minutos'] ?? 0) }} min</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Chamados vencidos</div><div class="text-lg font-bold">{{ $numero($dados['chamados_vencidos'] ?? 0, 0) }}</div></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-semibold text-sm mb-2">Quantidade por técnico</h4>
                                    <div class="overflow-auto border rounded-lg">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Técnico</th><th class="px-3 py-2 text-right">Quantidade</th></tr></thead>
                                            <tbody>
                                                @forelse(($dados['quantidade_por_tecnico'] ?? []) as $item)
                                                    <tr class="border-t"><td class="px-3 py-2">{{ $item->tecnico_nome ?? '-' }}</td><td class="px-3 py-2 text-right">{{ $numero($item->quantidade ?? 0, 0) }}</td></tr>
                                                @empty
                                                    <tr class="border-t"><td colspan="2" class="px-3 py-2 text-gray-500">Sem dados no período.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-sm mb-2">Receita por técnico</h4>
                                    <div class="overflow-auto border rounded-lg">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Técnico</th><th class="px-3 py-2 text-right">Receita</th></tr></thead>
                                            <tbody>
                                                @forelse(($dados['receita_por_tecnico'] ?? []) as $item)
                                                    <tr class="border-t"><td class="px-3 py-2">{{ $item->tecnico_nome ?? '-' }}</td><td class="px-3 py-2 text-right">{{ $moeda($item->receita ?? 0) }}</td></tr>
                                                @empty
                                                    <tr class="border-t"><td colspan="2" class="px-3 py-2 text-gray-500">Sem dados no período.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @break

                        @case('rh')
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Atrasos</div><div class="text-lg font-bold">{{ $numero($dados['total_atrasos'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Faltas</div><div class="text-lg font-bold">{{ $numero($dados['total_faltas'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Atestados</div><div class="text-lg font-bold">{{ $numero($dados['total_atestados'] ?? 0, 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Horas extras</div><div class="text-lg font-bold">{{ $numero($dados['horas_extras'] ?? 0) }} h</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Saldo banco de horas</div><div class="text-lg font-bold">{{ $numero($dados['saldo_banco_horas_consolidado'] ?? 0) }} h</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Absenteísmo</div><div class="text-lg font-bold">{{ $numero($dados['indice_absenteismo'] ?? 0) }}%</div></div>
                            </div>

                            <h4 class="font-semibold text-sm mb-2">Ranking por colaborador</h4>
                            <div class="overflow-auto border rounded-lg">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Colaborador</th><th class="px-3 py-2 text-right">Atrasos</th><th class="px-3 py-2 text-right">Faltas</th><th class="px-3 py-2 text-right">Atestados</th><th class="px-3 py-2 text-right">Horas extras</th><th class="px-3 py-2 text-right">Banco de horas</th></tr></thead>
                                    <tbody>
                                        @forelse(($dados['ranking_por_colaborador'] ?? []) as $item)
                                            <tr class="border-t">
                                                <td class="px-3 py-2">{{ $item['colaborador_nome'] ?? '-' }}</td>
                                                <td class="px-3 py-2 text-right">{{ $numero($item['atrasos'] ?? 0, 0) }}</td>
                                                <td class="px-3 py-2 text-right">{{ $numero($item['faltas'] ?? 0, 0) }}</td>
                                                <td class="px-3 py-2 text-right">{{ $numero($item['atestados'] ?? 0, 0) }}</td>
                                                <td class="px-3 py-2 text-right">{{ $numero($item['horas_extras'] ?? 0) }} h</td>
                                                <td class="px-3 py-2 text-right">{{ $numero($item['saldo_banco_horas'] ?? 0) }} h</td>
                                            </tr>
                                        @empty
                                            <tr class="border-t"><td colspan="6" class="px-3 py-2 text-gray-500">Sem dados no período.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @break

                        @default
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Receita total</div><div class="text-lg font-bold">{{ $moeda($dados['receita_total'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Despesa total</div><div class="text-lg font-bold">{{ $moeda($dados['despesa_total'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Lucro</div><div class="text-lg font-bold">{{ $moeda($dados['lucro'] ?? 0) }}</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Crescimento vs mês anterior</div><div class="text-lg font-bold">{{ $numero($dados['crescimento_vs_mes_anterior'] ?? 0) }}%</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Conversão comercial</div><div class="text-lg font-bold">{{ $numero($dados['conversao_comercial'] ?? 0) }}%</div></div>
                                <div class="bg-gray-50 rounded-lg p-3"><div class="text-xs text-gray-500">Absenteísmo</div><div class="text-lg font-bold">{{ $numero($dados['indice_absenteismo'] ?? 0) }}%</div></div>
                            </div>

                            <h4 class="font-semibold text-sm mb-2">Receita por técnico</h4>
                            <div class="overflow-auto border rounded-lg mb-4">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Técnico</th><th class="px-3 py-2 text-right">Receita</th></tr></thead>
                                    <tbody>
                                        @forelse(($dados['receita_por_tecnico'] ?? []) as $item)
                                            <tr class="border-t"><td class="px-3 py-2">{{ $item->tecnico_nome ?? '-' }}</td><td class="px-3 py-2 text-right">{{ $moeda($item->receita ?? 0) }}</td></tr>
                                        @empty
                                            <tr class="border-t"><td colspan="2" class="px-3 py-2 text-gray-500">Sem dados no período.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                    @endswitch

                    <h4 class="font-semibold text-sm mt-4 mb-2">Insights automáticos</h4>
                    <ul class="list-disc pl-5 text-sm text-gray-700 space-y-1">
                        @forelse(($dados['insights_automaticos'] ?? []) as $insight)
                            <li>{{ $insight }}</li>
                        @empty
                            <li>Sem insights para o período.</li>
                        @endforelse
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
