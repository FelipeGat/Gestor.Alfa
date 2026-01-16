<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ➕ Novo Pré-Cliente
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER --}}
            <div class="bg-slate-100 shadow rounded-lg px-6 py-4 mb-6">
                <h1 class="text-2xl font-bold text-black">Cadastrar Pré-Cliente</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Informe os dados básicos para criação do pré-cliente
                </p>
            </div>

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('pre-clientes.store') }}" class="space-y-6">
                @csrf

                {{-- DADOS BÁSICOS --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                        📋 Dados Básicos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Tipo de Pessoa *</label>
                            <select name="tipo_pessoa" class="input" required>
                                <option value="">Selecione</option>
                                <option value="PF" @selected(old('tipo_pessoa')=='PF' )>Pessoa Física</option>
                                <option value="PJ" @selected(old('tipo_pessoa')=='PJ' )>Pessoa Jurídica</option>
                            </select>
                        </div>

                        <div>
                            <label class="label">CPF / CNPJ *</label>
                            <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $valorBusca ?? '') }}"
                                class="input" required>
                        </div>

                        <div>
                            <label class="label">Razão Social</label>
                            <input type="text" name="razao_social" value="{{ old('razao_social') }}" class="input">
                        </div>

                        <div>
                            <label class="label">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}" class="input">
                        </div>
                    </div>
                </div>

                {{-- CONTATO --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                        📞 Contato
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="input"
                                placeholder="email@exemplo.com">
                        </div>

                        <div>
                            <label class="label">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" class="input"
                                placeholder="(00) 00000-0000">
                        </div>
                    </div>
                </div>

                {{-- ENDEREÇO --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                        🏠 Endereço
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input class="input" name="cep" placeholder="CEP" value="{{ old('cep') }}">
                        <input class="input" name="logradouro" placeholder="Logradouro" value="{{ old('logradouro') }}">
                        <input class="input" name="numero" placeholder="Número" value="{{ old('numero') }}">
                        <input class="input" name="bairro" placeholder="Bairro" value="{{ old('bairro') }}">
                        <input class="input" name="cidade" placeholder="Cidade" value="{{ old('cidade') }}">
                        <input class="input" name="estado" placeholder="UF" maxlength="2" value="{{ old('estado') }}">
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex justify-end gap-3 bg-white shadow rounded-lg p-6">
                    <a href="{{ route('pre-clientes.index') }}" class="inline-flex items-center justify-center px-6 py-2
                              rounded-lg border border-gray-300 text-gray-700 font-medium
                              hover:bg-red-50 transition">
                        ❌ Cancelar
                    </a>

                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2
                               rounded-lg border border-gray-300 text-gray-700 font-medium
                               hover:bg-green-50 transition">
                        ✔️ Cadastrar Pré-Cliente
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>