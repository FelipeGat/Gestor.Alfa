<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Equipamentos', 'url' => route('admin.equipamentos.index')],
            ['label' => 'Novo Equipamento']
        ]" />
    </x-slot>

    <x-page-title title="Novo Equipamento" :route="route('admin.equipamentos.index')" />

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow">
                <h3 class="font-medium mb-2">Erros encontrados:</h3>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('admin.equipamentos.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- SEÇÃO 1: DADOS BÁSICOS --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados Básicos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <x-form-input name="nome" label="Nome do Equipamento *" required placeholder="Ex: Ar Condicionado Split" />
                        <x-form-input name="modelo" label="Modelo" placeholder="Ex: Hi Wall 12000 BTUs" />
                        <x-form-input name="fabricante" label="Fabricante" placeholder="Ex: Samsung" />
                        <x-form-input name="numero_serie" label="Número de Série" placeholder="Ex: 123456789" />
                    </div>
                </div>

                {{-- SEÇÃO 2: VINCULAÇÃO --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Vinculação
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="sm:col-span-2">
                            <x-form-select name="cliente_id" label="Cliente *" required placeholder="Selecione um cliente">
                                <option value="">Selecione um cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                        {{ $cliente->nome_exibicao }}
                                    </option>
                                @endforeach
                            </x-form-select>
                        </div>

                        {{-- Setor com Autocomplete --}}
                        <div x-data="autocompleteSetor" x-init="initAutocomplete()">
                            <label for="setor_nome" class="block text-sm font-medium text-gray-700 mb-1">
                                Setor
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="setor_nome" 
                                    name="setor_nome" 
                                    value="{{ old('setor_nome') }}"
                                    placeholder="Ex: RH, TI, Produção..."
                                    x-model="search"
                                    @input="buscar()"
                                    @blur="handleBlur()"
                                    @focus="focar()"
                                    autocomplete="off"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]"
                                />
                                
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
                                <div x-show="carregando" class="absolute right-3 top-9" x-cloak>
                                    <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Responsável com Autocomplete --}}
                        <div x-data="autocompleteResponsavel" x-init="initAutocomplete()">
                            <label for="responsavel_nome" class="block text-sm font-medium text-gray-700 mb-1">
                                Responsável
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="responsavel_nome" 
                                    name="responsavel_nome" 
                                    value="{{ old('responsavel_nome') }}"
                                    placeholder="Ex: João Silva"
                                    x-model="search"
                                    @input="buscar()"
                                    @blur="handleBlur()"
                                    @focus="focar()"
                                    autocomplete="off"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]"
                                />
                                
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
                                <div x-show="carregando" class="absolute right-3 top-9" x-cloak>
                                    <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: MANUTENÇÃO E LIMPEZA --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Manutenção e Limpeza
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <x-form-input name="ultima_manutencao" label="Última Manutenção" type="date" />
                        <x-form-input name="ultima_limpeza" label="Última Limpeza" type="date" />
                        <x-form-input name="periodicidade_manutencao_meses" label="Periodicidade Manutenção (meses)" type="number" min="1" max="120" value="6" />
                        <x-form-input name="periodicidade_limpeza_meses" label="Periodicidade Limpeza (meses)" type="number" min="1" max="120" value="1" />
                    </div>
                </div>

                {{-- SEÇÃO 4: OBSERVAÇÕES E STATUS --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Observações e Status
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">
                                Observações
                            </label>
                            <textarea 
                                id="observacoes" 
                                name="observacoes" 
                                rows="4" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]"
                            >{{ old('observacoes') }}</textarea>
                        </div>

                        <div class="flex items-center gap-3">
                            <input 
                                type="checkbox" 
                                id="ativo" 
                                name="ativo" 
                                value="1" 
                                @checked(old('ativo', true)) 
                                class="rounded border-gray-300 text-[#3f9cae] shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]"
                            >
                            <label for="ativo" class="text-sm font-medium text-gray-700">
                                Equipamento ativo
                            </label>
                        </div>
                    </div>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <x-button href="{{ route('admin.equipamentos.index') }}" variant="danger" size="md" class="min-w-[130px]">
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
