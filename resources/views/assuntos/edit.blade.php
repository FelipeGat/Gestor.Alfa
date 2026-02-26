<x-app-layout>
    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Assuntos', 'url' => route('assuntos.index')],
            ['label' => 'Editar Assunto']
        ]" />
    </x-slot>

    <x-page-title title="Editar Assunto" :route="route('assuntos.index')" />

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

            <form method="POST" action="{{ route('assuntos.update', $assunto) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO: CLASSIFICAÇÃO --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Classificação do Assunto
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="col-span-1 sm:col-span-2">
                            <x-form-select name="empresa_id" label="Empresa" required placeholder="Selecione a empresa">
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" @selected(old('empresa_id', $assunto->empresa_id) == $empresa->id)>
                                        {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                                    </option>
                                @endforeach
                            </x-form-select>
                        </div>

                        <div>
                            <x-form-select name="tipo" label="Tipo" required placeholder="Selecione">
                                <option value="SERVICO" @selected(old('tipo', $assunto->tipo) == 'SERVICO')>Serviço</option>
                                <option value="VENDA" @selected(old('tipo', $assunto->tipo) == 'VENDA')>Venda</option>
                                <option value="COMERCIAL" @selected(old('tipo', $assunto->tipo) == 'COMERCIAL')>Comercial</option>
                                <option value="ADMINISTRATIVO" @selected(old('tipo', $assunto->tipo) == 'ADMINISTRATIVO')>Administrativo</option>
                            </x-form-select>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO: DADOS DO ASSUNTO --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados do Assunto
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="col-span-1 sm:col-span-2">
                            <x-form-input name="nome" label="Assunto" required placeholder="Ex: Pintura, Instalação de Ar Condicionado" :value="old('nome', $assunto->nome)" />
                        </div>

                        <div>
                            <x-form-input name="categoria" label="Categoria" required placeholder="Ex: Acabamento, Refrigeração" :value="old('categoria', $assunto->categoria)" />
                        </div>

                        <div>
                            <x-form-input name="subcategoria" label="SubCategoria" required placeholder="Ex: Reformas e Reparos" :value="old('subcategoria', $assunto->subcategoria)" />
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO: STATUS --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Status
                    </h3>

                    <div class="max-w-xs">
                        <x-form-select name="ativo" label="Situação do Assunto" placeholder="Selecione">
                            <option value="1" @selected(old('ativo', $assunto->ativo) == 1)>Ativo</option>
                            <option value="0" @selected(old('ativo', $assunto->ativo) == 0)>Inativo</option>
                        </x-form-select>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <x-button href="{{ route('assuntos.index') }}" variant="danger" size="md" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </x-button>

                    <x-button type="submit" variant="primary" size="md" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </x-button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
