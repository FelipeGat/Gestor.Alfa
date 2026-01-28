<x-app-layout>
    @push('styles')
    @vite('resources/css/dashboard/dashboard_comercial.css')
    @vite('resources/css/financeiro/index.css')
    @endpush

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                💰 Dashboard Financeiro
                {{ $empresaId ? '- ' . ($empresas->find($empresaId)->nome_fantasia ?? 'Empresa') : '(Global)' }}
            </h2>

            {{-- FILTROS --}}
            <form method="GET" class="flex flex-wrap gap-3">
                <select name="empresa_id"
                    onchange="this.form.submit()"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Todas as Empresas</option>
                    @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}" @selected($empresaId==$empresa->id)>
                        {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                    </option>
                    @endforeach
                </select>

                @if($empresaId || ($ano && $ano != date('Y')))
                <a href="{{ route('financeiro.dashboard') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    Limpar
                </a>
                @endif
            </form>
        </div>
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= NAVEGAÇÃO ================= --}}
            <div class="section-card financeiro-nav mb-6">
                {{-- BANCOS --}}
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-black font-bold rounded-lg transition shadow-sm border border-yellow-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Bancos
                </a>

                {{-- COBRANÇA --}}
                <a href="{{ route('financeiro.cobrar' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition shadow-md border border-indigo-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Cobrança
                </a>

                {{-- CONTAS A RECEBER --}}
                <a href="{{ route('financeiro.contasareceber' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-md border border-emerald-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-2-2m2 2l2-2" />
                    </svg>
                    Contas a Receber
                </a>

                {{-- CONTAS A PAGAR --}}
                <a href="#"
                    class="inline-flex items-center px-4 py-2.5 bg-gray-50 text-gray-400 font-bold rounded-lg cursor-not-allowed border border-gray-200 opacity-60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12V6m0 0l-2 2m2-2l2 2" />
                    </svg>
                    Contas a Pagar
                </a>

                {{-- MOVIMENTAÇÃO --}}
                <a href="{{ route('financeiro.movimentacao' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition shadow-md border border-blue-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Movimentação
                </a>
            </div>

            {{-- ================= GRÁFICO ================= --}}
            <div class="bg-white shadow rounded-xl p-6 mb-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                        </svg>
                        Fluxo Financeiro Mensal (Previsto x Recebido)
                    </h3>

                    <form method="GET" class="inline-block">
                        @if($empresaId)
                        <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                        @endif

                        <select name="ano"
                            onchange="this.form.submit()"
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                            @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" @selected(($ano ?? date('Y'))==$y)>
                                {{ $y }}
                            </option>
                            @endfor
                        </select>
                    </form>
                </div>

                <div class="h-96">
                    <canvas id="chartFinanceiroMensal"></canvas>
                </div>
            </div>

            {{-- ================= SALDO EM BANCOS ================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

                <div class="bg-white shadow rounded-xl p-6 relative">

                    {{-- HEADER --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            💳 Saldo em Bancos
                        </h3>

                        {{-- OLHO --}}
                        <button type="button"
                            onclick="toggleValoresBancos()"
                            class="text-gray-500 hover:text-gray-800 transition"
                            title="Mostrar / Ocultar valores">
                            👁
                        </button>
                    </div>

                    {{-- TOTAL (Apenas Contas Correntes) --}}
                    <div class="mb-4 pb-4 border-b">
                        <span class="text-xs text-gray-500 uppercase">Saldo Total (Contas Correntes)</span>
                        <p class="text-2xl font-bold text-gray-800 valor-banco hidden">
                            R$ {{ number_format($saldoTotalBancos, 2, ',', '.') }}
                        </p>
                        <p class="text-2xl font-bold text-gray-800 valor-banco-masked">
                            R$ •••••
                        </p>
                    </div>

                    {{-- LISTA DE CONTAS AGRUPADAS POR TIPO --}}
                    <div class="space-y-4">
                        @foreach($contasAgrupadasPorTipo as $tipo => $contas)
                        <div>
                            {{-- CABEÇALHO DO TIPO --}}
                            <div class="flex items-center gap-2 mb-2 px-2">
                                <div class="h-3 w-3 rounded-full {{ $tipo === 'corrente' ? 'bg-blue-500' : ($tipo === 'poupanca' ? 'bg-green-500' : 'bg-purple-500') }}"></div>
                                <span class="text-xs font-semibold text-gray-600 uppercase">
                                    {{ $tipo === 'corrente' ? 'Conta Corrente' : ($tipo === 'poupanca' ? 'Poupança' : ucfirst($tipo)) }}
                                </span>
                            </div>

                            {{-- CONTAS DESTE TIPO --}}
                            <div class="space-y-2">
                                @foreach($contas as $conta)
                                <div class="flex items-center justify-between rounded-lg px-4 py-3 
                                    {{ $tipo === 'corrente' ? 'bg-blue-50' : ($tipo === 'poupanca' ? 'bg-green-50' : 'bg-purple-50') }}">

                                    <div class="flex items-center gap-3">
                                        {{-- LOGO (opcional) --}}
                                        @if($conta->logo)
                                        <img src="{{ asset('images/bancos/'.$conta->logo) }}"
                                            class="h-6 w-6 object-contain">
                                        @endif

                                        <span class="text-sm font-medium text-gray-700">
                                            {{ $conta->nome }}
                                        </span>
                                    </div>

                                    {{-- VALOR --}}
                                    <div class="text-right">
                                        <span class="font-semibold valor-banco hidden
                                            {{ $conta->saldo < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                            R$ {{ number_format($conta->saldo, 2, ',', '.') }}
                                        </span>

                                        <span class="font-semibold text-gray-400 valor-banco-masked">
                                            R$ •••••
                                        </span>
                                    </div>

                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>


                {{-- RESUMO FINANCEIRO --}}

                <div class="bg-white shadow rounded-xl p-6 relative">

                    {{-- HEADER --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">
                            📊 Resumo Financeiro
                        </h3>

                        {{-- OLHO --}}
                        <button type="button"
                            onclick="toggleValoresBancos()"
                            class="text-gray-500 hover:text-gray-800 transition"
                            title="Mostrar / Ocultar valores">
                            👁
                        </button>
                    </div>

                    {{-- FILTRO DE PERÍODO --}}
                    <form method="GET" class="flex items-center gap-2 mb-6 text-sm">
                        @if($empresaId)
                        <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                        @endif
                        <input type="hidden" name="ano" value="{{ $ano }}">

                        <span class="text-gray-500">Período:</span>

                        <input type="date"
                            name="inicio"
                            value="{{ $inicio->format('Y-m-d') }}"
                            class="rounded-md border-gray-300 text-sm">

                        <span class="text-gray-400">até</span>

                        <input type="date"
                            name="fim"
                            value="{{ $fim->format('Y-m-d') }}"
                            class="rounded-md border-gray-300 text-sm">

                        <button class="px-3 py-1 bg-indigo-600 text-white rounded-md text-xs">
                            Filtrar
                        </button>
                    </form>

                    {{-- ================= REALIZADO ================= --}}
                    <div class="mb-6">
                        <span class="text-xs text-gray-500 uppercase block mb-2">
                            Realizado
                        </span>

                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <span class="text-xs text-gray-500">Receita</span>
                                <p class="font-bold text-green-600 valor-banco oculto">
                                    R$ {{ number_format($receitaRealizada, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>

                            <div>
                                <span class="text-xs text-gray-500">Despesa</span>
                                <p class="font-bold text-red-600 valor-banco oculto">
                                    R$ {{ number_format($despesaRealizada, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>

                            <div>
                                <span class="text-xs text-gray-500">Saldo</span>
                                <p class="font-bold text-blue-600 valor-banco oculto">
                                    R$ {{ number_format($saldoRealizado, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>
                        </div>
                    </div>

                    {{-- ================= PREVISTO ================= --}}
                    <div class="mb-6 border-t pt-4">
                        <span class="text-xs text-gray-500 uppercase block mb-2">
                            Previsto
                        </span>

                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <span class="text-xs text-gray-500">A Receber</span>
                                <p class="font-bold text-green-600 valor-banco oculto">
                                    R$ {{ number_format($aReceber, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>

                            <div>
                                <span class="text-xs text-gray-500">A Pagar</span>
                                <p class="font-bold text-red-600 valor-banco oculto">
                                    R$ {{ number_format($aPagar, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>

                            <div>
                                <span class="text-xs text-gray-500">Saldo</span>
                                <p class="font-bold text-blue-600 valor-banco oculto">
                                    R$ {{ number_format($saldoPrevisto, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>
                        </div>
                    </div>

                    {{-- ================= SITUAÇÃO ================= --}}
                    <div class="border-t pt-4">
                        <span class="text-xs text-gray-500 uppercase block mb-2">
                            Situação
                        </span>

                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <span class="text-xs text-gray-500">Atrasado</span>
                                <p class="font-bold text-red-600 valor-banco oculto">
                                    R$ {{ number_format($atrasado, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>

                            <div>
                                <span class="text-xs text-gray-500">Pago</span>
                                <p class="font-bold text-green-600 valor-banco oculto">
                                    R$ {{ number_format($pago, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>

                            <div>
                                <span class="text-xs text-gray-500">Diferença</span>
                                <p class="font-bold text-blue-600 valor-banco oculto">
                                    R$ {{ number_format($saldoSituacao, 2, ',', '.') }}
                                </p>
                                <p class="font-bold text-gray-400 valor-banco-masked">R$ •••••</p>
                            </div>
                        </div>
                    </div>

                </div>


            </div>


            {{-- ================= DADOS PARA JS ================= --}}
            <div id="financeiro-json-data"
                data-labels='@json($financeiroMensal->pluck("mes"))'
                data-previsto='@json($financeiroMensal->pluck("previsto"))'
                data-recebido='@json($financeiroMensal->pluck("recebido"))'
                style="display:none;">
            </div>

        </div>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const el = document.getElementById('financeiro-json-data');

            const labels = JSON.parse(el.dataset.labels || '[]');
            const previsto = JSON.parse(el.dataset.previsto || '[]');
            const recebido = JSON.parse(el.dataset.recebido || '[]');

            new Chart(document.getElementById('chartFinanceiroMensal'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Previsto',
                            data: previsto,
                            backgroundColor: '#93c5fd'
                        },
                        {
                            label: 'Recebido',
                            data: recebido,
                            backgroundColor: '#1e3a8a'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': R$ ' +
                                        context.raw.toLocaleString('pt-BR', {
                                            minimumFractionDigits: 2
                                        });
                                }
                            }
                        }
                    }
                }
            });

        });
    </script>

    <script>
        // Iniciar sempre com valores ocultos
        let valoresVisiveis = false;

        // Garantir que inicia oculto ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.valor-banco').forEach(el => {
                el.classList.add('hidden');
            });
            document.querySelectorAll('.valor-banco-masked').forEach(el => {
                el.classList.remove('hidden');
            });
        });

        function toggleValoresBancos() {
            valoresVisiveis = !valoresVisiveis;

            document.querySelectorAll('.valor-banco').forEach(el => {
                el.classList.toggle('hidden', !valoresVisiveis);
            });

            document.querySelectorAll('.valor-banco-masked').forEach(el => {
                el.classList.toggle('hidden', valoresVisiveis);
            });
        }
    </script>
    @endpush
</x-app-layout>