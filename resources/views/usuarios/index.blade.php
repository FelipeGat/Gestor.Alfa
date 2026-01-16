<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            👤 Usuários do Sistema
        </h2>
    </x-slot>

    <br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- AÇÕES --}}
            <div class="flex justify-end mb-4">
                <a href="{{ route('usuarios.create') }}" class="inline-flex items-center px-4 py-2
                          bg-green-600 hover:bg-green-700
                          text-white text-sm font-medium
                          rounded-lg shadow transition">
                    ➕ Novo Usuário
                </a>
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
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="inline-flex items-center px-3 py-1.5
                                            bg-blue-600 hover:bg-blue-700
                                            text-white text-xs font-medium
                                            rounded-lg shadow transition">
                                        ✏️ Editar
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

        </div>
    </div>
</x-app-layout>