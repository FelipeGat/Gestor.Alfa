
<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ➕ Novo Pré-Cliente
            </h2>

            <a href="{{ route('pre-clientes.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm group"
                title="Voltar para Pré-Clientes">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER DO FORMULÁRIO --}}
            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 sm:px-8 sm:py-6 mb-6">
                <h1 class="text-2xl font-bold text-black">Cadastro de Pré-Cliente</h1>
                <p class="text-sm text-gray-600 mt-1">Preencha os dados abaixo para criar um novo pré-cliente</p>
            </div>

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-medium text-red-800 mb-2">Erros encontrados:</h3>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('pre-clientes.store') }}" class="space-y-6">
                @csrf

                {{-- DADOS BÁSICOS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        📋 Dados Básicos
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pessoa <span class="text-red-500">*</span></label>
                            <select name="tipo_pessoa" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-gray-900" required>
                                <option value="">Selecione</option>
                                <option value="PF" @selected(old('tipo_pessoa')=='PF')>Pessoa Física</option>
                                <option value="PJ" @selected(old('tipo_pessoa')=='PJ')>Pessoa Jurídica</option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">CPF / CNPJ <span class="text-red-500">*</span></label>
                            <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $valorBusca ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 pr-10" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Razão Social</label>
                            <input type="text" name="razao_social" value="{{ old('razao_social') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2" placeholder="Digite o nome completo">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2" placeholder="Digite o nome fantasia">
                        </div>
                    </div>
                </div>

                {{-- CONTATO --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        📞 Contato
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2" placeholder="email@exemplo.com">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2" placeholder="(00) 00000-0000">
                        </div>
                    </div>
                </div>

                {{-- ENDEREÇO --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        🏠 Endereço
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                            <input type="text" name="cep" placeholder="CEP" value="{{ old('cep') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1 sm:col-span-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                            <input type="text" name="logradouro" placeholder="Logradouro" value="{{ old('logradouro') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                            <input type="text" name="numero" placeholder="Número" value="{{ old('numero') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mt-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                            <input type="text" name="bairro" placeholder="Bairro" value="{{ old('bairro') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                            <input type="text" name="cidade" placeholder="Cidade" value="{{ old('cidade') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                            <input type="text" name="estado" placeholder="UF" maxlength="2" value="{{ old('estado') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 uppercase">
                        </div>
                    </div>
                </div>

                {{-- AÇÕES --}}
                                <style>
                                    .btn-action {
                                        display: inline-flex;
                                        align-items: center;
                                        justify-content: center;
                                        padding: 0.5rem 1.5rem;
                                        border-radius: 0.5rem;
                                        font-weight: 500;
                                        font-size: 1rem;
                                        border: none;
                                        transition: background 0.2s;
                                    }
                                    .btn-action svg {
                                        margin-right: 0.5rem;
                                        width: 1.25rem;
                                        height: 1.25rem;
                                    }
                                    .btn-cancelar {
                                        background: #ef4444;
                                        color: #fff;
                                    }
                                    .btn-cancelar:hover {
                                        background: #dc2626;
                                    }
                                    .btn-primary {
                                        background: #2563eb;
                                        color: #fff;
                                    }
                                    .btn-primary:hover {
                                        background: #1d4ed8;
                                    }
                                </style>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 bg-white shadow rounded-lg p-6 sm:p-8">
                    <a href="{{ route('pre-clientes.index') }}" class="btn-action btn-cancelar">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-action btn-primary">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar Pré-Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>