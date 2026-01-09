<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Empresas
        </h2>
    </x-slot>

    <div class="flex justify-center min-h-screen bg-gray-100">
        <div class="w-3/4 py-12 space-y-6">

            {{-- FILTROS --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-wrap gap-4 items-end">

                    <div class="flex flex-col flex-1 min-w-[240px]">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Empresa
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Razão social ou nome fantasia"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>

                    <div class="flex flex-col w-48">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">Todas</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativa</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativa</option>
                        </select>
                    </div>

                    <div class="flex gap-3 items-end">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2
                            bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            🔍 Filtrar
                        </button>

                        <a href="{{ route('empresas.create') }}" class="inline-flex items-center justify-center px-4 py-2
                            bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            ➕ Nova Empresa
                        </a>
                    </div>
                </div>
            </form>

            {{-- RESUMO --}}
            <div class="flex flex-wrap gap-3">

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-blue-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Total de Empresas</p>
                    <p class="text-lg font-bold text-blue-600">
                        {{ $empresas->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-green-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Empresas Ativas</p>
                    <p class="text-lg font-bold text-green-600">
                        {{ $empresas->where('ativo', true)->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-red-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Empresas Inativas</p>
                    <p class="text-lg font-bold text-red-600">
                        {{ $empresas->where('ativo', false)->count() }}
                    </p>
                </div>

            </div>

            {{-- TABELA --}}
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Razão Social</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">CNPJ</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($empresas as $empresa)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs">{{ $empresa->id }}</td>
                            <td class="px-4 py-3 text-xs">
                                {{ $empresa->razao_social }}
                                @if($empresa->nome_fantasia)
                                <div class="text-gray-500 text-xs">
                                    {{ $empresa->nome_fantasia }}
                                </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $empresa->cnpj }}</td>

                            <td class="px-4 py-3 text-xs">
                                @if($empresa->ativo)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                    Ativa
                                </span>
                                @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                    Inativa
                                </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-xs flex gap-2">
                                <a href="{{ route('empresas.edit', $empresa) }}"
                                    class="px-3 py-1 bg-blue-600 text-green-600 rounded text-xs">
                                    Editar
                                </a>

                                <form action="{{ route('empresas.destroy', $empresa) }}" method="POST"
                                    onsubmit="return confirm('Deseja excluir esta empresa?')">
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
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Nenhuma empresa cadastrada.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>