<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Portal do Cliente
                </h2>

                <p class="text-sm text-gray-600 mt-1">
                    Unidade:
                    <span class="font-semibold text-gray-800">
                        {{ $cliente->nome_exibicao }}
                    </span>
                </p>
            </div>

            @if(auth()->user()->clientes()->count() > 1)
            <form method="POST" action="{{ route('portal.trocar-unidade') }}">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center px-3 py-1.5
                           bg-gray-100 hover:bg-gray-200
                           text-gray-700 text-sm font-medium
                           rounded-md border border-gray-300">
                    🔄 Trocar Unidade
                </button>
            </form>
            @endif

        </div>
    </x-slot>


    <div class="flex justify-center min-h-screen bg-gray-100">
        <div class="w-3/4 py-12 space-y-6">

            {{-- Cabeçalho --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">
                    Olá, {{ $cliente->nome }}
                </h3>

                <p class="text-gray-600">
                    Aqui você pode visualizar seus boletos e notas fiscais.
                </p>
            </div>

            {{-- 📊 RESUMO --}}
            <div class="flex flex-wrap gap-3">

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-blue-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Total de Boletos</p>
                    <p class="text-lg font-bold text-blue-600">
                        {{ $boletos->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-green-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Boletos Pagos</p>
                    <p class="text-lg font-bold text-green-600">
                        {{ $boletos->where('status', 'pago')->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-red-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Boletos Vencidos</p>
                    <p class="text-lg font-bold text-red-600">
                        {{ $boletos->where('status', 'vencido')->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-yellow-500">
                    <p class="text-xs font-medium text-gray-600 mb-1">Notas Fiscais</p>
                    <p class="text-lg font-bold text-yellow-600">
                        {{ $boletos->whereNotNull('nota_fiscal')->count() }}
                    </p>
                </div>

            </div>

            {{-- 📋 TABELA DE BOLETOS E NF --}}
            @if($boletos->count() > 0)
            <div class="bg-white shadow-md rounded-lg overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">
                                    Referência
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">
                                    Vencimento
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">
                                    Valor
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">
                                    Boleto
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">
                                    NF
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">
                                    Ação
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($boletos as $boleto)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                    {{ str_pad($boleto->mes, 2, '0', STR_PAD_LEFT) }}/{{ $boleto->ano }}
                                </td>

                                <td class="px-4 py-3 text-xs text-center text-gray-900">
                                    {{ $boleto->data_vencimento->format('d/m/Y') }}
                                </td>

                                <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                    R$ {{ number_format($boleto->valor, 2, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    @if($boleto->cobranca && $boleto->cobranca->status === 'pago')
                                    <span class="inline-flex px-2 py-1 rounded-full bg-green-100 text-green-800">
                                        Pago
                                    </span>
                                    @elseif($boleto->status === 'vencido')
                                    <span class="inline-flex px-2 py-1 rounded-full bg-red-100 text-red-800">
                                        Vencido
                                    </span>
                                    @else
                                    <span class="inline-flex px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                                        Em aberto
                                    </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-xs text-center">
                                    <a href="{{ route('portal.boletos.download', $boleto) }}"
                                        class="inline-flex items-center justify-center px-3 py-1
                                        bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                                        Baixar
                                    </a>
                                </td>

                                <td class="px-4 py-3 text-xs text-center">
                                    @if($boleto->nota_fiscal)
                                    <a href="{{ route('portal.notas.download', $boleto->nota_fiscal) }}"
                                        class="inline-flex items-center justify-center px-3 py-1
                                        bg-blue-600 hover:bg-blue-700 text-red-600 text-xs font-medium rounded-md shadow">
                                        Baixar NF
                                    </a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    @if($boleto->foiBaixado())
                                    <div class="text-gray-600">
                                        <span class="font-medium">Boleto Baixado</span><br>
                                        {{ $boleto->baixado_em->format('d/m/Y H:i') }}
                                    </div>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
            @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-500 text-lg">Nenhum boleto disponível.</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>