<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Categorias Financeiras', 'url' => route('categorias.index')],
            ['label' => 'Nova Categoria']
        ]" />
    </x-slot>

    <x-page-title title="Nova Categoria" :route="route('categorias.index')" />

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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

            <form action="{{ route('categorias.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados da Categoria
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="col-span-1 sm:col-span-2">
                            <x-form-input name="nome" label="Nome" required placeholder="Ex: Alimentação, Transporte" />
                        </div>

                        <div>
                            <x-form-select name="tipo" label="Tipo" required placeholder="Selecione">
                                <option value="FIXA" @selected(old('tipo') == 'FIXA')>Fixa</option>
                                <option value="VARIAVEL" @selected(old('tipo') == 'VARIAVEL')>Variável</option>
                                <option value="INVESTIMENTO" @selected(old('tipo') == 'INVESTIMENTO')>Investimento</option>
                            </x-form-select>
                        </div>

                        <div>
                            <x-form-select name="ativo" label="Status" placeholder="Selecione">
                                <option value="1" @selected(old('ativo', 1) == 1)>Ativo</option>
                                <option value="0" @selected(old('ativo') === 0)>Inativo</option>
                            </x-form-select>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <x-button href="{{ route('categorias.index') }}" variant="danger" size="md" class="min-w-[130px]">
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
