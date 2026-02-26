<x-app-layout>
    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @vite('resources/css/financeiro/contasareceber.css')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Receber']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">

            {{-- TÍTULO --}}
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-2-2m2 2l2-2" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Contas a Receber
                </h2>
            </div>

            {{-- BOTÃO VOLTAR --}}
            <x-button href="{{ route('financeiro.dashboard') }}" variant="light" size="sm" title="Voltar para Dashboard Financeiro">
                <x-slot name="iconLeft">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </x-slot>
                Voltar
            </x-button>
        </div>
    </x-slot><br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= NAVEGAÇÃO ================= --}}
            <div class="section-card financeiro-nav">
                {{-- BANCOS --}}
                <x-button href="{{ route('financeiro.contas-financeiras.index') }}" variant="warning" size="sm">
                    <x-slot name="iconLeft">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </x-slot>
                    Bancos
                </x-button>

                {{-- COBRANÇA --}}
                <x-button href="{{ route('financeiro.cobrar') }}" variant="primary" size="sm">
                    <x-slot name="iconLeft">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot>
                    Cobrar
                </x-button>

                {{-- CONTAS A RECEBER --}}
                <x-button href="{{ route('financeiro.contasareceber') }}" variant="success" size="sm">
                    <x-slot name="iconLeft">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-slot>
                    Receber
                </x-button>

                {{-- CONTAS A PAGAR --}}
                <x-button href="{{ route('financeiro.contasapagar') }}" variant="danger" size="sm">
                    <x-slot name="iconLeft">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-slot>
                    Pagar
                </x-button>

                {{-- MOVIMENTAÇÃO --}}
                <x-button href="{{ route('financeiro.movimentacao') }}" variant="info" size="sm">
                    <x-slot name="iconLeft">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </x-slot>
                    Extrato
                </x-button>
            </div>

            {{-- ================= FILTROS ================= --}}
            <x-card class="mb-6">
                <form method="GET" action="{{ route('financeiro.contasareceber') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

                    {{-- Busca --}}
                    <div class="filter-group relative">
                        <x-form-input name="search" label="Buscar" placeholder="Cliente ou descrição..." :value="request('search')" />
                        <input type="hidden" name="cliente_id" id="busca-cliente-id" value="{{ request('cliente_id') }}">
                        <div id="autocomplete-cliente" class="absolute left-0 right-0 z-10 bg-white border border-gray-200 rounded shadow max-h-40 overflow-y-auto hidden" style="top: 70px;"></div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Busca cliente dentro do campo Buscar
                            const inputBusca = document.getElementById('search');
                            const lista = document.getElementById('autocomplete-cliente');
                            const inputHidden = document.getElementById('busca-cliente-id');
                            let clientes = [];

                            clientes = [
                                @foreach(\App\Models\Cliente::where('ativo', true)->orderBy('nome_fantasia')->get() as $cliente)
                                { id: {{ $cliente->id }}, nome: @json($cliente->nome_fantasia ?? $cliente->nome ?? $cliente->razao_social) },
                                @endforeach
                            ];

                            function renderLista(filtro = '') {
                                lista.innerHTML = '';
                                if (!filtro || filtro.length < 2) {
                                    lista.classList.add('hidden');
                                    return;
                                }
                                const filtrados = clientes.filter(c => c.nome && c.nome.toLowerCase().includes(filtro.toLowerCase()));
                                if (filtrados.length === 0) {
                                    lista.innerHTML = '<span class="block text-gray-400 text-sm px-3 py-2">Nenhum cliente encontrado</span>';
                                    lista.classList.remove('hidden');
                                    return;
                                }
                                filtrados.forEach(c => {
                                    const div = document.createElement('div');
                                    div.className = 'px-3 py-2 cursor-pointer hover:bg-emerald-50 text-sm';
                                    div.textContent = c.nome;
                                    div.onclick = () => {
                                        inputHidden.value = c.id;
                                        inputBusca.value = c.nome;
                                        lista.classList.add('hidden');
                                        setTimeout(() => { inputBusca.form.submit(); }, 100);
                                    };
                                    lista.appendChild(div);
                                });
                                lista.classList.remove('hidden');
                            }

                            inputBusca.addEventListener('input', e => {
                                renderLista(e.target.value);
                                // Limpa cliente_id se o texto não corresponder a nenhum cliente
                                if (!clientes.some(c => c.nome && c.nome.toLowerCase() === e.target.value.toLowerCase())) {
                                    inputHidden.value = '';
                                }
                            });
                            inputBusca.addEventListener('focus', () => renderLista(inputBusca.value));
                            inputBusca.addEventListener('blur', () => setTimeout(() => lista.classList.add('hidden'), 150));
                        });
                        </script>
                    </div>

                    {{-- Empresa --}}
                    <div class="filter-group">
                        <x-form-select name="empresa_id" label="Empresa" placeholder="Todas as Empresas" onchange="this.form.submit()">
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome_fantasia }}
                            </option>
                            @endforeach
                        </x-form-select>
                    </div>

                    {{-- Navegação de Meses --}}
                    <div class="filter-group lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Navegação Rápida</label>
                        <div class="flex items-center gap-2">
                            @php
                                $hoje = \Carbon\Carbon::today();
                                $ontem = \Carbon\Carbon::yesterday();
                                $amanha = \Carbon\Carbon::tomorrow();
                                $dataAtual = request('vencimento_inicio')
                                    ? \Carbon\Carbon::parse(request('vencimento_inicio'))
                                    : \Carbon\Carbon::now();
                                $mesAnterior = $dataAtual->copy()->subMonth();
                                $proximoMes = $dataAtual->copy()->addMonth();
                                $meses = [
                                    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                                ];
                                $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                            @endphp

                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), [
                                'vencimento_inicio' => $ontem->format('Y-m-d'),
                                'vencimento_fim' => $ontem->format('Y-m-d')
                            ])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $ontem->format('Y-m-d') && request('vencimento_fim') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Ontem
                            </a>
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), [
                                'vencimento_inicio' => $hoje->format('Y-m-d'),
                                'vencimento_fim' => $hoje->format('Y-m-d')
                            ])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $hoje->format('Y-m-d') && request('vencimento_fim') == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Hoje
                            </a>
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), [
                                'vencimento_inicio' => $amanha->format('Y-m-d'),
                                'vencimento_fim' => $amanha->format('Y-m-d')
                            ])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $amanha->format('Y-m-d') && request('vencimento_fim') == $amanha->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Amanhã
                            </a>
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), [
                                'vencimento_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'),
                                'vencimento_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')
                            ])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                                title="Mês Anterior">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <div class="flex-1 text-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm">
                                {{ $mesAtualNome }}
                            </div>
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), [
                                'vencimento_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'),
                                'vencimento_fim' => $proximoMes->endOfMonth()->format('Y-m-d')
                            ])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                                title="Próximo Mês">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Período Personalizado (colapsável) --}}
                <div x-data="{ mostrarPeriodo: false }" class="mb-4">
                    <button
                        type="button"
                        @click="mostrarPeriodo = !mostrarPeriodo"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="mostrarPeriodo ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span x-text="mostrarPeriodo ? 'Ocultar período personalizado' : 'Escolher período personalizado'"></span>
                    </button>
                    @php
                        $dataAtual = request('vencimento_inicio') ? \Carbon\Carbon::parse(request('vencimento_inicio')) : \Carbon\Carbon::now();
                        $inicioPadrao = $dataAtual->copy()->startOfMonth()->format('Y-m-d');
                        $fimPadrao = $dataAtual->copy()->endOfMonth()->format('Y-m-d');
                        $vencimentoInicio = request('vencimento_inicio') ?? $inicioPadrao;
                        $vencimentoFim = request('vencimento_fim') ?? $fimPadrao;
                    @endphp
                    <div x-show="mostrarPeriodo" x-transition class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                            <input type="date" name="vencimento_inicio" id="vencimento_inicio_receber" value="{{ $vencimentoInicio }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                            <input type="date" name="vencimento_fim" id="vencimento_fim_receber" value="{{ $vencimentoFim }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const inicio = document.getElementById('vencimento_inicio_receber');
                            const fim = document.getElementById('vencimento_fim_receber');
                            if (inicio && fim) {
                                inicio.addEventListener('change', function () {
                                    if (inicio.value) {
                                        const data = new Date(inicio.value);
                                        data.setDate(data.getDate() + 1);
                                        const nextDay = data.toISOString().slice(0, 10);
                                        fim.value = nextDay;
                                    }
                                });
                            }
                        });
                    </script>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center gap-3">

                    {{-- Grupo de Filtros Rápidos (Esquerda) --}}
                    <div class="flex flex-wrap gap-2 flex-1">
                        <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('status'), ['status' => ['pendente']])) }}"
                            class="quick-filter-btn status-pendente {{ in_array('pendente', request('status', [])) ? 'active' : '' }}">
                            <span>Pendente</span>
                            <span class="count">{{ $contadoresStatus['pendente'] }}</span>
                        </a>
                        <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('status'), ['status' => ['vencido']])) }}"
                            class="quick-filter-btn status-vencido {{ in_array('vencido', request('status', [])) ? 'active' : '' }}">
                            <span>Vencido</span>
                            <span class="count">{{ $contadoresStatus['vencido'] }}</span>
                        </a>
                        <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('tipo'), ['tipo' => 'orcamento'])) }}"
                            class="quick-filter-btn {{ request('tipo') == 'orcamento' ? 'active' : '' }}">
                            <span>Orçamento</span>
                            <span class="count">{{ $contadoresTipo['orcamento'] }}</span>
                        </a>
                        <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('tipo'), ['tipo' => 'contrato'])) }}"
                            class="quick-filter-btn {{ request('tipo') == 'contrato' ? 'active' : '' }}">
                            <span>Contrato</span>
                            <span class="count">{{ $contadoresTipo['contrato'] }}</span>
                        </a>
                        <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('nota_fiscal'), ['nota_fiscal' => 1])) }}"
                            class="quick-filter-btn {{ request('nota_fiscal') == '1' ? 'active' : '' }}">
                            <span>Nota Fiscal</span>
                            <span class="count">{{ $contadoresNotaFiscal ?? 0 }}</span>
                        </a>
                    </div>

                    {{-- Grupo de Botões (Direita) --}}
                    <div class="flex flex-wrap gap-2 lg:justify-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('financeiro.contasareceber') }}" class="btn btn-secondary">Limpar</a>
                        <button
                            type="button"
                            x-data
                            @click="$dispatch('abrir-modal-conta-fixa')"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition shadow-md border border-purple-700/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Receitas Fixas
                        </button>
                    </div>
                </div>
            </form>
            </x-card>


            @if(request('vencimento_inicio') || request('vencimento_fim'))
            <div class="mb-4 px-4 py-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
                Conciliação ativa no período:
                <strong>
                    {{ request('vencimento_inicio') ? \Carbon\Carbon::parse(request('vencimento_inicio'))->format('d/m/Y') : 'início' }}
                    —
                    {{ request('vencimento_fim') ? \Carbon\Carbon::parse(request('vencimento_fim'))->format('d/m/Y') : 'hoje' }}
                </strong>
            </div>
            @endif

            {{-- ================= KPIs ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <x-kpi-card title="A Receber (no período)" :value="'R$ ' . number_format($kpis['a_receber'], 2, ',', '.')" color="blue" />
                <x-kpi-card title="Recebido (no período)" :value="'R$ ' . number_format($kpis['recebido'], 2, ',', '.')" color="green" />
                <x-kpi-card title="Vencido (no período)" :value="'R$ ' . number_format($kpis['vencido'], 2, ',', '.')" color="red" />
                <x-kpi-card title="Vence Hoje" :value="'R$ ' . number_format($kpis['vence_hoje'], 2, ',', '.')" color="yellow" />
            </div>

            {{-- ================= TABELA ================= --}}
            @if($cobrancas->count())
            <div x-data='{
                selecionadas: [],
                valores: @json($cobrancas->map(function($c){ return ["id" => (int)$c->id, "valor" => (float)$c->valor]; })),
                toggleAll(source) {
                    if (source.checked) {
                        this.selecionadas = @json($cobrancas->pluck("id"));
                    } else {
                        this.selecionadas = [];
                    }
                },
                getValorSelecionado() {
                    let total = 0;
                    this.valores.forEach(c => {
                        if (this.selecionadas.map(Number).includes(Number(c.id))) {
                            total += parseFloat(c.valor) || 0;
                        }
                    });
                    return total;
                }
            }' class="table-card">
                <div class="flex justify-end mb-2">
                    <template x-if="selecionadas.length >= 2">
                        <x-button variant="success" size="sm"
                            x-on:click="$dispatch('confirmar-baixa', {
                                action: '{{ route('financeiro.contasareceber.baixa-multipla') }}',
                                empresaId: null,
                                cobrancaIds: selecionadas
                            })">
                            <x-slot name="iconLeft">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </x-slot>
                            Receber Selecionadas
                        </x-button>
                    </template>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" x-on:change="toggleAll($event.target)" style="background:#f3f4f6;border:1.5px solid #d1d5db;border-radius:6px;width:16px;height:16px;box-shadow:0 1px 2px #00000010;appearance:auto;" class="checkbox-explicit">
                                </th>
                                <th>Vencimento</th>
                                <th>Empresa</th>
                                <th>Cliente</th>
                                <th>CNPJ/CPF</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th class="text-right">Valor</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalPagina = 0; @endphp
                            @forelse($cobrancas as $cobranca)
                            @php
                            $totalPagina += $cobranca->valor;
                            $statusClass = $cobranca->status_financeiro;
                            $venceHoje = $cobranca->status !== 'pago' && $cobranca->data_vencimento->isToday();
                            $vencido = $cobranca->status !== 'pago' && $cobranca->data_vencimento->isPast() && !$venceHoje;
                            $linhaClass = '';
                            if ($venceHoje) {
                                $linhaClass = 'bg-green-50 border-l-4 border-green-400';
                                $statusClass = 'vence-hoje';
                            } elseif ($vencido) {
                                $linhaClass = 'bg-red-50 border-l-4 border-red-400';
                            }
                            @endphp
                            <tr class="{{ $linhaClass }}" data-cobranca-id="{{ $cobranca->id }}" data-valor="{{ $cobranca->valor }}">
                                <td>
                                    <input type="checkbox" :value="{{ $cobranca->id }}" x-model.number="selecionadas" style="background:#f3f4f6;border:1.5px solid #d1d5db;border-radius:6px;width:16px;height:16px;box-shadow:0 1px 2px #00000010;appearance:auto;" class="checkbox-explicit">
                                </td>
                                <td data-label="Vencimento">
                                    {{ $cobranca->data_vencimento->format('d/m/Y') }}
                                </td>
                                <td data-label="Empresa">
                                    {{ $cobranca->orcamento?->empresa?->nome_fantasia ?? $cobranca->empresa_relacionada?->nome_fantasia ?? '—' }}
                                </td>
                                <td data-label="Cliente">
                                    {{ $cobranca->cliente?->nome ?? $cobranca->cliente?->nome_fantasia ?? $cobranca->cliente?->razao_social ?? '—' }}
                                </td>
                                <td data-label="CNPJ/CPF" class="whitespace-nowrap">
                                    {{ $cobranca->cliente?->cpf_cnpj_formatado ?? '—' }}
                                </td>
                                <td data-label="Descrição">
                                    {{ $cobranca->descricao }}
                                </td>
                                <td data-label="Tipo">
                                    <x-badge type="{{ $cobranca->tipo === 'contrato' ? 'primary' : 'info' }}" size="xs">
                                        {{ ucfirst($cobranca->tipo) }}
                                    </x-badge>
                                </td>
                                <td data-label="Valor" class="text-right font-semibold whitespace-nowrap">
                                    R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                                </td>
                                <td data-label="Ações">
                                    <div class="table-actions">

                                        {{-- Botão Editar (apenas para contas fixas) --}}
                                        @if($cobranca->conta_fixa_id)
                                        <button
                                            type="button"
                                            x-data
                                            class="btn action-btn btn-icon bg-blue-600 hover:bg-blue-700 text-white transition-transform duration-200 hover:scale-150"
                                            title="Editar Conta Fixa"
                                            @click="$dispatch('editar-conta-fixa', { contaFixaId: {{ $cobranca->conta_fixa_id }} })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        @endif

                                        @if($cobranca->status !== 'pago')
                                        {{-- Botão de Baixar (ícone de check com cifrão) --}}
                                        <button
                                            type="button"
                                            x-data
                                            class="btn action-btn btn-icon bg-green-600 hover:bg-green-700 text-white transition-transform duration-200 hover:scale-150"
                                            title="Confirmar Baixa"
                                            x-on:click="$dispatch('confirmar-baixa', {
    action: '{{ route('financeiro.contasareceber.pagar', $cobranca) }}',
    empresaId: {{ $cobranca->orcamento?->empresa_id ?? 'null' }},
    cobrancaId: {{ $cobranca->id }},
    valorTotal: {{ $cobranca->valor }},
    dataVencimento: '{{ $cobranca->data_vencimento->format('Y-m-d') }}'
})">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        @endif

                                        {{-- Botão Anexos --}}
                                        <button
                                            type="button"
                                            x-data
                                            class="btn action-btn btn-icon bg-gray-600 hover:bg-gray-700 text-white transition-transform duration-200 hover:scale-150 relative"
                                            title="Gerenciar Anexos (NF/Boleto)"
                                            @click="$dispatch('abrir-modal-anexos', { cobrancaId: {{ $cobranca->id }} })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                            </svg>
                                            @if($cobranca->anexos()->count() > 0)
                                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                {{ $cobranca->anexos()->count() }}
                                            </span>
                                            @endif
                                        </button>

                                        {{-- Botão Excluir --}}
                                        @php
                                        $totalParcelasOrcamento = $cobranca->orcamento_id
                                        ? \App\Models\Cobranca::where('orcamento_id', $cobranca->orcamento_id)
                                        ->where('status', '!=', 'pago')
                                        ->count()
                                        : 1;

                                        $totalCobrancasContrato = $cobranca->conta_fixa_id
                                        ? \App\Models\Cobranca::where('conta_fixa_id', $cobranca->conta_fixa_id)
                                        ->where('status', '!=', 'pago')
                                        ->where('data_vencimento', '>=', $cobranca->data_vencimento)
                                        ->count()
                                        : 0;
                                        @endphp
                                        <button
                                            type="button"
                                            x-data
                                            class="btn action-btn btn-icon bg-red-400 hover:bg-red-500 text-white transition-transform duration-200 hover:scale-150"
                                            title="Excluir"
                                            @click="$dispatch('excluir-cobranca', {
                                                cobrancaId: {{ $cobranca->id }},
                                                orcamentoId: {{ $cobranca->orcamento_id ?? 'null' }},
                                                totalParcelas: {{ $totalParcelasOrcamento }},
                                                contaFixaId: {{ $cobranca->conta_fixa_id ?? 'null' }},
                                                totalCobrancasContrato: {{ $totalCobrancasContrato }},
                                                dataVencimento: '{{ $cobranca->data_vencimento }}'
                                            })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-gray-500">
                                    Nenhuma cobrança encontrada para os filtros aplicados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Rodapé com Totais --}}
                <div class="table-footer">
                    <div class="footer-total">
                        <span class="label">Total na Página:</span>
                        <span class="value">R$ {{ number_format($totalPagina, 2, ',', '.' ) }}</span>
                    </div>
                    <div class="footer-total">
                        <span class="label">Total Geral (Filtrado):</span>
                        <span class="value">
                            <template x-if="selecionadas.length > 0">
                                <span>
                                    <span x-text="selecionadas.length"></span> selecionadas —
                                    R$ <span x-text="getValorSelecionado().toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                </span>
                            </template>
                            <template x-if="selecionadas.length === 0">
                                <span>R$ {{ number_format($totalGeralFiltrado, 2, ',', '.') }}</span>
                            </template>
                        </span>
                    </div>
                </div>
            </div>

            <x-pagination :paginator="$cobrancas" label="cobranças" />
            @else
            <x-card :padding="false" class="text-center py-12">
                <p class="text-gray-500">Nenhuma cobrança encontrada para os filtros aplicados.</p>
            </x-card>
            @endif

        </div>
    </div>
    @include('financeiro.partials.modal-confirmar-baixa')
    @include('financeiro.partials.modal-excluir-cobranca')
    @include('financeiro.partials.modal-conta-fixa')
    @include('financeiro.partials.modal-anexos')

</x-app-layout>