<x-app-layout>

    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ✏️ Editar Conta Bancária
            </h2>

            <a href="{{ route('financeiro.contas-financeiras.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm group"
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
            class="form-card">
            @csrf
            @method('PUT')

            <div class="card-header">
                Dados da Conta
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">

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

            <div class="p-6 flex justify-end gap-3">
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="btn btn-cancelar">
                    Cancelar
                </a>

                <button type="submit"
                    class="btn btn-success"
                    onclick="this.disabled=true; this.form.submit();">
                    Salvar Alterações
                </button>
            </div>

        </form>

    </div>

</x-app-layout>