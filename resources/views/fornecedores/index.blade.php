<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Fornecedores']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Fornecedor
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Razão Social, Nome Fantasia ou CNPJ/CPF" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="ativo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="1" @selected(request('ativo')=='1' )>Ativo</option>
                            <option value="0" @selected(request('ativo')=='0' )>Inativo</option>
                        </select>
                    </div>

                    <div class="flex items-end lg:col-span-2">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #3b82f6; border-radius: 9999px;">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>
                    </div>

                </div>
            </form>

            {{-- ================= RESUMO ================= --}}
            <style>
                @media (min-width: 1024px) {
                    .resumo-grid {
                        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                    }
                }
            </style>

            <div class="resumo-grid gap-4 mb-6" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));">
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Fornecedores</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $fornecedores->total() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{-- Se você tiver essas variáveis no Controller, pode usar. Caso contrário, use count() simples se for uma collection pequena --}}
                        {{ $fornecedores->where('ativo', 1)->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-red-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Inativos</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $fornecedores->where('ativo', 0)->count() }}
                    </p>
                </div>
            </div>

            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <a href="{{ route('fornecedores.create') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($fornecedores->count())
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">CNPJ/CPF</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Razão Social</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nome Fantasia</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Cidade/UF</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($fornecedores as $fornecedor)
                            <tr class="hover:bg-gray-50 transition">

                                {{-- CNPJ / CPF --}}
                                <td class="px-4 py-3 text-sm text-gray-600 font-mono whitespace-nowrap">
                                    {{ $fornecedor->cpf_cnpj }}
                                </td>

                                {{-- Razão Social --}}
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 min-w-[200px]">
                                    {{ $fornecedor->razao_social }}
                                </td>

                                {{-- Nome Fantasia --}}
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $fornecedor->nome_fantasia ?? '—' }}
                                </td>

                                {{-- Cidade/UF --}}
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $fornecedor->cidade }}/{{ $fornecedor->estado }}
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <x-status-badge :ativo="$fornecedor->ativo" />
                                </td>

                                {{-- Ações --}}
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <div class="flex gap-1 items-center">
                                        <a href="{{ route('fornecedores.edit', $fornecedor) }}" class="btn btn-sm btn-edit"
                                            style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>

                                        <form action="{{ route('fornecedores.destroy', $fornecedor) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este fornecedor?')"
                                            style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-delete"
                                                style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
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
                    Mostrando <strong>{{ $fornecedores->count() }}</strong> de
                    <strong>{{ $fornecedores->total() }}</strong>
                    fornecedores
                </div>

                <div class="pagination-links">
                    {{-- Link Anterior --}}
                    @if($fornecedores->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $fornecedores->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif

                    {{-- Links de Página --}}
                    @foreach($fornecedores->getUrlRange(1, $fornecedores->lastPage()) as $page => $url)
                    @if($page == $fornecedores->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach

                    {{-- Link Próximo --}}
                    @if($fornecedores->hasMorePages())
                    <a href="{{ $fornecedores->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>

            @else
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <h3 class="text-lg font-medium text-gray-900">Nenhum fornecedor encontrado</h3>
            </div>
            @endif

        </div>
    </div>

</x-app-layout>