<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Clientes
        </h2>
    </x-slot>

    <div class="flex justify-center min-h-screen bg-gray-100">
        <div class="w-3/4 py-12 space-y-6">

            {{-- FILTROS --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-wrap gap-4 items-end">

                    {{-- Buscar --}}
                    <div class="flex flex-col flex-1 min-w-[240px]">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Cliente
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome ou E-mail"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Status --}}
                    <div class="flex flex-col w-48">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="ativo" @selected(request('status')=='ativo' )>Ativo</option>
                            <option value="inativo" @selected(request('status')=='inativo' )>Inativo</option>
                        </select>
                    </div>

                    {{-- Botões --}}
                    <div class="flex gap-3 items-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2
                                        bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            🔍 Filtrar
                        </button>

                        <a href="{{ route('clientes.create') }}"
                            class="inline-flex items-center justify-center px-4 py-2
                                        bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            ➕ Novo Cliente
                        </a>
                    </div>
                </div>
            </form>

            {{-- RESUMO --}}
            <div class="flex flex-wrap gap-3">

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-blue-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Total de Clientes</p>
                    <p class="text-lg font-bold text-blue-600">
                        {{ $clientes->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-green-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Clientes Ativos</p>
                    <p class="text-lg font-bold text-green-600">
                        {{ $clientes->where('ativo', true)->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-red-600">
                    <p class="text-xs font-medium text-gray-600 mb-1">Clientes Inativos</p>
                    <p class="text-lg font-bold text-red-600">
                        {{ $clientes->where('ativo', false)->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow-md rounded-lg border-l-4 border-yellow-500">
                    <p class="text-xs font-medium text-gray-600 mb-1">Receita Mensal</p>
                    <p class="text-lg font-bold text-yellow-600">
                        R$ {{ number_format($clientes->sum('valor_mensal'), 2, ',', '.') }}
                    </p>
                </div>

            </div>

            {{-- TABELA DE CLIENTES --}}
            @if($clientes->count() > 0)
            <div class="bg-white shadow-md rounded-lg overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    ID</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Nome</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    E-mail</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Telefone</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Valor Mensal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Vencimento</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($clientes as $cliente)

                            @php
                            $emailPrincipal = $cliente->emails->where('principal', true)->first();
                            $telefonePrincipal = $cliente->telefones->where('principal', true)->first();
                            @endphp

                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                    {{ $cliente->id }}
                                </td>

                                <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                    {{ $cliente->nome }}
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-900">
                                    {{ $emailPrincipal->valor ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-900">
                                    {{ $telefonePrincipal->valor ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                    @if($cliente->valor_mensal)
                                    R$ {{ number_format($cliente->valor_mensal, 2, ',', '.') }}
                                    @else
                                    —
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-900">
                                    @if($cliente->dia_vencimento)
                                    Dia {{ $cliente->dia_vencimento }}
                                    @else
                                    —
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    @if($cliente->ativo)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Ativo
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inativo
                                    </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    <div class="flex gap-2 items-center">
                                        <a href="{{ route('clientes.edit', $cliente) }}"
                                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md transition duration-200">
                                            Editar
                                        </a>

                                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este cliente?')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1 border border-red-600 text-red-600 hover:bg-red-50 text-xs font-medium rounded-md transition duration-200">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    Nenhum cliente cadastrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
            @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-500 text-lg">Nenhum cliente cadastrado.</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>