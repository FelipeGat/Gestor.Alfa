<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            👤 Usuários do Sistema
        </h2>
    </x-slot>

    <br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            🔍 Pesquisar Usuários
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome ou E-mail"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex flex-col lg:col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativo</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativo</option>
                            <option value="primeiro acesso" @selected(request('status')=='primeiro_acesso' )>Primeiro
                                Acesso</option>
                        </select>
                    </div>
                    <br>

                    <div class="flex gap-3 items-end flex-col lg:flex-row lg:col-span-3 justify-end">
                        <button type="submit" class="btn btn-primary">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                            Filtrar
                        </button>

                        @if(auth()->user()->canPermissao('clientes', 'incluir'))
                        <a href="{{ route('usuarios.create') }}" class="btn btn-success">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Usuário
                        </a>
                        @endif
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
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Usuarios</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $totalUsuarios }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $usuariosAtivos }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-red-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Primeiro_Acesso</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $usuariosInativos }}
                    </p>
                </div>
            </div>

            {{-- TABELA --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Nome</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Perfis</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase">Ações</th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($usuarios as $usuario)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $usuario->name }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $usuario->email }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
            {{ $usuario->tipo === 'admin'
                ? 'bg-red-100 text-red-800'
                : ($usuario->tipo === 'administrativo'
                    ? 'bg-blue-100 text-blue-800'
                    : 'bg-green-100 text-green-800') }}">
                                        {{ ucfirst($usuario->tipo) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $usuario->perfis->pluck('nome')->join(', ') ?: '—' }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    @if($usuario->primeiro_acesso)
                                    <span class="text-yellow-600 font-medium">Primeiro acesso</span>
                                    @else
                                    <span class="text-green-600 font-medium">Ativo</span>
                                    @endif
                                </td>

                                {{-- AÇÕES --}}
                                <td class="px-4 py-3 text-sm text-center">
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-edit">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                        Editar
                                    </a>
                                </td>
                            </tr>

                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <br>

            {{-- ================= PAGINAÇÃO ================= --}}
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando
                    <strong>{{ $usuarios->firstItem() ?? 0 }}</strong>
                    –
                    <strong>{{ $usuarios->lastItem() ?? 0 }}</strong>
                    de
                    <strong>{{ $usuarios->total() }}</strong>
                    usuários
                </div>

                <div class="pagination-links">
                    {{-- Link Anterior --}}
                    @if($usuarios->onFirstPage())
                    <span class="pagination-link disabled">← Anterior</span>
                    @else
                    <a href="{{ $usuarios->previousPageUrl() }}" class="pagination-link">← Anterior</a>
                    @endif

                    {{-- Links de Página --}}
                    @foreach($usuarios->getUrlRange(1, $usuarios->lastPage()) as $page => $url)
                    @if($page == $usuarios->currentPage())
                    <span class="pagination-link active">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                    @endif
                    @endforeach

                    {{-- Link Próximo --}}
                    @if($usuarios->hasMorePages())
                    <a href="{{ $usuarios->nextPageUrl() }}" class="pagination-link">Próximo →</a>
                    @else
                    <span class="pagination-link disabled">Próximo →</span>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>