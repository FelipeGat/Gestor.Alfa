<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Empresa
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER --}}
            <div class="mb-4">
                <x-back-button />
            </div>

            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 sm:px-8 sm:py-6 mb-6">
                <h1 class="text-2xl font-bold text-black">Editar Empresa</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Atualize os dados cadastrais da empresa
                </p>
            </div>

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow">
                <h3 class="font-medium mb-2">Erros encontrados:</h3>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('empresas.update', $empresa) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO 1: DADOS DA EMPRESA --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados da Empresa
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Razão Social <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="razao_social"
                                value="{{ old('razao_social', $empresa->razao_social) }}" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome Fantasia
                            </label>
                            <input type="text" name="nome_fantasia"
                                value="{{ old('nome_fantasia', $empresa->nome_fantasia) }}" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                CNPJ <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="cnpj" value="{{ old('cnpj', $empresa->cnpj) }}" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Endereço
                            </label>
                            <input type="text" name="endereco" value="{{ old('endereco', $empresa->endereco) }}" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 2: CONTATOS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Contatos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Comercial
                            </label>
                            <input type="email" name="email_comercial"
                                value="{{ old('email_comercial', $empresa->email_comercial) }}" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Administrativo
                            </label>
                            <input type="email" name="email_administrativo"
                                value="{{ old('email_administrativo', $empresa->email_administrativo) }}" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: STATUS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Status
                    </h3>

                    <div class="max-w-xs">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Situação da Empresa
                        </label>
                        <select name="ativo" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            <option value="1" @selected(old('ativo', $empresa->ativo)==1)>Ativa</option>
                            <option value="0" @selected(old('ativo', $empresa->ativo)==0)>Inativa</option>
                        </select>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3
                            bg-white shadow rounded-lg p-6 sm:p-8">

                    <a href="{{ route('empresas.index') }}"
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