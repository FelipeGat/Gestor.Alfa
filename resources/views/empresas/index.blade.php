<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏢 Empresas
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
                            🔍 Pesquisar Empresa
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Razão social ou nome fantasia" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
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
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 w-full lg:w-auto
                                   bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium
                                   rounded-lg shadow transition">
                            🔍 Filtrar
                        </button>

                        <a href="{{ route('empresas.create') }}" class="inline-flex items-center justify-center px-4 py-2 w-full lg:w-auto
                                   bg-green-600 hover:bg-green-700 text-white text-sm font-medium
                                   rounded-lg shadow transition">
                            ➕ Nova Empresa
                        </a>
                    </div>
                </div>
            </form>

            {{-- ================= RESUMO (GRID EXPLÍCITO) ================= --}}
            <style>
            @media (min-width: 1024px) {
                .resumo-empresas {
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                }
            }
            </style>

            <div class="resumo-empresas gap-4" style="
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));
            ">

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600 w-full">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Total de Empresas
                    </p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $empresas->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600 w-full">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Empresas Ativas
                    </p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $empresas->where('ativo', true)->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-red-600 w-full">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Empresas Inativas
                    </p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $empresas->where('ativo', false)->count() }}
                    </p>
                </div>

            </div>

            {{-- ================= TABELA ================= --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Empresa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">CNPJ</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($empresas as $empresa)
                            <tr class="hover:bg-gray-50 transition">

                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $empresa->id }}
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-900 min-w-[220px]">
                                    <div class="font-medium">
                                        {{ $empresa->razao_social }}
                                    </div>

                                    @if($empresa->nome_fantasia)
                                    <div class="text-xs text-gray-500">
                                        {{ $empresa->nome_fantasia }}
                                    </div>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                    {{ $empresa->cnpj }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            {{ $empresa->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $empresa->ativo ? '✓ Ativa' : '✗ Inativa' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">

                                        <a href="{{ route('empresas.edit', $empresa) }}" class="inline-flex items-center px-3 py-1
                                                      bg-white hover:bg-blue-700
                                                      text-white text-xs rounded-md transition">
                                            ✏️
                                        </a>

                                        <form action="{{ route('empresas.destroy', $empresa) }}" method="POST"
                                            onsubmit="return confirm('Deseja excluir esta empresa?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="inline-flex items-center px-3 py-1
                                                            text-red-600
                                                           hover:bg-red-600 text-xs rounded-md transition">
                                                🗑️
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    Nenhuma empresa cadastrada.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>