<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
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
        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Tabela */
        .tabela-itens thead th {
            color: rgb(17, 24, 39) !important;
            font-size: 14px;
            font-weight: 600;
        }
        .tabela-itens tbody td {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-itens tbody td.font-medium {
            font-weight: 400 !important;
            color: rgb(17, 24, 39) !important;
        }
        .tabela-itens tbody td:nth-child(1),
        .tabela-itens tbody td:nth-child(2),
        .tabela-itens tbody td:nth-child(3),
        .tabela-itens tbody td:nth-child(4),
        .tabela-itens tbody td:nth-child(5),
        .tabela-itens tbody td:nth-child(6),
        .tabela-itens tbody td:nth-child(7) {
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
        .kpi-card-purple { border-color: #7c3aed; }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Comercial', 'url' => route('comercial.index')],
            ['label' => 'Serviços e Produtos']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Mensagens de erro/sucesso --}}
            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="filters-card p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">Pesquisar</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                    </div>

                    <div class="flex flex-col lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todas</option>
                            <option value="ativo" @selected(request('status')=='ativo')>Ativa</option>
                            <option value="inativo" @selected(request('status')=='inativo')>Inativa</option>
                        </select>
                    </div>

                    <div class="flex flex-col lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae] w-full">
                            <option value="">Todos</option>
                            <option value="produto" @selected(request('tipo')=='produto')>Produto</option>
                            <option value="servico" @selected(request('tipo')=='servico')>Serviço</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-3">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #3f9cae; border-radius: 9999px;">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>

                        <a href="{{ route('itemcomercial.index') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; width: 130px; justify-content: center; background: #9ca3af; border-radius: 9999px; box-shadow: 0 2px 4px rgba(156, 163, 175, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(156, 163, 175, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(156, 163, 175, 0.3)'">
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
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $itemcomercial->total() }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-green w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Produtos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $itemcomercial->where('tipo','produto')->count() }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-purple w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Serviços</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">
                        {{ $itemcomercial->where('tipo','servico')->count() }}
                    </p>
                </div>

                <div class="kpi-card kpi-card-yellow w-full max-w-none p-6">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        {{ $itemcomercial->where('ativo', true)->count() }}
                    </p>
                </div>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($itemcomercial->count())
            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <a href="{{ route('itemcomercial.create') }}" class="btn btn-success inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @php
            function sortLink($label, $column) {
            $direction = request('direction') === 'asc' ? 'desc' : 'asc';

            return '<a href="' . request()->fullUrlWithQuery([
                        'sort' => $column,
                        'direction' => $direction
                    ]) . '" class="flex items-center gap-1 hover:text-blue-600">'
                . $label .
                (request('sort') === $column
                ? (request('direction') === 'asc' ? ' ▲' : ' ▼')
                : '') .
                '</a>';
            }
            @endphp
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto tabela-itens">
                        <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                            <tr>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('ID', 'id') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Tipo', 'tipo') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Nome', 'nome') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Preço', 'preco_venda') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Unidade</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Estoque</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">{!! sortLink('Status', 'ativo') !!}</th>
                                <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($itemcomercial as $item)
                            <tr class="hover:bg-gray-50 transition" data-item-id="{{ $item->id }}">

                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    {{ $item->id }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <span class="font-medium text-gray-900">{{ ucfirst($item->tipo) }}</span>
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <span style="font-weight: 400; color: rgb(17, 24, 39);">{{ $item->nome }}</span>
                                </td>

                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    R$ {{ number_format($item->preco_venda, 2, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $item->unidade_medida }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $item->gerencia_estoque ? ($item->estoque_atual ?? 0) : '—' }}
                                </td>

                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <x-status-badge :ativo="$item->ativo" />
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex gap-1 items-center justify-center">
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->tipo === 'comercial')
                                        <a href="{{ route('itemcomercial.edit', $item) }}" class="btn btn-sm btn-edit" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Editar">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('itemcomercial.destroy', $item) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este item?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-delete" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Excluir">
                                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
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
                {{ $itemcomercial->links() }}
            </div>

            @else
            <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <h3 class="text-lg font-medium text-gray-900">Nenhum item encontrado</h3>
                <a href="{{ route('itemcomercial.create') }}" class="btn btn-success mt-4 inline-flex" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onmouseover="this.style.boxShadow='0 4px 6px rgba(34, 197, 94, 0.4)'" onmouseout="this.style.boxShadow='0 2px 4px rgba(34, 197, 94, 0.3)'">
                    <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>