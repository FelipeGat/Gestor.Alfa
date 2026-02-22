@php
    $user = auth()->user();
    $isAdmin = $user && $user->isAdminPanel();
    $isFinanceiro = $user && $user->perfis()->where('slug', 'financeiro')->exists();
@endphp

@if(!($isAdmin || $isFinanceiro))
    <div class="max-w-2xl mx-auto mt-16 p-8 bg-white rounded shadow text-center">
        <h2 class="text-2xl font-bold mb-4 text-red-600">Acesso negado</h2>
        <p class="text-gray-700">Você não tem permissão para acessar o Dashboard Financeiro.</p>
    </div>
    @php return; @endphp
@endif

    <x-app-layout>
    @push('styles')
    @vite('resources/css/dashboard/dashboard_comercial.css')
    @vite('resources/css/financeiro/index.css')
    <style>
        .filters-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        .card-grafico {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn-filtro-rapido {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            line-height: 1.25rem;
            border-radius: 9999px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-filtro-rapido.ativo {
            background: #3f9cae;
            color: white;
        }
        .btn-filtro-rapido.inativo {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        .btn-filtro-rapido.inativo:hover {
            background: #e5e7eb;
        }
        .nav-financeiro-tab {
            display: inline-flex;
            align-items: center;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem 0.5rem 0 0;
            border: 1px solid transparent;
            border-bottom: none;
            transition: all 0.2s;
            text-decoration: none;
        }
        .nav-financeiro-tab:hover {
            transform: translateY(-2px);
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Dashboard Financeiro']
        ]" />
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="py-8 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= GRÁFICO ================= --}}
            <div class="card-grafico p-6 mb-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        Fluxo Financeiro Mensal (Previsto x Recebido)
                    </h3>

                    <form method="GET" class="inline-block">
                        @if($empresaId)
                        <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                        @endif

                        <select name="ano"
                            onchange="this.form.submit()"
                            class="rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring focus:ring-[#3f9cae]/20 text-sm">
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
                    <script>
                        window.financeiroMensalLabels = @json($labels);
                        window.financeiroMensalData = [
                            {
                                label: 'Receita Prevista',
                                data: @json($previsto),
                                backgroundColor: '#60A5FA',
                                borderRadius: 6,
                            },
                            {
                                label: 'Receita Recebida',
                                data: @json($recebido),
                                backgroundColor: '#1E3A8A',
                                borderRadius: 6,
                            },
                            {
                                label: 'Despesa Prevista',
                                data: @json($despesaPrevista),
                                backgroundColor: '#F87171',
                                borderRadius: 6,
                            },
                            {
                                label: 'Despesa Paga',
                                data: @json($despesaPaga),
                                backgroundColor: '#B91C1C',
                                borderRadius: 6,
                            }
                        ];
                    </script>
                </div>
            </div>
            <div>
                {{-- FILTRO RÁPIDO DE PERÍODO --}}
                <div class="filters-card p-6 mb-6">
                    <div x-data="{ filtroRapido: '{{ request('filtro_rapido') ?: 'mes' }}', mostrarCustom: {{ request('filtro_rapido') === 'custom' ? 'true' : 'false' }}, aplicarFiltro(tipo) { this.filtroRapido = tipo; if (tipo !== 'custom') { this.mostrarCustom = false; const form = this.$refs.formFiltro; const inputInicio = form.querySelector('input[name=inicio]'); const inputFim = form.querySelector('input[name=fim]'); if (inputInicio) inputInicio.disabled = true; if (inputFim) inputFim.disabled = true; setTimeout(() => form.submit(), 10); } else { this.mostrarCustom = true; } } }">
                        <form method="GET" x-ref="formFiltro" action="/financeiro/dashboard">
                            @if($empresaId)
                            <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                            @endif
                            <input type="hidden" name="ano" value="{{ $ano }}">
                            <input type="hidden" name="filtro_rapido" :value="filtroRapido">
                            <div class="flex flex-wrap items-center justify-center gap-2 text-sm">
                                <span class="text-gray-700 font-semibold text-sm">Filtrar por Data:</span>
                                {{-- Botões de filtro rápido --}}
                                <button type="button"
                                    @click="aplicarFiltro('dia')"
                                    :class="filtroRapido === 'dia' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="">
                                    Dia
                                </button>
                                    <button type="button"
                                        @click="aplicarFiltro('semana')"
                                        :class="filtroRapido === 'semana' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                        class="">
                                        Semana
                                    </button>
                                    <button type="button"
                                        @click="aplicarFiltro('mes')"
                                        :class="filtroRapido === 'mes' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                        class="">
                                        Mês
                                    </button>
                                    <button type="button"
                                        @click="aplicarFiltro('ano')"
                                        :class="filtroRapido === 'ano' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                        class="">
                                        Ano
                                    </button>
                                    <button type="button"
                                        @click="aplicarFiltro('proximo_mes')"
                                        :class="filtroRapido === 'proximo_mes' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                        class="">
                                        Próximo Mês
                                    </button>
                                    <button type="button"
                                        @click="aplicarFiltro('custom')"
                                        :class="filtroRapido === 'custom' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                        class="">
                                        Outro período
                                    </button>
                                </div>
                                {{-- Campos de data personalizados --}}
                                <div x-show="mostrarCustom" x-transition class="flex items-center gap-2 mt-3">
                                    <input type="date"
                                        name="inicio"
                                        value="{{ $inicio->format('Y-m-d') }}"
                                        class="rounded-md border-gray-300 focus:border-[#3f9cae] focus:ring focus:ring-[#3f9cae]/20 text-sm">
                                    <span class="text-gray-400">até</span>
                                    <input type="date"
                                        name="fim"
                                        value="{{ $fim->format('Y-m-d') }}"
                                        class="rounded-md border-gray-300 focus:border-[#3f9cae] focus:ring focus:ring-[#3f9cae]/20 text-sm">
                                    <button type="submit" class="px-4 py-1.5 bg-[#3f9cae] text-white rounded-full text-xs font-medium hover:bg-[#358a96] transition shadow-md">
                                        Aplicar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            {{-- ================= SALDO EM BANCOS ================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
                <div class="card-grafico p-6 relative">
                    {{-- HEADER --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            {{-- ÍCONE DE BANCO (PRÉDIO) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Saldo em Bancos
                        </h3>
                        {{-- BOTAO MOSTRAR / OCULTAR --}}
                        <button type="button"
                            onclick="toggleValoresBancos()"
                            class="text-gray-400 hover:text-indigo-600 transition p-1 rounded-full hover:bg-gray-100"
                            title="Mostrar / Ocultar valores"
                            id="btnToggleBancos">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
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
                <div class="card-grafico p-6 relative">
                    {{-- HEADER --}}
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">
                            📊 Resumo Financeiro
                        </h3>
                    </div>
    
                    {{-- ================= REALIZADO ================= --}}
                    <div class="mb-6">
                        <span class="text-xs text-gray-500 uppercase block mb-2">
                            Realizado
                        </span>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <span class="text-xs text-gray-500">Receita</span>
                                <p class="font-bold text-green-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('receita')">
                                    R$ {{ number_format($receitaRealizada, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Despesa</span>
                                <p class="font-bold text-red-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('despesa')">
                                    R$ {{ number_format($despesaRealizada, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Saldo</span>
                                <p class="font-bold text-blue-600">
                                    R$ {{ number_format($saldoRealizado, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        <!-- Modal de Lançamentos -->
                        <div id="modal-lancamentos" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                            <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full p-0 relative" style="border: 1px solid #3f9cae; border-top-width: 4px;">
                                <button onclick="fecharModalLancamentos()" class="absolute top-3 right-3 text-gray-400 hover:text-red-600 text-2xl font-bold z-10">&times;</button>
                                
                                {{-- HEADER DO MODAL --}}
                                <div class="px-6 py-4 border-b border-gray-200" style="background-color: rgba(63, 156, 174, 0.05);">
                                    <div class="flex items-center justify-between">
                                        <h3 id="modal-titulo" class="text-lg font-semibold text-gray-900" style="font-size: 1.125rem; font-weight: 600;">Lançamentos</h3>
                                        <button onclick="imprimirModalLancamentos()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-lg shadow transition" style="font-size: 0.875rem; border-radius: 9999px;" title="Imprimir">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-6 0v4m0 0h4m-4 0H8" />
                                            </svg>
                                            Imprimir
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <span id="modal-total" class="text-sm font-semibold"></span>
                                    </div>
                                </div>

                                <div id="modal-lista-lancamentos" class="max-h-96 overflow-y-auto p-6">
                                    <!-- Conteúdo preenchido via JS -->
                                </div>
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
                                <p class="font-bold text-green-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('previsto_receber')">
                                    R$ {{ number_format($aReceber, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">A Pagar</span>
                                <p class="font-bold text-red-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('previsto_pagar')">
                                    R$ {{ number_format($aPagar, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Saldo</span>
                                <p class="font-bold text-blue-600">
                                    R$ {{ number_format($saldoPrevisto, 2, ',', '.') }}
                                </p>
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
                                <span class="text-xs text-gray-500">Receitas Atrasadas</span>
                                <p class="font-bold text-green-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('situacao_pago')">
                                    R$ {{ number_format($pago, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Despesas Atrasadas</span>
                                <p class="font-bold text-red-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('situacao_atrasado')">
                                    R$ {{ number_format($atrasado, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Diferença</span>
                                <p class="font-bold text-blue-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('situacao_diferenca')">
                                    R$ {{ number_format($saldoSituacao, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= GRÁFICOS DE GASTOS POR CATEGORIA ================= --}}
            <div class="filters-card p-6 mb-6">
                <div class="flex flex-wrap items-center justify-center gap-2 text-sm">
                    <span class="text-gray-700 font-semibold text-sm">Custos por Categorias:</span>
                    <button type="button" class="btn-filtro-rapido ativo btn-nivel-categoria" data-nivel="categoria">Categorias</button>
                    <button type="button" class="btn-filtro-rapido inativo btn-nivel-categoria" data-nivel="subcategoria">Subcategorias</button>
                    <button type="button" class="btn-filtro-rapido inativo btn-nivel-categoria" data-nivel="conta">Contas</button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
                @foreach($dadosCentros as $centro => $dados)
                <div class="card-grafico p-6 flex flex-col items-center">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 text-center">
                        Gastos por <span id="grafico-nivel-{{ Str::slug($centro) }}">Categoria</span><br><span class="text-xs text-gray-500">{{ $centro }}</span>
                    </h3>
                    <div class="w-full flex-1 flex items-center justify-center">
                        <canvas class="grafico-categoria" data-centro="{{ $centro }}" id="grafico-categoria-{{ Str::slug($centro) }}" width="220" height="220" style="cursor:pointer;"></canvas>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ================= NOVOS CARDS: INDICADORES E ALERTAS ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                {{-- CARD 1: INDICADORES INTELIGENTES --}}
                <div class="card-grafico p-6 flex flex-col gap-6">
                    <h3 class="text-base font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m4 0h-1v-4h-1m4 0h-1v-4h-1"/></svg>
                        Indicadores Inteligentes
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- % da renda comprometida --}}
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-gray-500">% da renda comprometida</span>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold">{{ $percentualRendaComprometida }}%</span>
                                @php
                                    $cor = $percentualRendaComprometida <= 60 ? 'bg-emerald-500' : ($percentualRendaComprometida <= 80 ? 'bg-yellow-400' : 'bg-red-500');
                                @endphp
                                <span class="px-2 py-1 rounded text-xs text-white {{ $cor }}">
                                    {{ $percentualRendaComprometida <= 60 ? 'Saudável' : ($percentualRendaComprometida <= 80 ? 'Atenção' : 'Alerta') }}
                                </span>
                            </div>
                        </div>
                        {{-- Ticket médio de despesas --}}
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-gray-500">Ticket médio de despesas</span>
                            <span class="text-2xl font-bold text-blue-700">R$ {{ number_format($ticketMedioDespesas, 2, ',', '.') }}</span>
                        </div>
                        {{-- Custo fixo x variável --}}
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-gray-500">Custo Fixo</span>
                            <span class="font-bold text-indigo-600">R$ {{ number_format($custoFixo, 2, ',', '.') }} <span class="text-xs text-gray-400">({{ $percentualFixo }}%)</span></span>
                            <span class="text-xs text-gray-500 mt-1">Custo Variável</span>
                            <span class="font-bold text-pink-600">R$ {{ number_format($custoVariavel, 2, ',', '.') }} <span class="text-xs text-gray-400">({{ $percentualVariavel }}%)</span></span>
                        </div>
                        {{-- Meta mensal (Orçado x Realizado) --}}
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-gray-500">Meta mensal (Orçado x Realizado)</span>
                            <div class="w-full bg-gray-200 rounded-full h-4 mb-1">
                                <div class="h-4 rounded-full {{ $percentualMeta >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ min($percentualMeta, 100) }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600">
                                <span>Orçado: <b>R$ {{ number_format($orcado, 2, ',', '.') }}</b></span>
                                <span>Realizado: <b>R$ {{ number_format($realizadoMeta, 2, ',', '.') }}</b></span>
                                <span>Dif: <b>R$ {{ number_format($diferencaMeta, 2, ',', '.') }}</b></span>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- CARD 2: ALERTAS E INSIGHTS AUTOMÁTICOS --}}
                <div class="card-grafico p-6 flex flex-col gap-4">
                    <h3 class="text-base font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m4 0h-1v-4h-1m4 0h-1v-4h-1"/></svg>
                        Alertas e Insights Automáticos
                    </h3>
                    @if(count($alertasFinanceiros) === 0)
                        <div class="text-gray-400 text-sm">Nenhum alerta ou insight para o período selecionado.</div>
                    @else
                        <ul class="space-y-2">
                            @foreach($alertasFinanceiros as $alerta)
                                <li>
                                    @if($alerta['tipo'] === 'alerta')
                                        <span class="inline-block px-2 py-1 bg-red-500 text-white rounded text-xs font-bold mr-2">Alerta</span>
                                    @elseif($alerta['tipo'] === 'sucesso')
                                        <span class="inline-block px-2 py-1 bg-emerald-500 text-white rounded text-xs font-bold mr-2">Sucesso</span>
                                    @else
                                        <span class="inline-block px-2 py-1 bg-blue-500 text-white rounded text-xs font-bold mr-2">Info</span>
                                    @endif
                                    <span class="text-gray-700">{!! $alerta['mensagem'] !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                </div>
            </div>


            {{-- ================= DADOS PARA JS ================= --}}
            <div id="financeiro-json-data"
                data-labels='@json($financeiroMensal->pluck("mes"))'
                data-previsto='@json($financeiroMensal->pluck("previsto"))'
                data-recebido='@json($financeiroMensal->pluck("recebido"))'
                data-despesa-paga='@json($financeiroMensal->pluck("despesaPaga"))'
                data-despesa-prevista='@json($financeiroMensal->pluck("despesaPrevista"))'
                style="display:none;">
            </div>

        </div>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    @push('scripts')
    <script>
        // Dados dos lançamentos vindos do backend (precisa ser enviado pelo controller)
        const lancamentosReceita = @json($lancamentosReceita ?? []);
        const lancamentosDespesa = @json($lancamentosDespesa ?? []);
        const lancamentosPrevistoReceber = @json($lancamentosPrevistoReceber ?? []);
        const lancamentosPrevistoPagar = @json($lancamentosPrevistoPagar ?? []);
        const lancamentosAtrasado = @json($lancamentosAtrasado ?? []);
        const lancamentosPago = @json($lancamentosPago ?? []);
        const lancamentosDiferenca = @json($lancamentosDiferenca ?? []);
        let currentTipoLancamento = '';

        function mostrarLancamentos(tipo) {
            currentTipoLancamento = tipo;
            let lista = [];
            let titulo = '';
            let totalColor = '';

            if (tipo === 'receita') {
                lista = lancamentosReceita;
                titulo = 'Lançamentos de Receita';
                totalColor = 'text-green-600';
            } else if (tipo === 'despesa') {
                lista = lancamentosDespesa;
                titulo = 'Lançamentos de Despesa';
                totalColor = 'text-red-600';
            } else if (tipo === 'previsto_receber') {
                lista = lancamentosPrevistoReceber;
                titulo = 'Lançamentos a Receber';
                totalColor = 'text-green-600';
            } else if (tipo === 'previsto_pagar') {
                lista = lancamentosPrevistoPagar;
                titulo = 'Lançamentos a Pagar';
                totalColor = 'text-red-600';
            } else if (tipo === 'situacao_atrasado') {
                lista = lancamentosAtrasado;
                titulo = 'Despesas Atrasadas';
                totalColor = 'text-red-600';
            } else if (tipo === 'situacao_pago') {
                lista = lancamentosPago;
                titulo = 'Receitas Atrasadas';
                totalColor = 'text-green-600';
            } else if (tipo === 'situacao_diferenca') {
                lista = lancamentosDiferenca;
                titulo = 'Lançamentos Diferença';
                totalColor = 'text-blue-600';
            }
            // Calcular total
            let total = lista.reduce((acc, l) => acc + (parseFloat(l.valor) || 0), 0);
            let totalFormatado = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            
            document.getElementById('modal-titulo').innerText = titulo;
            document.getElementById('modal-total').innerHTML = `<span class='${totalColor}'>${totalFormatado}</span>`;
            const container = document.getElementById('modal-lista-lancamentos');
            if (lista.length === 0) {
                container.innerHTML = '<div class="text-gray-500 text-center py-8" style="font-size: 0.875rem;">Nenhum lançamento encontrado.</div>';
            } else {
                let thExtra = '';
                if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_pago') {
                    thExtra = `<th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Empresa</th>`;
                } else if (tipo === 'despesa' || tipo === 'previsto_pagar' || tipo === 'situacao_atrasado') {
                    thExtra = `<th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Centro de Custo</th>`;
                }
                
                let thCnpj = '';
                if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_pago') {
                    thCnpj = `<th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>CNPJ/CPF</th>`;
                }
                
                container.innerHTML = `
                <div class='overflow-x-auto'>
                <table class='w-full table-auto' style='border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border-radius: 0.5rem;'>
                    <thead style='background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;'>
                        <tr>
                            <th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Pagamento</th>
                            ${thExtra}
                            <th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Cliente/Fornecedor</th>
                            ${thCnpj}
                            <th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Descrição</th>
                            <th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Tipo</th>
                            <th class='px-4 py-3 text-right uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Valor</th>
                        </tr>
                    </thead>
                    <tbody class='divide-y divide-gray-200'>
                        ${lista.map(l => `
                            <tr class='hover:bg-gray-50 transition'>
                                <td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.data ?? ''}</td>
                                ${(tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_pago') ? `<td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.empresa ?? '-'}</td>` : ''}
                                ${(tipo === 'despesa' || tipo === 'previsto_pagar' || tipo === 'situacao_atrasado') ? `<td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.centro_custo ?? '-'}</td>` : ''}
                                <td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.cliente ?? '-'}</td>
                                ${(tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_pago') ? `<td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.cnpjcpf ?? '-'}</td>` : ''}
                                <td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.descricao ?? '-'}</td>
                                <td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.tipo ?? '-'}</td>
                                <td class='px-4 py-3 text-sm text-right font-semibold' style='color: rgb(17, 24, 39);'>R$ ${parseFloat(l.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                </div>
                `;
            }
            document.getElementById('modal-lancamentos').classList.remove('hidden');
        }
        function fecharModalLancamentos() {
            document.getElementById('modal-lancamentos').classList.add('hidden');
        }

        function getTipoInfo(tipo) {
            const info = {
                'receita': { lista: lancamentosReceita, titulo: 'Lançamentos de Receita', cor: '#16a34a' },
                'despesa': { lista: lancamentosDespesa, titulo: 'Lançamentos de Despesa', cor: '#dc2626' },
                'previsto_receber': { lista: lancamentosPrevistoReceber, titulo: 'Lançamentos a Receber', cor: '#16a34a' },
                'previsto_pagar': { lista: lancamentosPrevistoPagar, titulo: 'Lançamentos a Pagar', cor: '#dc2626' },
                'situacao_atrasado': { lista: lancamentosAtrasado, titulo: 'Despesas Atrasadas', cor: '#dc2626' },
                'situacao_pago': { lista: lancamentosPago, titulo: 'Receitas Atrasadas', cor: '#16a34a' },
                'situacao_diferenca': { lista: lancamentosDiferenca, titulo: 'Lançamentos Diferença', cor: '#2563eb' }
            };
            return info[tipo] || { lista: [], titulo: 'Lançamentos', cor: '#333' };
        }

        function imprimirModalLancamentos() {
            const tipo = currentTipoLancamento || 'receita';
            const tipoInfo = getTipoInfo(tipo);
            const lista = tipoInfo.lista;
            const titulo = tipoInfo.titulo;
            const corTotal = tipoInfo.cor;

            const dataAtual = new Date().toLocaleDateString('pt-BR', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });

            let total = lista.reduce((acc, l) => acc + (parseFloat(l.valor) || 0), 0);
            let totalFormatado = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });

            let thExtra = '';
            if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_pago') {
                thExtra = '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Empresa</th>';
            } else if (tipo === 'despesa' || tipo === 'previsto_pagar' || tipo === 'situacao_atrasado') {
                thExtra = '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Centro de Custo</th>';
            }

            let thCnpj = '';
            if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_pago') {
                thCnpj = '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">CNPJ/CPF</th>';
            }

            let tableHeader = '<thead><tr>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Pagamento</th>';
            tableHeader += thExtra;
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Cliente/Fornecedor</th>';
            tableHeader += thCnpj;
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Descrição</th>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Tipo</th>';
            tableHeader += '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: right; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Valor</th>';
            tableHeader += '</tr></thead>';

            let tableRows = '';
            if (lista.length === 0) {
                tableRows = '<tr><td colspan="7" style="padding: 20px; text-align: center; color: #666;">Nenhum lançamento encontrado.</td></tr>';
            } else {
                lista.forEach(function(l) {
                    let valorFormatado = 'R$ ' + (parseFloat(l.valor) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                    tableRows += '<tr>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.data || '-') + '</td>';
                    if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_pago') {
                        tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.empresa || '-') + '</td>';
                    } else if (tipo === 'despesa' || tipo === 'previsto_pagar' || tipo === 'situacao_atrasado') {
                        tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.centro_custo || '-') + '</td>';
                    }
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.cliente || '-') + '</td>';
                    if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_pago') {
                        tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.cnpjcpf || '-') + '</td>';
                    }
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.descricao || '-') + '</td>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.tipo || '-') + '</td>';
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; text-align: right; font-weight: bold; color: ' + corTotal + '; white-space: nowrap;">' + valorFormatado + '</td>';
                    tableRows += '</tr>';
                });
            }

            let tableHtml = '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">' + tableHeader + '<tbody>' + tableRows + '</tbody></table>';

            let totalsHtml = '<div style="text-align: right; margin-top: 20px; padding: 10px; background-color: #f9fafb; border-top: 2px solid #e5e7eb;">';
            totalsHtml += '<span style="font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Total</span>';
            totalsHtml += '<span style="font-size: 13px; font-weight: bold; color: ' + corTotal + '; margin-left: 10px;">' + totalFormatado + '</span>';
            totalsHtml += '</div>';

            let printWindow = window.open('', '_blank', 'width=800,height=600');

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${titulo}</title>
                    <meta charset="UTF-8">
                    <style>
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                        @media print {
                            body { margin: 0; padding: 15mm; }
                            .no-print { display: none; }
                            table { page-break-inside: auto; }
                            tr { page-break-inside: avoid; page-break-after: auto; }
                            thead { display: table-header-group; }
                            tfoot { display: table-footer-group; }
                        }
                    </style>
                </head>
                <body style="font-family: Arial, sans-serif; margin: 0; padding: 15mm; color: #333; font-size: 11px;">
                    <h1 style="font-size: 18px; margin-bottom: 5px; color: #000;">${titulo}</h1>
                    <p style="font-size: 11px; color: #666; margin-bottom: 15px; border-bottom: 2px solid #ccc; padding-bottom: 10px;">
                        Gerado em: ${dataAtual}
                    </p>
                    <div>
                        ${tableHtml}
                    </div>
                    ${totalsHtml}
                    <div class="no-print" style="margin-top: 30px; text-align: center;">
                        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #1f2937; color: white; border: none; border-radius: 6px; font-weight: bold;">Imprimir</button>
                        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; font-weight: bold;">Fechar</button>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

        // Fechar modal ao clicar fora
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('modal-lancamentos');
            if (!modal.classList.contains('hidden') && e.target === modal) {
                fecharModalLancamentos();
            }
        });
    </script>
        <script src="{{ asset('js/vendor/chart.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                                                                // Função para normalizar strings (lowercase, sem acento, trim)
                                                                function normalizeString(str) {
                                                                    if (!str) return '';
                                                                    return str
                                                                        .toLowerCase()
                                                                        .normalize('NFD')
                                                                        .replace(/\p{Diacritic}/gu, '')
                                                                        .trim();
                                                                }
                                // Gráficos de pizza por centro de custo com navegação de níveis
                                const dadosCentros = @json($dadosCentros);

                                // Mapas de cores por nível
                                const colorMapCategorias = {
                                    'Despesas Fixas': '#2563EB',      // Azul
                                    'Despesas Variáveis': '#F59E0B',  // Laranja
                                };
                                // Função para normalizar strings (lowercase, sem acento, trim)
                                function normalizeString(str) {
                                    if (!str) return '';
                                    return str
                                        .toLowerCase()
                                        .normalize('NFD')
                                        .replace(/\p{Diacritic}/gu, '')
                                        .trim();
                                }

                                // Paleta de cores padrão
                                const palette = [
                                    '#2563EB', '#F59E0B', '#10B981', '#8B5CF6', '#EC4899', '#EF4444', '#14B8A6', '#6366F1', '#0EA5E9', '#F472B6', '#64748B', '#22D3EE', '#A21CAF', '#EAB308', '#F43F5E', '#9333EA'
                                ];

                                // Geração dinâmica dos mapas de cor
                                const colorMapSubcategorias = {};
                                const colorMapContas = {};

                                // Coletar todas as combinações de subcategoria e conta dos dadosCentros
                                let subcatKeys = [];
                                let contaKeys = [];
                                Object.values(dadosCentros).forEach(dados => {
                                    (dados.subcategorias || []).forEach(s => {
                                        const key = `${normalizeString(s.categoria)}::${normalizeString(s.nome)}`;
                                        if (!subcatKeys.includes(key)) subcatKeys.push(key);
                                    });
                                    (dados.contas || []).forEach(c => {
                                        const key = `${normalizeString(c.subcategoria)}::${normalizeString(c.nome)}`;
                                        if (!contaKeys.includes(key)) contaKeys.push(key);
                                    });
                                });

                                // Atribuir cores da paleta para cada combinação
                                subcatKeys.forEach((key, idx) => {
                                    colorMapSubcategorias[key] = palette[idx % palette.length];
                                });
                                contaKeys.forEach((key, idx) => {
                                    colorMapContas[key] = palette[idx % palette.length];
                                });
                                // Cor neutra fallback
                                const fallbackColor = '#CBD5E1';

                                // Função utilitária para obter cor por nome e nível
                                function getColorByLabel(label, nivel) {
                                    if (nivel === 'categoria') return colorMapCategorias[label] || fallbackColor;
                                    if (nivel === 'subcategoria') return colorMapSubcategorias[label] || fallbackColor;
                                    if (nivel === 'conta') return colorMapContas[label] || fallbackColor;
                                    return fallbackColor;
                                }
                                // Função utilitária para array de cores por labels e nível
                                function getColorsByLabels(labels, nivel) {
                                    // Categoria: label é string
                                    if (nivel === 'categoria') {
                                        return labels.map(label => getColorByLabel(label, nivel));
                                    }
                                    // Subcategoria: label é objeto {nome, categoria}
                                    if (nivel === 'subcategoria') {
                                        return labels.map(obj => {
                                            if (typeof obj === 'object' && obj.categoria && obj.nome) {
                                                const key = `${obj.categoria}::${obj.nome}`;
                                                return getColorByLabel(key, nivel);
                                            }
                                            return fallbackColor;
                                        });
                                    }
                                    // Conta: label é objeto {nome, subcategoria}
                                    if (nivel === 'conta') {
                                        return labels.map(obj => {
                                            if (typeof obj === 'object' && obj.subcategoria && obj.nome) {
                                                const key = `${obj.subcategoria}::${obj.nome}`;
                                                return getColorByLabel(key, nivel);
                                            }
                                            return fallbackColor;
                                        });
                                    }
                                    return labels.map(() => fallbackColor);
                                }
                                // ...existing code...
                // Estado de nível por centro: categoria, subcategoria, conta
                const niveis = ['categoria', 'subcategoria', 'conta'];
                const estadoNivel = {};
                const charts = {};

                Object.entries(dadosCentros).forEach(([centro, dados]) => {
                    const centroSlug = centro.toLowerCase().replace(/ /g, '-');
                    estadoNivel[centro] = 0; // 0: categoria, 1: subcategoria, 2: conta (padrão: categoria)
                    const ctx = document.getElementById('grafico-categoria-' + centroSlug);
                    if (!ctx) return;

                    function getChartData() {
                        const nivel = niveis[estadoNivel[centro]];
                        if (nivel === 'categoria') {
                            return {
                                labels: (dados.categorias || []).map(c => c.nome),
                                data: (dados.categorias || []).map(c => c.total),
                            };
                        } else if (nivel === 'subcategoria') {
                            return {
                                labels: (dados.subcategorias || []).map(s => s.nome),
                                data: (dados.subcategorias || []).map(s => s.total),
                                meta: dados.subcategorias || []
                            };
                        } else if (nivel === 'conta') {
                            return {
                                labels: (dados.contas || []).map(c => c.nome),
                                data: (dados.contas || []).map(c => c.total),
                                meta: dados.contas || []
                            };
                        }
                        return {labels: [], data: [], meta: []};
                    }

                    function updateChart() {
                        const nivel = niveis[estadoNivel[centro]];
                        const chartData = getChartData();
                        let backgroundColors = [];
                        if (nivel === 'subcategoria') {
                            backgroundColors = (chartData.meta || []).map(s => {
                                const key = `${normalizeString(s.categoria)}::${normalizeString(s.nome)}`;
                                const exists = !!colorMapSubcategorias[key];
                                console.log('Subcategoria:', s.categoria, s.nome, '-> chave:', key, 'existe:', exists);
                                return colorMapSubcategorias[key] || fallbackColor;
                            });
                        } else if (nivel === 'conta') {
                            backgroundColors = (chartData.meta || []).map(c => {
                                const key = `${normalizeString(c.subcategoria)}::${normalizeString(c.nome)}`;
                                const exists = !!colorMapContas[key];
                                console.log('Conta:', c.subcategoria, c.nome, '-> chave:', key, 'existe:', exists);
                                return colorMapContas[key] || fallbackColor;
                            });
                        } else {
                            backgroundColors = getColorsByLabels(chartData.labels, nivel);
                        }
                        if (charts[centro]) {
                            charts[centro].data.labels = chartData.labels;
                            charts[centro].data.datasets[0].data = chartData.data;
                            charts[centro].data.datasets[0].backgroundColor = backgroundColors;
                            charts[centro].update();
                        } else {
                            charts[centro] = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: chartData.labels,
                                    datasets: [{
                                        data: chartData.data,
                                        backgroundColor: backgroundColors,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                            labels: {
                                                font: { size: 13 }
                                            }
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    const label = context.label || '';
                                                    const value = context.raw || 0;
                                                    return label + ': R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                        // Atualiza o texto do nível
                        const nivelEl = document.getElementById('grafico-nivel-' + centroSlug);
                        if (nivelEl) {
                            nivelEl.textContent = nivel.charAt(0).toUpperCase() + nivel.slice(1);
                        }
                    }

                    // Inicializa
                    updateChart();

                    // Ao clicar no gráfico, alterna o nível
                    ctx.onclick = function() {
                        estadoNivel[centro] = (estadoNivel[centro] + 1) % niveis.length;
                        updateChart();
                    };

                    // Botões de nível: alternar visual
                    document.querySelectorAll('.btn-nivel-categoria').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const nivel = this.getAttribute('data-nivel');
                            const idx = niveis.indexOf(nivel);
                            if (idx === -1) return;

                            // Atualiza todos os centros
                            Object.keys(estadoNivel).forEach(c => {
                                estadoNivel[c] = idx;
                            });

                            // Atualiza todos os gráficos
                            Object.entries(charts).forEach(([centro, chart]) => {
                                const dados = dadosCentros[centro];
                                let chartData;
                                if (nivel === 'categoria') {
                                    chartData = {
                                        labels: (dados.categorias || []).map(c => c.nome),
                                        data: (dados.categorias || []).map(c => c.total),
                                    };
                                } else if (nivel === 'subcategoria') {
                                    chartData = {
                                        labels: (dados.subcategorias || []).map(s => s.nome),
                                        data: (dados.subcategorias || []).map(s => s.total),
                                        meta: dados.subcategorias || []
                                    };
                                } else if (nivel === 'conta') {
                                    chartData = {
                                        labels: (dados.contas || []).map(c => c.nome),
                                        data: (dados.contas || []).map(c => c.total),
                                        meta: dados.contas || []
                                    };
                                }

                                let backgroundColors;
                                if (nivel === 'subcategoria') {
                                    backgroundColors = (chartData.meta || []).map(s => {
                                        const key = `${normalizeString(s.categoria)}::${normalizeString(s.nome)}`;
                                        return colorMapSubcategorias[key] || fallbackColor;
                                    });
                                } else if (nivel === 'conta') {
                                    backgroundColors = (chartData.meta || []).map(c => {
                                        const key = `${normalizeString(c.subcategoria)}::${normalizeString(c.nome)}`;
                                        return colorMapContas[key] || fallbackColor;
                                    });
                                } else {
                                    backgroundColors = getColorsByLabels(chartData.labels, nivel);
                                }

                                chart.data.labels = chartData.labels;
                                chart.data.datasets[0].data = chartData.data;
                                chart.data.datasets[0].backgroundColor = backgroundColors;
                                chart.update();

                                // Atualiza texto do nível
                                const nivelEl = document.getElementById('grafico-nivel-' + centro.toLowerCase().replace(/ /g, '-'));
                                if (nivelEl) {
                                    nivelEl.textContent = nivel.charAt(0).toUpperCase() + nivel.slice(1);
                                }
                            });

                            // Atualiza visual dos botões
                            document.querySelectorAll('.btn-nivel-categoria').forEach(b => {
                                if (b.getAttribute('data-nivel') === nivel) {
                                    b.classList.remove('btn-filtro-rapido', 'inativo');
                                    b.classList.add('btn-filtro-rapido', 'ativo');
                                } else {
                                    b.classList.remove('btn-filtro-rapido', 'ativo');
                                    b.classList.add('btn-filtro-rapido', 'inativo');
                                }
                            });
                        });
                    });
                });
            });
        </script>
    <script src="{{ asset('js/vendor/chart.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const el = document.getElementById('financeiro-json-data');

            const labels = JSON.parse(el.dataset.labels || '[]');
            const previsto = JSON.parse(el.dataset.previsto || '[]');
            const recebido = JSON.parse(el.dataset.recebido || '[]');
            const despesaPaga = JSON.parse(el.dataset.despesaPaga || '[]');
            const despesaPrevista = JSON.parse(el.dataset.despesaPrevista || '[]');

            new Chart(document.getElementById('chartFinanceiroMensal'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Receita Prevista',
                            data: previsto,
                            backgroundColor: '#93c5fd',
                            stack: 'receita'
                        },
                        {
                            label: 'Receita Recebida',
                            data: recebido,
                            backgroundColor: '#1e3a8a',
                            stack: 'receita'
                        },
                        {
                            label: 'Despesa Prevista',
                            data: despesaPrevista,
                            backgroundColor: '#fca5a5',
                            stack: 'despesa'
                        },
                        {
                            label: 'Despesa Paga',
                            data: despesaPaga,
                            backgroundColor: '#dc2626',
                            stack: 'despesa'
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
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(context) {
                                    return context[0].label.toUpperCase();
                                },
                                label: function(context) {
                                    const valor = context.raw || 0;
                                    return `${context.dataset.label}: R$ ${valor.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2
                                    })}`;
                                },
                                footer: function(context) {
                                    let totalReceita = 0;
                                    context.forEach(item => {
                                        if (
                                            item.dataset.label === 'Receita Prevista' ||
                                            item.dataset.label === 'Receita Recebida'
                                        ) {
                                            totalReceita += item.raw || 0;
                                        }
                                    });
                                    return `Total Receita: R$ ${totalReceita.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2
                                    })}`;
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