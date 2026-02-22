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

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="filters-card p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-4">
                        <label class="text-sm font-medium text-gray-700 mb-2">Pesquisar Conta</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome da conta" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">Empresa</label>
                        <select name="empresa_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @selected(request('empresa_id')==$empresa->id)>
                                {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todos</option>
                            <option value="banco">Banco</option>
                            <option value="pix">Pix</option>
                            <option value="caixa">Caixa</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>

                    <div class="flex flex-col lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todos</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-3">
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

            {{-- ================= BOTÃO ADICIONAR ================= --}}
            <div class="flex justify-start mb-4">
                <a href="{{ route('financeiro.contas-financeiras.create') }}" class="btn btn-success inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
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
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    {{ $conta->id }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $conta->empresa->nome_fantasia ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $conta->nome }}</td>
                                <td class="px-4 py-3 text-left">
                                    <span class="inline-flex items-center justify-center h-8 px-3 rounded-full text-xs font-semibold w-28
                                        @if($conta->tipo === 'corrente') bg-blue-100 text-blue-800
                                        @elseif($conta->tipo === 'poupanca') bg-yellow-100 text-yellow-800
                                        @elseif($conta->tipo === 'investimento') bg-green-100 text-green-800
                                        @elseif($conta->tipo === 'credito') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ strtoupper($conta->tipo) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    R$ {{ number_format($conta->saldo, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    R$ {{ number_format($conta->saldo_total, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-left">
                                    <span class="inline-flex items-center justify-center h-8 px-3 rounded-full text-xs font-semibold w-28 uppercase
                                        {{ $conta->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $conta->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-left">
                                    <div class="flex gap-1 items-center justify-start">
                                        <a href="{{ route('financeiro.contas-financeiras.edit', $conta) }}" class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition" title="Editar">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>

                                        <form method="POST"
                                            action="{{ route('financeiro.contas-financeiras.destroy', $conta) }}"
                                            onsubmit="return confirm('Deseja remover esta conta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-red-600 hover:bg-red-700 text-white rounded-full transition" title="Excluir">
                                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>

                                        <button type="button" class="inline-flex items-center justify-center w-8 h-8 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full transition" title="Ajuste Manual" onclick="abrirModalAjusteUnificado({{ $conta->id }}, '{{ $conta->nome }}')">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
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


