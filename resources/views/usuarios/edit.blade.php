<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ✏️ Editar Usuário
        </h2>
    </x-slot>

    <br>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('usuarios.update', $usuario) }}"
                class="bg-white shadow rounded-lg p-6">
                @csrf
                @method('PUT')

                {{-- DADOS DO USUÁRIO --}}
                <h3 class="text-lg font-semibold mb-4">Dados do Usuário</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Nova Senha <span class="text-xs text-gray-500">(opcional)</span>
                        </label>
                        <input type="password" name="password"
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Tipo de Usuário</label>
                        <select name="tipo" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            @foreach(['admin','administrativo','financeiro','comercial','cliente'] as $tipo)
                            <option value="{{ $tipo }}" @selected($usuario->tipo === $tipo)>
                                {{ ucfirst($tipo) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <hr class="my-6">

                {{-- PERFIS --}}
                <h3 class="text-lg font-semibold mb-4">Perfis</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($perfis as $perfil)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="perfis[]" value="{{ $perfil->id }}" class="rounded border-gray-300"
                            @checked($usuario->perfis->contains($perfil->id))>
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
                            class="rounded border-gray-300" @checked($usuario->empresas->contains($empresa->id))>
                        <span class="text-sm text-gray-700">
                            {{ $empresa->nome_fantasia ?? $empresa->nome }}
                        </span>
                    </label>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button href="{{ route('usuarios.index') }}">
                        Cancelar
                    </x-secondary-button>

                    <x-primary-button>
                        Atualizar Usuário
                    </x-primary-button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>