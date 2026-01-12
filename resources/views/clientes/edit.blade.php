<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Cliente
        </h2>
    </x-slot>

    {{-- ================= JS (MANTIDO) ================= --}}
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

        if (e.target.name === 'cpf_cnpj') {
            let v = e.target.value.replace(/\D/g, '');

            if (v.length <= 11) {
                e.target.value = v
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                e.target.value = v
                    .replace(/(\d{2})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1/$2')
                    .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            }
        }

        if (e.target.name === 'cep') {
            let v = e.target.value.replace(/\D/g, '');
            e.target.value = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
        }
    });

    function toggleContrato() {
        const tipo = document.querySelector('[name="tipo_cliente"]').value;
        const bloco = document.getElementById('bloco-contrato');
        bloco.style.display = (tipo === 'AVULSO') ? 'none' : 'grid';
    }

    document.addEventListener('DOMContentLoaded', toggleContrato);
    document.addEventListener('change', function(e) {
        if (e.target.name === 'tipo_cliente') toggleContrato();
    });
    </script>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">

                {{-- ERROS --}}
                @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('clientes.update', $cliente) }}">
                    @csrf
                    @method('PUT')

                    {{-- ================= DADOS BÁSICOS ================= --}}
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Dados Básicos</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Tipo de Pessoa</label>
                                <select name="tipo_pessoa" class="w-full rounded border-gray-300" required>
                                    <option value="PF" @selected(old('tipo_pessoa', $cliente->tipo_pessoa)=='PF')>Pessoa
                                        Física</option>
                                    <option value="PJ" @selected(old('tipo_pessoa', $cliente->tipo_pessoa)=='PJ')>Pessoa
                                        Jurídica</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">CPF / CNPJ</label>
                                <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj) }}"
                                    class="w-full rounded border-gray-300" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Data de Cadastro</label>
                                <input type="date" name="data_cadastro"
                                    value="{{ old('data_cadastro', $cliente->data_cadastro) }}"
                                    class="w-full rounded border-gray-300" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium">Nome / Razão Social</label>
                                <input type="text" name="nome" value="{{ old('nome', $cliente->nome) }}"
                                    class="w-full rounded border-gray-300" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia"
                                    value="{{ old('nome_fantasia', $cliente->nome_fantasia) }}"
                                    class="w-full rounded border-gray-300">
                            </div>
                        </div>
                    </div>

                    {{-- ================= CONTATOS ================= --}}
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Contatos</h3>

                        <div class="mb-4">
                            <label class="block font-medium text-gray-700">Emails</label>
                            <div id="emails">
                                @foreach($cliente->emails as $i => $email)
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="email" name="emails[]" value="{{ $email->valor }}"
                                        class="border w-full rounded">
                                    <input type="radio" name="email_principal" value="{{ $i }}"
                                        {{ $email->principal ? 'checked' : '' }}>
                                    <span class="text-sm">Principal</span>
                                </div>
                                @endforeach
                            </div>

                            <button type="button" onclick="addEmail()" class="text-sm text-blue-600 mt-1">
                                + Adicionar email
                            </button>
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700">Telefones</label>
                            <div id="telefones">
                                @foreach($cliente->telefones as $i => $telefone)
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" name="telefones[]" value="{{ $telefone->valor }}"
                                        class="border w-full telefone rounded">
                                    <input type="radio" name="telefone_principal" value="{{ $i }}"
                                        {{ $telefone->principal ? 'checked' : '' }}>
                                    <span class="text-sm">Principal</span>
                                </div>
                                @endforeach
                            </div>

                            <button type="button" onclick="addTelefone()" class="text-sm text-blue-600 mt-1">
                                + Adicionar telefone
                            </button>
                        </div>
                    </div>

                    {{-- ================= ENDEREÇO ================= --}}
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Endereço</h3>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input type="text" name="cep" value="{{ old('cep', $cliente->cep) }}" placeholder="CEP"
                                class="rounded border-gray-300">
                            <input type="text" name="logradouro" value="{{ old('logradouro', $cliente->logradouro) }}"
                                placeholder="Logradouro" class="rounded border-gray-300">
                            <input type="text" name="numero" value="{{ old('numero', $cliente->numero) }}"
                                placeholder="Nº" class="rounded border-gray-300">
                            <input type="text" name="cidade" value="{{ old('cidade', $cliente->cidade) }}"
                                placeholder="Cidade" class="rounded border-gray-300">
                        </div>

                        <div class="mt-4">
                            <input type="text" name="complemento"
                                value="{{ old('complemento', $cliente->complemento) }}" placeholder="Complemento"
                                class="w-full rounded border-gray-300">
                        </div>
                    </div>

                    {{-- ================= CONTRATO ================= --}}
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Informações do Cliente</h3>

                        <div id="bloco-contrato" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Tipo de Cliente</label>
                                <select name="tipo_cliente" class="w-full rounded border-gray-300" required>
                                    <option value="CONTRATO" @selected(old('tipo_cliente', $cliente->
                                        tipo_cliente)=='CONTRATO')>Contrato</option>
                                    <option value="AVULSO" @selected(old('tipo_cliente', $cliente->
                                        tipo_cliente)=='AVULSO')>Avulso</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Valor Mensal</label>
                                <input type="number" step="0.01" name="valor_mensal"
                                    value="{{ old('valor_mensal', $cliente->valor_mensal) }}"
                                    class="w-full rounded border-gray-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Dia de Vencimento</label>
                                <select name="dia_vencimento" class="w-full rounded border-gray-300">
                                    <option value="">Selecione</option>
                                    @for($i=1;$i<=28;$i++) <option value="{{ $i }}" @selected(old('dia_vencimento',
                                        $cliente->dia_vencimento)==$i)>
                                        Dia {{ $i }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <textarea name="observacoes" rows="3" class="w-full rounded border-gray-300"
                                placeholder="Observações">{{ old('observacoes', $cliente->observacoes) }}</textarea>
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700">Status</label>
                        <select name="ativo" class="w-full rounded border-gray-300">
                            <option value="1" @selected($cliente->ativo)>Ativo</option>
                            <option value="0" @selected(!$cliente->ativo)>Inativo</option>
                        </select>
                    </div>

                    {{-- BOTÕES --}}
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('clientes.index') }}" class="px-4 py-2 border rounded text-sm">
                            Cancelar
                        </a>

                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">
                            Atualizar
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>