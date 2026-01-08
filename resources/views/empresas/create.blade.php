<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nova Empresa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('empresas.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Razão Social</label>
                        <input type="text" name="razao_social" class="mt-1 block w-full rounded border-gray-300"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Nome Fantasia</label>
                        <input type="text" name="nome_fantasia" class="mt-1 block w-full rounded border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">CNPJ</label>
                        <input type="text" name="cnpj" class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Endereço</label>
                        <input type="text" name="endereco" class="mt-1 block w-full rounded border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Email Comercial</label>
                        <input type="email" name="email_comercial" class="mt-1 block w-full rounded border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Email Administrativo</label>
                        <input type="email" name="email_administrativo"
                            class="mt-1 block w-full rounded border-gray-300">
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-gray-700">Status</label>
                        <select name="ativo" class="mt-1 block w-full rounded border-gray-300">
                            <option value="1" selected>Ativa</option>
                            <option value="0">Inativa</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('empresas.index') }}"
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