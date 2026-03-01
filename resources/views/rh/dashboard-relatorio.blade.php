<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'RH', 'url' => route('rh.dashboard', $filtrosQuery)],
            ['label' => 'Relatório']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @php
                $indicadoresComTotalizadorMinutos = ['atrasos_hoje', 'atrasos_mes', 'saidas_antecipadas', 'saidas_antecipadas_mes'];
                $mostrarTotalizadorMinutos = in_array($indicador, $indicadoresComTotalizadorMinutos, true);
                $totalMinutos = 0;
                $mostrarTotalizadorExtras = $indicador === 'trabalho_domingo_feriado';
                $totalSegundosExtras = 0;

                $formatarMinutosTotalizador = function (int $minutos): string {
                    $valor = max(0, $minutos);
                    if ($valor <= 59) {
                        return $valor . ' min';
                    }

                    $horas = intdiv($valor, 60);
                    $restoMinutos = $valor % 60;

                    return sprintf('%02dh %02dmin', $horas, $restoMinutos);
                };

                $rotuloTotalizador = in_array($indicador, ['saidas_antecipadas', 'saidas_antecipadas_mes'], true)
                    ? 'Totalizador de Saídas'
                    : 'Totalizador de Atrasos';

                $formatarSegundosTotalizador = function (int $segundos): string {
                    $totalMin = max(0, intdiv($segundos, 60));
                    if ($totalMin <= 59) {
                        return $totalMin . ' min';
                    }

                    $horas = intdiv($totalMin, 60);
                    $restoMinutos = $totalMin % 60;

                    return sprintf('%02dh %02dmin', $horas, $restoMinutos);
                };

                if ($mostrarTotalizadorMinutos) {
                    foreach ($rows as $row) {
                        $colunaMinutos = is_array($row) && !empty($row) ? (string) end($row) : '';
                        if (preg_match('/(-?\d+)/', $colunaMinutos, $matches) === 1) {
                            $totalMinutos += abs((int) $matches[1]);
                        }
                    }
                }

                if ($mostrarTotalizadorExtras) {
                    foreach ($rows as $row) {
                        $colunaExtra = is_array($row) && !empty($row) ? (string) end($row) : '';
                        if (preg_match('/(\d{1,2}):(\d{2})/', $colunaExtra, $matches) === 1) {
                            $horas = (int) $matches[1];
                            $minutos = (int) $matches[2];
                            $totalSegundosExtras += ($horas * 3600) + ($minutos * 60);
                        }
                    }
                }
            @endphp

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <form method="GET" action="{{ route('rh.relatorios.show', ['indicador' => $indicador]) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
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
                        <x-button href="{{ route('rh.relatorios.show', ['indicador' => $indicador]) }}" variant="secondary" size="sm">Limpar</x-button>
                    </div>
                    <div class="text-sm text-gray-600 md:text-right">
                        Período: <span class="font-semibold">{{ $periodoLabel }}</span>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">{{ $titulo }}</h1>
                        @if($descricao)
                            <p class="text-sm text-gray-600 mt-1">{{ $descricao }}</p>
                        @endif
                        <p class="text-xs text-gray-500 mt-2">Atualizado em {{ $updatedAt->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="flex items-end gap-6 md:justify-end">
                        <div class="text-right">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Total</div>
                            <div class="text-3xl font-bold text-cyan-700">{{ $count }}</div>
                        </div>
                        @if($mostrarTotalizadorMinutos)
                            <div class="text-right">
                                <div class="text-xs uppercase tracking-wide text-gray-500">{{ $rotuloTotalizador }}</div>
                                <div class="text-3xl font-bold text-amber-700">{{ $formatarMinutosTotalizador($totalMinutos) }}</div>
                            </div>
                        @endif
                        @if($mostrarTotalizadorExtras)
                            <div class="text-right">
                                <div class="text-xs uppercase tracking-wide text-gray-500">Totalizador de Extras</div>
                                <div class="text-3xl font-bold text-amber-700">{{ $formatarSegundosTotalizador($totalSegundosExtras) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                @if(empty($rows))
                    <div class="text-sm text-gray-600">Nenhum registro encontrado para este indicador no período atual.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left border border-gray-200 rounded">
                            <thead class="bg-gray-50">
                                <tr>
                                    @foreach($columns as $column)
                                        <th class="px-3 py-2 font-semibold text-gray-700 border-b border-gray-200">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                    <tr class="hover:bg-gray-50">
                                        @foreach($row as $cell)
                                            <td class="px-3 py-2 border-b border-gray-100 text-gray-700">{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="flex justify-end">
                <x-button href="{{ route('rh.dashboard', $filtrosQuery) }}" variant="secondary" size="sm">Voltar ao Dashboard RH</x-button>
            </div>
        </div>
    </div>
</x-app-layout>
