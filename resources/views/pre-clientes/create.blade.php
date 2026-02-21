<x-app-layout>
    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    <style>
        /* Cards de Seção */
        .section-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        .section-card > h3,
        .section-card .card-header h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .section-card h4 {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: #111827;
        }
        /* Labels */
        .filter-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
            display: block;
        }
        /* Inputs e Selects */
        .filter-select,
        input[type="text"],
        input[type="date"],
        input[type="number"],
        input[type="email"],
        textarea,
        select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }
        .filter-select:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        textarea:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Botões */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-success {
            background: #22c55e !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);
            transition: all 0.2s;
            color: white !important;
        }
        .btn-success:hover {
            box-shadow: 0 4px 6px rgba(34, 197, 94, 0.4);
        }
        .btn-cancel {
            background: #ef4444 !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
            transition: all 0.2s;
            color: white !important;
        }
        .btn-cancel:hover {
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.4);
        }
        .btn-save {
            background: #3f9cae !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(63, 156, 174, 0.3);
            transition: all 0.2s;
            color: white !important;
        }
        .btn-save:hover {
            box-shadow: 0 4px 6px rgba(63, 156, 174, 0.4);
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Comercial', 'url' => route('comercial.index')],
            ['label' => 'Pré-Clientes', 'url' => route('pre-clientes.index')],
            ['label' => 'Novo']
        ]" />
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
                <div class="section-card p-5 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Dados Básicos</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <div class="section-card p-5 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Contato</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <div class="section-card p-5 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Endereço</h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-2">
                                <label class="filter-label">CEP</label>
                                <input type="text" name="cep" placeholder="CEP" value="{{ old('cep') }}" class="filter-select w-full">
                            </div>
                            <div class="md:col-span-8">
                                <label class="filter-label">Logradouro</label>
                                <input type="text" name="logradouro" placeholder="Logradouro" value="{{ old('logradouro') }}" class="filter-select w-full">
                            </div>
                            <div class="md:col-span-2">
                                <label class="filter-label">Número</label>
                                <input type="text" name="numero" placeholder="Número" value="{{ old('numero') }}" class="filter-select w-full">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-4">
                                <label class="filter-label">Bairro</label>
                                <input type="text" name="bairro" placeholder="Bairro" value="{{ old('bairro') }}" class="filter-select w-full">
                            </div>
                            <div class="md:col-span-5">
                                <label class="filter-label">Cidade</label>
                                <input type="text" name="cidade" placeholder="Cidade" value="{{ old('cidade') }}" class="filter-select w-full">
                            </div>
                            <div class="md:col-span-3">
                                <label class="filter-label">UF</label>
                                <input type="text" name="estado" placeholder="UF" maxlength="2" value="{{ old('estado') }}" class="filter-select w-full uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= AÇÕES ================= --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3" style="margin-top: 1.5rem;">
                    <a href="{{ route('pre-clientes.index') }}" class="btn btn-cancel" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center;">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-save" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center;">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
