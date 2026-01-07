<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Novo Cliente
        </h2>
    </x-slot>

    <script>
    function addEmail() {
        document.getElementById('emails').insertAdjacentHTML(
            'beforeend',
            `<div class="flex items-center gap-2 mb-2">
                    <input type="email" name="emails[]" class="border w-full rounded">
                    <input type="radio" name="email_principal" value="">
                    <span class="text-sm">Principal</span>
                </div>`
        );
    }

    function addTelefone() {
        document.getElementById('telefones').insertAdjacentHTML(
            'beforeend',
            `<div class="flex items-center gap-2 mb-2">
                    <input type="text" name="telefones[]" class="border w-full telefone rounded">
                    <input type="radio" name="telefone_principal" value="">
                    <span class="text-sm">Principal</span>
                </div>`
        );
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('telefone')) {
            let v = e.target.value.replace(/\D/g, '');

            if (v.length <= 10) {
                e.target.value = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else {
                e.target.value = v.replace(/(\d{2})(\d{1})(\d{4})(\d{0,4})/, '($1) $2.$3-$4');
            }
        }
    });
    </script>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf

                    {{-- Nome --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Nome</label>
                        <input type="text" name="nome" class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    {{-- Valor Mensal --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Valor Mensal (R$)</label>
                        <input type="number" step="0.01" name="valor_mensal"
                            class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    {{-- Dia de Vencimento --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Dia de Vencimento</label>
                        <select name="dia_vencimento" class="mt-1 block w-full rounded border-gray-300" required>
                            <option value="">Selecione</option>
                            @for($i = 1; $i <= 28; $i++) <option value="{{ $i }}">Dia {{ $i }}</option>
                                @endfor
                        </select>
                    </div>

                    {{-- Emails --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Emails</label>

                        <div id="emails">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="email" name="emails[]" class="border w-full rounded" required>
                                <input type="radio" name="email_principal" value="0" checked>
                                <span class="text-sm">Principal</span>
                            </div>
                        </div>

                        <button type="button" onclick="addEmail()" class="text-sm text-blue-600 mt-1">
                            + Adicionar email
                        </button>
                    </div>

                    {{-- Telefones --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Telefones</label>

                        <div id="telefones">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="text" name="telefones[]" class="border w-full telefone rounded">
                                <input type="radio" name="telefone_principal" value="0" checked>
                                <span class="text-sm">Principal</span>
                            </div>
                        </div>

                        <button type="button" onclick="addTelefone()" class="text-sm text-blue-600 mt-1">
                            + Adicionar telefone
                        </button>
                    </div>


                    {{-- Status --}}
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700">Status</label>
                        <select name="ativo" class="mt-1 block w-full rounded border-gray-300">
                            <option value="1" selected>Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>

                    {{-- Botões --}}
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('clientes.index') }}"
                            class="inline-flex items-center justify-center px-3 py-1
                                        bg-blue-600 hover:bg-blue-700 text-red-600 text-xs font-medium rounded-md shadow">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center justify-center px-3 py-1
                                        bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            Salvar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>