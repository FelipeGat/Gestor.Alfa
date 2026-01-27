<x-app-layout>

    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ➕ Nova Conta Bancária
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">

        <form method="POST"
            action="{{ route('financeiro.contas-financeiras.store') }}"
            class="form-card">
            @csrf

            <div class="card-header">
                Dados da Conta
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- EMPRESA --}}
                <div>
                    <label class="label-text">Empresa</label>
                    <select name="empresa_id" class="input-field" required>
                        @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}">
                            {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- NOME DO BANCO --}}
                <div>
                    <label class="label-text">Banco</label>
                    <input type="text"
                        name="nome"
                        class="input-field"
                        placeholder="Ex: Banco do Brasil, Sicoob"
                        required>
                </div>

                {{-- TIPO DA CONTA --}}
                <div>
                    <label class="label-text">Tipo da Conta</label>
                    <select name="tipo" class="input-field" required>
                        <option value="corrente">Conta Corrente</option>
                        <option value="poupanca">Poupança</option>
                        <option value="investimento">Investimento</option>
                        <option value="credito">Cartão de Crédito</option>
                    </select>
                </div>

                {{-- SALDO --}}
                <div>
                    <label class="label-text">Saldo Inicial</label>
                    <input type="number"
                        step="0.01"
                        name="saldo"
                        class="input-field"
                        placeholder="Pode ser negativo">
                </div>

                {{-- LIMITE CARTÃO --}}
                <div>
                    <label class="label-text">Limite do Cartão de Crédito</label>
                    <input type="number"
                        step="0.01"
                        name="limite_credito"
                        class="input-field"
                        placeholder="Ex: 5000.00">
                </div>

                {{-- LIMITE UTILIZADO --}}
                <div>
                    <label class="label-text">Limite Utilizado (Cartão)</label>
                    <input type="number"
                        step="0.01"
                        name="limite_credito_utilizado"
                        class="input-field"
                        placeholder="Ex: 1200.00">
                </div>

                {{-- CHEQUE ESPECIAL --}}
                <div>
                    <label class="label-text">Limite Cheque Especial</label>
                    <input type="number"
                        step="0.01"
                        name="limite_cheque_especial"
                        class="input-field"
                        placeholder="Ex: 3000.00">
                </div>

                {{-- STATUS --}}
                <div>
                    <label class="label-text">Conta Ativa</label>
                    <select name="ativo" class="input-field" required>
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>

            </div>

            <div class="p-6 flex justify-end gap-3">
                <a href="{{ route('financeiro.contas-financeiras.index') }}"
                    class="btn btn-cancelar">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.form.submit();">
                    Salvar Conta
                </button>
            </div>

        </form>

    </div>

</x-app-layout>