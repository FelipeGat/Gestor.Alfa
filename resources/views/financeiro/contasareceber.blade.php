<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @vite('resources/css/financeiro/contasareceber.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Receber']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
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

            <form method="GET" action="{{ route('financeiro.contasareceber') }}" 
                class="bg-white rounded-lg p-6"
                style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-2 relative">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Buscar</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Cliente ou descrição..."
                            autocomplete="off"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <input type="hidden" name="cliente_id" id="busca-cliente-id" value="{{ request('cliente_id') }}">
                        <div id="autocomplete-cliente" class="absolute left-0 right-0 z-10 bg-white border border-gray-200 rounded shadow max-h-40 overflow-y-auto hidden"></div>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
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
                            if (!clientes.some(c => c.nome && c.nome.toLowerCase() === e.target.value.toLowerCase())) {
                                inputHidden.value = '';
                            }
                        });
                        inputBusca.addEventListener('focus', () => renderLista(inputBusca.value));
                        inputBusca.addEventListener('blur', () => setTimeout(() => lista.classList.add('hidden'), 150));
                    });
                    </script>

                    <div class="md:col-span-2">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Empresa</label>
                        <select name="empresa_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todas as Empresas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome_fantasia }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-6">
                        <label style="font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; display: block; margin-bottom: 4px;">Navegação</label>
                        <div class="flex items-center gap-2 flex-wrap" style="max-width: 700px;">
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $ontem->format('Y-m-d'), 'vencimento_fim' => $ontem->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $ontem->format('Y-m-d') && request('vencimento_fim') == $ontem->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Ontem
                            </a>
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $hoje->format('Y-m-d'), 'vencimento_fim' => $hoje->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $hoje->format('Y-m-d') && request('vencimento_fim') == $hoje->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Hoje
                            </a>
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $amanha->format('Y-m-d'), 'vencimento_fim' => $amanha->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center px-3 h-10 rounded-lg transition border font-semibold min-w-[70px]
                                    {{ request('vencimento_inicio') == $amanha->format('Y-m-d') && request('vencimento_fim') == $amanha->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50 text-gray-600 border-gray-300 shadow-sm' }}">
                                Amanhã
                            </a>
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'), 'vencimento_fim' => $mesAnterior->endOfMonth()->format('Y-m-d')])) }}"
                                class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <div class="flex-1 text-center font-bold text-gray-700 bg-white px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                                {{ $mesAtualNome }}
                            </div>
                            <a href="{{ route('financeiro.contasareceber', array_merge(request()->except(['vencimento_inicio', 'vencimento_fim']), ['vencimento_inicio' => $proximoMes->startOfMonth()->format('Y-m-d'), 'vencimento_fim' => $proximoMes->endOfMonth()->format('Y-m-d')])) }}"
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
                    <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('tipo', 'nota_fiscal'), ['status' => ['pendente']])) }}"
                        class="quick-filter-btn status-pendente {{ in_array('pendente', request('status', [])) ? 'active' : '' }}">
                        <span>Pendente</span>
                        <span class="count">{{ $contadoresStatus['pendente'] }}</span>
                    </a>
                    <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('tipo', 'nota_fiscal'), ['status' => ['vencido']])) }}"
                        class="quick-filter-btn status-vencido {{ in_array('vencido', request('status', [])) ? 'active' : '' }}">
                        <span>Vencido</span>
                        <span class="count">{{ $contadoresStatus['vencido'] }}</span>
                    </a>
                    <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('status', 'nota_fiscal'), ['tipo' => 'orcamento'])) }}"
                        class="quick-filter-btn tipo-orcamento {{ request('tipo') == 'orcamento' ? 'active' : '' }}">
                        <span>Orçamento</span>
                        <span class="count">{{ $contadoresTipo['orcamento'] }}</span>
                    </a>
                    <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('status', 'nota_fiscal'), ['tipo' => 'contrato'])) }}"
                        class="quick-filter-btn tipo-contrato {{ request('tipo') == 'contrato' ? 'active' : '' }}">
                        <span>Contrato</span>
                        <span class="count">{{ $contadoresTipo['contrato'] }}</span>
                    </a>
                    <a href="{{ route('financeiro.contasareceber', array_merge(request()->except('status', 'tipo'), ['nota_fiscal' => 1])) }}"
                        class="quick-filter-btn tipo-nota-fiscal {{ request('nota_fiscal') == '1' ? 'active' : '' }}">
                        <span>Nota Fiscal</span>
                        <span class="count">{{ $contadoresNotaFiscal ?? 0 }}</span>
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
                    <x-button href="{{ route('financeiro.contasareceber') }}" variant="secondary" size="sm" class="min-w-[100px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Limpar
                    </x-button>
                </div>
            </form>

            <div class="flex justify-start">
                <x-button variant="success" size="sm" class="min-w-[130px]" x-data @click="$dispatch('abrir-modal-conta-fixa')">
                    <x-slot name="iconLeft">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </x-slot>
                    Receitas Fixas
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
                <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead style="background-color: rgba(63, 156, 174, 0.05);">
                                <tr>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">
                                        <input type="checkbox" x-on:change="toggleAll($event.target)" style="background:#f3f4f6;border:1.5px solid #d1d5db;border-radius:9999px;width:16px;height:16px;box-shadow:0 1px 2px #00000010;appearance:auto;">
                                    </th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">Vencimento</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">Empresa</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">Cliente</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">CNPJ/CPF</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">Descrição</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">Tipo</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">Valor</th>
                                    <th class="px-4 py-3 text-left uppercase text-sm font-bold text-gray-700">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
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
                            <tr class="{{ $linhaClass }} hover:bg-gray-50 transition" data-cobranca-id="{{ $cobranca->id }}" data-valor="{{ $cobranca->valor }}">
                                <td class="px-4 py-3">
                                    <input type="checkbox" :value="{{ $cobranca->id }}" x-model.number="selecionadas" style="background:#f3f4f6;border:1.5px solid #d1d5db;border-radius:9999px;width:16px;height:16px;box-shadow:0 1px 2px #00000010;appearance:auto;">
                                </td>
                                <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Vencimento">
                                    {{ $cobranca->data_vencimento->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Empresa">
                                    {{ $cobranca->orcamento?->empresa?->nome_fantasia ?? $cobranca->empresa_relacionada?->nome_fantasia ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Cliente">
                                    {{ $cobranca->cliente?->nome ?? $cobranca->cliente?->nome_fantasia ?? $cobranca->cliente?->razao_social ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="CNPJ/CPF">
                                    {{ $cobranca->cliente?->cpf_cnpj_formatado ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Descrição">
                                    {{ $cobranca->descricao }}
                                </td>
                                <td class="px-4 py-3" data-label="Tipo">
                                    <x-badge type="{{ $cobranca->tipo === 'contrato' ? 'primary' : 'info' }}">
                                        {{ ucfirst($cobranca->tipo) }}
                                    </x-badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-left font-semibold whitespace-nowrap" style="font-weight: 500; color: rgb(17, 24, 39);" data-label="Valor">
                                    R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-left" data-label="Ações">
                                    <div class="flex gap-1">

                                        {{-- Botão Editar (apenas para contas fixas) --}}
                                        @if($cobranca->conta_fixa_id)
                                        <button
                                            type="button"
                                            x-data
                                            class="p-2 rounded-full inline-flex items-center justify-center text-[#3f9cae] hover:bg-[#3f9cae]/10 transition"
                                            title="Editar Conta Fixa"
                                            @click="$dispatch('editar-conta-fixa', { contaFixaId: {{ $cobranca->conta_fixa_id }} })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        @endif

                                        @if($cobranca->status !== 'pago')
                                        {{-- Botão de Baixar (ícone de check com cifrão) --}}
                                        <button
                                            type="button"
                                            x-data
                                            class="p-2 rounded-full inline-flex items-center justify-center text-green-600 hover:bg-green-50 transition"
                                            title="Confirmar Baixa"
                                            x-on:click="$dispatch('confirmar-baixa', {
    action: '{{ route('financeiro.contasareceber.pagar', $cobranca) }}',
    empresaId: {{ $cobranca->orcamento?->empresa_id ?? 'null' }},
    cobrancaId: {{ $cobranca->id }},
    valorTotal: {{ $cobranca->valor }},
    dataVencimento: '{{ $cobranca->data_vencimento->format('Y-m-d') }}'
})">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        @endif

                                        {{-- Botão Anexos --}}
                                        <button
                                            type="button"
                                            x-data
                                            class="p-2 rounded-full inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 transition relative"
                                            title="Gerenciar Anexos (NF/Boleto)"
                                            @click="$dispatch('abrir-modal-anexos', { cobrancaId: {{ $cobranca->id }} })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                            </svg>
                                            @if($cobranca->anexos()->count() > 0)
                                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
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
                                            class="p-2 rounded-full inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition"
                                            title="Excluir"
                                            @click="$dispatch('excluir-cobranca', {
                                                cobrancaId: {{ $cobranca->id }},
                                                orcamentoId: {{ $cobranca->orcamento_id ?? 'null' }},
                                                totalParcelas: {{ $totalParcelasOrcamento }},
                                                contaFixaId: {{ $cobranca->conta_fixa_id ?? 'null' }},
                                                totalCobrancasContrato: {{ $totalCobrancasContrato }},
                                                dataVencimento: '{{ $cobranca->data_vencimento }}'
                                            })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                                    Nenhuma cobrança encontrada para os filtros aplicados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                {{-- Rodapé com Totais --}}
                <div class="px-4 py-3 bg-gray-50 flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        <span class="font-medium">Total na Página:</span>
                        <span class="font-bold ml-2">R$ {{ number_format($totalPagina, 2, ',', '.' ) }}</span>
                    </div>
                    <div class="text-sm text-gray-700">
                        <span class="font-medium">Total Geral (Filtrado):</span>
                        <template x-if="selecionadas.length > 0">
                            <span class="font-bold ml-2">
                                <span x-text="selecionadas.length"></span> selecionadas —
                                R$ <span x-text="getValorSelecionado().toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                            </span>
                        </template>
                        <template x-if="selecionadas.length === 0">
                            <span class="font-bold ml-2">R$ {{ number_format($totalGeralFiltrado, 2, ',', '.') }}</span>
                        </template>
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