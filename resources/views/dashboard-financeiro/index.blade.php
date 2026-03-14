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

        /* ── Flip Card ── */
        .resumo-flip-scene {
            perspective: 1400px;
            width: 100%;
        }
        .resumo-flip-inner {
            position: relative;
            width: 100%;
            transform-style: preserve-3d;
            transition: transform 0.65s cubic-bezier(0.4, 0.2, 0.2, 1);
        }
        .resumo-flip-inner.flipped {
            transform: rotateY(180deg);
        }
        /* FRENTE: fluxo normal — define a altura do container */
        .resumo-flip-front {
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        /* VERSO: absolute overlay — não colapsa o container */
        .resumo-flip-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            transform: rotateY(180deg);
        }
        .resumo-flip-trigger {
            cursor: pointer;
            user-select: none;
        }
        .resumo-flip-trigger:hover .flip-hint {
            opacity: 1;
            transform: translateX(0);
        }
        .flip-hint {
            opacity: 0.55;
            transform: translateX(-4px);
            transition: all 0.2s;
        }

        /* ── Bancos Flip Card ── */
        .bancos-flip-scene {
            perspective: 1400px;
            width: 100%;
        }
        .bancos-flip-inner {
            position: relative;
            width: 100%;
            transform-style: preserve-3d;
            transition: transform 0.65s cubic-bezier(0.4, 0.2, 0.2, 1);
        }
        .bancos-flip-inner.flipped {
            transform: rotateY(180deg);
        }
        /* FRENTE: fluxo normal — define a altura do container */
        .bancos-flip-front {
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        /* VERSO: absolute overlay — não colapsa o container */
        .bancos-flip-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            transform: rotateY(180deg);
        }
        .bancos-flip-trigger {
            cursor: pointer;
            user-select: none;
        }
        .bancos-flip-trigger:hover .flip-hint {
            opacity: 1;
            transform: translateX(0);
        }

        /* ── Categorias / Receitas Flip Card ── */
        .categorias-flip-scene {
            perspective: 1400px;
            width: 100%;
        }
        .categorias-flip-inner {
            position: relative;
            width: 100%;
            transform-style: preserve-3d;
            transition: transform 0.65s cubic-bezier(0.4, 0.2, 0.2, 1);
        }
        .categorias-flip-inner.flipped {
            transform: rotateY(180deg);
        }
        .categorias-flip-front {
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .categorias-flip-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            transform: rotateY(180deg);
        }
        .categorias-flip-trigger {
            cursor: pointer;
            user-select: none;
        }
        .categorias-flip-trigger:hover .flip-hint {
            opacity: 1;
            transform: translateX(0);
        }

        /* ── Indicadores Flip Card ── */
        .indicadores-flip-scene {
            perspective: 1400px;
            width: 100%;
        }
        .indicadores-flip-inner {
            position: relative;
            width: 100%;
            transform-style: preserve-3d;
            transition: transform 0.65s cubic-bezier(0.4, 0.2, 0.2, 1);
        }
        .indicadores-flip-inner.flipped {
            transform: rotateY(180deg);
        }
        .indicadores-flip-front {
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .indicadores-flip-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            transform: rotateY(180deg);
        }
        .indicadores-flip-trigger { cursor: pointer; user-select: none; }
        .indicadores-flip-trigger:hover .flip-hint { opacity: 1; transform: translateX(0); }

        /* ── Alertas Flip Card ── */
        .alertas-flip-scene {
            perspective: 1400px;
            width: 100%;
        }
        .alertas-flip-inner {
            position: relative;
            width: 100%;
            transform-style: preserve-3d;
            transition: transform 0.65s cubic-bezier(0.4, 0.2, 0.2, 1);
        }
        .alertas-flip-inner.flipped {
            transform: rotateY(180deg);
        }
        .alertas-flip-front {
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .alertas-flip-back {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            transform: rotateY(180deg);
        }
        .alertas-flip-trigger { cursor: pointer; user-select: none; }
        .alertas-flip-trigger:hover .flip-hint { opacity: 1; transform: translateX(0); }
        /* Cards de receita por empresa */
        .receita-empresa-card {
            background: #fff;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        .receita-empresa-card:hover {
            box-shadow: 0 4px 16px rgba(63,156,174,0.15);
            border-color: #3f9cae;
        }
        .dark .receita-empresa-card {
            background: #1f2937;
            border-color: #374151;
        }
        .dark .receita-empresa-card:hover {
            border-color: #3f9cae;
        }
        .receita-progress-bar {
            height: 8px;
            border-radius: 4px;
            background: #e5e7eb;
            overflow: hidden;
        }
        .dark .receita-progress-bar { background: #374151; }
        .receita-progress-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            transition: width 0.6s ease;
        }

        .cartao-credito-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.75rem;
            background: #f9fafb;
            transition: box-shadow 0.2s;
        }
        .cartao-credito-card:hover {
            box-shadow: 0 2px 8px rgba(63,156,174,0.18);
            border-color: #3f9cae;
        }

        .empresa-day-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.75rem;
            background: #f9fafb;
            transition: box-shadow 0.2s;
        }
        .empresa-day-card:hover {
            box-shadow: 0 2px 8px rgba(63,156,174,0.18);
            border-color: #3f9cae;
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
                    <div x-data="{ filtroRapido: '{{ $filtroRapido }}', mostrarCustom: {{ $filtroRapido === 'custom' ? 'true' : 'false' }}, aplicarFiltro(tipo) { this.filtroRapido = tipo; if (tipo !== 'custom') { this.mostrarCustom = false; const form = this.$refs.formFiltro; const inputInicio = form.querySelector('input[name=inicio]'); const inputFim = form.querySelector('input[name=fim]'); if (inputInicio) inputInicio.disabled = true; if (inputFim) inputFim.disabled = true; setTimeout(() => form.submit(), 10); } else { this.mostrarCustom = true; } } }">
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
                                    @click="aplicarFiltro('mes_anterior')"
                                    :class="filtroRapido === 'mes_anterior' ? 'btn-filtro-rapido ativo' : 'btn-filtro-rapido inativo'"
                                    class="">
                                    Último Mês
                                </button>
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
                {{-- ═══════════════ BANCOS FLIP CARD ═══════════════ --}}
                <div class="bancos-flip-scene" id="bancos-flip-scene">
                <div class="bancos-flip-inner" id="bancos-flip-inner">

                {{-- ═══════════════ FRENTE ═══════════════ --}}
                <div class="bancos-flip-front card-grafico p-6">

                    {{-- HEADER --}}
                    <div class="flex items-center justify-between mb-4">
                        {{-- Título clicável que dispara o flip --}}
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2 bancos-flip-trigger"
                            onclick="flipBancosCard()"
                            title="Ver cartões de crédito">
                            {{-- ÍCONE DE BANCO (PRÉDIO) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Saldo em Bancos
                            <span class="flip-hint flex items-center gap-1 text-xs text-teal-600 font-normal ml-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Cartões
                            </span>
                        </h3>
                        {{-- BOTAO MOSTRAR / OCULTAR (independente do flip) --}}
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
                        @continue($tipo === 'credito')
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

                </div>{{-- /bancos-flip-front --}}

                {{-- ═══════════════ VERSO ═══════════════ --}}
                <div class="bancos-flip-back card-grafico p-6">

                    {{-- HEADER clicável (volta ao saldo) --}}
                    <div class="mb-4 bancos-flip-trigger" onclick="flipBancosCard()" title="Voltar ao Saldo em Bancos">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                {{-- Ícone de cartão de crédito --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Cartões de Crédito
                            </span>
                            <span class="flip-hint flex items-center gap-1 text-xs text-teal-600 font-normal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                </svg>
                                Saldo em Bancos
                            </span>
                        </h3>
                    </div>

                    {{-- CARTÕES DE CRÉDITO --}}
                    @if(isset($cartoesCredito) && !$cartoesCredito->isEmpty())
                    <div class="space-y-3 overflow-y-auto" style="max-height: 360px;">
                        @foreach($cartoesCredito as $cartao)
                        @php
                            $utilizadoPct = $cartao->limite_total > 0
                                ? ($cartao->limite_utilizado / $cartao->limite_total) * 100
                                : 0;
                            $disponivelPct = $cartao->limite_total > 0
                                ? ($cartao->limite_disponivel / $cartao->limite_total) * 100
                                : 100;

                            // Cor da barra: baseada na utilização
                            if ($utilizadoPct < 50) {
                                $barColor = 'bg-green-500';
                            } elseif ($utilizadoPct < 75) {
                                $barColor = 'bg-yellow-400';
                            } else {
                                $barColor = 'bg-red-500';
                            }

                            // Cor do limite disponível: baseada na % disponível
                            if ($disponivelPct > 50) {
                                $limitColor = 'text-green-600';
                            } elseif ($disponivelPct >= 25) {
                                $limitColor = 'text-yellow-600';
                            } else {
                                $limitColor = 'text-red-600';
                            }

                            // Badge de bandeira
                            $bandeira = strtoupper($cartao->bandeira ?? '');
                            $bandeiraBg = match($bandeira) {
                                'VISA'       => 'bg-blue-600',
                                'MASTERCARD' => 'bg-orange-500',
                                'ELO'        => 'bg-yellow-500',
                                'AMEX'       => 'bg-green-600',
                                'HIPERCARD'  => 'bg-red-600',
                                default      => 'bg-gray-500',
                            };
                        @endphp
                        <div class="cartao-credito-card">
                            {{-- Linha superior: bandeira + nome + empresa --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold text-white {{ $bandeiraBg }}">
                                        {{ $bandeira ?: 'OUTRO' }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-700">{{ $cartao->nome }}</span>
                                </div>
                                <span class="text-xs text-gray-400 truncate max-w-[100px]" title="{{ $cartao->empresa }}">
                                    {{ $cartao->empresa }}
                                </span>
                            </div>

                            {{-- Barra de utilização --}}
                            <div class="mb-3">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Limite utilizado</span>
                                    <span>{{ number_format($utilizadoPct, 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all"
                                        style="width: {{ min($utilizadoPct, 100) }}%"></div>
                                </div>
                            </div>

                            {{-- Grid de informações --}}
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                <div>
                                    <span class="text-gray-500 block">Limite Total</span>
                                    <span class="font-semibold text-gray-700">
                                        R$ {{ number_format($cartao->limite_total, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Disponível</span>
                                    <span class="font-semibold {{ $limitColor }}">
                                        R$ {{ number_format($cartao->limite_disponivel, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Parcelas em Aberto</span>
                                    <span class="font-semibold text-gray-700">{{ $cartao->parcelas_em_aberto }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Vencimento Fatura</span>
                                    <span class="font-semibold text-gray-700">Dia {{ $cartao->dia_vencimento_fatura }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-500 block">Melhor Dia de Compra</span>
                                    <span class="font-semibold text-gray-700">Dia {{ $cartao->melhor_dia_compra }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-xs text-gray-400 py-8">
                        Nenhum cartão de crédito cadastrado.
                    </div>
                    @endif

                </div>{{-- /bancos-flip-back --}}

                </div>{{-- /bancos-flip-inner --}}
                </div>{{-- /bancos-flip-scene --}}
                {{-- RESUMO FINANCEIRO — flip card --}}
                <div class="resumo-flip-scene" id="resumo-flip-scene">
                <div class="resumo-flip-inner" id="resumo-flip-inner">

                {{-- ═══════════════ FRENTE ═══════════════ --}}
                <div class="resumo-flip-front card-grafico p-6">

                    {{-- HEADER clicável --}}
                    <div class="mb-4 resumo-flip-trigger" onclick="flipResumoCard()" title="Ver por empresa hoje">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center justify-between">
                            <span>📊 Resumo Financeiro</span>
                            <span class="flip-hint flex items-center gap-1 text-xs text-teal-600 font-normal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Por empresa
                            </span>
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
                        <span class="text-xs text-gray-500 uppercase block mb-1">
                            Previsto
                        </span>
                        <p class="text-xs text-gray-400 mb-2">Contratos + Orçamentos ativos</p>
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
                        <span class="text-xs text-gray-500 uppercase block mb-1">
                            Situação
                        </span>
                        <p class="text-xs text-amber-600 mb-2">⚠ Total global — independente do filtro de período</p>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <span class="text-xs text-gray-500">Receitas Atrasadas</span>
                                <p class="font-bold text-amber-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('situacao_receitas_atrasadas')">
                                    R$ {{ number_format($receitasAtrasadas, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Despesas Atrasadas</span>
                                <p class="font-bold text-red-600 cursor-pointer hover:underline" onclick="mostrarLancamentos('situacao_atrasado')">
                                    R$ {{ number_format($despesasAtrasadas, 2, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Diferença</span>
                                <p class="font-bold {{ $saldoSituacao >= 0 ? 'text-green-600' : 'text-red-700' }}">
                                    R$ {{ number_format($saldoSituacao, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>{{-- /resumo-flip-front --}}

                {{-- ═══════════════ VERSO ═══════════════ --}}
                <div class="resumo-flip-back card-grafico p-6">

                    {{-- HEADER clicável (volta) --}}
                    <div class="mb-4 resumo-flip-trigger" onclick="flipResumoCard()" title="Voltar ao resumo financeiro">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center justify-between">
                            <span>🏢 Hoje por Empresa</span>
                            <span class="flip-hint flex items-center gap-1 text-xs text-teal-600 font-normal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                </svg>
                                Resumo geral
                            </span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</p>
                    </div>

                    {{-- CARDS DAS EMPRESAS — 2 colunas no mobile, 2 no desktop (4 empresas = 2×2) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($hojeResumoPorEmpresa as $emp)
                        <div class="empresa-day-card">
                            <p class="text-xs font-semibold text-gray-700 mb-2 truncate" title="{{ $emp['nome'] }}">
                                {{ $emp['nome'] }}
                            </p>
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="text-gray-500">A Receber</span>
                                <span class="font-bold {{ $emp['a_receber'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    R$ {{ number_format($emp['a_receber'], 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-xs mb-2">
                                <span class="text-gray-500">A Pagar</span>
                                <span class="font-bold {{ $emp['a_pagar'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                    R$ {{ number_format($emp['a_pagar'], 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200 pt-1 flex justify-between items-center text-xs">
                                <span class="text-gray-500 font-medium">Saldo do dia</span>
                                <span class="font-bold {{ $emp['saldo'] >= 0 ? 'text-teal-700' : 'text-red-700' }}">
                                    R$ {{ number_format($emp['saldo'], 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-2 text-center text-xs text-gray-400 py-6">
                            Nenhuma empresa ativa encontrada.
                        </div>
                        @endforelse
                    </div>

                    {{-- Totalizador do dia --}}
                    @php
                        $totalReceberHoje = collect($hojeResumoPorEmpresa)->sum('a_receber');
                        $totalPagarHoje   = collect($hojeResumoPorEmpresa)->sum('a_pagar');
                        $saldoTotalHoje   = $totalReceberHoje - $totalPagarHoje;
                    @endphp
                    <div class="border-t border-gray-200 mt-4 pt-3">
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <div>
                                <span class="block text-gray-400">Total Receber</span>
                                <span class="font-bold text-green-600">R$ {{ number_format($totalReceberHoje, 2, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Total Pagar</span>
                                <span class="font-bold text-red-600">R$ {{ number_format($totalPagarHoje, 2, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Saldo</span>
                                <span class="font-bold {{ $saldoTotalHoje >= 0 ? 'text-teal-700' : 'text-red-700' }}">R$ {{ number_format($saldoTotalHoje, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                </div>{{-- /resumo-flip-back --}}

                </div>{{-- /resumo-flip-inner --}}
                </div>{{-- /resumo-flip-scene --}}
            </div>

            {{-- ================= FLIP: CUSTOS POR CATEGORIA (Frente) / RECEITAS POR EMPRESA (Verso) ================= --}}
            <div class="categorias-flip-scene mb-10">
            <div class="categorias-flip-inner" id="categorias-flip-inner">

            {{-- ========== FRENTE: Custos por Categorias ========== --}}
            <div class="categorias-flip-front">
                <div class="filters-card p-6 mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-gray-700 dark:text-gray-300 font-semibold text-sm">Custos por Categorias:</span>
                            <button type="button" class="btn-filtro-rapido ativo btn-nivel-categoria" data-nivel="categoria">Categorias</button>
                            <button type="button" class="btn-filtro-rapido inativo btn-nivel-categoria" data-nivel="subcategoria">Subcategorias</button>
                            <button type="button" class="btn-filtro-rapido inativo btn-nivel-categoria" data-nivel="conta">Contas</button>
                        </div>
                        <button onclick="flipCategoriasCard()" class="categorias-flip-trigger inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-semibold hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="flip-hint">Receitas por Empresa</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    @foreach($dadosCentros as $centro => $dados)
                    <div class="card-grafico p-6 flex flex-col items-center">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 text-center">
                            Gastos por <span id="grafico-nivel-{{ Str::slug($centro) }}">Categoria</span><br><span class="text-xs text-gray-500 dark:text-gray-400">{{ $centro }}</span>
                        </h3>
                        <div class="w-full flex-1 flex items-center justify-center">
                            <canvas class="grafico-categoria" data-centro="{{ $centro }}" id="grafico-categoria-{{ Str::slug($centro) }}" width="220" height="220" style="cursor:pointer;"></canvas>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>{{-- /categorias-flip-front --}}

            {{-- ========== VERSO: Receitas por Empresas ========== --}}
            <div class="categorias-flip-back">
                {{-- Header --}}
                <div class="filters-card p-6 mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">Receitas por Empresa</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Período: {{ $inicio->format('d/m/Y') }} → {{ $fim->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            {{-- Totalizador rápido --}}
                            @if($receitasPorEmpresa->isNotEmpty())
                            <div class="hidden sm:flex items-center gap-4 text-xs font-semibold">
                                <span class="flex items-center gap-1 text-emerald-700 dark:text-emerald-400">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                    Realizado: R$ {{ number_format($receitasPorEmpresa->sum('realizada'), 2, ',', '.') }}
                                </span>
                                <span class="flex items-center gap-1 text-blue-700 dark:text-blue-400">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                                    A Receber: R$ {{ number_format($receitasPorEmpresa->sum('a_receber'), 2, ',', '.') }}
                                </span>
                                @if($receitasPorEmpresa->sum('atrasado') > 0)
                                <span class="flex items-center gap-1 text-red-700 dark:text-red-400">
                                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                    Atrasado: R$ {{ number_format($receitasPorEmpresa->sum('atrasado'), 2, ',', '.') }}
                                </span>
                                @endif
                            </div>
                            @endif
                            <button onclick="flipCategoriasCard()" class="categorias-flip-trigger inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Voltar a Custos
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Grid de cards por empresa --}}
                @if($receitasPorEmpresa->isEmpty())
                <div class="card-grafico p-10 text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">Nenhuma receita no período selecionado</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Ajuste o filtro de período para visualizar os dados</p>
                </div>
                @else
                @php
                    $coresEmpresas = [
                        ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300', 'borda' => 'border-emerald-200 dark:border-emerald-800', 'bar' => '#22c55e'],
                        ['dot' => 'bg-blue-500',    'badge' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',           'borda' => 'border-blue-200 dark:border-blue-800',    'bar' => '#3b82f6'],
                        ['dot' => 'bg-violet-500',  'badge' => 'bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300',   'borda' => 'border-violet-200 dark:border-violet-800', 'bar' => '#8b5cf6'],
                        ['dot' => 'bg-amber-500',   'badge' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',       'borda' => 'border-amber-200 dark:border-amber-800',  'bar' => '#f59e0b'],
                        ['dot' => 'bg-rose-500',    'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300',           'borda' => 'border-rose-200 dark:border-rose-800',    'bar' => '#f43f5e'],
                        ['dot' => 'bg-cyan-500',    'badge' => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300',           'borda' => 'border-cyan-200 dark:border-cyan-800',    'bar' => '#06b6d4'],
                    ];
                    $totalGobal = $receitasPorEmpresa->sum('realizada') + $receitasPorEmpresa->sum('a_receber') ?: 1;
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    @foreach($receitasPorEmpresa as $idx => $empR)
                    @php
                        $cor = $coresEmpresas[$idx % count($coresEmpresas)];
                        $totalEmp   = $empR->realizada + $empR->a_receber;
                        $pctBarra   = $totalEmp > 0 ? min(100, round(($empR->realizada / $totalEmp) * 100)) : 0;
                        $pctOrc     = ($empR->realizada > 0) ? round(($empR->realizada_orcamento / max($empR->realizada, 0.01)) * 100) : 0;
                        $pctCont    = 100 - $pctOrc;
                        $valorAnual = $empR->receita_anual;
                    @endphp
                    <div class="receita-empresa-card border {{ $cor['borda'] }}">
                        {{-- Título empresa --}}
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-3 h-3 rounded-full {{ $cor['dot'] }} flex-shrink-0"></span>
                            <span class="font-bold text-gray-800 dark:text-gray-100 text-sm leading-tight">{{ $empR->nome }}</span>
                        </div>

                        {{-- 3 KPIs principais --}}
                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div class="text-center">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5 uppercase tracking-wide font-medium">Realizado</div>
                                <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    R$ {{ number_format($empR->realizada, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-center border-x border-gray-100 dark:border-gray-700">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5 uppercase tracking-wide font-medium">A Receber</div>
                                <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                    R$ {{ number_format($empR->a_receber, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5 uppercase tracking-wide font-medium">Atrasado</div>
                                <div class="text-sm font-bold {{ $empR->atrasado > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400 dark:text-gray-600' }}">
                                    R$ {{ number_format($empR->atrasado, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- Barra de progresso: Realizado / Total período --}}
                        <div class="mb-3">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                <span>Realização no período</span>
                                <span class="font-semibold">{{ $pctBarra }}%</span>
                            </div>
                            <div class="receita-progress-bar">
                                <div class="receita-progress-fill" style="width: {{ $pctBarra }}%; background: {{ $cor['bar'] }};"></div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>

                        {{-- Tipo de receita: Orçamento vs Contrato --}}
                        @if($empR->realizada > 0)
                        <div class="mb-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1.5 font-medium">Composição do Realizado</div>
                            <div class="flex gap-1.5 flex-wrap">
                                @if($empR->realizada_orcamento > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Orçamentos {{ $pctOrc }}%
                                </span>
                                @endif
                                @if($empR->realizada_contrato > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Contratos {{ $pctCont }}%
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Métricas adicionais --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="flex flex-col">
                                <span class="text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wide">% do Total</span>
                                <span class="font-bold text-gray-700 dark:text-gray-200 mt-0.5">
                                    <span class="{{ $cor['badge'] }} px-1.5 py-0.5 rounded-full inline-block">{{ $empR->percentual }}%</span>
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wide">Ticket Médio</span>
                                <span class="font-bold text-gray-700 dark:text-gray-200 mt-0.5">
                                    @if($empR->ticket_medio > 0)
                                        R$ {{ number_format($empR->ticket_medio, 0, ',', '.') }}
                                        <span class="text-gray-400 dark:text-gray-500 font-normal">({{ $empR->qtd_cobrancas }}x)</span>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Receita anual (se diferente do período) --}}
                        @if($valorAnual > 0 && $valorAnual != $empR->realizada)
                        <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400 dark:text-gray-500 font-medium uppercase tracking-wide">Receita Anual {{ now()->year }}</span>
                                <span class="font-bold text-gray-600 dark:text-gray-300">R$ {{ number_format($valorAnual, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>{{-- /categorias-flip-back --}}

            </div>{{-- /categorias-flip-inner --}}
            </div>{{-- /categorias-flip-scene --}}

            {{-- ================= CARDS: A PAGAR PRÓXIMO MÊS (2 colunas) ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- CARD 1: A Pagar Total --}}
                <div class="card-grafico p-6">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <h3 class="text-base font-semibold text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            A Pagar — {{ $nomeProximoMes }}
                        </h3>
                        <span class="text-xs text-gray-400">{{ $diasUteisProximoMes }} dias úteis</span>
                    </div>

                    {{-- Totalizador --}}
                    <div class="flex flex-wrap items-center gap-6 mb-4 p-4 rounded-xl bg-teal-50 border border-teal-100">
                        <div>
                            <span class="text-xs text-gray-500 block">Total a Pagar</span>
                            <span class="text-2xl font-bold text-teal-700">R$ {{ number_format($custoFixoProximoMes, 2, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block">Ticket Médio / dia útil</span>
                            <span class="text-xl font-bold text-teal-600">R$ {{ number_format($ticketMedioCustoFixoGlobal, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Por empresa --}}
                    @if($custoFixoProximoMesPorEmpresa->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($custoFixoProximoMesPorEmpresa as $emp)
                        <div class="p-3 rounded-xl border border-teal-100 bg-white flex flex-col gap-1">
                            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">{{ $emp->nome }}</span>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500">A Pagar</span>
                                <span class="font-bold text-gray-800">R$ {{ number_format($emp->a_pagar, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Ticket / dia útil</span>
                                <span class="font-semibold text-teal-600">R$ {{ number_format($emp->ticket_medio_dia, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                        <p class="text-sm text-gray-400 text-center py-2">Nenhum lançamento a pagar em {{ $nomeProximoMes }}.</p>
                    @endif
                </div>

                {{-- CARD 2: Ticket Líquido (A Pagar − A Receber) --}}
                <div class="card-grafico p-6">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <h3 class="text-base font-semibold text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                            Custo Líquido — {{ $nomeProximoMes }}
                        </h3>
                        <span class="text-xs text-gray-400">{{ $diasUteisProximoMes }} dias úteis</span>
                    </div>

                    {{-- Totalizador --}}
                    <div class="flex flex-wrap items-center gap-4 mb-4 p-4 rounded-xl bg-indigo-50 border border-indigo-100">
                        <div>
                            <span class="text-xs text-gray-500 block">A Pagar</span>
                            <span class="text-lg font-bold text-red-600">R$ {{ number_format($custoFixoProximoMes, 2, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block">A Receber</span>
                            <span class="text-lg font-bold text-emerald-600">R$ {{ number_format($aReceberProximoMes, 2, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block">Ticket Líquido / dia útil</span>
                            <span class="text-xl font-bold text-indigo-700">R$ {{ number_format($ticketLiquidoGlobal, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Por empresa --}}
                    @if($custoFixoProximoMesPorEmpresa->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($custoFixoProximoMesPorEmpresa as $emp)
                        <div class="p-3 rounded-xl border border-indigo-100 bg-white flex flex-col gap-1">
                            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">{{ $emp->nome }}</span>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500">A Pagar</span>
                                <span class="font-bold text-red-600">R$ {{ number_format($emp->a_pagar, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">A Receber</span>
                                <span class="font-semibold text-emerald-600">R$ {{ number_format($emp->a_receber, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-1 mt-0.5">
                                <span class="text-xs text-gray-500">Ticket Líquido / dia útil</span>
                                <span class="font-bold text-indigo-700">R$ {{ number_format($emp->ticket_liquido_dia, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                        <p class="text-sm text-gray-400 text-center py-2">Nenhum lançamento em {{ $nomeProximoMes }}.</p>
                    @endif
                </div>

            </div>{{-- /grid 2 cards A Pagar --}}

            {{-- ================= NOVOS CARDS: INDICADORES E ALERTAS ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

                {{-- CARD 1: INDICADORES INTELIGENTES (flip card) --}}
                <div class="indicadores-flip-scene">
                <div class="indicadores-flip-inner" id="indicadores-flip-inner">

                {{-- ── FRENTE: Indicadores Inteligentes ── --}}
                <div class="indicadores-flip-front card-grafico p-6 flex flex-col gap-6">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Indicadores Inteligentes
                        </h3>
                        <button onclick="flipIndicadoresCard()" class="indicadores-flip-trigger inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="flip-hint">Despesas por Área</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
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
                            <span class="text-xs text-gray-500 font-semibold">Ticket médio de despesas</span>
                            <div class="flex flex-col gap-1 mt-0.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Fixas</span>
                                    <span class="font-bold text-indigo-700">R$ {{ number_format($ticketMedioDespesasFixas, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Variáveis</span>
                                    <span class="font-bold text-pink-600">R$ {{ number_format($ticketMedioDespesasVariaveis, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-gray-100 pt-1 mt-0.5">
                                    <span class="text-xs text-gray-400">Global</span>
                                    <span class="text-sm font-semibold text-blue-700">R$ {{ number_format($ticketMedioDespesas, 2, ',', '.') }}</span>
                                </div>
                            </div>
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
                </div>{{-- /indicadores-flip-front --}}

                {{-- ── VERSO: Despesas por Área (Centro de Custo) ── --}}
                <div class="indicadores-flip-back card-grafico p-6">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">Despesas por Área</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Período: {{ $inicio->format('d/m/Y') }} → {{ $fim->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <button onclick="flipIndicadoresCard()" class="indicadores-flip-trigger inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span class="flip-hint">Indicadores</span>
                        </button>
                    </div>
                    <div class="space-y-3">
                        @foreach($dadosCentros as $centro => $dados)
                        @php $totalCentro = collect($dados['categorias'])->sum('total'); @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-100 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 flex-shrink-0"></span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">{{ $centro }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-indigo-700 dark:text-indigo-300 text-sm">R$ {{ number_format($totalCentro, 2, ',', '.') }}</span>
                                @if(count($dados['categorias']) > 0)
                                <div class="text-xs text-gray-400 mt-0.5">{{ count($dados['categorias']) }} {{ count($dados['categorias']) === 1 ? 'categoria' : 'categorias' }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @if(count($dadosCentros) === 0)
                            <div class="text-center text-gray-400 text-sm py-4">Nenhuma despesa registrada no período.</div>
                        @endif
                    </div>
                </div>{{-- /indicadores-flip-back --}}

                </div>{{-- /indicadores-flip-inner --}}
                </div>{{-- /indicadores-flip-scene --}}

                {{-- CARD 2: ALERTAS E INSIGHTS AUTOMÁTICOS (flip card) --}}
                <div class="alertas-flip-scene">
                <div class="alertas-flip-inner" id="alertas-flip-inner">

                {{-- ── FRENTE: Alertas e Insights ── --}}
                <div class="alertas-flip-front card-grafico p-6 flex flex-col gap-4">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            Alertas e Insights Automáticos
                        </h3>
                        <button onclick="flipAlertasCard()" class="alertas-flip-trigger inline-flex items-center gap-1.5 px-3 py-1.5 bg-pink-50 dark:bg-pink-900/30 text-pink-700 dark:text-pink-300 rounded-full text-xs font-semibold hover:bg-pink-100 dark:hover:bg-pink-900/50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="flip-hint">Saúde por Empresa</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
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
                                    <span class="text-gray-700 dark:text-gray-300">{!! $alerta['mensagem'] !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>{{-- /alertas-flip-front --}}

                {{-- ── VERSO: Saúde Financeira por Empresa ── --}}
                <div class="alertas-flip-back card-grafico p-6">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/40 flex items-center justify-center">
                                <svg class="w-5 h-5 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">Saúde Financeira por Empresa</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Período: {{ $inicio->format('d/m/Y') }} → {{ $fim->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <button onclick="flipAlertasCard()" class="alertas-flip-trigger inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span class="flip-hint">Alertas</span>
                        </button>
                    </div>
                    <div class="space-y-3">
                        @forelse($receitasPorEmpresa as $emp)
                        @php
                            $saudeEmpresa = $emp->atrasado == 0 ? 'saudavel' : ($emp->atrasado < $emp->realizada * 0.2 ? 'atencao' : 'critico');
                            $corSaude = match($saudeEmpresa) {
                                'saudavel' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 dark:border-emerald-800',
                                'atencao'  => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-100 dark:border-yellow-800',
                                'critico'  => 'bg-red-50 dark:bg-red-900/20 border-red-100 dark:border-red-800',
                            };
                            $dotCor = match($saudeEmpresa) {
                                'saudavel' => 'bg-emerald-500',
                                'atencao'  => 'bg-yellow-500',
                                'critico'  => 'bg-red-500',
                            };
                        @endphp
                        <div class="p-3 rounded-xl border {{ $corSaude }}">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $dotCor }} flex-shrink-0"></span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $emp->nome }}</span>
                                </div>
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-400">{{ $emp->percentual }}%</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pl-4">
                                <span>Receita: <b class="text-gray-700 dark:text-gray-300">R$ {{ number_format($emp->realizada, 0, ',', '.') }}</b></span>
                                @if($emp->atrasado > 0)
                                    <span class="text-red-600 dark:text-red-400 font-semibold">Atraso: R$ {{ number_format($emp->atrasado, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Em dia</span>
                                @endif
                            </div>
                        </div>
                        @empty
                            <div class="text-center text-gray-400 text-sm py-4">Nenhuma empresa com movimentação no período.</div>
                        @endforelse
                    </div>
                </div>{{-- /alertas-flip-back --}}

                </div>{{-- /alertas-flip-inner --}}
                </div>{{-- /alertas-flip-scene --}}

            </div>{{-- /grid indicadores-alertas --}}


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
        const lancamentosReceitasAtrasadas = @json($lancamentosReceitasAtrasadas ?? []);
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
            } else if (tipo === 'situacao_receitas_atrasadas') {
                lista = lancamentosReceitasAtrasadas;
                titulo = 'Receitas Atrasadas';
                totalColor = 'text-amber-600';
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
                if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_receitas_atrasadas') {
                    thExtra = `<th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Empresa</th>`;
                } else if (tipo === 'despesa' || tipo === 'previsto_pagar' || tipo === 'situacao_atrasado') {
                    thExtra = `<th class='px-4 py-3 text-left uppercase' style='font-size: 14px; font-weight: 600; color: rgb(17, 24, 39);'>Centro de Custo</th>`;
                }

                let thCnpj = '';
                if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_receitas_atrasadas') {
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
                                ${(tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_receitas_atrasadas') ? `<td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.empresa ?? '-'}</td>` : ''}
                                ${(tipo === 'despesa' || tipo === 'previsto_pagar' || tipo === 'situacao_atrasado') ? `<td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.centro_custo ?? '-'}</td>` : ''}
                                <td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.cliente ?? '-'}</td>
                                ${(tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_receitas_atrasadas') ? `<td class='px-4 py-3 text-sm' style='font-weight: 400; color: rgb(17, 24, 39); font-family: Inter, sans-serif;'>${l.cnpjcpf ?? '-'}</td>` : ''}
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

        // ── Flip Card: Resumo Financeiro ──────────────────────────────────
        function flipResumoCard() {
            const inner = document.getElementById('resumo-flip-inner');
            const back  = inner.querySelector('.resumo-flip-back');

            inner.classList.toggle('flipped');
            const mostrandoVerso = inner.classList.contains('flipped');

            if (mostrandoVerso) {
                // Verso é position:absolute — precisamos forçar uma altura mínima
                // para que o container não colapse enquanto a frente fica invisível
                inner.style.minHeight = back.scrollHeight + 'px';
            } else {
                // Voltou para a frente: remove a altura forçada, frente volta ao fluxo normal
                inner.style.minHeight = '';
            }
        }

        // ── Flip Card: Saldo em Bancos ──────────────────────────────────
        function flipBancosCard() {
            const inner = document.getElementById('bancos-flip-inner');
            const back  = inner.querySelector('.bancos-flip-back');

            inner.classList.toggle('flipped');
            const mostrandoVerso = inner.classList.contains('flipped');

            if (mostrandoVerso) {
                // Verso é position:absolute — força altura mínima para não colapsar
                inner.style.minHeight = back.scrollHeight + 'px';
            } else {
                // Voltou para a frente: remove a altura forçada
                inner.style.minHeight = '';
            }
        }

        // ── Flip Card: Custos por Categorias / Receitas por Empresas ──────────────────────────────────
        function flipCategoriasCard() {
            const inner = document.getElementById('categorias-flip-inner');
            const back  = inner.querySelector('.categorias-flip-back');

            inner.classList.toggle('flipped');
            const mostrandoVerso = inner.classList.contains('flipped');

            if (mostrandoVerso) {
                inner.style.minHeight = back.scrollHeight + 'px';
            } else {
                inner.style.minHeight = '';
            }
        }

        // ── Flip Card: Indicadores Inteligentes / Despesas por Empresa ────────────────────────────────
        function flipIndicadoresCard() {
            const inner = document.getElementById('indicadores-flip-inner');
            const back  = inner.querySelector('.indicadores-flip-back');
            inner.classList.toggle('flipped');
            if (inner.classList.contains('flipped')) {
                inner.style.minHeight = back.scrollHeight + 'px';
            } else {
                inner.style.minHeight = '';
            }
        }

        // ── Flip Card: Alertas e Insights / Saúde Financeira por Empresa ─────────────────────────────
        function flipAlertasCard() {
            const inner = document.getElementById('alertas-flip-inner');
            const back  = inner.querySelector('.alertas-flip-back');
            inner.classList.toggle('flipped');
            if (inner.classList.contains('flipped')) {
                inner.style.minHeight = back.scrollHeight + 'px';
            } else {
                inner.style.minHeight = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const inner = document.getElementById('resumo-flip-inner');
            if (!inner) return;
            // Pré-mede altura do verso (está em position:absolute, tem scrollHeight no DOM)
            // e garante que ao virar não haverá salto visual
            const back = inner.querySelector('.resumo-flip-back');
            if (back) {
                // Nada a fazer no load: a frente está em fluxo normal e define a altura
                // O verso só precisa de altura explícita quando estiver ativo
            }
        });

        function getTipoInfo(tipo) {
            const info = {
                'receita': { lista: lancamentosReceita, titulo: 'Lançamentos de Receita', cor: '#16a34a' },
                'despesa': { lista: lancamentosDespesa, titulo: 'Lançamentos de Despesa', cor: '#dc2626' },
                'previsto_receber': { lista: lancamentosPrevistoReceber, titulo: 'Lançamentos a Receber', cor: '#16a34a' },
                'previsto_pagar': { lista: lancamentosPrevistoPagar, titulo: 'Lançamentos a Pagar', cor: '#dc2626' },
                'situacao_atrasado': { lista: lancamentosAtrasado, titulo: 'Despesas Atrasadas', cor: '#dc2626' },
                'situacao_receitas_atrasadas': { lista: lancamentosReceitasAtrasadas, titulo: 'Receitas Atrasadas', cor: '#d97706' }
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
            if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_receitas_atrasadas') {
                thExtra = '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Empresa</th>';
            } else if (tipo === 'despesa' || tipo === 'previsto_pagar' || tipo === 'situacao_atrasado') {
                thExtra = '<th style="background-color: #f5f5f5; padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 11px;">Centro de Custo</th>';
            }

            let thCnpj = '';
            if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_receitas_atrasadas') {
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
                    if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_receitas_atrasadas') {
                        tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.empresa || '-') + '</td>';
                    } else if (tipo === 'despesa' || tipo === 'previsto_pagar' || tipo === 'situacao_atrasado') {
                        tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.centro_custo || '-') + '</td>';
                    }
                    tableRows += '<td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">' + (l.cliente || '-') + '</td>';
                    if (tipo === 'receita' || tipo === 'previsto_receber' || tipo === 'situacao_receitas_atrasadas') {
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

            // Datasets definidos como variável para acesso no onHover
            const fluxoDatasets = [{
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
            ];

            // Rastreia qual stack está sendo hovereada para filtrar o tooltip
            let fluxoHoverStack = null;

            // Formatação monetária brasileira
            const fmtBRL = v => 'R$ ' + (parseFloat(v) || 0).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            new Chart(document.getElementById('chartFinanceiroMensal'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: fluxoDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onHover: function(event, activeElements) {
                        if (activeElements && activeElements.length > 0) {
                            fluxoHoverStack = fluxoDatasets[activeElements[0].datasetIndex].stack;
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + (parseFloat(value) || 0).toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            // Exibe apenas os datasets da stack hovereada (receita OU despesa)
                            filter: function(item) {
                                if (!fluxoHoverStack) return true;
                                return item.dataset.stack === fluxoHoverStack;
                            },
                            callbacks: {
                                title: function(context) {
                                    return context[0].label.toUpperCase();
                                },
                                label: function(context) {
                                    return `${context.dataset.label}: ${fmtBRL(context.raw)}`;
                                },
                                footer: function(context) {
                                    let total = 0;
                                    context.forEach(item => { total += parseFloat(item.raw) || 0; });
                                    const stack = context[0]?.dataset?.stack;
                                    const label = stack === 'receita' ? 'Total Receita' : 'Total Despesa';
                                    return [
                                        '─────────────────────────',
                                        `${label} : ${fmtBRL(total)}`
                                    ];
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
