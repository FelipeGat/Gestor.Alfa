<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏷️ Assuntos
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
                            🔍 Pesquisar Assunto
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
                    </div><br>

                    {{-- AÇÕES --}}
                    <div class="flex gap-3 flex-col lg:flex-row lg:col-span-3 justify-end">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 w-full lg:w-auto
                                       bg-blue-600 hover:bg-blue-700 text-green-600 text-sm font-medium
                                       rounded-lg shadow transition">
                            🔍 Filtrar
                        </button>

                        @if(auth()->user()->canPermissao('assuntos','incluir'))
                        <a href="{{ route('assuntos.create') }}" class="inline-flex items-center justify-center px-4 py-2 w-full lg:w-auto
                                      bg-green-600 hover:bg-green-700 text-blue text-sm font-medium
                                      rounded-lg shadow transition">
                            ➕ Novo Assunto
                        </a>
                        @endif
                    </div>

                </div>
            </form>

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
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                {{ $assunto->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $assunto->ativo ? '✓ Ativo' : '✗ Inativo' }}
                                    </span>
                                </td>

                                {{-- AÇÕES --}}
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">

                                        @if(auth()->user()->isAdminPanel() ||
                                        auth()->user()->canPermissao('assuntos','incluir'))
                                        <a href="{{ route('assuntos.edit', $assunto) }}" class="inline-flex items-center justify-center w-8 h-8
                                                              bg-blue-600 hover:bg-blue-700
                                                              text-white text-xs rounded-md transition">
                                            ✏️
                                        </a>
                                        @endif

                                        @if(auth()->user()->isAdminPanel() ||
                                        auth()->user()->canPermissao('assuntos','excluir'))
                                        <form action="{{ route('assuntos.destroy', $assunto) }}" method="POST"
                                            onsubmit="return confirm('Deseja excluir este assunto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8
                                                                       border border-red-600 text-red-600
                                                                       hover:bg-red-50 text-xs rounded-md transition">
                                                🗑️
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