<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registro de Ponto
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Registro de Hoje ({{ now()->format('d/m/Y') }})</h3>
                    <p class="text-sm text-gray-600 mb-4">Próximo evento esperado: <span class="font-semibold">{{ $eventos[$proximoEvento] ?? '—' }}</span></p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                        <div class="p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase">Entrada</div>
                            <div class="text-lg font-semibold text-gray-900">{{ optional($registroHoje?->entrada_em)->format('H:i:s') ?? '—' }}</div>
                        </div>
                        <div class="p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase">Saída almoço</div>
                            <div class="text-lg font-semibold text-gray-900">{{ optional($registroHoje?->intervalo_inicio_em)->format('H:i:s') ?? '—' }}</div>
                        </div>
                        <div class="p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase">Retorno almoço</div>
                            <div class="text-lg font-semibold text-gray-900">{{ optional($registroHoje?->intervalo_fim_em)->format('H:i:s') ?? '—' }}</div>
                        </div>
                        <div class="p-3 rounded border border-gray-200">
                            <div class="text-xs text-gray-500 uppercase">Saída</div>
                            <div class="text-lg font-semibold text-gray-900">{{ optional($registroHoje?->saida_em)->format('H:i:s') ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach($eventos as $chave => $rotulo)
                            <form method="POST" action="{{ route('portal-funcionario.ponto.store') }}">
                                @csrf
                                <input type="hidden" name="tipo" value="{{ $chave }}">
                                <x-button type="submit" variant="primary" size="sm" :disabled="$proximoEvento !== $chave || ($chave === 'saida' && $registroHoje?->saida_em)">
                                    {{ $rotulo }}
                                </x-button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Histórico (últimos 15 dias)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600 border-b border-gray-200">
                                    <th class="py-2 pr-4">Data</th>
                                    <th class="py-2 pr-4">Entrada</th>
                                    <th class="py-2 pr-4">Saída almoço</th>
                                    <th class="py-2 pr-4">Retorno almoço</th>
                                    <th class="py-2 pr-4">Saída</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historico as $linha)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2 pr-4">{{ optional($linha->data_referencia)->format('d/m/Y') }}</td>
                                        <td class="py-2 pr-4">{{ optional($linha->entrada_em)->format('H:i:s') ?? '—' }}</td>
                                        <td class="py-2 pr-4">{{ optional($linha->intervalo_inicio_em)->format('H:i:s') ?? '—' }}</td>
                                        <td class="py-2 pr-4">{{ optional($linha->intervalo_fim_em)->format('H:i:s') ?? '—' }}</td>
                                        <td class="py-2 pr-4">{{ optional($linha->saida_em)->format('H:i:s') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-gray-500">Nenhum registro encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <x-button href="{{ route('portal-funcionario.index') }}" variant="secondary" size="sm">Voltar ao Portal</x-button>
            </div>
        </div>
    </div>
</x-app-layout>
