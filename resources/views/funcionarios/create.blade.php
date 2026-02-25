<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Funcionários', 'url' => route('funcionarios.index')],
            ['label' => 'Novo Funcionário']
        ]" />
    </x-slot>

    <x-page-title title="Novo Funcionário" :route="route('funcionarios.index')" />

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

            <form action="{{ route('funcionarios.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- SEÇÃO 1: DADOS DO FUNCIONÁRIO --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados do Funcionário
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <x-form-input name="nome" label="Nome" required placeholder="Nome completo do funcionário" />
                        <x-form-input name="email" label="Email de Acesso" type="email" required placeholder="email@empresa.com" />
                    </div>
                </div>

                {{-- SEÇÃO 2: STATUS --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Status
                    </h3>

                    <div class="max-w-xs">
                        <x-form-select name="ativo" label="Situação do Funcionário" placeholder="Selecione">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </x-form-select>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <x-button href="{{ route('funcionarios.index') }}" variant="danger" size="md" class="min-w-[130px]">
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
