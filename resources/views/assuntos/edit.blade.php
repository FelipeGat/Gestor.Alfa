<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Assunto
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">

                <form method="POST" action="{{ route('assuntos.update', $assunto) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Nome do Assunto</label>
                        <input type="text" name="nome" value="{{ old('nome', $assunto->nome) }}"
                            class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    <div class="mb-6">
                        <input type="hidden" name="ativo" value="0">

                        <label class="inline-flex items-center">
                            <input type="checkbox" name="ativo" value="1"
                                {{ old('ativo', $assunto->ativo) ? 'checked' : '' }}>
                            <span class="ml-2">Assunto ativo</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('assuntos.index') }}"
                            class="px-4 py-2 bg-gray-300 text-red-600 rounded hover:bg-gray-400">
                            Cancelar
                        </a>

                        <button type="submit" class="px-4 py-2 bg-blue-600 text-green-600 rounded hover:bg-blue-700">
                            Atualizar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>