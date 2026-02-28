<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'RH', 'url' => route('rh.dashboard')],
            ['label' => 'Ponto & Jornada']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-filter :action="route('rh.ponto-jornada.index')" :show-clear-button="true">
                <x-filter-field name="funcionario_id" label="Funcionário" type="select" placeholder="Todos" colSpan="lg:col-span-4">
                    @foreach($funcionarios as $funcionario)
                        <option value="{{ $funcionario->id }}" @selected((int) request('funcionario_id') === (int) $funcionario->id)>{{ $funcionario->nome }}</option>
                    @endforeach
                </x-filter-field>
                <x-filter-field name="inicio" label="Início" type="date" :value="$filtros['inicio']" colSpan="lg:col-span-3" />
                <x-filter-field name="fim" label="Fim" type="date" :value="$filtros['fim']" colSpan="lg:col-span-3" />
            </x-filter>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">SEÇÃO 1 – Jornada Legal (Registro de Ponto do Portal)</h2>
                @php
                    $columnsLegal = [
                        ['label' => 'Funcionário'],
                        ['label' => 'Data'],
                        ['label' => 'Entrada'],
                        ['label' => 'Início Intervalo'],
                        ['label' => 'Fim Intervalo'],
                        ['label' => 'Saída'],
                        ['label' => 'Total Trabalhado'],
                        ['label' => 'Status'],
                    ];
                @endphp

                <x-table :columns="$columnsLegal" :data="$jornadaLegal['rows']" emptyMessage="Sem registros de jornada legal no período.">
                    @foreach($jornadaLegal['rows'] as $linha)
                        @php
                            $estiloLinha = '';
                            if (!empty($linha['eh_feriado'])) {
                                $estiloLinha = 'background-color: #fef3c7;';
                            } elseif (!empty($linha['eh_domingo'])) {
                                $estiloLinha = 'background-color: #fee2e2;';
                            }
                        @endphp
                        <tr @if($estiloLinha) style="{{ $estiloLinha }}" @endif>
                            <x-table-cell>{{ $linha['funcionario'] }}</x-table-cell>
                            <x-table-cell>
                                <div class="flex items-center gap-2">
                                    <span>{{ $linha['data'] }}</span>
                                    @if(!empty($linha['eh_feriado']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background-color:#f59e0b; color:white;">Feriado</span>
                                    @elseif(!empty($linha['eh_domingo']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background-color:#ef4444; color:white;">Domingo</span>
                                    @endif
                                </div>
                                @if(!empty($linha['feriado_nome']))
                                    <div class="text-xs text-amber-800 mt-1">{{ $linha['feriado_nome'] }}</div>
                                @endif
                            </x-table-cell>
                            <x-table-cell>{{ $linha['entrada'] }}</x-table-cell>
                            <x-table-cell>{{ $linha['intervalo_inicio'] }}</x-table-cell>
                            <x-table-cell>{{ $linha['intervalo_fim'] }}</x-table-cell>
                            <x-table-cell>{{ $linha['saida'] }}</x-table-cell>
                            <x-table-cell>{{ $linha['total'] }}</x-table-cell>
                            <x-table-cell>
                                @php
                                    $ehExtra = in_array($linha['status'], ['Extra', 'Extra feriado'], true);
                                    $ehDomingoOuFeriado = !empty($linha['eh_domingo']) || !empty($linha['eh_feriado']);
                                    $statusClass = $ehExtra
                                        ? 'bg-amber-100 text-amber-900 border border-amber-300'
                                        : ($ehDomingoOuFeriado
                                            ? 'bg-red-100 text-red-800 border border-red-300'
                                            : 'bg-gray-900 text-white border border-gray-900');
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $linha['status'] }}</span>
                            </x-table-cell>
                        </tr>
                    @endforeach
                </x-table>

                <x-pagination :paginator="$jornadaLegal['rows']" label="registros legais" />
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
