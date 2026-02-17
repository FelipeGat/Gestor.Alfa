<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Usuário
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER DO FORMULÁRIO --}}
            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 sm:px-8 sm:py-6 mb-6">
                <h1 class="text-2xl font-bold text-black">Editar Usuário</h1>
                <p class="text-gray-600 text-sm mt-1">
                    Atualize os dados do usuário
                </p>
            </div>

            <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO 1: DADOS DO USUÁRIO --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados do Usuário
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                                class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                                class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nova Senha <span class="text-xs text-gray-500">(opcional)</span>
                            </label>
                            <input type="password" name="password"
                                class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tipo de Usuário
                            </label>
                            <select name="tipo" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                @foreach(['admin','administrativo','financeiro','comercial','cliente'] as $tipo)
                                <option value="{{ $tipo }}" @selected($usuario->tipo === $tipo)>
                                    {{ ucfirst($tipo) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 2: PERFIS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Perfis
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($perfis as $perfil)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="perfis[]" value="{{ $perfil->id }}" class="rounded-full border-gray-300"
                                @checked($usuario->perfis->contains($perfil->id))>
                            <span class="text-sm text-gray-700">{{ $perfil->nome }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- SEÇÃO 3: EMPRESAS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Empresas com Acesso
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($empresas as $empresa)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="empresas[]" value="{{ $empresa->id }}"
                                class="rounded-full border-gray-300" @checked($usuario->empresas->contains($empresa->id))>
                            <span class="text-sm text-gray-700">
                                {{ $empresa->nome_fantasia ?? $empresa->nome }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3
                            bg-white shadow rounded-lg p-6 sm:p-8">

                    <a href="{{ route('usuarios.index') }}"
                        class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition duration-200"
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #ef4444; color: white; border: none; border-radius: 9999px; min-width: 130px; justify-content: center;">

                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>

                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary"
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #3b82f6; border-radius: 9999px; min-width: 130px; justify-content: center;">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
