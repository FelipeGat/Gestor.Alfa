<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ✏️ Editar Assunto
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER DO FORMULÁRIO --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg rounded-lg
                        px-6 py-4 sm:px-8 sm:py-6 mb-6">
                <h1 class="text-2xl font-bold text-black">Editar Assunto</h1>
                <p class="text-blue-100 text-sm mt-1">
                    Atualize as informações do assunto
                </p>
            </div>

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

            <form method="POST" action="{{ route('assuntos.update', $assunto) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO: CLASSIFICAÇÃO --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        🏢 Classificação do Assunto
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

                        {{-- EMPRESA --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Empresa <span class="text-red-500">*</span>
                            </label>
                            <select name="empresa_id" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                           focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                <option value="">Selecione a empresa</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id', $assunto->empresa_id) ==
                                    $empresa->id)>
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
                                           focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                <option value="">Selecione</option>
                                <option value="SERVICO" @selected(old('tipo', $assunto->tipo)=='SERVICO')>
                                    Serviço
                                </option>
                                <option value="VENDA" @selected(old('tipo', $assunto->tipo)=='VENDA')>
                                    Venda
                                </option>
                            </select>
                        </div>

                        {{-- STATUS --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select name="ativo" class="w-full rounded-lg border border-gray-300 shadow-sm
                                           focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                <option value="1" @selected(old('ativo', $assunto->ativo)==1)>Ativo</option>
                                <option value="0" @selected(old('ativo', $assunto->ativo)==0)>Inativo</option>
                            </select>
                        </div>

                    </div>
                </div>

                {{-- SEÇÃO: DADOS DO ASSUNTO --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        🏷️ Dados do Assunto
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">

                        {{-- ASSUNTO --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Assunto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nome" value="{{ old('nome', $assunto->nome) }}" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                          focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        {{-- CATEGORIA --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Categoria <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="categoria" value="{{ old('categoria', $assunto->categoria) }}"
                                required class="w-full rounded-lg border border-gray-300 shadow-sm
                                          focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        {{-- SUBCATEGORIA --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                SubCategoria <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="subcategoria"
                                value="{{ old('subcategoria', $assunto->subcategoria) }}" required class="w-full rounded-lg border border-gray-300 shadow-sm
                                          focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3
                            bg-white shadow rounded-lg p-6 sm:p-8">

                    <a href="{{ route('assuntos.index') }}" class="inline-flex items-center justify-center px-6 py-2
                              rounded-lg border border-gray-300 text-gray-700 font-medium
                              hover:bg-gray-50 transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancelar
                    </a>

                    <button type="submit" class="inline-flex items-center justify-center px-8 py-2
                                   bg-gradient-to-r from-blue-600 to-blue-700
                                   text-green-600 rounded-lg font-medium
                                   hover:from-blue-700 hover:to-blue-800
                                   transition duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Atualizar Assunto
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>