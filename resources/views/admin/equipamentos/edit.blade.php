<x-app-layout>

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Equipamentos', 'url' => route('admin.equipamentos.index')],
            ['label' => 'Editar Equipamento']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Editar Equipamento</h2>
                    <p class="text-sm text-gray-500 mt-1">Atualize as informações do equipamento</p>
                </div>

                <form action="{{ route('admin.equipamentos.update', $equipamento->id) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Dados Básicos --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-4">Dados Básicos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-label for="nome" value="Nome do Equipamento *" />
                                <x-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome', $equipamento->nome)" required autofocus />
                                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="modelo" value="Modelo" />
                                <x-input id="modelo" name="modelo" type="text" class="mt-1 block w-full" :value="old('modelo', $equipamento->modelo)" />
                                <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="fabricante" value="Fabricante" />
                                <x-input id="fabricante" name="fabricante" type="text" class="mt-1 block w-full" :value="old('fabricante', $equipamento->fabricante)" />
                                <x-input-error :messages="$errors->get('fabricante')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-label for="numero_serie" value="Número de Série" />
                                <x-input id="numero_serie" name="numero_serie" type="text" class="mt-1 block w-full" :value="old('numero_serie', $equipamento->numero_serie)" />
                                <x-input-error :messages="$errors->get('numero_serie')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Vinculação --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-4">Vinculação</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-label for="cliente_id" value="Cliente *" />
                                <select id="cliente_id" name="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Selecione um cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" @selected(old('cliente_id', $equipamento->cliente_id) == $cliente->id)>
                                            {{ $cliente->nome_exibicao }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="setor_id" value="Setor" />
                                <select id="setor_id" name="setor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecione um setor</option>
                                    @foreach($setores as $setor)
                                        <option value="{{ $setor->id }}" @selected(old('setor_id', $equipamento->setor_id) == $setor->id)>
                                            {{ $setor->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('setor_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="responsavel_id" value="Responsável" />
                                <select id="responsavel_id" name="responsavel_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecione um responsável</option>
                                    @foreach($responsaveis as $responsavel)
                                        <option value="{{ $responsavel->id }}" @selected(old('responsavel_id', $equipamento->responsavel_id) == $responsavel->id)>
                                            {{ $responsavel->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('responsavel_id')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Manutenção e Limpeza --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-4">Manutenção e Limpeza</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label for="ultima_manutencao" value="Última Manutenção" />
                                <x-input id="ultima_manutencao" name="ultima_manutencao" type="date" class="mt-1 block w-full" :value="old('ultima_manutencao', $equipamento->ultima_manutencao?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('ultima_manutencao')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="ultima_limpeza" value="Última Limpeza" />
                                <x-input id="ultima_limpeza" name="ultima_limpeza" type="date" class="mt-1 block w-full" :value="old('ultima_limpeza', $equipamento->ultima_limpeza?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('ultima_limpeza')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="periodicidade_manutencao_meses" value="Periodicidade Manutenção (meses)" />
                                <x-input id="periodicidade_manutencao_meses" name="periodicidade_manutencao_meses" type="number" min="1" max="120" class="mt-1 block w-full" :value="old('periodicidade_manutencao_meses', $equipamento->periodicidade_manutencao_meses)" />
                                <x-input-error :messages="$errors->get('periodicidade_manutencao_meses')" class="mt-2" />
                            </div>

                            <div>
                                <x-label for="periodicidade_limpeza_meses" value="Periodicidade Limpeza (meses)" />
                                <x-input id="periodicidade_limpeza_meses" name="periodicidade_limpeza_meses" type="number" min="1" max="120" class="mt-1 block w-full" :value="old('periodicidade_limpeza_meses', $equipamento->periodicidade_limpeza_meses)" />
                                <x-input-error :messages="$errors->get('periodicidade_limpeza_meses')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Observações --}}
                    <div>
                        <x-label for="observacoes" value="Observações" />
                        <textarea id="observacoes" name="observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('observacoes', $equipamento->observacoes) }}</textarea>
                        <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
                    </div>

                    {{-- Status --}}
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="ativo" name="ativo" value="1" @checked(old('ativo', $equipamento->ativo)) class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label for="ativo" class="text-sm text-gray-700">Equipamento ativo</label>
                    </div>

                    {{-- QR Code --}}
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">QR Code do Equipamento</h4>
                                <p class="text-xs text-gray-500 mt-1">Token: {{ $equipamento->qrcode_token }}</p>
                            </div>
                            <a href="{{ route('admin.equipamentos.qrcode', $equipamento->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Baixar QR Code
                            </a>
                        </div>
                    </div>

                    {{-- Botões --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                        <x-button type="submit" variant="success">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Salvar Alterações
                        </x-button>
                        <a href="{{ route('admin.equipamentos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
