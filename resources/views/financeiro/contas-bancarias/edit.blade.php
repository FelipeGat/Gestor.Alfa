<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Contas Bancárias', 'url' => route('financeiro.contas-financeiras.index')],
            ['label' => 'Editar Conta']
        ]" />
    </x-slot>

    <x-page-title title="Editar Conta Bancária" :route="route('financeiro.contas-financeiras.index')" />

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <x-alert type="error" title="Erros encontrados">
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <form method="POST" action="{{ route('financeiro.contas-financeiras.update', $contaFinanceira) }}">
                @csrf
                @method('PUT')

                <x-form-section title="Dados da Conta">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-select name="empresa_id" label="Empresa" required placeholder="Selecione a empresa" :selected="old('empresa_id', $contaFinanceira->empresa_id)">
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id', $contaFinanceira->empresa_id) == $empresa->id)>
                                    {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                                </option>
                            @endforeach
                        </x-form-select>

                        <x-form-input name="nome" label="Banco" :value="old('nome', $contaFinanceira->nome)" required />

                        <x-form-select name="tipo" label="Tipo da Conta" required :selected="old('tipo', $contaFinanceira->tipo)">
                            <option value="corrente" @selected(old('tipo', $contaFinanceira->tipo) === 'corrente')>Conta Corrente</option>
                            <option value="poupanca" @selected(old('tipo', $contaFinanceira->tipo) === 'poupanca')>Poupança</option>
                            <option value="investimento" @selected(old('tipo', $contaFinanceira->tipo) === 'investimento')>Investimento</option>
                            <option value="credito" @selected(old('tipo', $contaFinanceira->tipo) === 'credito')>Cartão de Crédito</option>
                            <option value="pix" @selected(old('tipo', $contaFinanceira->tipo) === 'pix')>Pix</option>
                            <option value="caixa" @selected(old('tipo', $contaFinanceira->tipo) === 'caixa')>Caixa</option>
                        </x-form-select>

                        <x-form-input name="saldo" label="Saldo" type="number" step="0.01" :value="old('saldo', $contaFinanceira->saldo)" />

                        <x-form-input name="limite_credito" label="Limite do Cartão" type="number" step="0.01" :value="old('limite_credito', $contaFinanceira->limite_credito)" />

                        <x-form-input name="limite_credito_utilizado" label="Limite Utilizado (Cartão)" type="number" step="0.01" :value="old('limite_credito_utilizado', $contaFinanceira->limite_credito_utilizado)" />

                        <x-form-input name="limite_cheque_especial" label="Limite Cheque Especial" type="number" step="0.01" :value="old('limite_cheque_especial', $contaFinanceira->limite_cheque_especial)" />

                        <x-form-select name="ativo" label="Conta Ativa" required :selected="old('ativo', $contaFinanceira->ativo)">
                            <option value="1" @selected(old('ativo', $contaFinanceira->ativo) == 1)>Sim</option>
                            <option value="0" @selected(old('ativo', $contaFinanceira->ativo) == 0)>Não</option>
                        </x-form-select>
                    </div>
                </x-form-section>

                <div class="flex justify-end gap-3 mt-6">
                    <x-button href="{{ route('financeiro.contas-financeiras.index') }}" variant="danger" size="md" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </x-button>

                    <x-button type="submit" variant="primary" size="md" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </x-button>
                </div>

            </form>

        </div>
    </div>

</x-app-layout>
