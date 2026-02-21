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
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: #111827;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"],
        select {
            font-family: 'Inter', sans-serif !important;
            font-size: 14px !important;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: #3f9cae !important;
            box-shadow: 0 0 0 1px rgba(63, 156, 174, 0.2) !important;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Assuntos', 'url' => route('assuntos.index')],
            ['label' => 'Novo Assunto']
        ]" />
    </x-slot>

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-medium text-red-800 mb-2">
                            Erros encontrados:
                        </h3>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('assuntos.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- SEÇÃO: CLASSIFICAÇÃO --}}
                <div class="form-section p-6 sm:p-8" style="margin-top: 0 !important;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Classificação do Assunto
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

                        {{-- EMPRESA --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Empresa <span class="text-red-500">*</span>
                            </label>
                            <select name="empresa_id" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                           focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2 text-gray-900">
                                <option value="">Selecione a empresa</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id')==$empresa->id)>
                                    {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- TIPO --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tipo <span class="text-red-500">*</span>
                            </label>
                            <select name="tipo" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                           focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2 text-gray-900">
                                <option value="">Selecione</option>
                                <option value="SERVICO" @selected(old('tipo')=='SERVICO' )>Serviço</option>
                                <option value="VENDA" @selected(old('tipo')=='VENDA' )>Venda</option>
                                <option value="COMERCIAL" @selected(old('tipo')=='COMERCIAL' )>Comercial</option>
                                <option value="ADMINISTRATIVO" @selected(old('tipo')=='ADMINISTRATIVO' )>Administrativo
                                </option>

                            </select>
                        </div>

                    </div>
                </div>

                {{-- SEÇÃO: DADOS DO ASSUNTO --}}
                <div class="form-section p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados do Assunto
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">

                        {{-- ASSUNTO --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Assunto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nome" value="{{ old('nome') }}" required
                                placeholder="Ex: Pintura, Instalação de Ar Condicionado" class="w-full rounded-lg border border-gray-300 shadow-sm
                                          focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>

                        {{-- CATEGORIA --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Categoria <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="categoria" value="{{ old('categoria') }}" required
                                placeholder="Ex: Acabamento, Refrigeração" class="w-full rounded-lg border border-gray-300 shadow-sm
                                          focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>

                        {{-- SUBCATEGORIA --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                SubCategoria <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="subcategoria" value="{{ old('subcategoria') }}" required
                                placeholder="Ex: Reformas e Reparos" class="w-full rounded-lg border border-gray-300 shadow-sm
                                          focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>

                    </div>
                </div>

                {{-- SEÇÃO: STATUS --}}
                <div class="form-section p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Status
                    </h3>

                    <div class="max-w-xs">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Situação do Assunto
                        </label>
                        <select name="ativo" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2 text-gray-900">
                            <option value="1" @selected(old('ativo',1)==1)>Ativo</option>
                            <option value="0" @selected(old('ativo')===0)>Inativo</option>
                        </select>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <a href="{{ route('assuntos.index') }}"
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
