<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Empresa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">

                <form method="POST" action="{{ route('empresas.update', $empresa) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Razão Social</label>
                        <input type="text" name="razao_social" value="{{ old('razao_social', $empresa->razao_social) }}"
                            class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Nome Fantasia</label>
                        <input type="text" name="nome_fantasia"
                            value="{{ old('nome_fantasia', $empresa->nome_fantasia) }}"
                            class="mt-1 block w-full rounded border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">CNPJ</label>
                        <input type="text" name="cnpj" value="{{ old('cnpj', $empresa->cnpj) }}"
                            class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Endereço</label>
                        <input type="text" name="endereco" value="{{ old('endereco', $empresa->endereco) }}"
                            class="mt-1 block w-full rounded border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Email Comercial</label>
                        <input type="email" name="email_comercial"
                            value="{{ old('email_comercial', $empresa->email_comercial) }}"
                            class="mt-1 block w-full rounded border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Email Administrativo</label>
                        <input type="email" name="email_administrativo"
                            value="{{ old('email_administrativo', $empresa->email_administrativo) }}"
                            class="mt-1 block w-full rounded border-gray-300">
                    </div>

                    <div class="mb-6">
                        <input type="hidden" name="ativo" value="0">

                        <label class="inline-flex items-center">
                            <input type="checkbox" name="ativo" value="1"
                                {{ old('ativo', $empresa->ativo) ? 'checked' : '' }}>
                            <span class="ml-2">Empresa ativa</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('empresas.index') }}"
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