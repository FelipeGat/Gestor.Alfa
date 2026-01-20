<x-app-layout>

    @php
    $orcamentosResumo = $orcamentosPorEmpresa->keyBy('empresa_id');
    @endphp

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @vite('resources/js/dashboard.js')
    @endpush

    {{-- DADOS PARA O JS --}}
    <div id="dashboard-data"
         data-status='@json($orcamentosPorStatus ?? [])'
         data-empresas='@json($orcamentosPorEmpresa ?? [])'
         data-aprovados="{{ $aprovados ?? 0 }}"
         data-recusados="{{ $recusados ?? 0 }}">
    </div>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📈 Dashboard Comercial
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= RESUMO ================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-blue-500">
                    <h3 class="text-sm font-medium text-gray-500">Alfa</h3>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ $orcamentosResumo[4]->total_qtd ?? 0 }} {{-- empresa_id --}}
                    </p>
                </div>

                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-indigo-500">
                    <h3 class="text-sm font-medium text-gray-500">Delta</h3>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ $orcamentosResumo[1]->total_qtd ?? 0 }} {{-- empresa_id --}}
                    </p>
                </div>

                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-emerald-500">
                    <h3 class="text-sm font-medium text-gray-500">Invest</h3>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ $orcamentosResumo[3]->total_qtd ?? 0 }} {{-- empresa_id --}}
                    </p>
                </div>

                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-amber-500">
                    <h3 class="text-sm font-medium text-gray-500">GW</h3>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ $orcamentosResumo[2]->total_qtd ?? 0 }} {{-- empresa_id --}}
                    </p>
                </div>

            </div>

            {{-- ================= GRÁFICOS ================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">

                {{-- ORÇAMENTOS POR STATUS --}}
                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        Orçamentos por Status
                    </h3>
                    <div class="h-64">
                        <canvas id="chartStatus"></canvas>
                    </div>
                </div>

                {{-- CONVERSÃO --}}
                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        Concluidos x Recusados
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="chartConversao"></canvas>
                    </div>
                </div>

            </div>

            {{-- ORÇAMENTOS POR EMPRESA --}}
            {{-- VALOR --}}
            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    Orçamentos por Empresa (Valor Total)
                </h3>
                <div class="h-[420px]">
                    <canvas id="chartEmpresa"></canvas>
                </div>
            </div>
            <br>

            {{-- QUANTIDADE --}}
            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    Orçamentos por Empresa (QQuantidade)
                </h3>
                <div class="h-[420px]">
                    <canvas id="chartQtdaEmpresa"></canvas>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
