<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ✏️ Editar Pré-Cliente
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER --}}
            <div class="bg-slate-100 shadow rounded-lg px-6 py-4 mb-6">
                <h1 class="text-2xl font-bold text-black">Editar Pré-Cliente</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Atualize os dados básicos do pré-cliente
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

            <form method="POST" action="{{ route('pre-clientes.update', $preCliente) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- DADOS BÁSICOS --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                        📋 Dados Básicos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Tipo de Pessoa *</label>
                            <select name="tipo_pessoa" class="input" required>
                                <option value="PF" @selected(old('tipo_pessoa', $preCliente->tipo_pessoa)=='PF')>
                                    Pessoa Física
                                </option>
                                <option value="PJ" @selected(old('tipo_pessoa', $preCliente->tipo_pessoa)=='PJ')>
                                    Pessoa Jurídica
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="label">CPF / CNPJ *</label>
                            <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $preCliente->cpf_cnpj) }}"
                                class="input" required>
                        </div>

                        <div>
                            <label class="label">Razão Social</label>
                            <input type="text" name="razao_social"
                                value="{{ old('razao_social', $preCliente->razao_social) }}" class="input">
                        </div>

                        <div>
                            <label class="label">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia"
                                value="{{ old('nome_fantasia', $preCliente->nome_fantasia) }}" class="input">
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
                            <input type="email" name="email" value="{{ old('email', $preCliente->email) }}"
                                class="input">
                        </div>

                        <div>
                            <label class="label">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone', $preCliente->telefone) }}"
                                class="input">
                        </div>
                    </div>
                </div>

                {{-- ENDEREÇO --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                        🏠 Endereço
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input class="input" name="cep" placeholder="CEP" value="{{ old('cep', $preCliente->cep) }}">
                        <input class="input" name="logradouro" placeholder="Logradouro"
                            value="{{ old('logradouro', $preCliente->logradouro) }}">
                        <input class="input" name="numero" placeholder="Número"
                            value="{{ old('numero', $preCliente->numero) }}">
                        <input class="input" name="bairro" placeholder="Bairro"
                            value="{{ old('bairro', $preCliente->bairro) }}">
                        <input class="input" name="cidade" placeholder="Cidade"
                            value="{{ old('cidade', $preCliente->cidade) }}">
                        <input class="input" name="estado" placeholder="UF" maxlength="2"
                            value="{{ old('estado', $preCliente->estado) }}">
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex justify-end gap-3 bg-white shadow rounded-lg p-6">
                    <a href="{{ route('pre-clientes.index') }}" class="btn-secondary">
                        ❌ Cancelar
                    </a>

                    <button type="submit" class="btn-primary">
                        ✔️ Atualizar Pré-Cliente
                    </button>
                </div>
            </form>
            {{-- CONVERTER PARA CLIENTE --}}
            <form method="POST"
                action="{{ route('pre-clientes.converter', $preCliente) }}"
                onsubmit="return confirm('Deseja converter este pré-cliente em cliente?')"
                class="mt-6">

                @csrf

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg shadow">
                    🔄 Converter para Cliente
                </button>
            </form>
        </div>
    </div>
</x-app-layout>