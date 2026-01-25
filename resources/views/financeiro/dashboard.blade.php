<x-app-layout>

    @push('styles')
    @vite('resources/css/financeiro/dashboard.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💰 Dashboard Financeiro
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            {{-- ================= KPIs ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-600">
                    <p class="text-sm text-gray-500">Total a Receber</p>
                    <p class="text-2xl font-bold text-gray-800">
                        R$ {{ number_format($totalReceber, 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-600">
                    <p class="text-sm text-gray-500">Total Pago</p>
                    <p class="text-2xl font-bold text-gray-800">
                        R$ {{ number_format($totalPago, 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-600">
                    <p class="text-sm text-gray-500">Vencidos</p>
                    <p class="text-2xl font-bold text-red-600">
                        R$ {{ number_format($totalVencido, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $qtdVencidos }} cobranças</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-amber-500">
                    <p class="text-sm text-gray-500">Vencem Hoje</p>
                    <p class="text-2xl font-bold text-amber-600">
                        R$ {{ number_format($venceHoje, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $qtdVenceHoje }} cobranças</p>
                </div>

            </div>

            {{-- ================= ORÇAMENTOS PARA FINANCEIRO ================= --}}
            <div class="bg-white rounded-xl shadow">
                <div class="px-6 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">
                        📄 Orçamentos pendentes do Financeiro
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Orçamento</th>
                                <th class="px-4 py-3 text-left">Cliente</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orcamentosFinanceiro as $orcamento)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2 font-mono">
                                    {{ $orcamento->numero_orcamento }}
                                </td>
                                <td class="px-4 py-2">
                                    {{ $orcamento->cliente?->nome ?? $orcamento->preCliente?->nome_fantasia }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">
                                        {{ ucfirst(str_replace('_',' ', $orcamento->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right font-semibold">
                                    R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                    Nenhum orçamento aguardando ação do financeiro.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= CONTAS A RECEBER ================= --}}
            <div class="bg-white rounded-xl shadow">
                <div class="px-6 py-4 border-b">
                    <h3 class="font-semibold text-gray-700">
                        🧾 Contas a Receber (Contratos)
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Cliente</th>
                                <th class="px-4 py-3 text-center">Vencimento</th>
                                <th class="px-4 py-3 text-right">Valor</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cobrancasPendentes as $cobranca)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $cobranca->cliente->nome }}</td>
                                <td class="px-4 py-2 text-center">
                                    {{ $cobranca->data_vencimento->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 text-right font-semibold">
                                    R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                        Pendente
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                    Nenhuma cobrança pendente.
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