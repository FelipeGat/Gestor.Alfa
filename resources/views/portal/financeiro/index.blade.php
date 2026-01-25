<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💰 Meu Financeiro
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ================= RESUMO FINANCEIRO ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-600">
                    <p class="text-sm text-gray-500">Total em Aberto</p>
                    <p class="text-2xl font-bold text-gray-800">
                        R$ {{ number_format($resumo['total_pendente'], 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-600">
                    <p class="text-sm text-gray-500">Total Pago</p>
                    <p class="text-2xl font-bold text-gray-800">
                        R$ {{ number_format($resumo['total_pago'], 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-600">
                    <p class="text-sm text-gray-500">Total Vencido</p>
                    <p class="text-2xl font-bold text-gray-800">
                        R$ {{ number_format($resumo['total_vencido'], 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-gray-400">
                    <p class="text-sm text-gray-500">Total Geral</p>
                    <p class="text-2xl font-bold text-gray-800">
                        R$ {{ number_format($resumo['total_geral'], 2, ',', '.') }}
                    </p>
                </div>

            </div>

            {{-- ================= LISTA DE COBRANÇAS ================= --}}
            <div class="bg-white shadow rounded-xl overflow-hidden">

                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-700">
                        📄 Histórico de Cobranças
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 text-left">Descrição</th>
                                <th class="px-4 py-3 text-center">Vencimento</th>
                                <th class="px-4 py-3 text-right">Valor</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Arquivos</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($cobrancas as $cobranca)
                            <tr class="hover:bg-gray-50">

                                <td class="px-4 py-3 font-medium">
                                    {{ $cobranca->descricao }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    {{ $cobranca->data_vencimento->format('d/m/Y') }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold">
                                    R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($cobranca->status === 'pago')
                                    <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-700">
                                        Pago
                                    </span>
                                    @elseif($cobranca->data_vencimento->isPast())
                                    <span class="px-3 py-1 rounded-full text-xs bg-red-100 text-red-700">
                                        Vencido
                                    </span>
                                    @else
                                    <span class="px-3 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">
                                        Pendente
                                    </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center space-x-2">

                                    {{-- BOLETO --}}
                                    @if($cobranca->boleto && $cobranca->boleto->arquivo)
                                    <a href="{{ route('portal.boletos.download', $cobranca->boleto) }}"
                                        class="inline-flex items-center px-3 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-700">
                                        📄 Boleto
                                    </a>
                                    @endif

                                    {{-- NOTA FISCAL --}}
                                    @if($cobranca->notaFiscal && $cobranca->notaFiscal->arquivo)
                                    <a href="{{ route('portal.notas.download', $cobranca->notaFiscal) }}"
                                        class="inline-flex items-center px-3 py-1 text-xs rounded bg-gray-700 text-white hover:bg-gray-800">
                                        🧾 NF
                                    </a>
                                    @endif

                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    Nenhuma cobrança encontrada.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>

</x-app-layout>