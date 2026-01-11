<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Novo Cliente
        </h2>
    </x-slot>

    {{-- JS EXISTENTE (mantido) --}}
    <script>
    function addEmail() {
        document.getElementById('emails').insertAdjacentHTML(
            'beforeend',
            `<div class="flex items-center gap-2 mb-2">
                    <input type="email" name="emails[]" class="border w-full rounded" required>
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

    <script>
    document.addEventListener('input', function(e) {

        // CPF / CNPJ
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

        // CEP
        if (e.target.name === 'cep') {
            let v = e.target.value.replace(/\D/g, '');
            e.target.value = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
        }
    });
    </script>

    <script>
    function toggleContrato() {
        const tipo = document.querySelector('[name="tipo_cliente"]').value;
        const bloco = document.getElementById('bloco-contrato');

        if (tipo === 'AVULSO') {
            bloco.style.display = 'none';
        } else {
            bloco.style.display = 'grid';
        }
    }

    document.addEventListener('DOMContentLoaded', toggleContrato);
    document.addEventListener('change', function(e) {
        if (e.target.name === 'tipo_cliente') {
            toggleContrato();
        }
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

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf

                    {{-- ================= DADOS BÁSICOS ================= --}}
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Dados Básicos</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Tipo Pessoa --}}
                            <div>
                                <label class="block text-sm font-medium">Tipo de Pessoa</label>
                                <select name="tipo_pessoa" class="w-full rounded border-gray-300" required>
                                    <option value="">Selecione</option>
                                    <option value="PF" @selected(old('tipo_pessoa')=='PF' )>Pessoa Física</option>
                                    <option value="PJ" @selected(old('tipo_pessoa')=='PJ' )>Pessoa Jurídica</option>
                                </select>
                            </div>

                            {{-- CPF / CNPJ --}}
                            <div>
                                <label class="block text-sm font-medium">CPF / CNPJ</label>
                                <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}"
                                    class="w-full rounded border-gray-300" required>
                            </div>

                            {{-- Data Cadastro --}}
                            <div>
                                <label class="block text-sm font-medium">Data de Cadastro</label>
                                <input type="date" name="data_cadastro"
                                    value="{{ old('data_cadastro', date('Y-m-d')) }}"
                                    class="w-full rounded border-gray-300" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium">Nome / Razão Social</label>
                                <input type="text" name="nome" value="{{ old('nome') }}"
                                    class="w-full rounded border-gray-300" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}"
                                    class="w-full rounded border-gray-300">
                            </div>
                        </div>
                    </div>

                    {{-- ================= CONTATOS ================= --}}
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Contatos</h3>

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
                        <div>
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
                    </div>

                    {{-- ================= ENDEREÇO ================= --}}
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Endereço</h3>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input type="text" name="cep" placeholder="CEP" value="{{ old('cep') }}"
                                class="rounded border-gray-300">

                            <input type="text" name="logradouro" placeholder="Logradouro"
                                value="{{ old('logradouro') }}" class="rounded border-gray-300">

                            <input type="text" name="numero" placeholder="Nº" value="{{ old('numero') }}"
                                class="rounded border-gray-300">

                            <input type="text" name="cidade" placeholder="Cidade" value="{{ old('cidade') }}"
                                class="rounded border-gray-300">
                        </div>

                        <div class="mt-4">
                            <input type="text" name="complemento" placeholder="Complemento"
                                value="{{ old('complemento') }}" class="w-full rounded border-gray-300">
                        </div>
                    </div>

                    {{-- ================= CONTRATO ================= --}}
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-800 mb-4">Informações do Cliente</h3>

                        <div id="bloco-contrato" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Tipo de Cliente</label>
                                <select name="tipo_cliente" class="w-full rounded border-gray-300" required>
                                    <option value="">Selecione</option>
                                    <option value="CONTRATO" @selected(old('tipo_cliente')=='CONTRATO' )>Contrato
                                    </option>
                                    <option value="AVULSO" @selected(old('tipo_cliente')=='AVULSO' )>Avulso</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Valor Mensal</label>
                                <input type="number" step="0.01" name="valor_mensal" value="{{ old('valor_mensal') }}"
                                    class="w-full rounded border-gray-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Dia de Vencimento</label>
                                <select name="dia_vencimento" class="w-full rounded border-gray-300">
                                    <option value="">Selecione</option>
                                    @for($i=1;$i<=28;$i++) <option value="{{ $i }}"
                                        @selected(old('dia_vencimento')==$i)>
                                        Dia {{ $i }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <textarea name="observacoes" rows="3" class="w-full rounded border-gray-300"
                                placeholder="Observações">{{ old('observacoes') }}</textarea>
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700">Status</label>
                        <select name="ativo" class="mt-1 block w-full rounded border-gray-300">
                            <option value="1" selected>Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>

                    {{-- BOTÕES --}}
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('clientes.index') }}" class="px-4 py-2 border rounded text-sm">
                            Voltar
                        </a>

                        <button type="submit" class="px-4 py-2 bg-blue-600 text-blue-600 rounded text-sm">
                            Salvar
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>