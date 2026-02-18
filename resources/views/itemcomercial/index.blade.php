<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <nav class="flex items-center gap-2 text-base font-semibold leading-tight rounded-full py-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-800 font-medium">Serviços e Produtos</span>
        </nav>
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            🔍 Pesquisar
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nome" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativa</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativa</option>
                        </select>
                    </div><br>

                    <div class="flex gap-3 items-end flex-col lg:flex-row lg:col-span-3 justify-end">
                        <button type="submit" class="btn btn-primary">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>

                        {{--@if(auth()->user()->canPermissao('clientes', 'incluir'))--}}
                        <a href="{{ route('itemcomercial.create') }}" class="btn btn-success">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Novo Item
                        </a>
                        {{--@endif--}}
                    </div>
                </div>
            </form>

            {{-- ================= RESUMO ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600">
                    <p class="text-xs text-gray-600 uppercase">Total</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $itemcomercial->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600">
                    <p class="text-xs text-gray-600 uppercase">Produtos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $itemcomercial->where('tipo','produto')->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-indigo-600">
                    <p class="text-xs text-gray-600 uppercase">Serviços</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">
                        {{ $itemcomercial->where('tipo','servico')->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-yellow-500">
                    <p class="text-xs text-gray-600 uppercase">Ativos</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        {{ $itemcomercial->where('ativo', true)->count() }}
                    </p>
                </div>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($itemcomercial->count())
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nome</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Preço</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Unidade</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Estoque</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($itemcomercial as $item)
                            <tr class="hover:bg-gray-50 transition">

                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-semibold">
                                        {{ $item->id }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                                {{ $item->tipo === 'produto'
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-indigo-100 text-indigo-800' }}">
                                        {{ ucfirst($item->tipo) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ $item->nome }}
                                </td>

                                <td class="px-4 py-3 text-sm font-semibold">
                                    R$ {{ number_format($item->preco_venda, 2, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ $item->unidade_medida }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ $item->gerencia_estoque ? ($item->estoque_atual ?? 0) : '—' }}
                                </td>

                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <x-status-badge :ativo="$item->ativo" />
                                </td>

                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <div class="flex gap-1 items-center">
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->tipo === 'comercial')
                                        <a href="{{ route('itemcomercial.edit', $item) }}" class="btn btn-sm btn-edit">
                                            <svg fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                            Editar
                                        </a>
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
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <strong>{{ $itemcomercial->count() }}</strong> de
                    <strong>{{ $itemcomercial->total() }}</strong>
                    item
                </div>

                <div class="pagination-links">
                    {{-- Link Anterior --}}
                    @if($itemcomercial->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $itemcomercial->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif

                    {{-- Links de Página --}}
                    @foreach($itemcomercial->getUrlRange(1, $itemcomercial->lastPage()) as $page => $url)
                    @if($page == $itemcomercial->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach

                    {{-- Link Próximo --}}
                    @if($itemcomercial->hasMorePages())
                    <a href="{{ $itemcomercial->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>

            @else
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <h3 class="text-lg font-medium text-gray-900">Nenhum cliente encontrado</h3>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>