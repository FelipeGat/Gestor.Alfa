<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🧾 Pré-Clientes
        </h2>
    </x-slot>
    <br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-8">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            🔍 Pesquisar Pré-Cliente
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="CPF/CNPJ, Razão Social ou Nome Fantasia" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex gap-3 items-end flex-col lg:flex-row lg:col-span-4 justify-end">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 w-full lg:w-auto
                                   bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium
                                   rounded-lg shadow transition">
                            🔍 Filtrar
                        </button>

                        <a href="{{ route('pre-clientes.create') }}" class="inline-flex items-center justify-center px-4 py-2 w-full lg:w-auto
                                  bg-green-600 hover:bg-green-700 text-white text-sm font-medium
                                  rounded-lg shadow transition">
                            ➕ Novo Pré-Cliente
                        </a>
                    </div>
                </div>
            </form>

            {{-- ================= RESUMO ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Total de Pré-Clientes
                    </p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $preClientes->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Convertidos
                    </p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $preClientes->where('convertido_em_cliente', true)->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-yellow-500">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">
                        Pendentes
                    </p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        {{ $preClientes->where('convertido_em_cliente', false)->count() }}
                    </p>
                </div>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($preClientes->count())
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">CPF/CNPJ
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nome</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">E-mail
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Telefone
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Origem
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($preClientes as $preCliente)
                            <tr class="hover:bg-gray-50 transition">

                                {{-- ID --}}
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-semibold">
                                        {{ $preCliente->id }}
                                    </span>
                                </td>

                                {{-- CPF / CNPJ --}}
                                <td class="px-4 py-3 text-sm text-gray-600 font-mono whitespace-nowrap">
                                    {{ \App\Helpers\FormatHelper::cpfCnpj($preCliente->cpf_cnpj) }}
                                </td>

                                {{-- Nome --}}
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 max-w-[260px] truncate">
                                    {{ $preCliente->nome_fantasia ?? $preCliente->razao_social ?? '—' }}
                                </td>

                                {{-- Email --}}
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $preCliente->email ?? '—' }}
                                </td>

                                {{-- Telefone --}}
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $preCliente->telefone ?? '—' }}
                                </td>

                                {{-- Origem --}}
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                {{ $preCliente->origem === 'orcamento'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($preCliente->origem) }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                {{ $preCliente->convertido_em_cliente
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $preCliente->convertido_em_cliente ? 'Convertido' : 'Pendente' }}
                                    </span>
                                </td>

                                {{-- Ações --}}
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-1">
                                        <a href="{{ route('pre-clientes.edit', $preCliente) }}"
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition">
                                            ✏️
                                        </a>

                                        <form action="{{ route('pre-clientes.destroy', $preCliente) }}" method="POST"
                                            onsubmit="return confirm('Deseja excluir este pré-cliente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="inline-flex items-center px-3 py-1 text-red-600 hover:bg-red-600 hover:text-white text-xs rounded-md transition">
                                                🗑️
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
            @else
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Nenhum pré-cliente encontrado
                </h3>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>