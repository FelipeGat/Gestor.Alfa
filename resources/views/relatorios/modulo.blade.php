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

                <form method="GET" action="{{ route('relatorios.modulo') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Empresa</label>
                        <select name="empresa_id" class="w-full border rounded-lg px-3 py-2" required>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected((int)$filtros['empresa_id'] === (int)$empresa->id)>
                                    {{ $empresa->nome_fantasia ?: $empresa->razao_social }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Data início</label>
                        <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] }}" class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Data fim</label>
                        <input type="date" name="data_fim" value="{{ $filtros['data_fim'] }}" class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Centro de custo</label>
                        <select name="centro_custo_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Todos</option>
                            @foreach($centrosCusto as $centro)
                                <option value="{{ $centro->id }}" @selected((int)($filtros['centro_custo_id'] ?? 0) === (int)$centro->id)>
                                    {{ $centro->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Relatório</label>
                        <select name="tipo" class="w-full border rounded-lg px-3 py-2">
                            <option value="financeiro" @selected($filtros['tipo'] === 'financeiro')>Financeiro</option>
                            <option value="tecnico" @selected($filtros['tipo'] === 'tecnico')>Técnico</option>
                            <option value="comercial" @selected($filtros['tipo'] === 'comercial')>Comercial</option>
                            <option value="rh" @selected($filtros['tipo'] === 'rh')>RH</option>
                            <option value="painel-executivo" @selected($filtros['tipo'] === 'painel-executivo')>Painel Executivo</option>
                        </select>
                    </div>

                    <div class="md:col-span-6 flex gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg text-white" style="background-color:#3f9cae;">Gerar relatório</button>
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
