<x-app-layout>

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Setores', 'url' => route('admin.setores.index')],
            ['label' => 'Novo Setor']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Novo Setor</h2>
                    <p class="text-sm text-gray-500 mt-1">Preencha as informações abaixo para cadastrar um novo setor</p>
                </div>

                <form action="{{ route('admin.setores.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="cliente_id" value="Cliente *" />
                        <select id="cliente_id" name="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Selecione um cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                    {{ $cliente->nome_exibicao }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nome" value="Nome do Setor *" />
                        <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome')" required autofocus />
                        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="descricao" value="Descrição" />
                        <textarea id="descricao" name="descricao" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('descricao') }}</textarea>
                        <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
                    </div>

                    {{-- Botões --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                        <x-button type="submit" variant="success">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Salvar
                        </x-button>
                        <a href="{{ route('admin.setores.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
