<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏢 Nova Empresa
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER DO FORMULÁRIO --}}
            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 sm:px-8 sm:py-6 mb-6">
                <h1 class="text-2xl font-bold text-black">Cadastro de Empresa</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Preencha os dados abaixo para cadastrar uma nova empresa
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

            <form action="{{ route('empresas.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- SEÇÃO 1: DADOS DA EMPRESA --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        🏷️ Dados da Empresa
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Razão Social <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="razao_social" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                placeholder="Razão Social da empresa">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome Fantasia
                            </label>
                            <input type="text" name="nome_fantasia" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                placeholder="Nome fantasia (opcional)">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                CNPJ <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="cnpj" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                placeholder="00.000.000/0000-00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Endereço
                            </label>
                            <input type="text" name="endereco" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                placeholder="Endereço completo">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 2: CONTATOS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        📧 Contatos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Comercial
                            </label>
                            <input type="email" name="email_comercial" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                placeholder="comercial@empresa.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Administrativo
                            </label>
                            <input type="email" name="email_administrativo" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                placeholder="adm@empresa.com">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: STATUS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        ⚙️ Status
                    </h3>

                    <div class="max-w-xs">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Situação da Empresa
                        </label>
                        <select name="ativo" class="w-full rounded-lg border border-gray-300 shadow-sm
                                   focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            <option value="1" selected>Ativa</option>
                            <option value="0">Inativa</option>
                        </select>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3
                            bg-white shadow rounded-lg p-6 sm:p-8">

                    <a href="{{ route('empresas.index') }}"
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
                        Salvar Empresa
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>