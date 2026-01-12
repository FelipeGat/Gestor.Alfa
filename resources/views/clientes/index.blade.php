@php
abort_if(
!auth()->user()->canPermissao('clientes', 'ler'),
403,
'Acesso não autorizado'
);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Clientes
        </h2>
    </x-slot>

    <div class="flex justify-center min-h-screen bg-gray-100">
        <div class="w-3/4 py-12 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-wrap gap-4 items-end">

                    <div class="flex flex-col flex-1 min-w-[240px]">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Cliente
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome ou E-mail"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

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

                    <div class="flex gap-3 items-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2
                                        bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            🔍 Filtrar
                        </button>

                        @if(auth()->user()->canPermissao('clientes', 'incluir'))
                        <a href="{{ route('clientes.create') }}"
                            class="inline-flex items-center justify-center px-4 py-2
                                    bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            ➕ Novo Cliente
                        </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- ================= RESUMO ================= --}}
            <div class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[140px] bg-white p-4 shadow rounded-lg border-l-4 border-blue-600">
                    <p class="text-xs text-gray-600">Total</p>
                    <p class="text-lg font-bold text-blue-600">{{ $clientes->count() }}</p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow rounded-lg border-l-4 border-green-600">
                    <p class="text-xs text-gray-600">Ativos</p>
                    <p class="text-lg font-bold text-green-600">
                        {{ $clientes->where('ativo', true)->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow rounded-lg border-l-4 border-red-600">
                    <p class="text-xs text-gray-600">Inativos</p>
                    <p class="text-lg font-bold text-red-600">
                        {{ $clientes->where('ativo', false)->count() }}
                    </p>
                </div>

                <div class="flex-1 min-w-[140px] bg-white p-4 shadow rounded-lg border-l-4 border-yellow-500">
                    <p class="text-xs text-gray-600">Receita Mensal</p>
                    <p class="text-lg font-bold text-yellow-600">
                        R$ {{ number_format($clientes->sum('valor_mensal'), 2, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- ================= TABELA ================= --}}
            @if($clientes->count())
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700">ID</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700">Nome</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700">E-mail</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700">Telefone</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700">Valor</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700">Venc.</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-700">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach($clientes as $cliente)
                            @php
                            $email = $cliente->emails->where('principal', true)->first();
                            $tel = $cliente->telefones->where('principal', true)->first();
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-xs">{{ $cliente->id }}</td>
                                <td class="px-4 py-3 text-xs font-medium">{{ $cliente->nome }}</td>
                                <td class="px-4 py-3 text-xs">{{ $email->valor ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs">{{ $tel->valor ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $cliente->valor_mensal ? 'R$ '.number_format($cliente->valor_mensal,2,',','.') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $cliente->dia_vencimento ? 'Dia '.$cliente->dia_vencimento : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs
                                            {{ $cliente->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $cliente->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    <div class="flex gap-2 items-center">

                                        @if(
                                        auth()->user()->tipo === 'admin' ||
                                        auth()->user()->canPermissao('clientes', 'editar')
                                        )
                                        <a href="{{ route('clientes.edit', $cliente) }}"
                                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md transition duration-200">
                                            Editar
                                        </a>
                                        @endif

                                        @if(
                                        auth()->user()->tipo === 'admin' ||
                                        auth()->user()->canPermissao('clientes', 'excluir')
                                        )
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
                                        @endif

                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>