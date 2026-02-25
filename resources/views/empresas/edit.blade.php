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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-end mt-4 mb-2">
            <a href="{{ route('empresas.index') }}" class="inline-flex items-center px-4 py-2 rounded-full border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
        </div>
    </div>

        </div>
    </div>

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ERROS --}}
            @if ($errors->any())
            <x-alert type="danger" class="mb-4">
                <h3 class="font-medium mb-2">Erros encontrados:</h3>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </x-alert>
            @endif

            <form method="POST" action="{{ route('empresas.update', $empresa) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO 1: DADOS DA EMPRESA --}}
                <x-card>
                    <x-slot name="title">Dados da Empresa</x-slot>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="razao_social" label="Razão Social" required placeholder="Razão Social da empresa" :value="old('razao_social', $empresa->razao_social)" />
                        <x-form-input name="nome_fantasia" label="Nome Fantasia" placeholder="Nome fantasia (opcional)" :value="old('nome_fantasia', $empresa->nome_fantasia)" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <x-form-input name="cnpj" label="CNPJ" required placeholder="00.000.000/0000-00" :value="old('cnpj', $empresa->cnpj)" />
                        <x-form-input name="endereco" label="Endereço" placeholder="Endereço completo" :value="old('endereco', $empresa->endereco)" />
                    </div>
                </x-card>

                {{-- SEÇÃO 2: CONTATOS --}}
                <x-card>
                    <x-slot name="title">Contatos</x-slot>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="email_comercial" label="Email Comercial" type="email" placeholder="comercial@empresa.com" :value="old('email_comercial', $empresa->email_comercial)" />
                        <x-form-input name="email_administrativo" label="Email Administrativo" type="email" placeholder="adm@empresa.com" :value="old('email_administrativo', $empresa->email_administrativo)" />
                    </div>
                </x-card>

                {{-- SEÇÃO 3: STATUS --}}
                <x-card>
                    <x-slot name="title">Status</x-slot>

                    <div class="max-w-xs">
                        <x-form-select name="ativo" label="Situação da Empresa" placeholder="Selecione" :selected="old('ativo', $empresa->ativo)">
                            <option value="1">Ativa</option>
                            <option value="0">Inativa</option>
                        </x-form-select>
                    </div>
                </x-card>

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
