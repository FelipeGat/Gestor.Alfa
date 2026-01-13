<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            👥 Clientes
        </h2>
    </x-slot>
    <br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <div class="flex flex-col lg:col-span-6">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            🔍 Pesquisar Cliente
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nome, CPF/CNPJ ou E-mail" class="border border-gray-300 rounded-lg px-3 py-2 text-sm
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
                        </select>
                    </div>
                    <br>
                    <div class="flex gap-3 items-end flex-col lg:flex-row lg:col-span-3 justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 w-full lg:w-auto
                                        bg-blue-600 hover:bg-blue-700 text-green-600 text-sm font-medium rounded-lg shadow transition duration-200">
                            🔍 Filtrar
                        </button>

                        @if(auth()->user()->canPermissao('clientes', 'incluir'))
                        <a href="{{ route('clientes.create') }}"
                            class="inline-flex items-center justify-center px-4 py-2 w-full lg:w-auto
                                    bg-green-600 hover:bg-green-700 text-blue text-sm font-medium rounded-lg shadow transition duration-200">
                            ➕ Novo Cliente
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
        grid-template-columns: repeat(1, minmax(0, 1fr));
    ">
                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-blue-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Clientes</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $clientes->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-green-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $clientes->where('ativo', true)->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-red-600 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Inativos</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $clientes->where('ativo', false)->count() }}
                    </p>
                </div>

                <div class="bg-white p-6 shadow rounded-lg border-l-4 border-yellow-500 w-full max-w-none">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Receita Mensal</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">
                        R$ {{ number_format($clientes->sum('valor_mensal'), 2, ',', '.') }}
                    </p>
                </div>
            </div>



            {{-- ================= TABELA ================= --}}
            @if($clientes->count())
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
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Valor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Venc.</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($clientes as $cliente)
                            @php
                            $email = $cliente->emails->where('principal', true)->first();
                            $tel = $cliente->telefones->where('principal', true)->first();
                            @endphp

                            <tr class="hover:bg-gray-50 transition">

                                {{-- ID --}}
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-semibold">
                                        {{ $cliente->id }}
                                    </span>
                                </td>

                                {{-- CPF / CNPJ --}}
                                <td class="px-4 py-3 text-sm text-gray-600 font-mono whitespace-nowrap min-w-[160px]">
                                    {{ \App\Helpers\FormatHelper::cpfCnpj($cliente->cpf_cnpj) }}
                                </td>

                                {{-- Nome --}}
                                <td
                                    class="px-4 py-3 text-sm font-medium text-gray-900 min-w-[200px] max-w-[280px] truncate">
                                    {{ $cliente->nome }}
                                </td>

                                {{-- E-mail --}}
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap min-w-[260px]">
                                    @if($email)
                                    <a href="mailto:{{ $email->valor }}"
                                        class="text-blue-600 hover:text-blue-700 hover:underline">
                                        {{ $email->valor }}
                                    </a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Telefone --}}
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap min-w-[140px]">
                                    @if($tel)
                                    <a href="tel:{{ preg_replace('/\D/', '', $tel->valor) }}"
                                        class="text-blue-600 hover:text-blue-700 hover:underline">
                                        {{ $tel->valor }}
                                    </a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Valor --}}
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                    @if($cliente->valor_mensal)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        R$ {{ number_format($cliente->valor_mensal, 2, ',', '.') }}
                                    </span>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Vencimento --}}
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    @if($cliente->dia_vencimento)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Dia {{ $cliente->dia_vencimento }}
                                    </span>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                {{ $cliente->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $cliente->ativo ? '✓ Ativo' : '✗ Inativo' }}
                                    </span>
                                </td>

                                {{-- Ações --}}
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <div class="flex gap-1 items-center">

                                        @if(auth()->user()->tipo === 'admin' || auth()->user()->canPermissao('clientes',
                                        'editar'))
                                        <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700
                                              text-white text-xs rounded-md transition">
                                            ✏️
                                        </a>
                                        @endif

                                        @if(auth()->user()->tipo === 'admin' || auth()->user()->canPermissao('clientes',
                                        'excluir'))
                                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este cliente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center px-2 py-1 border border-red-600
                                                   text-red-600 hover:bg-red-50 text-xs rounded-md transition">
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
                <h3 class="text-lg font-medium text-gray-900">Nenhum cliente encontrado</h3>
            </div>
            @endif

        </div>
    </div>

    </div>
    </div>
</x-app-layout>