<x-app-layout>

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Equipamentos', 'url' => route('admin.equipamentos.index')],
            ['label' => 'Novo Equipamento']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Novo Equipamento</h2>
                    <p class="text-sm text-gray-500 mt-1">Preencha as informações abaixo para cadastrar um novo equipamento</p>
                </div>

                <form action="{{ route('admin.equipamentos.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    {{-- Dados Básicos --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-4">Dados Básicos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-input-label for="nome" value="Nome do Equipamento *" />
                                <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome')" required autofocus />
                                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="modelo" value="Modelo" />
                                <x-text-input id="modelo" name="modelo" type="text" class="mt-1 block w-full" :value="old('modelo')" />
                                <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fabricante" value="Fabricante" />
                                <x-text-input id="fabricante" name="fabricante" type="text" class="mt-1 block w-full" :value="old('fabricante')" />
                                <x-input-error :messages="$errors->get('fabricante')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="numero_serie" value="Número de Série" />
                                <x-text-input id="numero_serie" name="numero_serie" type="text" class="mt-1 block w-full" :value="old('numero_serie')" />
                                <x-input-error :messages="$errors->get('numero_serie')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Vinculação --}}
                    <div x-data="{ clienteId: '{{ old('cliente_id') }}' }">
                        <h3 class="text-sm font-medium text-gray-900 mb-4">Vinculação</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-input-label for="cliente_id" value="Cliente *" />
                                <select 
                                    id="cliente_id" 
                                    name="cliente_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    required
                                    x-model="clienteId">
                                    <option value="">Selecione um cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                            {{ $cliente->nome_exibicao }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
                            </div>

                            <div x-data="autocompleteSetor" x-init="initAutocomplete()">
                                <x-input-label for="setor_nome" value="Setor" />
                                <div class="relative">
                                    <x-text-input 
                                        id="setor_nome" 
                                        name="setor_nome" 
                                        type="text" 
                                        class="mt-1 block w-full" 
                                        :value="old('setor_nome')" 
                                        placeholder="Ex: RH, TI, Produção..."
                                        x-model="search"
                                        x-on:input="buscar()"
                                        x-on:blur="handleBlur()"
                                        x-on:focus="focar()"
                                        autocomplete="off"
                                    />
                                    <x-input-error :messages="$errors->get('setor_nome')" class="mt-2" />
                                    
                                    {{-- Dropdown de sugestões --}}
                                    <div 
                                        x-show="mostrarDropdown && sugestoes.length > 0" 
                                        x-cloak
                                        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                        @click.outside="mostrarDropdown = false"
                                    >
                                        <template x-for="setor in sugestoes" :key="setor.id">
                                            <div 
                                                class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                                                x-text="setor.nome"
                                                @click="selecionar(setor.nome)"
                                            ></div>
                                        </template>
                                        <div class="px-4 py-2 text-xs text-gray-500 border-t border-gray-200">
                                            Digite para criar um novo setor
                                        </div>
                                    </div>
                                    
                                    {{-- Loading --}}
                                    <div x-show="carregando" class="absolute right-3 top-10" x-cloak>
                                        <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div x-data="autocompleteResponsavel" x-init="initAutocomplete()">
                                <x-input-label for="responsavel_nome" value="Responsável" />
                                <div class="relative">
                                    <x-text-input 
                                        id="responsavel_nome" 
                                        name="responsavel_nome" 
                                        type="text" 
                                        class="mt-1 block w-full" 
                                        :value="old('responsavel_nome')" 
                                        placeholder="Ex: João Silva"
                                        x-model="search"
                                        x-on:input="buscar()"
                                        x-on:blur="handleBlur()"
                                        x-on:focus="focar()"
                                        autocomplete="off"
                                    />
                                    <x-input-error :messages="$errors->get('responsavel_nome')" class="mt-2" />
                                    
                                    {{-- Dropdown de sugestões --}}
                                    <div 
                                        x-show="mostrarDropdown && sugestoes.length > 0" 
                                        x-cloak
                                        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                                        @click.outside="mostrarDropdown = false"
                                    >
                                        <template x-for="resp in sugestoes" :key="resp.id">
                                            <div 
                                                class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                                                @click="selecionar(resp.nome)"
                                            >
                                                <span x-text="resp.nome"></span>
                                                <span x-show="resp.cargo" class="text-xs text-gray-500 ml-2" x-text="'(' + resp.cargo + ')'"></span>
                                            </div>
                                        </template>
                                        <div class="px-4 py-2 text-xs text-gray-500 border-t border-gray-200">
                                            Digite para criar um novo responsável
                                        </div>
                                    </div>
                                    
                                    {{-- Loading --}}
                                    <div x-show="carregando" class="absolute right-3 top-10" x-cloak>
                                        <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Manutenção e Limpeza --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-4">Manutenção e Limpeza</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="ultima_manutencao" value="Última Manutenção" />
                                <x-text-input id="ultima_manutencao" name="ultima_manutencao" type="date" class="mt-1 block w-full" :value="old('ultima_manutencao')" />
                                <x-input-error :messages="$errors->get('ultima_manutencao')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="ultima_limpeza" value="Última Limpeza" />
                                <x-text-input id="ultima_limpeza" name="ultima_limpeza" type="date" class="mt-1 block w-full" :value="old('ultima_limpeza')" />
                                <x-input-error :messages="$errors->get('ultima_limpeza')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="periodicidade_manutencao_meses" value="Periodicidade Manutenção (meses)" />
                                <x-text-input id="periodicidade_manutencao_meses" name="periodicidade_manutencao_meses" type="number" min="1" max="120" class="mt-1 block w-full" :value="old('periodicidade_manutencao_meses', 6)" />
                                <x-input-error :messages="$errors->get('periodicidade_manutencao_meses')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="periodicidade_limpeza_meses" value="Periodicidade Limpeza (meses)" />
                                <x-text-input id="periodicidade_limpeza_meses" name="periodicidade_limpeza_meses" type="number" min="1" max="120" class="mt-1 block w-full" :value="old('periodicidade_limpeza_meses', 1)" />
                                <x-input-error :messages="$errors->get('periodicidade_limpeza_meses')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Observações --}}
                    <div>
                        <x-input-label for="observacoes" value="Observações" />
                        <textarea id="observacoes" name="observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('observacoes') }}</textarea>
                        <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
                    </div>

                    {{-- Status --}}
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="ativo" name="ativo" value="1" @checked(old('ativo', true)) class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label for="ativo" class="text-sm text-gray-700">Equipamento ativo</label>
                    </div>

                    {{-- Botões --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                        <x-button type="submit" variant="success">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Salvar
                        </x-button>
                        <a href="{{ route('admin.equipamentos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Função auxiliar para obter o cliente selecionado
        function getClienteId() {
            const select = document.getElementById('cliente_id');
            return select ? select.value : '';
        }

        // Componente Alpine para autocomplete de Setor
        function autocompleteSetor() {
            return {
                search: '',
                sugestoes: [],
                mostrarDropdown: false,
                carregando: false,
                debounceTimer: null,

                initAutocomplete() {
                    // Watch para mudanças no cliente_id
                    this.$watch('search', () => {
                        if (!getClienteId() && this.search.length > 0) {
                            alert('Selecione um cliente primeiro');
                            this.search = '';
                        }
                    });
                },

                buscar() {
                    const clienteId = getClienteId();
                    
                    if (!clienteId) {
                        this.sugestoes = [];
                        this.mostrarDropdown = false;
                        return;
                    }

                    clearTimeout(this.debounceTimer);
                    
                    if (this.search.length < 1) {
                        this.sugestoes = [];
                        this.mostrarDropdown = false;
                        return;
                    }

                    this.carregando = true;

                    this.debounceTimer = setTimeout(() => {
                        fetch(`/equipamentos/api/setores/${clienteId}`)
                            .then(response => response.json())
                            .then(data => {
                                const termo = this.search.toLowerCase();
                                this.sugestoes = data.filter(s => 
                                    s.nome.toLowerCase().includes(termo)
                                );
                                this.mostrarDropdown = this.sugestoes.length > 0;
                                this.carregando = false;
                            })
                            .catch(() => {
                                this.carregando = false;
                                this.sugestoes = [];
                            });
                    }, 300);
                },

                selecionar(nome) {
                    this.search = nome;
                    this.mostrarDropdown = false;
                    this.sugestoes = [];
                },

                focar() {
                    if (this.search.length > 0 && this.sugestoes.length > 0) {
                        this.mostrarDropdown = true;
                    }
                },

                handleBlur() {
                    setTimeout(() => {
                        this.mostrarDropdown = false;
                    }, 200);
                }
            }
        }

        // Componente Alpine para autocomplete de Responsável
        function autocompleteResponsavel() {
            return {
                search: '',
                sugestoes: [],
                mostrarDropdown: false,
                carregando: false,
                debounceTimer: null,

                initAutocomplete() {
                    // Watch para mudanças no cliente_id
                    this.$watch('search', () => {
                        if (!getClienteId() && this.search.length > 0) {
                            alert('Selecione um cliente primeiro');
                            this.search = '';
                        }
                    });
                },

                buscar() {
                    const clienteId = getClienteId();
                    
                    if (!clienteId) {
                        this.sugestoes = [];
                        this.mostrarDropdown = false;
                        return;
                    }

                    clearTimeout(this.debounceTimer);
                    
                    if (this.search.length < 1) {
                        this.sugestoes = [];
                        this.mostrarDropdown = false;
                        return;
                    }

                    this.carregando = true;

                    this.debounceTimer = setTimeout(() => {
                        fetch(`/equipamentos/api/responsaveis/${clienteId}`)
                            .then(response => response.json())
                            .then(data => {
                                const termo = this.search.toLowerCase();
                                this.sugestoes = data.filter(r => 
                                    r.nome.toLowerCase().includes(termo)
                                );
                                this.mostrarDropdown = this.sugestoes.length > 0;
                                this.carregando = false;
                            })
                            .catch(() => {
                                this.carregando = false;
                                this.sugestoes = [];
                            });
                    }, 300);
                },

                selecionar(nome) {
                    this.search = nome;
                    this.mostrarDropdown = false;
                    this.sugestoes = [];
                },

                focar() {
                    if (this.search.length > 0 && this.sugestoes.length > 0) {
                        this.mostrarDropdown = true;
                    }
                },

                handleBlur() {
                    setTimeout(() => {
                        this.mostrarDropdown = false;
                    }, 200);
                }
            }
        }
    </script>
</x-app-layout>
