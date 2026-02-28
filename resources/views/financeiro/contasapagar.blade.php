<x-app-layout>

    @push('styles')
    @vite('resources/css/financeiro/contasareceber.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Pagar']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            {{-- FILTROS --}}
            @php
                $hoje = \Carbon\Carbon::today();
                $ontem = \Carbon\Carbon::yesterday();
                $amanha = \Carbon\Carbon::tomorrow();
                $dataAtual = request('vencimento_inicio') ? \Carbon\Carbon::parse(request('vencimento_inicio')) : \Carbon\Carbon::now();
                $mesAnterior = $dataAtual->copy()->subMonth();
                $proximoMes = $dataAtual->copy()->addMonth();
                $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                $mesAtualNome = $meses[$dataAtual->month] . '/' . $dataAtual->year;
                $vencimentoInicio = request('vencimento_inicio') ?? $dataAtual->copy()->startOfMonth()->format('Y-m-d');
                $vencimentoFim = request('vencimento_fim') ?? $dataAtual->copy()->endOfMonth()->format('Y-m-d');
            @endphp

            <form method="GET" action="{{ route('financeiro.contasapagar') }}"
                class="bg-white rounded-lg p-6"
                style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-3 relative">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Buscar</label>
                        <input type="text" name="search" id="busca-geral" value="{{ request('search') }}"
                            placeholder="Descrição, centro de custo ou fornecedor..."
                            autocomplete="off"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <input type="hidden" name="fornecedor_id" id="busca-fornecedor-id" value="{{ request('fornecedor_id') }}">
                        <div id="autocomplete-fornecedor" class="absolute left-0 right-0 z-10 bg-white border border-gray-200 rounded shadow max-h-40 overflow-y-auto hidden"></div>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const inputBusca = document.getElementById('busca-geral');
                        const lista = document.getElementById('autocomplete-fornecedor');
                        const inputHidden = document.getElementById('busca-fornecedor-id');
                        let fornecedores = [];

                        fornecedores = [
                            @foreach($fornecedores as $fornecedor)
                            { id: {{ $fornecedor->id }}, nome: @json($fornecedor->razao_social ?: $fornecedor->nome_fantasia ?: '-') },
                            @endforeach
                        ];

                        function renderLista(filtro = '') {
                            lista.innerHTML = '';
                            if (!filtro || filtro.length < 2) {
                                lista.classList.add('hidden');
                                return;
                            }
                            const filtrados = fornecedores.filter(f => f.nome && f.nome.toLowerCase().includes(filtro.toLowerCase()));
                            if (filtrados.length === 0) {
                                lista.innerHTML = '<span class="block text-gray-400 text-sm px-3 py-2">Nenhum fornecedor encontrado</span>';
                                lista.classList.remove('hidden');
                                return;
                            }
                            filtrados.forEach(f => {
                                const div = document.createElement('div');
                                div.className = 'px-3 py-2 cursor-pointer hover:bg-emerald-50 text-sm';
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
                            if (!fornecedores.some(f => f.nome && f.nome.toLowerCase() === e.target.value.toLowerCase())) {
                                inputHidden.value = '';
                            }
                        });
                        inputBusca.addEventListener('focus', () => renderLista(inputBusca.value));
                        inputBusca.addEventListener('blur', () => setTimeout(() => lista.classList.add('hidden'), 150));
                    });
                    </script>

                    <div class="md:col-span-2">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Centro de Custo</label>
                        <select name="centro_custo_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todos</option>
                            @foreach($centrosCusto as $centro)
                            <option value="{{ $centro->id }}" {{ request('centro_custo_id') == $centro->id ? 'selected' : '' }}>
                                {{ $centro->nome }} ({{ $centro->tipo }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Categorias</label>
                        <select name="categoria_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">SubCategorias</label>
                        <select name="subcategoria_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($subcategorias as $subcategoria)
                            <option value="{{ $subcategoria->id }}" {{ request('subcategoria_id') == $subcategoria->id ? 'selected' : '' }}>
                                {{ $subcategoria->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Contas</label>
                        <select name="conta_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($contasFiltro as $conta)
                            <option value="{{ $conta->id }}" {{ request('conta_id') == $conta->id ? 'selected' : '' }}>
                                {{ $conta->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-6">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Navegação</label>
                        <div class="flex items-center gap-2 flex-wrap" style="max-width: 700px;">
                            <a href="{{ route('financeiro.contasapagar', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $ontem->format('Y-m-d'), 'vencimento_fim' => $ontem->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $ontem->format('Y-m-d') && request('vencimento_fim') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Ontem
                            </a>
                            <a href="{{ route('financeiro.contasapagar', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $hoje->format('Y-m-d'), 'vencimento_fim' => $hoje->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $hoje->format('Y-m-d') && request('vencimento_fim') == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Hoje
                            </a>
                            <a href="{{ route('financeiro.contasapagar', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $amanha->format('Y-m-d'), 'vencimento_fim' => $amanha->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $amanha->format('Y-m-d') && request('vencimento_fim') == $amanha->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Amanhã
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

                <details class="mt-4" {{ request('vencimento_inicio') || request('vencimento_fim') ? 'open' : '' }}>
                    <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-blue-600 transition">
                        Período Personalizado
                    </summary>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div>
                            <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Data Inicial</label>
                            <input type="date" name="vencimento_inicio" id="vencimento_inicio" value="{{ $vencimentoInicio }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        <div>
                            <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Data Final</label>
                            <input type="date" name="vencimento_fim" id="vencimento_fim" value="{{ $vencimentoFim }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>
                </details>

                <div class="flex flex-wrap gap-2 mt-4">
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

                <div class="flex justify-start gap-2 mt-4">
                    <x-button type="submit" variant="primary" size="sm" class="min-w-[100px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Filtrar
                    </x-button>
                    <x-button href="{{ route('financeiro.contasapagar') }}" variant="secondary" size="sm" class="min-w-[100px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Limpar
                    </x-button>
                    <x-button type="button" variant="success" size="sm" class="min-w-[130px]" x-data @click="$dispatch('abrir-modal-conta-fixa-pagar')">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Despesas Fixas
                    </x-button>
                    <x-button type="button" variant="danger" size="sm" class="min-w-[130px]" x-data @click="$dispatch('abrir-modal-conta-pagar')">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Despesas Variadas
                    </x-button>
                </div>
            </form>

            {{-- KPIs --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <x-kpi-card title="A Pagar (no período)" :value="'R$ ' . number_format($kpis['a_pagar'], 2, ',', '.')" color="blue" />
                <x-kpi-card title="Pago (no período)" :value="'R$ ' . number_format($kpis['pago'], 2, ',', '.')" color="green" />
                <x-kpi-card title="Vencido (no período)" :value="'R$ ' . number_format($kpis['vencido'], 2, ',', '.')" color="red" />
                <x-kpi-card title="Vence Hoje" :value="'R$ ' . number_format($kpis['vence_hoje'], 2, ',', '.')" color="yellow" />
            </div>

            {{-- TABELA --}}
            @if($contas->count())
            <div x-data='{
                selecionadas: [],
                valores: @json($contas->map(function($c){ return ["id" => (int)$c->id, "valor" => (float)$c->valor]; })),
                toggleAll(source) {
                    if (source.checked) {
                        this.selecionadas = @json($contas->pluck("id"));
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
            }'>
                <div class="flex justify-start mb-4">
                    <x-button type="button" variant="success" size="sm" class="min-w-[130px]" x-data @click="$dispatch('abrir-modal-conta-fixa-pagar')">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Despesas Fixas
                    </x-button>
                </div>

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

                <div class="table-card">
                    <div class="rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto">
                                <thead style="background-color: rgba(63, 156, 174, 0.05);">
                                    <tr>
                                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">
                                            <input type="checkbox" x-on:change="toggleAll($event.target)" :checked="selecionadas.length > 0" class="rounded-full border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae] w-5 h-5">
                                        </th>
                                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Vencimento</th>
                                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Fornecedor</th>
                                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Centro de Custo</th>
                                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Categoria</th>
                                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Descrição</th>
                                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Valor</th>
                                        <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700 whitespace-nowrap">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @php $totalPagina = 0; @endphp
                                    @foreach($contas as $conta)
                                    @php
                                    $totalPagina += $conta->valor;
                                    $venceHoje = $conta->status !== 'pago' && $conta->data_vencimento->isToday();
                                    $vencido = $conta->status !== 'pago' && $conta->data_vencimento->isPast() && !$venceHoje;
                                    $linhaClass = '';
                                    if ($venceHoje) {
                                        $linhaClass = 'bg-green-50 border-l-4 border-green-400';
                                    } elseif ($vencido) {
                                        $linhaClass = 'bg-red-50 border-l-4 border-red-400';
                                    }
                                    @endphp
                                    <tr class="{{ $linhaClass }} hover:bg-gray-50 transition">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" value="{{ $conta->id }}" x-model.number="selecionadas" class="rounded-full border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae] w-5 h-5">
                                        </td>
                                        <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Vencimento">
                                            {{ $conta->data_vencimento->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Fornecedor">
                                            {{ $conta->fornecedor ? ($conta->fornecedor->razao_social ?: $conta->fornecedor->nome_fantasia ?: '-') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Centro de Custo">
                                            {{ $conta->centroCusto->nome }}
                                        </td>
                                        <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Categoria">
                                            {{ $conta->conta->nome }}
                                        </td>
                                        <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Descrição">
                                            {{ $conta->descricao }}
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap font-semibold" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Valor">
                                            R$ {{ number_format($conta->valor, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3" data-label="Ações">
                                            <div class="flex gap-1 justify-end">
                                                @if($conta->status !== 'pago')
                                                {{-- Botão de Baixar (ícone de check com cifrão) --}}
                                                <button
                                                    type="button"
                                                    x-data
                                                    class="p-2 rounded-full inline-flex items-center justify-center text-green-600 hover:bg-green-50 transition"
                                                    title="Confirmar Pagamento"
                                                    @click="$dispatch('confirmar-pagamento', {
                                                        action: '{{ route('financeiro.contasapagar.pagar', $conta) }}',
                                                        contaId: {{ $conta->id }},
                                                        valorTotal: {{ $conta->valor }},
                                                        dataVencimento: '{{ $conta->data_vencimento->format('Y-m-d') }}'
                                                    })">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                @endif

                                                {{-- Botão Anexos --}}
                                                <button
                                                    type="button"
                                                    x-data
                                                    class="p-2 rounded-full inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 transition relative"
                                                    title="Gerenciar Anexos (NF/Boleto)"
                                                    @click="$dispatch('abrir-modal-anexos-pagar', { contaId: {{ $conta->id }} })">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                                    </svg>
                                                    @if($conta->anexos()->count() > 0)
                                                    <span class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                                        {{ $conta->anexos()->count() }}
                                                    </span>
                                                    @endif
                                                </button>

                                                @if($conta->status !== 'pago')
                                                <button
                                                    type="button"
                                                    x-data
                                                    class="p-2 rounded-full inline-flex items-center justify-center text-[#3f9cae] hover:bg-[#3f9cae]/10 transition"
                                                    title="Editar {{ $conta->conta_fixa_pagar_id ? 'Despesa Fixa' : 'Conta' }}"
                                                    @click="$dispatch('{{ $conta->conta_fixa_pagar_id ? 'editar-conta-fixa-pagar' : 'editar-conta-pagar' }}', { {{ $conta->conta_fixa_pagar_id ? 'contaFixaId' : 'contaId' }}: {{ $conta->conta_fixa_pagar_id ?? $conta->id }} })">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                </button>
                                                @endif

                                                <button
                                                    type="button"
                                                    x-data
                                                    class="p-2 rounded-full inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition"
                                                    title="Excluir"
                                                    @click="$dispatch('excluir-conta-pagar', { contaId: {{ $conta->id }}, tipo: '{{ $conta->tipo }}', contaFixaId: {{ $conta->conta_fixa_pagar_id ?? 'null' }} })">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
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
                    </div>

                    {{-- Rodapé com Totais --}}
                    <x-card class="mt-4 mb-6">
                        <div class="p-4 flex justify-end">
                            <div class="text-right space-y-2">
                                <div>
                                    <span class="text-sm text-gray-600 uppercase tracking-wide">Total da Página:</span>
                                    <span class="ml-2 text-xl font-bold text-gray-900">R$ {{ number_format($totalPagina, 2, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600 uppercase tracking-wide">Total Geral (Filtrado):</span>
                                    <template x-if="selecionadas.length > 0">
                                        <span class="ml-2 text-xl font-bold text-gray-900">
                                            <span x-text="selecionadas.length"></span> selecionadas —
                                            R$ <span x-text="getValorSelecionado().toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                                        </span>
                                    </template>
                                    <template x-if="selecionadas.length === 0">
                                        <span class="ml-2 text-xl font-bold text-gray-900">R$ {{ number_format($totalGeralFiltrado, 2, ',', '.') }}</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>

                <x-pagination :paginator="$contas" label="contas" />
            </div>
            @else
            <x-card :padding="false" class="text-center py-12">
                <p class="text-gray-500">Nenhuma conta encontrada para os filtros aplicados.</p>
            </x-card>
            @endif

        </div>
    </div>

    @include('financeiro.partials.modal-conta-pagar')
    @include('financeiro.partials.modal-conta-fixa-pagar')
    @include('financeiro.partials.modal-confirmar-pagamento')
    @include('financeiro.partials.modal-excluir-conta-pagar')
    @include('financeiro.partials.modal-anexos-pagar')
    @include('financeiro.partials.modal-confirmar-pagamento-multiplo')

</x-app-layout>