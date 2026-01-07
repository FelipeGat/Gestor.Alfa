<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Cliente
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

                <form method="POST" action="{{ route('clientes.update', $cliente) }}">
                    @csrf
                    @method('PUT')

                    {{-- Nome --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Nome</label>
                        <input type="text" name="nome" value="{{ old('nome', $cliente->nome) }}"
                            class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    {{-- Valor Mensal --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Valor Mensal (R$)</label>
                        <input type="number" step="0.01" name="valor_mensal"
                            value="{{ old('valor_mensal', $cliente->valor_mensal) }}"
                            class="mt-1 block w-full rounded border-gray-300" required>
                    </div>

                    {{-- Dia de Vencimento --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Dia de Vencimento</label>
                        <select name="dia_vencimento" class="mt-1 block w-full rounded border-gray-300" required>
                            <option value="">Selecione</option>
                            @for($i = 1; $i <= 28; $i++) <option value="{{ $i }}"
                                {{ (int) old('dia_vencimento', $cliente->dia_vencimento) === $i ? 'selected' : '' }}>
                                Dia {{ $i }}
                                </option>
                                @endfor
                        </select>
                    </div>

                    {{-- Emails --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Emails</label>

                        <div id="emails">
                            @foreach($cliente->emails as $email)
                            <div class="flex items-center gap-2 mb-2">
                                <input type="email" name="emails[{{ $email->id }}]" value="{{ $email->valor }}"
                                    class="border w-full rounded">

                                <input type="radio" name="email_principal" value="{{ $email->id }}"
                                    {{ $email->principal ? 'checked' : '' }}>

                                <span class="text-sm">Principal</span>
                            </div>
                            @endforeach

                            <div class="flex items-center gap-2 mb-2">
                                <input type="email" name="emails[]" class="border w-full rounded">
                                <input type="radio" name="email_principal" value="">
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
                            @foreach($cliente->telefones as $telefone)
                            <div class="flex items-center gap-2 mb-2">
                                <input type="text" name="telefones[{{ $telefone->id }}]" value="{{ $telefone->valor }}"
                                    class="border w-full telefone rounded">

                                <input type="radio" name="telefone_principal" value="{{ $telefone->id }}"
                                    {{ $telefone->principal ? 'checked' : '' }}>

                                <span class="text-sm">Principal</span>
                            </div>
                            @endforeach

                            <div class="flex items-center gap-2 mb-2">
                                <input type="text" name="telefones[]" class="border w-full telefone rounded">
                                <input type="radio" name="telefone_principal" value="">
                                <span class="text-sm">Principal</span>
                            </div>
                        </div>

                        <button type="button" onclick="addTelefone()" class="text-sm text-blue-600 mt-1">
                            + Adicionar telefone
                        </button>
                    </div>

                    {{-- Status --}}
                    <div class="mb-6">
                        {{-- Valor padrão quando desmarcado --}}
                        <input type="hidden" name="ativo" value="0">

                        <label class="inline-flex items-center">
                            <input type="checkbox" name="ativo" value="1"
                                {{ old('ativo', $cliente->ativo) ? 'checked' : '' }}>
                            <span class="ml-2">Cliente ativo</span>
                        </label>
                    </div>

                    {{-- Botões --}}
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('clientes.index') }}"
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