<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ➕ Novo Usuário
        </h2>
    </x-slot>

    <br>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('usuarios.store') }}" class="bg-white shadow rounded-lg p-6">
                @csrf

                {{-- DADOS DO USUÁRIO --}}
                <h3 class="text-lg font-semibold mb-4">Dados do Usuário</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" name="name" required
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Senha</label>
                        <input type="password" name="password" required
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Tipo de Usuário</label>
                        <select name="tipo" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="admin">Admin</option>
                            <option value="administrativo">Administrativo</option>
                            <option value="comercial">Comercial</option>
                        </select>
                    </div>

                </div>

                <hr class="my-6">

                {{-- PERFIS --}}
                <h3 class="text-lg font-semibold mb-4">Perfis</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($perfis as $perfil)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="perfis[]" value="{{ $perfil->id }}"
                            class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">{{ $perfil->nome }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('usuarios.index') }}"
                        class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>

                    <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700
                                   text-white font-medium">
                        💾 Salvar
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>