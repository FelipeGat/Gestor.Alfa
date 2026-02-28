<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                    Plano de Manutenção (PMOC)
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $cliente->nome_exibicao }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.equipamentos.index') }}" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-green-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header do Relatório -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Relatório PMOC</h3>
                        <p class="text-sm text-gray-500">Plano de Manutenção, Operação e Controle</p>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <p>Data de geração: {{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            @if($equipamentos->count() > 0)
                <!-- Tabela PMOC -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Equipamento</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Setor</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Responsável</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Período Manut.</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Última Manut.</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Próxima Prevista</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Período Limpeza</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Última Limpeza</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Próxima Prevista</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($equipamentos as $equipamento)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $equipamento->nome }}</p>
                                            <p class="text-xs text-gray-500">{{ $equipamento->modelo ?? '' }} {{ $equipamento->fabricante ? '/ ' . $equipamento->fabricante : '' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $equipamento->setor->nome ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $equipamento->responsavel->nome ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                                        {{ $equipamento->periodicidade_manutencao_meses }} meses
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                                        {{ $equipamento->ultima_manutencao?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-medium">
                                        {{ $equipamento->proxima_manutencao?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                                        {{ $equipamento->periodicidade_limpeza_meses }} {{ $equipamento->periodicidade_limpeza_meses == 1 ? 'mês' : 'meses' }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-600">
                                        {{ $equipamento->ultima_limpeza?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-medium">
                                        {{ $equipamento->proxima_limpeza?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($equipamento->status_manutencao['cor'] === 'verde')
                                        <span class="inline-flex px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Em dia</span>
                                        @elseif($equipamento->status_manutencao['cor'] === 'amarelo')
                                        <span class="inline-flex px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">Atenção</span>
                                        @elseif($equipamento->status_manutencao['cor'] === 'vermelho')
                                        <span class="inline-flex px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Vencido</span>
                                        @else
                                        <span class="inline-flex px-2 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-semibold">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Resumo -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                        <p class="text-sm font-semibold text-blue-800">Total Equipamentos</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $equipamentos->count() }}</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                        <p class="text-sm font-semibold text-green-800">Manutenções em Dia</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">
                            {{ $equipamentos->filter(fn($e) => $e->status_manutencao['cor'] === 'verde' || $e->status_manutencao['cor'] === 'gray')->count() }}
                        </p>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                        <p class="text-sm font-semibold text-yellow-800">Próximas (30 dias)</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2">
                            {{ $equipamentos->filter(fn($e) => $e->status_manutencao['cor'] === 'amarelo')->count() }}
                        </p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-6 border border-red-200">
                        <p class="text-sm font-semibold text-red-800">Vencidas</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">
                            {{ $equipamentos->filter(fn($e) => $e->status_manutencao['cor'] === 'vermelho')->count() }}
                        </p>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center border border-gray-100">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Nenhum equipamento cadastrado.</p>
                    <p class="text-gray-400 text-sm mt-2">O relatório PMOC será gerado quando houver equipamentos cadastrados.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
