
<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <nav class="flex items-center gap-2 text-base font-semibold leading-tight rounded-full pt-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('pre-clientes.index') }}" class="text-gray-500 hover:text-gray-700 transition">Pré-Clientes</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-800 font-medium">Novo</span>
        </nav>
    </x-slot>

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= ERROS ================= --}}
            @if ($errors->any())
            <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-5 rounded-xl shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg></div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800 uppercase tracking-wide">Erros encontrados:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $erro) <li>{{ $erro }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('pre-clientes.store') }}" class="space-y-6">
                @csrf

                {{-- ================= DADOS BÁSICOS ================= --}}
                <div class="section-card">
                    <div class="card-header">
                        <h3 class="text-base font-semibold text-gray-800">Dados Básicos</h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="filter-label">Tipo de Pessoa <span class="text-red-500">*</span></label>
                            <select name="tipo_pessoa" required class="filter-select w-full">
                                <option value="">Selecione</option>
                                <option value="PF" @selected(old('tipo_pessoa')=='PF')>Pessoa Física</option>
                                <option value="PJ" @selected(old('tipo_pessoa')=='PJ')>Pessoa Jurídica</option>
                            </select>
                        </div>
                        <div>
                            <label class="filter-label">CPF / CNPJ <span class="text-red-500">*</span></label>
                            <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $valorBusca ?? '') }}" class="filter-select w-full" required>
                        </div>
                        <div>
                            <label class="filter-label">Razão Social</label>
                            <input type="text" name="razao_social" value="{{ old('razao_social') }}" class="filter-select w-full" placeholder="Digite o nome completo">
                        </div>
                        <div>
                            <label class="filter-label">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}" class="filter-select w-full" placeholder="Digite o nome fantasia">
                        </div>
                    </div>
                </div>

                {{-- ================= CONTATO ================= --}}
                <div class="section-card">
                    <div class="card-header">
                        <h3 class="text-base font-semibold text-gray-800">Contato</h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="filter-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="filter-select w-full" placeholder="email@exemplo.com">
                        </div>
                        <div>
                            <label class="filter-label">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" class="filter-select w-full" placeholder="(00) 00000-0000">
                        </div>
                    </div>
                </div>

                {{-- ================= ENDEREÇO ================= --}}
                <div class="section-card">
                    <div class="card-header">
                        <h3 class="text-base font-semibold text-gray-800">Endereço</h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="filter-label">CEP</label>
                            <input type="text" name="cep" placeholder="CEP" value="{{ old('cep') }}" class="filter-select w-full">
                        </div>
                        <div class="md:col-span-2">
                            <label class="filter-label">Logradouro</label>
                            <input type="text" name="logradouro" placeholder="Logradouro" value="{{ old('logradouro') }}" class="filter-select w-full">
                        </div>
                        <div>
                            <label class="filter-label">Número</label>
                            <input type="text" name="numero" placeholder="Número" value="{{ old('numero') }}" class="filter-select w-full">
                        </div>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 pt-0">
                        <div>
                            <label class="filter-label">Bairro</label>
                            <input type="text" name="bairro" placeholder="Bairro" value="{{ old('bairro') }}" class="filter-select w-full">
                        </div>
                        <div>
                            <label class="filter-label">Cidade</label>
                            <input type="text" name="cidade" placeholder="Cidade" value="{{ old('cidade') }}" class="filter-select w-full">
                        </div>
                        <div>
                            <label class="filter-label">UF</label>
                            <input type="text" name="estado" placeholder="UF" maxlength="2" value="{{ old('estado') }}" class="filter-select w-full uppercase">
                        </div>
                    </div>
                </div>

                {{-- ================= AÇÕES ================= --}}
                <div class="flex flex-col-reverse md:flex-row justify-end gap-3">
                    <a href="{{ route('pre-clientes.index') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #ef4444; border-radius: 9999px;">
                        <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #3b82f6; border-radius: 9999px;">
                        <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
