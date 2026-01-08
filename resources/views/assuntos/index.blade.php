<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Assuntos
        </h2>
    </x-slot>

    <div class="flex justify-center min-h-screen bg-gray-100">
        <div class="w-3/4 py-12 space-y-6">

            {{-- FILTROS --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-wrap gap-4 items-end">

                    <div class="flex flex-col flex-1 min-w-[240px]">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Assunto
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome do assunto"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>

                    <div class="flex flex-col w-48">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativo</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativo</option>
                        </select>
                    </div>

                    <div class="flex gap-3 items-end">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-green-600 text-xs rounded-md">
                            🔍 Filtrar
                        </button>

                        <a href="{{ route('assuntos.create') }}"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-green-600 text-xs rounded-md">
                            ➕ Novo Assunto
                        </a>
                    </div>
                </div>
            </form>

            {{-- TABELA --}}
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($assuntos as $assunto)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs">{{ $assunto->id }}</td>
                            <td class="px-4 py-3 text-xs">{{ $assunto->nome }}</td>

                            <td class="px-4 py-3 text-xs">
                                @if($assunto->ativo)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                    Ativo
                                </span>
                                @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                    Inativo
                                </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-xs flex gap-2">
                                <a href="{{ route('assuntos.edit', $assunto) }}"
                                    class="px-3 py-1 bg-blue-600 text-green-600 rounded text-xs">
                                    Editar
                                </a>

                                <form action="{{ route('assuntos.destroy', $assunto) }}" method="POST"
                                    onsubmit="return confirm('Deseja excluir este assunto?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1 border border-red-600 text-red-600 rounded text-xs">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                Nenhum assunto cadastrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>