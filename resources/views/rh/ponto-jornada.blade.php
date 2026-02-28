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
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ajuste Manual de Ponto</h2>
                <form method="POST" action="{{ route('rh.ponto-jornada.ajustes.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Funcionário</label>
                        <select name="funcionario_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            <option value="">Selecione</option>
                            @foreach($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}">{{ $funcionario->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-form-input name="atendimento_id" type="number" label="Atendimento (opcional)" placeholder="ID do atendimento" />
                    <x-form-input name="minutos_ajuste" type="number" label="Minutos (+/-)" required placeholder="Ex.: 30 ou -15" />
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Justificativa</label>
                        <textarea name="justificativa" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Justificativa obrigatória para o ajuste manual"></textarea>
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <x-button type="submit" variant="primary" size="sm">Registrar Ajuste</x-button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Visualização de Ponto por Funcionário</h2>
                @php
                    $columns = [
                        ['label' => 'Atendimento'],
                        ['label' => 'Funcionário'],
                        ['label' => 'Início'],
                        ['label' => 'Fim'],
                        ['label' => 'Tempo Execução'],
                    ];
                @endphp
                <x-table :columns="$columns" :data="$registros" emptyMessage="Nenhum registro de ponto no período">
                    @foreach($registros as $registro)
                        <tr>
                            <x-table-cell>#{{ $registro->id }}</x-table-cell>
                            <x-table-cell>{{ $registro->funcionario?->nome ?? '—' }}</x-table-cell>
                            <x-table-cell>{{ optional($registro->iniciado_em)->format('d/m/Y H:i') ?? '—' }}</x-table-cell>
                            <x-table-cell>{{ optional($registro->finalizado_em)->format('d/m/Y H:i') ?? '—' }}</x-table-cell>
                            <x-table-cell>{{ gmdate('H:i:s', (int) ($registro->tempo_execucao_segundos ?? 0)) }}</x-table-cell>
                        </tr>
                    @endforeach
                </x-table>
                <x-pagination :paginator="$registros" label="registros" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Relatório de Horas Extras</h2>
                    <div class="space-y-3">
                        @forelse($horasExtras as $linha)
                            <div class="border border-gray-200 rounded p-3">
                                <p class="font-semibold text-gray-800">{{ $linha['funcionario']?->nome ?? 'Funcionário não encontrado' }}</p>
                                <p class="text-sm text-gray-600">Trabalhadas: {{ gmdate('H:i:s', max(0, $linha['segundos_trabalhados'])) }}</p>
                                <p class="text-sm text-gray-600">Ajustes: {{ gmdate('H:i:s', max(0, abs($linha['segundos_ajuste']))) }} ({{ $linha['segundos_ajuste'] >= 0 ? '+' : '-' }})</p>
                                <p class="text-sm font-semibold text-gray-800">Saldo: {{ gmdate('H:i:s', max(0, $linha['saldo_segundos'])) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Sem dados para exibir no período.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Banco de Horas (Ajustes)</h2>
                    <div class="space-y-3 max-h-[460px] overflow-auto pr-1">
                        @forelse($ajustes as $ajuste)
                            <div class="border border-gray-200 rounded p-3">
                                <p class="font-semibold text-gray-800">{{ $ajuste->funcionario?->nome ?? '—' }}</p>
                                <p class="text-sm text-gray-600">Ajuste: {{ $ajuste->minutos_ajuste }} min</p>
                                <p class="text-sm text-gray-600">Em: {{ optional($ajuste->ajustado_em)->format('d/m/Y H:i') }}</p>
                                <p class="text-sm text-gray-600">Por: {{ $ajuste->ajustadoPor?->name ?? '—' }}</p>
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
