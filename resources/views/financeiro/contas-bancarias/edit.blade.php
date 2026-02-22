<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    <style>
        /* Card de Seção */
        .form-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        .form-card > h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        /* Inputs */
        .input-field {
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }
        .input-field:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Label */
        .label-text {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            display: block;
        }
        /* Botão Salvar */
        .btn-success {
            background: #22c55e !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);
            transition: all 0.2s;
        }
        .btn-success:hover {
            box-shadow: 0 4px 6px rgba(34, 197, 94, 0.4);
        }
        /* Botão Cancelar */
        .btn-cancelar {
            background: #ef4444 !important;
            color: white !important;
            border-radius: 9999px !important;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Contas Bancárias', 'url' => route('financeiro.contas-financeiras.index')],
            ['label' => 'Editar Conta']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Editar Conta Bancária
                </h2>
            </div>

            <a href="{{ route('financeiro.contas-financeiras.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-indigo-600 transition-all shadow-sm group"
                title="Voltar para Contas Bancárias">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">

        <form method="POST"
            action="{{ route('financeiro.contas-financeiras.update', $contaFinanceira) }}"
            class="form-card p-0">
            @csrf
            @method('PUT')

            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50" style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                <h3 class="font-semibold text-gray-800" style="font-size: 1.125rem; font-weight: 600;">Dados da Conta</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- EMPRESA --}}
                <div>
                    <label class="label-text">Empresa</label>
                    <select name="empresa_id" class="input-field" required>
                        @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}"
                            @selected(old('empresa_id', $contaFinanceira->empresa_id) == $empresa->id)>
                            {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- BANCO --}}
                <div>
                    <label class="label-text">Banco</label>
                    <input type="text"
                        name="nome"
                        class="input-field"
                        value="{{ old('nome', $contaFinanceira->nome) }}"
                        required>
                </div>

                {{-- TIPO --}}
                <div>
                    <label class="label-text">Tipo da Conta</label>
                    <select name="tipo" class="input-field" required>
                        <option value="corrente"
                            @selected(old('tipo', $contaFinanceira->tipo) === 'corrente')>
                            Conta Corrente
                        </option>
                        <option value="poupanca"
                            @selected(old('tipo', $contaFinanceira->tipo) === 'poupanca')>
                            Poupança
                        </option>
                        <option value="investimento"
                            @selected(old('tipo', $contaFinanceira->tipo) === 'investimento')>
                            Investimento
                        </option>
                        <option value="credito"
                            @selected(old('tipo', $contaFinanceira->tipo) === 'credito')>
                            Cartão de Crédito
                        </option>
                    </select>
                </div>

                {{-- SALDO --}}
                <div>
                    <label class="label-text">Saldo</label>
                    <input type="number"
                        step="0.01"
                        name="saldo"
                        class="input-field"
                        value="{{ old('saldo', $contaFinanceira->saldo) }}">
                </div>

                {{-- LIMITE CRÉDITO --}}
                <div>
                    <label class="label-text">Limite do Cartão</label>
                    <input type="number"
                        step="0.01"
                        name="limite_credito"
                        class="input-field"
                        value="{{ old('limite_credito', $contaFinanceira->limite_credito) }}">
                </div>

                {{-- LIMITE UTILIZADO --}}
                <div>
                    <label class="label-text">Limite Utilizado (Cartão)</label>
                    <input type="number"
                        step="0.01"
                        name="limite_credito_utilizado"
                        class="input-field"
                        value="{{ old('limite_credito_utilizado', $contaFinanceira->limite_credito_utilizado) }}">
                </div>

                {{-- CHEQUE ESPECIAL --}}
                <div>
                    <label class="label-text">Limite Cheque Especial</label>
                    <input type="number"
                        step="0.01"
                        name="limite_cheque_especial"
                        class="input-field"
                        value="{{ old('limite_cheque_especial', $contaFinanceira->limite_cheque_especial) }}">
                </div>

                {{-- STATUS --}}
                <div>
                    <label class="label-text">Conta Ativa</label>
                    <select name="ativo" class="input-field" required>
                        <option value="1"
                            @selected(old('ativo', $contaFinanceira->ativo) == 1)>
                            Sim
                        </option>
                        <option value="0"
                            @selected(old('ativo', $contaFinanceira->ativo) == 0)>
                            Não
                        </option>
                    </select>
                </div>

            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3" style="background-color: rgba(63, 156, 174, 0.05); border-top: 1px solid #e5e7eb;">
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-full hover:bg-red-700 transition shadow-md" 
                    style="padding: 0.625rem 1.25rem; border-radius: 9999px; background: #ef4444;">
                    Cancelar
                </a>

                <button type="submit" 
                    class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white rounded-full hover:opacity-90 transition shadow-md" 
                    style="padding: 0.625rem 1.25rem; border-radius: 9999px; background: #22c55e; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);"
                    onclick="this.disabled=true; this.form.submit();">
                    Salvar Alterações
                </button>
            </div>

        </form>

    </div>

</x-app-layout>
