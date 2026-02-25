<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Empresas', 'url' => route('empresas.index')],
            ['label' => 'Editar Empresa']
        ]" />
    </x-slot>

    <x-page-title title="Editar Empresa" :route="route('empresas.index')" />

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

            <form method="POST" action="{{ route('empresas.update', $empresa) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO 1: DADOS DA EMPRESA --}}
                <div class="bg-white rounded-lg p-6 border" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
                        Dados da Empresa
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="razao_social" label="Razão Social" required placeholder="Razão Social da empresa" :value="old('razao_social', $empresa->razao_social)" />
                        <x-form-input name="nome_fantasia" label="Nome Fantasia" placeholder="Nome fantasia (opcional)" :value="old('nome_fantasia', $empresa->nome_fantasia)" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <x-form-input name="cnpj" label="CNPJ" required placeholder="00.000.000/0000-00" :value="old('cnpj', $empresa->cnpj)" />
                        <x-form-input name="endereco" label="Endereço" placeholder="Endereço completo" :value="old('endereco', $empresa->endereco)" />
                    </div>
                </div>

                {{-- SEÇÃO 2: CONTATOS --}}
                <div class="bg-white rounded-lg p-6 border" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
                        Contatos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="email_comercial" label="Email Comercial" type="email" placeholder="comercial@empresa.com" :value="old('email_comercial', $empresa->email_comercial)" />
                        <x-form-input name="email_administrativo" label="Email Administrativo" type="email" placeholder="adm@empresa.com" :value="old('email_administrativo', $empresa->email_administrativo)" />
                    </div>
                </div>

                {{-- SEÇÃO 3: STATUS --}}
                <div class="bg-white rounded-lg p-6 border" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">
                        Status
                    </h3>

                    <div class="max-w-xs">
                        <x-form-select name="ativo" label="Situação da Empresa" placeholder="Selecione" :selected="old('ativo', $empresa->ativo)">
                            <option value="1">Ativa</option>
                            <option value="0">Inativa</option>
                        </x-form-select>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex justify-end gap-3 mt-6">
                    <x-button href="{{ route('empresas.index') }}" variant="danger" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </x-button>

                    <x-button type="submit" variant="primary" class="min-w-[130px]">
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
