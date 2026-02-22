<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    <style>
        /* Filtros */
        .filters-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        /* Inputs e Selects */
        .filters-card input:focus,
        .filters-card select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Tabela */
        .tabela-contas thead th {
            color: rgb(17, 24, 39) !important;
            font-size: 14px;
            font-weight: 600;
        }
        .tabela-contas tbody td {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-contas tbody td.font-medium {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-contas tbody td:nth-child(2),
        .tabela-contas tbody td:nth-child(3),
        .tabela-contas tbody td:nth-child(5),
        .tabela-contas tbody td:nth-child(6) {
            font-family: 'Inter', sans-serif !important;
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        /* Paginação */
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
        /* Cards KPI */
        .kpi-card {
            background: white;
            border: 1px solid;
            border-left-width: 4px;
            border-top-width: 1px;
            border-right-width: 1px;
            border-bottom-width: 1px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .kpi-card-blue { border-color: #2563eb; }
        .kpi-card-green { border-color: #16a34a; }
        .kpi-card-yellow { border-color: #eab308; }
        .kpi-card-red { border-color: #dc2626; }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Bancos']
        ]" />
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= NAVEGAÇÃO ================= --}}
            <div class="section-card financeiro-nav">
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
                    Cobrar
                </a>

                {{-- CONTAS A RECEBER --}}
                <a href="{{ route('financeiro.contasareceber' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-md border border-emerald-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0l-2-2m2 2l2-2" />
                    </svg>
                    Receber
                </a>

                {{-- CONTAS A PAGAR --}}
                <a href="{{ route('financeiro.contasapagar') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition shadow-md border border-red-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12V6m0 0l-2 2m2-2l2 2" />
                    </svg>
                    Pagar
                </a>

                {{-- MOVIMENTAÇÃO --}}
                <a href="{{ route('financeiro.movimentacao' ) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition shadow-md border border-blue-700/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Extrato
                </a>
            </div>

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="filters-card">
                <div class="filters-grid">

                    <div class="filter-group">
                        <label>Pesquisar Conta</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome da conta">
                    </div>

                    <div class="filter-group">
                        <label>Empresa</label>
                        <select name="empresa_id">
                            <option value="">Todas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @selected(request('empresa_id')==$empresa->id)>
                                {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Tipo</label>
                        <select name="tipo">
                            <option value="">Todos</option>
                            <option value="banco">Banco</option>
                            <option value="pix">Pix</option>
                            <option value="caixa">Caixa</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">Todos</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>

                <div class="actions-container">
                    {{-- Espaço vazio (Esquerda) --}}
                    <div></div>

                    {{-- Grupo de Botões (Direita) --}}
                    <div class="filters-actions">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #3f9cae; border-radius: 9999px;">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>
                        <a href="{{ route('financeiro.contas-financeiras.index') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #9ca3af; border-radius: 9999px; box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(156, 163, 175, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(156, 163, 175, 0.3)'">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Limpar
                        </a>
                        <a href="{{ route('financeiro.contas-financeiras.create') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(34, 197, 94, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(34, 197, 94, 0.3)'">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Nova Conta
                        </a>
                    </div>
                </div>
            </form>

            {{-- ================= RESUMO ================= --}}
            <style>
            @media (min-width: 1024px) {
                .resumo-grid {
                    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                }
            }
            </style>

            <div class="resumo-grid gap-4 mb-6" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));">
                <div class="kpi-card kpi-card-blue w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Contas</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $totalContas }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-green w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Contas Ativas</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $contasAtivas }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-red w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Contas Inativas</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $contasInativas }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-yellow w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Saldo Total</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        R$ {{ number_format($contas->sum('saldo'), 2, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($contas->count())
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto tabela-contas">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">ID</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Empresa</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Conta</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Tipo</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Saldo</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Disponível</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Status</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($contas as $conta)
                            <tr>
                                <td>
                                    <span class="table-number">{{ $conta->id }}</span>
                                </td>
                                <td>{{ $conta->empresa->nome_fantasia ?? '—' }}</td>
                                <td class="font-semibold">{{ $conta->nome }}</td>
                                <td>
                                    <span class="table-badge badge-{{ $conta->tipo }}">
                                        {{ strtoupper($conta->tipo) }}
                                    </span>
                                </td>
                                <td>R$ {{ number_format($conta->saldo, 2, ',', '.') }}</td>
                                <td class="font-semibold">
                                    R$ {{ number_format($conta->saldo_total, 2, ',', '.') }}
                                </td>
                                <td>
                                    <span class="table-badge {{ $conta->ativo ? 'badge-baixa' : 'badge-alta' }}">
                                        {{ $conta->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex gap-1 items-center justify-start">
                                        <a href="{{ route('financeiro.contas-financeiras.edit', $conta) }}" class="btn btn-sm btn-edit" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Editar">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>

                                        <form method="POST"
                                            action="{{ route('financeiro.contas-financeiras.destroy', $conta) }}"
                                            onsubmit="return confirm('Deseja remover esta conta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-delete" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Excluir">
                                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-sm" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center; background: #f59e0b; color: white; border: none;" title="Ajuste Manual" onclick="abrirModalAjusteUnificado({{ $conta->id }}, '{{ $conta->nome }}')">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
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

            {{-- ================= PAGINAÇÃO ================= --}}
            <div class="pagination-container">
                {{ $contas->links() }}
            </div>
            @else
            <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <h3 class="text-lg font-medium text-gray-900">Nenhuma conta cadastrada</h3>
                <a href="{{ route('financeiro.contas-financeiras.create') }}" class="btn btn-success mt-4 inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(34, 197, 94, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(34, 197, 94, 0.3)'">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @endif

        </div>
    </div>



    {{-- Modal Ajuste Unificado Moderno --}}
    @include('financeiro.partials.modal-ajuste-unificado')

</x-app-layout>


