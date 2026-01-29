<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            💰 Meu Financeiro — {{ $cliente->nome_fantasia ?? $cliente->nome }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ================= FILTROS DE PERÍODO ================= --}}
            <div class="bg-white shadow rounded-xl p-6">
                <form method="GET" action="{{ route('portal.financeiro') }}" class="space-y-4">

                    {{-- Navegação de Meses --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Navegação Rápida</label>
                        <div class="flex items-center gap-2">
                            @php
                            $dataAtual = request('data_inicio')
                            ? \Carbon\Carbon::parse(request('data_inicio'))
                            : \Carbon\Carbon::now();

                            $mesAnterior = $dataAtual->copy()->subMonth();
                            $proximoMes = $dataAtual->copy()->addMonth();

                            // Meses em português
                            $meses = [
                            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                            ];
                            $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                            @endphp

                            <a href="{{ route('portal.financeiro', [
                                'data_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'),
                                'data_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')
                            ]) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                                title="Mês Anterior">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>

                            <div class="flex-1 text-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm">
                                {{ $mesAtualNome }}
                            </div>

                            <a href="{{ route('portal.financeiro', [
                                'data_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'),
                                'data_fim' => $proximoMes->endOfMonth()->format('Y-m-d')
                            ]) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                                title="Próximo Mês">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Período Personalizado (colapsável) --}}
                    <div x-data="{ mostrarPeriodo: false }">
                        <button
                            type="button"
                            @click="mostrarPeriodo = !mostrarPeriodo"
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="mostrarPeriodo ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span x-text="mostrarPeriodo ? 'Ocultar período personalizado' : 'Escolher período personalizado'"></span>
                        </button>
                        <div x-show="mostrarPeriodo" x-transition class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                                <input type="date" name="data_inicio" value="{{ $dataInicio }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                                <input type="date" name="data_fim" value="{{ $dataFim }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="sm:col-span-2 flex gap-2 justify-end">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition text-sm">
                                    🔍 Filtrar
                                </button>
                                <a href="{{ route('portal.financeiro') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition text-sm">
                                    🔄 Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ================= RESUMO FINANCEIRO / KPIs ================= --}}
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
                        <span class="text-sm text-gray-500 font-normal ml-2">({{ $cobrancas->count() }} registro(s))</span>
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
                                <th class="px-4 py-3 text-center">Anexos</th>
                                <th class="px-4 py-3 text-center">Ações</th>
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
                                    <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-700 font-semibold">
                                        ✓ Pago
                                    </span>
                                    @elseif($cobranca->data_vencimento->isToday())
                                    <span class="px-3 py-1 rounded-full text-xs bg-orange-100 text-orange-700 font-semibold">
                                        🔔 Vence Hoje
                                    </span>
                                    @elseif($cobranca->data_vencimento->isPast())
                                    <span class="px-3 py-1 rounded-full text-xs bg-red-100 text-red-700 font-semibold">
                                        ⚠ Vencido
                                    </span>
                                    @else
                                    <span class="px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-700 font-semibold">
                                        📅 A Vencer
                                    </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($cobranca->anexos && $cobranca->anexos->count() > 0)
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @foreach($cobranca->anexos as $anexo)
                                        <a
                                            href="{{ route('portal.cobrancas.anexos.download', $anexo) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg font-semibold transition shadow-sm
                                                {{ $anexo->tipo === 'nf' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-yellow-500 hover:bg-yellow-600 text-white' }}">
                                            @if($anexo->tipo === 'nf')
                                            📄 NF
                                            @else
                                            💳 Boleto
                                            @endif
                                        </a>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($cobranca->orcamento_id)
                                    <a
                                        href="{{ route('portal.orcamentos.imprimir', $cobranca->orcamento_id) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg font-semibold transition shadow-sm bg-gray-700 hover:bg-gray-800 text-white"
                                        title="Imprimir Orçamento">
                                        🖨️ Imprimir
                                    </a>
                                    @else
                                    <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    Nenhuma cobrança encontrada no período selecionado.
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