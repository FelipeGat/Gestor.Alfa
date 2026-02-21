<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    <style>
        select[name="ativo"] {
            border-color: #d1d5db !important;
            color: #111827 !important;
            font-family: 'Inter', sans-serif !important;
            font-size: 14px !important;
        }
        select[name="ativo"]:focus {
            border-color: #3f9cae !important;
            box-shadow: 0 0 0 1px rgba(63, 156, 174, 0.2) !important;
        }
        select[name="ativo"] option {
            color: #111827;
        }
        .form-section {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        .form-section h3 {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: #111827;
        }
        input[type="text"],
        input[type="email"] {
            font-family: 'Inter', sans-serif !important;
            font-size: 14px !important;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #3f9cae !important;
            box-shadow: 0 0 0 3px rgba(63, 156, 174, 0.1) !important;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Empresas', 'url' => route('empresas.index')],
            ['label' => 'Nova Empresa']
        ]" />
    </x-slot>

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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
                <div class="form-section p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados da Empresa
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Razão Social <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="razao_social" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2"
                                placeholder="Razão Social da empresa">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nome Fantasia
                            </label>
                            <input type="text" name="nome_fantasia" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2"
                                placeholder="Nome fantasia (opcional)">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                CNPJ <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="cnpj" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2"
                                placeholder="00.000.000/0000-00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Endereço
                            </label>
                            <input type="text" name="endereco" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2"
                                placeholder="Endereço completo">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 2: CONTATOS --}}
                <div class="form-section p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Contatos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Comercial
                            </label>
                            <input type="email" name="email_comercial" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2"
                                placeholder="comercial@empresa.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email Administrativo
                            </label>
                            <input type="email" name="email_administrativo" class="w-full rounded-lg border border-gray-300 shadow-sm
                                       focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2"
                                placeholder="adm@empresa.com">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: STATUS --}}
                <div class="form-section p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Status
                    </h3>

                    <div class="max-w-xs">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Situação da Empresa
                        </label>
                        <select name="ativo" class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2">
                            <option value="1" selected>Ativa</option>
                            <option value="0">Inativa</option>
                        </select>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">

                    <a href="{{ route('empresas.index') }}"
                        class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition duration-200"
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #ef4444; color: white; border: none; border-radius: 9999px; min-width: 130px; justify-content: center; box-shadow: none;"
                        onmouseover="this.style.boxShadow='0 4px 6px rgba(239, 68, 68, 0.4)'" onmouseout="this.style.boxShadow='none'">

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