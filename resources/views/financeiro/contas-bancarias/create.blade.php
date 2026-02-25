<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Contas Bancárias', 'url' => route('financeiro.contas-financeiras.index')],
            ['label' => 'Nova Conta']
        ]" />
    </x-slot>

    <x-page-title title="Nova Conta Bancária" :route="route('financeiro.contas-financeiras.index')" />

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

            <form method="POST" action="{{ route('financeiro.contas-financeiras.store') }}">
                @csrf

                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados da Conta
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-select name="empresa_id" label="Empresa" required placeholder="Selecione a empresa">
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}">
                                    {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                                </option>
                            @endforeach
                        </x-form-select>

                        <x-form-input name="nome" label="Banco" required placeholder="Ex: Banco do Brasil, Sicoob" />

                        <x-form-select name="tipo" label="Tipo da Conta" required placeholder="Selecione o tipo">
                            <option value="corrente">Conta Corrente</option>
                            <option value="poupanca">Poupança</option>
                            <option value="investimento">Investimento</option>
                            <option value="credito">Cartão de Crédito</option>
                            <option value="pix">Pix</option>
                            <option value="caixa">Caixa</option>
                        </x-form-select>

                        <x-form-input name="saldo" label="Saldo Inicial" type="number" step="0.01" placeholder="Pode ser negativo" />

                        <x-form-input name="limite_credito" label="Limite do Cartão de Crédito" type="number" step="0.01" placeholder="Ex: 5000.00" />

                        <x-form-input name="limite_credito_utilizado" label="Limite Utilizado (Cartão)" type="number" step="0.01" placeholder="Ex: 1200.00" />

                        <x-form-input name="limite_cheque_especial" label="Limite Cheque Especial" type="number" step="0.01" placeholder="Ex: 3000.00" />

                        <x-form-select name="ativo" label="Conta Ativa" required placeholder="Selecione">
                            <option value="1">Sim</option>
                            <option value="0">Não</option>
                        </x-form-select>
                    </div>
                </div>

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
