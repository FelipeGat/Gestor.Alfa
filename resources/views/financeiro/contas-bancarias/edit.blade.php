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

    <x-page-title title="Editar Conta Bancária / Cartão" :route="route('financeiro.contas-financeiras.index')" />

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

            <form method="POST" action="{{ route('financeiro.contas-financeiras.update', $contaFinanceira) }}"
                  x-data="{ tipo: '{{ old('tipo', $contaFinanceira->tipo) }}' }">
                @csrf
                @method('PUT')

                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados da Conta
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-select name="empresa_id" label="Empresa" required>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id', $contaFinanceira->empresa_id) == $empresa->id)>
                                    {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                                </option>
                            @endforeach
                        </x-form-select>

                        <x-form-input name="nome" label="Banco / Nome do Cartão" :value="old('nome', $contaFinanceira->nome)" required />

                        {{-- Tipo com Alpine binding --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                            <select name="tipo" required x-model="tipo"
                                class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                <option value="corrente" @selected(old('tipo', $contaFinanceira->tipo) === 'corrente')>Conta Corrente</option>
                                <option value="poupanca" @selected(old('tipo', $contaFinanceira->tipo) === 'poupanca')>Poupança</option>
                                <option value="investimento" @selected(old('tipo', $contaFinanceira->tipo) === 'investimento')>Investimento</option>
                                <option value="credito" @selected(old('tipo', $contaFinanceira->tipo) === 'credito')>Cartão de Crédito</option>
                                <option value="pix" @selected(old('tipo', $contaFinanceira->tipo) === 'pix')>Pix</option>
                                <option value="caixa" @selected(old('tipo', $contaFinanceira->tipo) === 'caixa')>Caixa</option>
                            </select>
                        </div>

                        {{-- Saldo - oculto para cartão de crédito --}}
                        <div x-show="tipo !== 'credito'">
                            <x-form-input name="saldo" label="Saldo" type="number" step="0.01"
                                :value="old('saldo', $contaFinanceira->saldo)" />
                        </div>

                        {{-- Cheque especial - oculto para cartão de crédito --}}
                        <div x-show="tipo !== 'credito'">
                            <x-form-input name="limite_cheque_especial" label="Limite Cheque Especial"
                                type="number" step="0.01" :value="old('limite_cheque_especial', $contaFinanceira->limite_cheque_especial)" />
                        </div>

                        {{-- ========== CAMPOS EXCLUSIVOS DE CARTÃO DE CRÉDITO ========== --}}
                        <div x-show="tipo === 'credito'" x-cloak class="col-span-1 md:col-span-2">
                            <div class="rounded-xl p-5 mt-2" style="background: rgba(63,156,174,0.05); border: 1px solid rgba(63,156,174,0.3);">
                                <h4 class="text-sm font-semibold text-[#3f9cae] mb-4 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Dados do Cartão de Crédito
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Bandeira</label>
                                        <select name="bandeira"
                                            class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                            <option value="">Selecione a bandeira...</option>
                                            <option value="VISA" @selected(old('bandeira', $contaFinanceira->bandeira) === 'VISA')>VISA</option>
                                            <option value="MASTERCARD" @selected(old('bandeira', $contaFinanceira->bandeira) === 'MASTERCARD')>MASTERCARD</option>
                                            <option value="ELO" @selected(old('bandeira', $contaFinanceira->bandeira) === 'ELO')>ELO</option>
                                            <option value="AMEX" @selected(old('bandeira', $contaFinanceira->bandeira) === 'AMEX')>American Express (AMEX)</option>
                                            <option value="HIPERCARD" @selected(old('bandeira', $contaFinanceira->bandeira) === 'HIPERCARD')>HIPERCARD</option>
                                            <option value="OTHER" @selected(old('bandeira', $contaFinanceira->bandeira) === 'OTHER')>Outra</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Limite Total do Cartão</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">R$</span>
                                            <input type="number" name="limite_credito" step="0.01" min="0"
                                                value="{{ old('limite_credito', $contaFinanceira->limite_credito) }}"
                                                class="w-full pl-9 rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Limite Já Utilizado (saldo da fatura)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">R$</span>
                                            <input type="number" name="limite_credito_utilizado" step="0.01" min="0"
                                                value="{{ old('limite_credito_utilizado', $contaFinanceira->limite_credito_utilizado) }}"
                                                class="w-full pl-9 rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Melhor Dia de Compra
                                            <span class="ml-1 text-xs text-gray-400">(compras neste dia fecham na próxima fatura)</span>
                                        </label>
                                        <input type="number" name="melhor_dia_compra" min="1" max="28"
                                            value="{{ old('melhor_dia_compra', $contaFinanceira->melhor_dia_compra) }}"
                                            class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Dia de Fechamento da Fatura</label>
                                        <input type="number" name="dia_fechamento_fatura" min="1" max="31"
                                            value="{{ old('dia_fechamento_fatura', $contaFinanceira->dia_fechamento_fatura) }}"
                                            class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Dia de Vencimento da Fatura</label>
                                        <input type="number" name="dia_vencimento_fatura" min="1" max="31"
                                            value="{{ old('dia_vencimento_fatura', $contaFinanceira->dia_vencimento_fatura) }}"
                                            class="w-full rounded-lg border border-gray-300 shadow-sm px-3 py-2 text-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]/20">
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- ========== FIM CAMPOS CARTÃO ========== --}}

                        <x-form-select name="ativo" label="Conta Ativa" required>
                            <option value="1" @selected(old('ativo', $contaFinanceira->ativo) == 1)>Sim</option>
                            <option value="0" @selected(old('ativo', $contaFinanceira->ativo) == 0)>Não</option>
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
