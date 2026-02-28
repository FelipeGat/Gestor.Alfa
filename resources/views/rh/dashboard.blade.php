<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'RH']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <form method="GET" action="{{ route('rh.dashboard') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data início</label>
                        <input type="date" name="inicio" value="{{ $filtros['inicio'] }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data fim</label>
                        <input type="date" name="fim" value="{{ $filtros['fim'] }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                    <div class="flex gap-2">
                        <x-button type="submit" variant="primary" size="sm">Aplicar período</x-button>
                        <x-button href="{{ route('rh.dashboard') }}" variant="secondary" size="sm">Limpar</x-button>
                    </div>
                    <div class="text-sm text-gray-600 md:text-right">
                        Período: <span class="font-semibold">{{ $periodoLabel }}</span>
                    </div>
                </form>
            </div>

            @php
                $monitoramentoCards = [
                    'faltas_hoje' => ['color' => '#ef4444', 'text' => 'text-red-600'],
                    'atrasos_hoje' => ['color' => '#f59e0b', 'text' => 'text-amber-600'],
                    'saidas_antecipadas' => ['color' => '#3b82f6', 'text' => 'text-blue-600'],
                    'atestados_mes' => ['color' => '#8b5cf6', 'text' => 'text-violet-600'],
                    'banco_horas_acima_20h' => ['color' => '#16a34a', 'text' => 'text-green-600'],
                    'banco_horas_abaixo_menos_20h' => ['color' => '#dc2626', 'text' => 'text-red-700'],
                ];

                $performanceCards = [
                    'media_avaliacao_funcionario' => ['color' => '#3b82f6', 'text' => 'text-blue-600'],
                    'ranking_top5_tecnicos' => ['color' => '#16a34a', 'text' => 'text-green-600'],
                    'ranking_maior_indice_atraso' => ['color' => '#f59e0b', 'text' => 'text-amber-600'],
                    'ultima_avaliacao_negativa' => ['color' => '#ef4444', 'text' => 'text-red-600'],
                ];

                $riscoCards = [
                    'documentos_vencendo_30' => ['color' => '#f59e0b', 'text' => 'text-amber-600'],
                    'epi_vencido' => ['color' => '#8b5cf6', 'text' => 'text-violet-600'],
                    'ferias_vencidas' => ['color' => '#ef4444', 'text' => 'text-red-600'],
                    'aso_vencido' => ['color' => '#dc2626', 'text' => 'text-red-700'],
                    'funcionario_sem_jornada' => ['color' => '#3b82f6', 'text' => 'text-blue-600'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #3b82f6; border-top: 1px solid #3b82f6; border-right: 1px solid #3b82f6; border-bottom: 1px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Funcionários Ativos</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $funcionariosAtivos }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #f59e0b; border-top: 1px solid #f59e0b; border-right: 1px solid #f59e0b; border-bottom: 1px solid #f59e0b; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Documentos Vencendo</p>
                    <p class="text-3xl font-bold text-amber-600 mt-2">{{ $documentosVencendo }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #8b5cf6; border-top: 1px solid #8b5cf6; border-right: 1px solid #8b5cf6; border-bottom: 1px solid #8b5cf6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">EPIs Vencendo</p>
                    <p class="text-3xl font-bold text-violet-600 mt-2">{{ $episVencendo }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #ef4444; border-top: 1px solid #ef4444; border-right: 1px solid #ef4444; border-bottom: 1px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Férias Vencidas</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $feriasVencidas }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #22c55e; border-top: 1px solid #22c55e; border-right: 1px solid #22c55e; border-bottom: 1px solid #22c55e; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Banco de Horas</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ gmdate('H:i', max(0, $bancoHorasSegundos)) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Monitoramento Diário</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($monitoramento as $slug => $card)
                        @php
                            $theme = $monitoramentoCards[$slug] ?? ['color' => '#3f9cae', 'text' => 'text-cyan-700'];
                        @endphp
                        <a href="{{ route('rh.relatorios.show', array_merge(['indicador' => $slug], $filtrosQuery)) }}" class="block bg-white p-5 rounded-lg border-l-4 hover:shadow-md transition" style="border-color: {{ $theme['color'] }}; border-top: 1px solid {{ $theme['color'] }}; border-right: 1px solid {{ $theme['color'] }}; border-bottom: 1px solid {{ $theme['color'] }};">
                            <p class="text-xs text-gray-600 uppercase tracking-wide">{{ $card['title'] }}</p>
                            <p class="text-3xl font-bold mt-2 {{ $theme['text'] }}">{{ $card['count'] }}</p>
                            <p class="text-xs text-gray-500 mt-3">Ver relatório detalhado</p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Performance</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach($performance as $slug => $card)
                        @php
                            $theme = $performanceCards[$slug] ?? ['color' => '#3f9cae', 'text' => 'text-cyan-700'];
                        @endphp
                        <a href="{{ route('rh.relatorios.show', array_merge(['indicador' => $slug], $filtrosQuery)) }}" class="block bg-white p-5 rounded-lg border-l-4 hover:shadow-md transition" style="border-color: {{ $theme['color'] }}; border-top: 1px solid {{ $theme['color'] }}; border-right: 1px solid {{ $theme['color'] }}; border-bottom: 1px solid {{ $theme['color'] }};">
                            <p class="text-xs text-gray-600 uppercase tracking-wide">{{ $card['title'] }}</p>
                            <p class="text-3xl font-bold mt-2 {{ $theme['text'] }}">{{ $card['count'] }}</p>
                            <p class="text-xs text-gray-500 mt-3">Ver relatório detalhado</p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Risco Trabalhista</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                    @foreach($risco as $slug => $card)
                        @php
                            $theme = $riscoCards[$slug] ?? ['color' => '#3f9cae', 'text' => 'text-cyan-700'];
                        @endphp
                        <a href="{{ route('rh.relatorios.show', array_merge(['indicador' => $slug], $filtrosQuery)) }}" class="block bg-white p-5 rounded-lg border-l-4 hover:shadow-md transition" style="border-color: {{ $theme['color'] }}; border-top: 1px solid {{ $theme['color'] }}; border-right: 1px solid {{ $theme['color'] }}; border-bottom: 1px solid {{ $theme['color'] }};">
                            <p class="text-xs text-gray-600 uppercase tracking-wide">{{ $card['title'] }}</p>
                            <p class="text-3xl font-bold mt-2 {{ $theme['text'] }}">{{ $card['count'] }}</p>
                            <p class="text-xs text-gray-500 mt-3">Ver relatório detalhado</p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações RH</h2>
                <div class="flex flex-wrap gap-3">
                    <x-button href="{{ route('rh.funcionarios.index') }}" variant="primary" size="sm">Funcionários</x-button>
                    <x-button href="{{ route('rh.ponto-jornada.index') }}" variant="secondary" size="sm">Ponto & Jornada</x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
