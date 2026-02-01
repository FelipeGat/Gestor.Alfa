<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

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
                            <option value="financeiro">Financeiro</option>
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

                <hr class="my-6">

                {{-- EMPRESAS --}}
                <h3 class="text-lg font-semibold mb-4">Empresas com Acesso</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($empresas as $empresa)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="empresas[]" value="{{ $empresa->id }}"
                            class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">
                            {{ $empresa->nome_fantasia ?? $empresa->nome }}
                        </span>
                    </label>
                    @endforeach
                </div>


                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('usuarios.index') }}"
                        class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition duration-200">

                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>

                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Salvar Funcionario
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>