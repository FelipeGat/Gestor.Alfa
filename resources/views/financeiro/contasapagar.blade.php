<x-app-layout>
    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @vite('resources/css/financeiro/contasareceber.css')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg text-red-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12V6m0 0l-2 2m2-2l2 2" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Contas a Pagar
                </h2>
            </div>

            <a href="{{ route('financeiro.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-red-600 transition-all shadow-sm group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot><br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- MENSAGENS DE SUCESSO E ERRO --}}
            @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        @foreach($errors->all() as $error)
                        <p class="text-red-800 font-medium">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- NAVEGAÇÃO --}}
            <div class="section-card financeiro-nav">
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-yellow-400 hover:bg-yellow-500 text-black font-bold rounded-lg transition shadow-sm border border-yellow-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Bancos
                </a>

                <a href="{{ route('financeiro.cobrar' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition shadow-md border border-indigo-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Cobrar
                </a>

                <a href="{{ route('financeiro.contasareceber' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-md border border-emerald-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-2-2m2 2l2-2" />
                    </svg>
                    Receber
                </a>

                <a href="{{ route('financeiro.contasapagar') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition shadow-md border border-red-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12V6m0 0l-2 2m2-2l2 2" />
                    </svg>
                    Pagar
                </a>

                <a href="{{ route('financeiro.movimentacao' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition shadow-md border border-blue-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Extrato
                </a>
            </div>

            {{-- FILTROS --}}
            <form method="GET" action="{{ route('financeiro.contasapagar') }}" class="filters-card">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                    </script>
                    <div class="filter-group relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" id="busca-geral" value="{{ request('search') }}"
                            placeholder="Descrição, centro de custo ou fornecedor..."
                            autocomplete="off"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                        <input type="hidden" name="fornecedor_id" id="busca-fornecedor-id" value="{{ request('fornecedor_id') }}">
                        <div id="autocomplete-fornecedor" class="absolute left-0 right-0 z-10 bg-white border border-gray-200 rounded shadow max-h-40 overflow-y-auto hidden"></div>
                    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Busca fornecedor dentro do campo Buscar
    const inputBusca = document.getElementById('busca-geral');
    const lista = document.getElementById('autocomplete-fornecedor');
    const inputHidden = document.getElementById('busca-fornecedor-id');
    let fornecedores = [];

    fornecedores = [
        @foreach(\App\Models\Fornecedor::where('ativo', true)->orderBy('razao_social')->get() as $fornecedor)
        { id: {{ $fornecedor->id }}, nome: @json($fornecedor->razao_social) },
        @endforeach
    ];

    function renderLista(filtro = '') {
        lista.innerHTML = '';
        if (!filtro || filtro.length < 2) {
            lista.classList.add('hidden');
            return;
        }
        const filtrados = fornecedores.filter(f => f.nome.toLowerCase().includes(filtro.toLowerCase()));
        if (filtrados.length === 0) {
            lista.innerHTML = '<span class="block text-gray-400 text-sm px-3 py-2">Nenhum fornecedor encontrado</span>';
            lista.classList.remove('hidden');
            return;
        }
        filtrados.forEach(f => {
            const div = document.createElement('div');
            div.className = 'px-3 py-2 cursor-pointer hover:bg-red-50 text-sm';
            div.textContent = f.nome;
            div.onclick = () => {
                inputHidden.value = f.id;
                inputBusca.value = f.nome;
                lista.classList.add('hidden');
                setTimeout(() => { inputBusca.form.submit(); }, 100);
            };
            lista.appendChild(div);
        });
        lista.classList.remove('hidden');
    }

    inputBusca.addEventListener('input', e => {
        renderLista(e.target.value);
        // Limpa fornecedor_id se o texto não corresponder a nenhum fornecedor
        if (!fornecedores.some(f => f.nome.toLowerCase() === e.target.value.toLowerCase())) {
            inputHidden.value = '';
        }
    });
    inputBusca.addEventListener('focus', () => renderLista(inputBusca.value));
    inputBusca.addEventListener('blur', () => setTimeout(() => lista.classList.add('hidden'), 150));
});
</script>

                    <div class="filter-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Centro de Custo</label>
                        <select name="centro_custo_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">Todos</option>
                            @foreach($centrosCusto as $centro)
                            <option value="{{ $centro->id }}" {{ request('centro_custo_id') == $centro->id ? 'selected' : '' }}>
                                {{ $centro->nome }} ({{ $centro->tipo }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categorias</label>
                        <select name="categoria_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">SubCategorias</label>
                        <select name="subcategoria_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($subcategorias as $subcategoria)
                            <option value="{{ $subcategoria->id }}" {{ request('subcategoria_id') == $subcategoria->id ? 'selected' : '' }}>
                                {{ $subcategoria->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contas</label>
                        <select name="conta_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($contasFiltro as $conta)
                            <option value="{{ $conta->id }}" {{ request('conta_id') == $conta->id ? 'selected' : '' }}>
                                {{ $conta->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                                        
                    <div class="filter-group lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Navegação Rápida</label>
                        <div class="flex items-center gap-2">
                            @php
                                $hoje = \Carbon\Carbon::today();
                                $ontem = \Carbon\Carbon::yesterday();
                                $dataAtual = request('vencimento_inicio') ? \Carbon\Carbon::parse(request('vencimento_inicio')) : \Carbon\Carbon::now();
                                $mesAnterior = $dataAtual->copy()->subMonth();
                                $proximoMes = $dataAtual->copy()->addMonth();
                                $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                                $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                            @endphp

                            <a href="{{ route('financeiro.contasapagar', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), [
                                'vencimento_inicio' => $ontem->format('Y-m-d'),
                                'vencimento_fim' => $ontem->format('Y-m-d')
                            ])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $ontem->format('Y-m-d') && request('vencimento_fim') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}"
                                >
                                Ontem
                            </a>
                            <a href="{{ route('financeiro.contasapagar', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), [
                                'vencimento_inicio' => $hoje->format('Y-m-d'),
                                'vencimento_fim' => $hoje->format('Y-m-d')
                            ])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $hoje->format('Y-m-d') && request('vencimento_fim') == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}"
                                >
                                Hoje
                            </a>

                            <a href="{{ route('financeiro.contasapagar', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'), 'vencimento_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>

                            <div class="flex-1 text-center font-bold text-gray-700 bg-white px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                                {{ $mesAtualNome }}
                            </div>

                            <a href="{{ route('financeiro.contasapagar', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'), 'vencimento_fim' => $proximoMes->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <details class="mt-4">
                    <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-red-600 transition">
                        🗓️ Período Personalizado
                    </summary>
                    @php
                        $dataAtual = request('vencimento_inicio') ? \Carbon\Carbon::parse(request('vencimento_inicio')) : \Carbon\Carbon::now();
                        $inicioPadrao = $dataAtual->copy()->startOfMonth()->format('Y-m-d');
                        $fimPadrao = $dataAtual->copy()->endOfMonth()->format('Y-m-d');
                        $vencimentoInicio = request('vencimento_inicio') ?? $inicioPadrao;
                        $vencimentoFim = request('vencimento_fim') ?? $fimPadrao;
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
                            <input type="date" name="vencimento_inicio" id="vencimento_inicio" value="{{ $vencimentoInicio }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                            <input type="date" name="vencimento_fim" id="vencimento_fim" value="{{ $vencimentoFim }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const inicio = document.getElementById('vencimento_inicio');
                            const fim = document.getElementById('vencimento_fim');
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
                </details>

                <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-end justify-between mt-6">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('financeiro.contasapagar', array_merge(request()->except('status'), ['status' => ['em_aberto']])) }}"
                            class="quick-filter-btn status-em-aberto {{ in_array('em_aberto', request('status', [])) ? 'active' : '' }}">
                            <span>Em Aberto</span>
                            <span class="count">{{ $contadoresStatus['em_aberto'] }}</span>
                        </a>
                        <a href="{{ route('financeiro.contasapagar', array_merge(request()->except('status'), ['status' => ['vencido']])) }}"
                            class="quick-filter-btn status-vencido {{ in_array('vencido', request('status', [])) ? 'active' : '' }}">
                            <span>Vencido</span>
                            <span class="count">{{ $contadoresStatus['vencido'] }}</span>
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-2 lg:justify-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('financeiro.contasapagar') }}" class="btn btn-secondary">Limpar</a>
                        <button type="button" x-data @click="$dispatch('abrir-modal-conta-fixa-pagar')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Despesas Fixas
                        </button>
                        <button type="button" x-data @click="$dispatch('abrir-modal-conta-pagar')"
                            class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Despesas Variadas
                        </button>
                    </div>
                </div>
            </form>

            {{-- KPIs --}}
            <div class="kpi-grid mb-6">
                <div class="kpi-card border-blue">
                    <div class="label">A Pagar (no período)</div>
                    <div class="value">R$ {{ number_format($kpis['a_pagar'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-green">
                    <div class="label">Pago (no período)</div>
                    <div class="value">R$ {{ number_format($kpis['pago'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-red">
                    <div class="label">Vencido (no período)</div>
                    <div class="value">R$ {{ number_format($kpis['vencido'], 2, ',', '.') }}</div>
                </div>
                <div class="kpi-card border-yellow">
                    <div class="label">Vence Hoje</div>
                    <div class="value">R$ {{ number_format($kpis['vence_hoje'], 2, ',', '.') }}</div>
                </div>
            </div>

            {{-- TABELA --}}
            @if($contas->count())
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vencimento</th>
                                <th>Fornecedor</th>
                                <th>Centro de Custo</th>
                                <th>Categoria</th>
                                <th>Descrição</th>
                                <th class="text-right">Valor</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalPagina = 0; @endphp
                            @foreach($contas as $conta)
                            @php
                            $totalPagina += $conta->valor;
                            $venceHoje = $conta->status !== 'pago' && $conta->data_vencimento->isToday();
                            $vencido = $conta->status !== 'pago' && $conta->data_vencimento->isPast() && !$venceHoje;
                            $linhaClass = $venceHoje ? 'bg-orange-50 border-l-4 border-orange-400' : ($vencido ? 'bg-red-50 border-l-4 border-red-400' : '');
                            @endphp
                            <tr class="{{ $linhaClass }}">
                                <td data-label="Vencimento">{{ $conta->data_vencimento->format('d/m/Y') }}</td>
                                <td data-label="Fornecedor">
                                    {{ $conta->fornecedor ? ($conta->fornecedor->razao_social ?: $conta->fornecedor->nome_fantasia ?: '-') : '-' }}
                                </td>
                                <td data-label="Centro de Custo">{{ $conta->centroCusto->nome }}</td>
                                <td data-label="Categoria">{{ $conta->conta->nome }}</td>
                                <td data-label="Descrição">{{ $conta->descricao }}</td>
                                <td data-label="Valor" class="text-right font-semibold">R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                                <td data-label="Ações">
                                    <div class="table-actions">
                                        @if($conta->status !== 'pago')
                                        {{-- Botão Confirmar Pagamento --}}
                                        <button type="button" x-data class="btn action-btn btn-icon bg-green-600 hover:bg-green-700 text-white transition-transform duration-200 hover:scale-150"
                                            title="Confirmar Pagamento"
                                            @click="$dispatch('confirmar-pagamento', { 
                                                action: '{{ route('financeiro.contasapagar.pagar', $conta) }}', 
                                                contaId: {{ $conta->id }}, 
                                                valorTotal: {{ $conta->valor }},
                                                dataVencimento: '{{ $conta->data_vencimento->format('Y-m-d') }}'
                                            })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        @endif

                                        {{-- Botão Anexos --}}
                                        <button type="button" x-data class="btn action-btn btn-icon bg-gray-600 hover:bg-gray-700 text-white transition-transform duration-200 hover:scale-150 relative"
                                            title="Gerenciar Anexos (NF/Boleto)"
                                            @click="$dispatch('abrir-modal-anexos-pagar', { contaId: {{ $conta->id }} })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                            </svg>
                                            @if($conta->anexos()->count() > 0)
                                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                {{ $conta->anexos()->count() }}
                                            </span>
                                            @endif
                                        </button>

                                        @if($conta->status !== 'pago')
                                        {{-- Botão Editar --}}
                                        <button type="button" x-data class="btn action-btn btn-icon bg-blue-600 hover:bg-blue-700 text-white transition-transform duration-200 hover:scale-150"
                                            title="Editar {{ $conta->conta_fixa_pagar_id ? 'Despesa Fixa' : 'Conta' }}"
                                            @click="$dispatch('{{ $conta->conta_fixa_pagar_id ? 'editar-conta-fixa-pagar' : 'editar-conta-pagar' }}', { {{ $conta->conta_fixa_pagar_id ? 'contaFixaId' : 'contaId' }}: {{ $conta->conta_fixa_pagar_id ?? $conta->id }} })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        @endif

                                        {{-- Botão Excluir --}}
                                        <button type="button" x-data class="btn action-btn btn-icon bg-red-400 hover:bg-red-500 text-white transition-transform duration-200 hover:scale-150"
                                            title="Excluir Conta"
                                            @click="$dispatch('excluir-conta-pagar', { contaId: {{ $conta->id }}, tipo: '{{ $conta->tipo }}', contaFixaId: {{ $conta->conta_fixa_pagar_id ?? 'null' }} })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="footer-total">
                        <span class="label">Total na Página:</span>
                        <span class="value">R$ {{ number_format($totalPagina, 2, ',', '.' ) }}</span>
                    </div>
                    <div class="footer-total">
                        <span class="label">Total Geral (Filtrado):</span>
                        <span class="value">R$ {{ number_format($totalGeralFiltrado, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{ $contas->links() }}
            @else
            <div class="empty-state">
                <h3 class="empty-state-title">Nenhuma conta encontrada</h3>
            </div>
            @endif

        </div>
    </div>

    @include('financeiro.partials.modal-conta-pagar')
    @include('financeiro.partials.modal-conta-fixa-pagar')
    @include('financeiro.partials.modal-confirmar-pagamento')
    @include('financeiro.partials.modal-excluir-conta-pagar')
    @include('financeiro.partials.modal-anexos-pagar')

</x-app-layout>