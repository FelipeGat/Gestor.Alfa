<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'RH', 'url' => route('rh.dashboard')],
            ['label' => 'Ponto & Jornada']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                @php
                    $dataAtualFiltro = !empty($filtros['inicio'])
                        ? \Carbon\Carbon::parse($filtros['inicio'])
                        : now();
                    $mesAnterior = $dataAtualFiltro->copy()->subMonth();
                    $proximoMes = $dataAtualFiltro->copy()->addMonth();
                    $mesesNomes = [
                        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
                    ];
                    $mesAtualNome = ($mesesNomes[$dataAtualFiltro->month] ?? $dataAtualFiltro->format('m')) . '/' . $dataAtualFiltro->year;
                    $periodoLabel = \Carbon\Carbon::parse($filtros['inicio'])->format('d/m/Y') . ' até ' . \Carbon\Carbon::parse($filtros['fim'])->format('d/m/Y');
                @endphp

                <form method="GET" action="{{ route('rh.ponto-jornada.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Navegação Rápida</label>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('rh.ponto-jornada.index', array_filter([
                                    'inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'),
                                    'fim' => $mesAnterior->endOfMonth()->format('Y-m-d'),
                                    'funcionario_id' => $filtros['funcionario_id'] ?? null,
                                ])) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                                    title="Mês anterior">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>

                                <div class="flex-1 text-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm">
                                    {{ $mesAtualNome }}
                                </div>

                                <a href="{{ route('rh.ponto-jornada.index', array_filter([
                                    'inicio' => $proximoMes->startOfMonth()->format('Y-m-d'),
                                    'fim' => $proximoMes->endOfMonth()->format('Y-m-d'),
                                    'funcionario_id' => $filtros['funcionario_id'] ?? null,
                                ])) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                                    title="Próximo mês">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Funcionário</label>
                            <div class="flex items-center gap-2">
                                <select name="funcionario_id" class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm">
                                    <option value="">Todos</option>
                                    @foreach($funcionarios as $funcionario)
                                        <option value="{{ $funcionario->id }}" @selected((string) ($filtros['funcionario_id'] ?? '') === (string) $funcionario->id)>
                                            {{ $funcionario->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-button type="submit" variant="primary" size="sm">Filtrar</x-button>
                                <x-button href="{{ route('rh.ponto-jornada.index') }}" variant="secondary" size="sm">Limpar</x-button>
                            </div>
                        </div>

                        <div class="text-sm text-gray-600 md:text-right">
                            Período: <span class="font-semibold">{{ $periodoLabel }}</span>
                        </div>
                    </div>

                    <div x-data="{ mostrarPeriodo: false }">
                        <button type="button" @click="mostrarPeriodo = !mostrarPeriodo" class="text-sm text-[#3f9cae] hover:text-[#2d7a8a] font-medium flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="mostrarPeriodo ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span x-text="mostrarPeriodo ? 'Ocultar período personalizado' : 'Escolher período personalizado'"></span>
                        </button>

                        <div x-show="mostrarPeriodo" x-transition class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data início</label>
                                <input type="date" name="inicio" value="{{ $filtros['inicio'] }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data fim</label>
                                <input type="date" name="fim" value="{{ $filtros['fim'] }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div class="flex sm:items-end gap-2">
                                <x-button type="submit" variant="primary" size="sm">Filtrar</x-button>
                                <x-button href="{{ route('rh.ponto-jornada.index') }}" variant="secondary" size="sm">Limpar</x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">SEÇÃO 1 – Jornada Legal (Registro de Ponto do Portal)</h2>
                @php
                    $columnsLegal = [
                        ['label' => 'Funcionário'],
                        ['label' => 'Data'],
                        ['label' => 'Dia'],
                        ['label' => 'Entrada'],
                        ['label' => 'Início Intervalo'],
                        ['label' => 'Fim Intervalo'],
                        ['label' => 'Saída'],
                        ['label' => 'Total'],
                        ['label' => 'Extra 50%'],
                        ['label' => 'Extra 100%'],
                        ['label' => 'Atraso'],
                    ];
                @endphp

                <x-table :columns="$columnsLegal" :data="$jornadaLegal['rows']" emptyMessage="Sem registros de jornada legal no período.">
                    @foreach($jornadaLegal['rows'] as $linha)
                        @php
                            $estiloLinha = '';
                            $ehFalta = ($linha['status'] ?? '') === 'Falta';
                            $tipoCelula = $ehFalta ? 'danger' : 'default';

                            if (!empty($linha['eh_feriado'])) {
                                $estiloLinha = 'background-color: #fff7ed;';
                            } elseif (!empty($linha['eh_domingo'])) {
                                $estiloLinha = 'background-color: #fef2f2;';
                            }
                        @endphp
                        <tr @if($estiloLinha) style="{{ $estiloLinha }}" @endif>
                            <x-table-cell :type="$tipoCelula">{{ $linha['funcionario'] }}</x-table-cell>
                            <x-table-cell :type="$tipoCelula">
                                <div class="flex items-center gap-2">
                                    <span>{{ $linha['data'] }}</span>
                                </div>
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">
                                <div class="flex items-center gap-2">
                                    <span>{{ $linha['dia'] ?? '—' }}</span>
                                    @if(!empty($linha['eh_feriado']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background-color:#f59e0b; color:white;">Feriado</span>
                                    @endif
                                </div>
                                @if(!empty($linha['feriado_nome']))
                                    <div class="text-xs text-amber-800 mt-1">{{ $linha['feriado_nome'] }}</div>
                                @endif
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">{{ $linha['entrada'] }}</x-table-cell>
                            <x-table-cell :type="$tipoCelula">{{ $linha['intervalo_inicio'] }}</x-table-cell>
                            <x-table-cell :type="$tipoCelula">{{ $linha['intervalo_fim'] }}</x-table-cell>
                            <x-table-cell :type="$tipoCelula">{{ $linha['saida'] }}</x-table-cell>
                            @php
                                $saldoSegundos = (int) ($linha['saldo_segundos'] ?? 0);
                                $toleranciaSegundos = (int) ($linha['tolerancia_segundos'] ?? 0);
                                $destacarAtrasoTotal = $saldoSegundos < 0 && abs($saldoSegundos) > $toleranciaSegundos;
                                $destacarAcrescimoTotal = $saldoSegundos > 0 && $saldoSegundos > $toleranciaSegundos;
                            @endphp
                            <x-table-cell :type="$tipoCelula">
                                <span class="{{ $destacarAtrasoTotal ? 'text-red-700 font-semibold' : ($destacarAcrescimoTotal ? 'text-green-700 font-semibold' : '') }}">{{ $linha['total'] }}</span>
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">{{ $linha['extra_50'] ?? '—' }}</x-table-cell>
                            <x-table-cell :type="$tipoCelula">{{ $linha['extra_100'] ?? '—' }}</x-table-cell>
                            @php
                                $saldoSegundos = (int) ($linha['saldo_segundos'] ?? 0);
                                $toleranciaSegundos = (int) ($linha['tolerancia_segundos'] ?? 0);
                                $destacarAtrasoColuna = $saldoSegundos < 0 && abs($saldoSegundos) > $toleranciaSegundos;
                            @endphp
                            <x-table-cell :type="$tipoCelula">
                                <span class="text-xs font-semibold {{ $destacarAtrasoColuna ? 'text-red-700' : 'text-gray-700' }}">{{ $linha['atraso'] ?? '—' }}</span>
                            </x-table-cell>
                        </tr>
                    @endforeach
                </x-table>

                @php
                    $totaisSecao1 = $jornadaLegal['totais_secao_1'] ?? [];
                    $faltasQtd = (int) ($totaisSecao1['faltas_qtd'] ?? 0);
                    $atrasosQtd = (int) ($totaisSecao1['atrasos_qtd'] ?? 0);
                    $extras50Segundos = (int) ($totaisSecao1['extras_50_segundos'] ?? 0);
                    $extras100Segundos = (int) ($totaisSecao1['extras_100_segundos'] ?? 0);
                    $atrasosSegundos = (int) ($totaisSecao1['atrasos_segundos'] ?? 0);
                @endphp

                <div class="mt-4 border-t border-gray-200 pt-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Totalizador da Seção 1</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Quantidade de Faltas</p>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ $faltasQtd }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Quantidade de Atrasos</p>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ $atrasosQtd }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Total Horas Extras 50%</p>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ gmdate('H:i', max(0, $extras50Segundos)) }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Total Horas Extras 100%</p>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ gmdate('H:i', max(0, $extras100Segundos)) }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Total de Atrasos</p>
                            <p class="text-xl font-bold text-red-700 mt-1">{{ gmdate('H:i', max(0, $atrasosSegundos)) }}</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">SEÇÃO 2 – Indicadores de Produtividade (Atendimentos)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-4">
                    <div class="bg-white p-4 rounded border" style="border-color:#3b82f6; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Tempo em Atendimento</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ gmdate('H:i', max(0, $indicadores['total_tempo_atendimento_segundos'])) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded border" style="border-color:#22c55e; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Tempo Médio / Atendimento</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ gmdate('H:i', max(0, $indicadores['tempo_medio_segundos'])) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded border" style="border-color:#8b5cf6; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Atendimentos no Período</p>
                        <p class="text-2xl font-bold text-violet-600 mt-1">{{ $indicadores['total_atendimentos'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded border" style="border-color:#f59e0b; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Produtividade (%)</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($indicadores['produtividade_percentual'], 2, ',', '.') }}%</p>
                    </div>
                    <div class="bg-white p-4 rounded border" style="border-color:#ef4444; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Tempo Ocioso</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ gmdate('H:i', max(0, $indicadores['tempo_ocioso_segundos'])) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-600 uppercase">Assiduidade Mensal</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($indicadores['assiduidade_mensal'], 2, ',', '.') }}%</p>
                    </div>
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-600 uppercase">Pontualidade Mensal</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($indicadores['pontualidade_mensal'], 2, ',', '.') }}%</p>
                    </div>
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-600 uppercase">Horas Extras no Período</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ gmdate('H:i', max(0, $indicadores['horas_extras_segundos'])) }}</p>
                    </div>
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-600 uppercase">Banco de Horas Acumulado</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ ($indicadores['banco_horas_acumulado_segundos'] < 0 ? '-' : '+') . gmdate('H:i', abs($indicadores['banco_horas_acumulado_segundos'])) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ajuste Manual</h2>
                <form method="POST" action="{{ route('rh.ponto-jornada.ajustes.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    @csrf
                    <input type="hidden" name="inicio" value="{{ $filtros['inicio'] }}">
                    <input type="hidden" name="fim" value="{{ $filtros['fim'] }}">

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Funcionário</label>
                        <select name="funcionario_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            <option value="">Selecione</option>
                            @foreach($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}" @selected((int) old('funcionario_id', $filtros['funcionario_id']) === (int) $funcionario->id)>{{ $funcionario->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ajuste</label>
                        <select name="tipo_ajuste" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            <option value="">Selecione</option>
                            @foreach($tiposAjuste as $valor => $label)
                                <option value="{{ $valor }}" @selected(old('tipo_ajuste') === $valor)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-form-input name="atendimento_id" type="number" label="Atendimento (opcional)" placeholder="ID" />
                    <x-form-input name="minutos_ajuste" type="number" label="Minutos (+/-)" required placeholder="Ex.: 30 ou -15" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Autorizado por</label>
                        <select name="autorizado_por_user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            <option value="">Selecione</option>
                            @foreach($autorizadores as $usuario)
                                <option value="{{ $usuario->id }}" @selected((int) old('autorizado_por_user_id') === (int) $usuario->id)>{{ $usuario->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Justificativa</label>
                        <textarea name="justificativa" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Justificativa obrigatória para o ajuste manual">{{ old('justificativa') }}</textarea>
                    </div>

                    <div class="md:col-span-6 flex justify-end">
                        <x-button type="submit" variant="primary" size="sm">Registrar Ajuste</x-button>
                    </div>
                </form>

                <div class="mt-6 border-t border-gray-200 pt-4">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Histórico de Ajustes</h3>
                    <div class="space-y-3 max-h-[420px] overflow-auto pr-1">
                        @forelse($ajustes as $ajuste)
                            <div class="border border-gray-200 rounded p-3">
                                <p class="font-semibold text-gray-800">{{ $ajuste->funcionario?->nome ?? '—' }}</p>
                                <p class="text-sm text-gray-600">Tipo: {{ $tiposAjuste[$ajuste->tipo_ajuste] ?? $ajuste->tipo_ajuste }}</p>
                                <p class="text-sm text-gray-600">Ajuste: {{ $ajuste->minutos_ajuste }} min</p>
                                <p class="text-sm text-gray-600">Autorizado por: {{ $ajuste->autorizadoPor?->name ?? '—' }}</p>
                                <p class="text-sm text-gray-600">Registrado por: {{ $ajuste->ajustadoPor?->name ?? '—' }} em {{ optional($ajuste->ajustado_em)->format('d/m/Y H:i') }}</p>
                                <p class="text-sm text-gray-700 mt-1">{{ $ajuste->justificativa }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Nenhum ajuste registrado no período.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
