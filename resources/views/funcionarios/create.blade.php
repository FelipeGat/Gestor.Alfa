<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Novo Funcionário
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('funcionarios.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Nome</label>
                        <input type="text" name="nome" class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Email de Acesso</label>
                        <input type="email" name="email" class="mt-1 block w-full rounded border-gray-300
                              @error('email') border-red-500 @enderror" value="{{ old('email') }}" required>

                        @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700">Status</label>
                        <select name="ativo" class="mt-1 block w-full rounded border-gray-300">
                            <option value="1" selected>Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('funcionarios.index') }}"
                            class="px-4 py-2 bg-gray-300 text-red-600 rounded hover:bg-gray-400">
                            Voltar
                        </a>

                        <button type="submit" class="px-4 py-2 bg-blue-600 text-green-600 rounded hover:bg-blue-700">
                            Salvar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>