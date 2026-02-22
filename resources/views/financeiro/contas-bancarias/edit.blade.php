<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    <style>
        .section-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        .section-card > h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .filter-select:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
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
        .filter-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .btn-success {
            background: #22c55e !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);
            transition: all 0.2s;
        }
        .btn-success:hover {
            box-shadow: 0 4px 6px rgba(34, 197, 94, 0.4);
        }
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

    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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

            <form method="POST"
                action="{{ route('financeiro.contas-financeiras.update', $contaFinanceira) }}">
                @csrf
                @method('PUT')

                <div class="section-card p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Dados da Conta</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="filter-label">Empresa <span class="text-red-500">*</span></label>
                            <select name="empresa_id" class="filter-select w-full" required>
                                <option value="">Selecione a empresa</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}"
                                    @selected(old('empresa_id', $contaFinanceira->empresa_id) == $empresa->id)>
                                    {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="filter-label">Banco <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="nome"
                                class="filter-select w-full"
                                value="{{ old('nome', $contaFinanceira->nome) }}"
                                required>
                        </div>

                        <div>
                            <label class="filter-label">Tipo da Conta <span class="text-red-500">*</span></label>
                            <select name="tipo" class="filter-select w-full" required>
                                <option value="">Selecione o tipo</option>
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

                        <div>
                            <label class="filter-label">Saldo</label>
                            <input type="number"
                                step="0.01"
                                name="saldo"
                                class="filter-select w-full"
                                value="{{ old('saldo', $contaFinanceira->saldo) }}">
                        </div>

                        <div>
                            <label class="filter-label">Limite do Cartão</label>
                            <input type="number"
                                step="0.01"
                                name="limite_credito"
                                class="filter-select w-full"
                                value="{{ old('limite_credito', $contaFinanceira->limite_credito) }}">
                        </div>

                        <div>
                            <label class="filter-label">Limite Utilizado (Cartão)</label>
                            <input type="number"
                                step="0.01"
                                name="limite_credito_utilizado"
                                class="filter-select w-full"
                                value="{{ old('limite_credito_utilizado', $contaFinanceira->limite_credito_utilizado) }}">
                        </div>

                        <div>
                            <label class="filter-label">Limite Cheque Especial</label>
                            <input type="number"
                                step="0.01"
                                name="limite_cheque_especial"
                                class="filter-select w-full"
                                value="{{ old('limite_cheque_especial', $contaFinanceira->limite_cheque_especial) }}">
                        </div>

                        <div>
                            <label class="filter-label">Conta Ativa <span class="text-red-500">*</span></label>
                            <select name="ativo" class="filter-select w-full" required>
                                <option value="">Selecione</option>
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
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('financeiro.contas-financeiras.index') }}" 
                        class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2" 
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #ef4444; color: white; border: none; border-radius: 9999px; min-width: 130px; justify-content: center; width: 130px; box-shadow: none;" 
                        onmouseover="this.style.boxShadow='0 4px 6px rgba(239, 68, 68, 0.4)'" 
                        onmouseout="this.style.boxShadow='none'">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </a>

                    <button type="submit" 
                        class="btn btn-primary inline-flex items-center justify-center px-6 py-2" 
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #3f9cae; border-radius: 9999px; box-shadow: 0 2px 4px rgba(63, 156, 174, 0.3); transition: all 0.2s; min-width: 130px; width: 130px;" 
                        onmouseover="this.style.background='#358a96'; this.style.boxShadow='0 4px 6px rgba(63, 156, 174, 0.4)'" 
                        onmouseout="this.style.background='#3f9cae'; this.style.boxShadow='0 2px 4px rgba(63, 156, 174, 0.3)'"
                        onclick="this.disabled=true; this.form.submit();">
                        <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </button>
                </div>

            </form>

        </div>
    </div>

</x-app-layout>
