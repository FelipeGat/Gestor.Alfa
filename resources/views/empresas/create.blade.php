<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Empresas', 'url' => route('empresas.index')],
            ['label' => 'Nova Empresa']
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

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <form action="{{ route('empresas.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="bg-white rounded-lg p-6 border" style="border: 1px solid #3f9cae; border-top-width: 4px;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Dados da Empresa</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="razao_social" label="Razão Social" required placeholder="Razão Social da empresa" />
                        <x-form-input name="nome_fantasia" label="Nome Fantasia" placeholder="Nome fantasia (opcional)" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <x-form-input name="cnpj" label="CNPJ" required placeholder="00.000.000/0000-00" />
                        <x-form-input name="endereco" label="Endereço" placeholder="Endereço completo" />
                    </div>
                </div>

                <div class="bg-white rounded-lg p-6 border" style="border: 1px solid #3f9cae; border-top-width: 4px;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contatos</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="email_comercial" label="Email Comercial" type="email" placeholder="comercial@empresa.com" />
                        <x-form-input name="email_administrativo" label="Email Administrativo" type="email" placeholder="adm@empresa.com" />
                    </div>
                </div>

                <div class="bg-white rounded-lg p-6 border" style="border: 1px solid #3f9cae; border-top-width: 4px;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>

                    <div class="max-w-xs">
                        <x-form-select name="ativo" label="Situação da Empresa" placeholder="Selecione">
                            <option value="1" selected>Ativa</option>
                            <option value="0">Inativa</option>
                        </x-form-select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-button href="{{ route('empresas.index') }}" variant="danger" class="min-w-[130px]">
                        Cancelar
                    </x-button>

                    <x-button type="submit" variant="primary" class="min-w-[130px]">
                        Salvar
                    </x-button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
