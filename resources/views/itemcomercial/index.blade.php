<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🧾 Serviços e Produtos
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= AÇÕES ================= --}}
            <div class="flex justify-end mb-6">
                @if(auth()->user()->isAdminPanel() || auth()->user()->tipo === 'comercial')
                <a href="{{ route('itemcomercial.create') }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm shadow">
                    ➕ Novo Item
                </a>
                @endif
            </div>

            {{-- ================= RESUMO ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600">
                    <p class="text-xs text-gray-600 uppercase">Total</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $itens->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600">
                    <p class="text-xs text-gray-600 uppercase">Produtos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $itens->where('tipo','produto')->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-indigo-600">
                    <p class="text-xs text-gray-600 uppercase">Serviços</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">
                        {{ $itens->where('tipo','servico')->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-yellow-500">
                    <p class="text-xs text-gray-600 uppercase">Ativos</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        {{ $itens->where('ativo', true)->count() }}
                    </p>
                </div>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($itens->count())
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3 text-xs text-left">ID</th>
                                <th class="px-4 py-3 text-xs text-left">Tipo</th>
                                <th class="px-4 py-3 text-xs text-left">Nome</th>
                                <th class="px-4 py-3 text-xs text-left">Preço</th>
                                <th class="px-4 py-3 text-xs text-left">Unidade</th>
                                <th class="px-4 py-3 text-xs text-left">Estoque</th>
                                <th class="px-4 py-3 text-xs text-left">Status</th>
                                <th class="px-4 py-3 text-xs text-left">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach($itens as $item)
                            <tr class="hover:bg-gray-50">

                                <td class="px-4 py-3 text-sm">{{ $item->id }}</td>

                                <td class="px-4 py-3 text-sm">
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

                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                                {{ $item->ativo
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800' }}">
                                        {{ $item->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->tipo === 'comercial')
                                        <a href="{{ route('itemcomercial.edit', $item) }}">✏️</a>
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
                Nenhum item cadastrado
            </div>
            @endif

        </div>
    </div>
</x-app-layout>