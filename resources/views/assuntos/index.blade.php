<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Assuntos
        </h2>
    </x-slot>
    <br>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    {{-- PESQUISA --}}
                    <div class="flex flex-col lg:col-span-4">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Assunto
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome do assunto"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- EMPRESA --}}
                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Empresa
                        </label>
                        <select name="empresa_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @selected(request('empresa_id')==$empresa->id)>
                                {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- STATUS --}}
                    <div class="flex flex-col lg:col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativo</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativo</option>
                        </select>
                    </div>

                    {{-- AÇÕES --}}
                    <div class="flex items-end lg:col-span-3">
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

            @if(auth()->user()->canPermissao('assuntos','incluir'))
            <div class="flex justify-start" style="margin-bottom: 1rem;">
                <a href="{{ route('assuntos.create') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </a>
            </div>
            @endif

            {{-- ================= TABELA ================= --}}
            @if($assuntos->count())
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Empresa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Assunto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Categoria</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($assuntos as $assunto)
                            <tr class="hover:bg-gray-50 transition">

                                {{-- ID --}}
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center justify-center w-8 h-8
                                                         rounded-full bg-blue-100 text-blue-600 font-semibold">
                                        {{ $assunto->id }}
                                    </span>
                                </td>

                                {{-- EMPRESA --}}
                                <td class="px-4 py-3 text-sm font-medium text-gray-800 whitespace-nowrap">
                                    {{ $assunto->empresa->nome_fantasia ?? '—' }}
                                </td>

                                {{-- ASSUNTO --}}
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 min-w-[200px] truncate">
                                    {{ $assunto->nome }}
                                </td>

                                {{-- TIPO --}}
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                {{ $assunto->tipo === 'SERVICO'
                                                    ? 'bg-indigo-100 text-indigo-800'
                                                    : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $assunto->tipo }}
                                    </span>
                                </td>

                                {{-- CATEGORIA --}}
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <div class="font-medium">{{ $assunto->categoria }}</div>
                                    <div class="text-xs text-gray-500">{{ $assunto->subcategoria }}</div>
                                </td>

                                {{-- STATUS --}}
                                <td class="px-4 py-3 text-sm">
                                    <x-status-badge :ativo="$assunto->ativo" />
                                </td>

                                {{-- AÇÕES --}}
                                <td class="px-4 py-3">
                                    <div class="table-actions">
                                        @if(auth()->user()->isAdminPanel() ||
                                        auth()->user()->canPermissao('assuntos','incluir'))
                                        <a href="{{ route('assuntos.edit', $assunto) }}" class="btn btn-sm btn-edit"
                                            style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                            <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                <path
                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        @endif

                                        @if(auth()->user()->isAdminPanel() ||
                                        auth()->user()->canPermissao('assuntos','excluir'))
                                        <form action="{{ route('assuntos.destroy', $assunto) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este Assunto?')"
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
                                        @endif
                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <br>

            {{-- ================= PAGINAÇÃO ================= --}}
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando <strong>{{ $assuntos->count() }}</strong> de
                    <strong>{{ $assuntos->total() }}</strong>
                    assuntos
                </div>

                <div class="pagination-links">
                    {{-- Link Anterior --}}
                    @if($assuntos->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $assuntos->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif

                    {{-- Links de Página --}}
                    @foreach($assuntos->getUrlRange(1, $assuntos->lastPage()) as $page => $url)
                    @if($page == $assuntos->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach

                    {{-- Link Próximo --}}
                    @if($assuntos->hasMorePages())
                    <a href="{{ $assuntos->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>

            @else
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Nenhum assunto encontrado
                </h3>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>