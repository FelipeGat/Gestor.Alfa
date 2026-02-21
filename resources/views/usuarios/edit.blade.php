<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    <style>
        .form-section {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        .form-section h3 {
            font-family: Figtree, sans-serif;
            font-weight: 600;
            color: #111827;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            font-family: Figtree, sans-serif !important;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #3f9cae !important;
            box-shadow: 0 0 0 1px rgba(63, 156, 174, 0.2) !important;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Usuários', 'url' => route('usuarios.index')],
            ['label' => 'Editar Usuário']
        ]" />
    </x-slot>

    <div class="pb-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">

            <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO 1: DADOS DO USUÁRIO --}}
                <div class="form-section p-6 sm:p-8" style="margin-top: 0 !important;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados do Usuário
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nova Senha <span class="text-xs text-gray-500">(opcional)</span>
                            </label>
                            <input type="password" name="password"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tipo de Usuário
                            </label>
                            <select name="tipo" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2 text-gray-900">
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
                <div class="form-section p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Perfis
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($perfis as $perfil)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="perfis[]" value="{{ $perfil->id }}" class="rounded-lg border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae]"
                                @checked($usuario->perfis->contains($perfil->id))>
                            <span class="text-sm text-gray-700">{{ $perfil->nome }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- SEÇÃO 3: EMPRESAS --}}
                <div class="form-section p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Empresas com Acesso
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($empresas as $empresa)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="empresas[]" value="{{ $empresa->id }}"
                                class="rounded-lg border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae]" @checked($usuario->empresas->contains($empresa->id))>
                            <span class="text-sm text-gray-700">
                                {{ $empresa->nome_fantasia ?? $empresa->nome }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">

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
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #3f9cae; border-radius: 9999px; min-width: 130px; justify-content: center;">
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
