<x-app-layout>
    @push('styles')
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-btn.active {
            background-color: #3f9cae;
            color: white;
            border-color: #3f9cae;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Equipamentos']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros (comuns a todas as abas) --}}
            <div class="bg-white rounded-lg p-4" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <form action="{{ route('admin.equipamentos.index') }}" method="GET" class="space-y-4">
                    <input type="hidden" name="tab" :value="request('tab', 'equipamentos')">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <x-input-label value="Pesquisar" />
                            <x-text-input type="text" name="search" :value="request('search')" placeholder="Digite para pesquisar..." class="w-full" />
                        </div>
                        <div>
                            <x-input-label value="Cliente" />
                            <select name="cliente_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach(\App\Models\Cliente::where('ativo', true)->orderBy('nome')->get() as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(request('cliente_id') == $cliente->id)>
                                        {{ $cliente->nome_exibicao }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="Status" />
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="ativo" @selected(request('status')=='ativo')>Ativo</option>
                                <option value="inativo" @selected(request('status')=='inativo')>Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <x-button type="submit" variant="primary" size="sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Filtrar
                        </x-button>
                        <a href="{{ route('admin.equipamentos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpar</a>
                    </div>
                </form>
            </div>

            {{-- Card de Abas --}}
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex gap-2 p-4" aria-label="Tabs">
                        <button type="button" class="tab-btn active px-4 py-2 text-sm font-medium rounded-full border border-gray-300 hover:bg-gray-50 transition" data-tab="equipamentos">
                            Equipamentos ({{ $equipamentos->count() }})
                        </button>
                        <button type="button" class="tab-btn px-4 py-2 text-sm font-medium rounded-full border border-gray-300 hover:bg-gray-50 transition" data-tab="setores">
                            Setores ({{ $setores->count() }})
                        </button>
                        <button type="button" class="tab-btn px-4 py-2 text-sm font-medium rounded-full border border-gray-300 hover:bg-gray-50 transition" data-tab="responsaveis">
                            Responsáveis ({{ $responsaveis->count() }})
                        </button>
                    </nav>
                </div>
            </div>

            {{-- Aba: Equipamentos --}}
            <div id="tab-equipamentos" class="tab-content active space-y-6">
                @if(auth()->user()->canPermissao('equipamentos', 'incluir'))
                <div class="flex justify-start">
                    <x-button href="{{ route('admin.equipamentos.create') }}" variant="success" size="sm" class="min-w-[130px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Adicionar
                    </x-button>
                </div>
                @endif

                @if($equipamentos->count())
                @php
                    $columns = [
                        ['label' => 'ID'],
                        ['label' => 'Cliente'],
                        ['label' => 'Equipamento'],
                        ['label' => 'Modelo'],
                        ['label' => 'Setor'],
                        ['label' => 'Responsável'],
                        ['label' => 'Status'],
                        ['label' => 'Ações'],
                    ];
                @endphp
                <x-table :columns="$columns" :data="$equipamentos" :actions="false">
                    @foreach($equipamentos as $equipamento)
                    <tr class="hover:bg-gray-50 transition">
                        <x-table-cell :nowrap="true">{{ $equipamento->id }}</x-table-cell>
                        <x-table-cell>{{ $equipamento->cliente->nome_exibicao }}</x-table-cell>
                        <x-table-cell class="font-medium text-gray-900">{{ $equipamento->nome }}</x-table-cell>
                        <x-table-cell>{{ $equipamento->modelo ?? '-' }}</x-table-cell>
                        <x-table-cell>{{ $equipamento->setor->nome ?? '-' }}</x-table-cell>
                        <x-table-cell>{{ $equipamento->responsavel->nome ?? '-' }}</x-table-cell>
                        <x-table-cell :nowrap="true">
                            @if($equipamento->ativo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inativo
                                </span>
                            @endif
                        </x-table-cell>
                        <x-table-cell :nowrap="true">
                            <div class="flex items-center gap-2">
                                @if(auth()->user()->canPermissao('equipamentos', 'ler'))
                                    <a href="{{ route('admin.equipamentos.show', $equipamento->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                @endif
                                @if(auth()->user()->canPermissao('equipamentos', 'alterar'))
                                    <a href="{{ route('admin.equipamentos.edit', $equipamento->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @endif
                                @if(auth()->user()->canPermissao('equipamentos', 'excluir'))
                                    <form action="{{ route('admin.equipamentos.destroy', $equipamento->id) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este equipamento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </x-table-cell>
                    </tr>
                    @endforeach
                </x-table>
                @else
                <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-medium text-gray-900">Nenhum equipamento encontrado</h3>
                </div>
                @endif
            </div>

            {{-- Aba: Setores --}}
            <div id="tab-setores" class="tab-content space-y-6">
                @if(auth()->user()->canPermissao('equipamentos', 'incluir'))
                <div class="flex justify-start">
                    <x-button href="{{ route('admin.setores.create') }}" variant="success" size="sm" class="min-w-[130px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Adicionar
                    </x-button>
                </div>
                @endif

                @if($setores->count())
                @php
                    $columns = [
                        ['label' => 'ID'],
                        ['label' => 'Cliente'],
                        ['label' => 'Setor'],
                        ['label' => 'Descrição'],
                        ['label' => 'Equipamentos'],
                        ['label' => 'Ações'],
                    ];
                @endphp
                <x-table :columns="$columns" :data="$setores" :actions="false">
                    @foreach($setores as $setor)
                    <tr class="hover:bg-gray-50 transition">
                        <x-table-cell :nowrap="true">{{ $setor->id }}</x-table-cell>
                        <x-table-cell>{{ $setor->cliente->nome_exibicao }}</x-table-cell>
                        <x-table-cell class="font-medium text-gray-900">{{ $setor->nome }}</x-table-cell>
                        <x-table-cell>{{ $setor->descricao ?? '-' }}</x-table-cell>
                        <x-table-cell :nowrap="true">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $setor->equipamentos_count }} equipamento(s)
                            </span>
                        </x-table-cell>
                        <x-table-cell :nowrap="true">
                            <div class="flex items-center gap-2">
                                @if(auth()->user()->canPermissao('equipamentos', 'ler'))
                                    <a href="{{ route('admin.setores.show', $setor->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                @endif
                                @if(auth()->user()->canPermissao('equipamentos', 'alterar'))
                                    <a href="{{ route('admin.setores.edit', $setor->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @endif
                                @if(auth()->user()->canPermissao('equipamentos', 'excluir'))
                                    <form action="{{ route('admin.setores.destroy', $setor->id) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este setor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </x-table-cell>
                    </tr>
                    @endforeach
                </x-table>
                @else
                <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-medium text-gray-900">Nenhum setor encontrado</h3>
                </div>
                @endif
            </div>

            {{-- Aba: Responsáveis --}}
            <div id="tab-responsaveis" class="tab-content space-y-6">
                @if(auth()->user()->canPermissao('equipamentos', 'incluir'))
                <div class="flex justify-start">
                    <x-button href="{{ route('admin.responsaveis.create') }}" variant="success" size="sm" class="min-w-[130px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Adicionar
                    </x-button>
                </div>
                @endif

                @if($responsaveis->count())
                @php
                    $columns = [
                        ['label' => 'ID'],
                        ['label' => 'Cliente'],
                        ['label' => 'Nome'],
                        ['label' => 'Cargo'],
                        ['label' => 'Contato'],
                        ['label' => 'Equipamentos'],
                        ['label' => 'Ações'],
                    ];
                @endphp
                <x-table :columns="$columns" :data="$responsaveis" :actions="false">
                    @foreach($responsaveis as $responsavel)
                    <tr class="hover:bg-gray-50 transition">
                        <x-table-cell :nowrap="true">{{ $responsavel->id }}</x-table-cell>
                        <x-table-cell>{{ $responsavel->cliente->nome_exibicao }}</x-table-cell>
                        <x-table-cell class="font-medium text-gray-900">{{ $responsavel->nome }}</x-table-cell>
                        <x-table-cell>{{ $responsavel->cargo ?? '-' }}</x-table-cell>
                        <x-table-cell :nowrap="true">
                            @if($responsavel->telefone || $responsavel->email)
                                <div class="flex flex-col">
                                    @if($responsavel->telefone)
                                        <span class="text-xs text-gray-600">{{ $responsavel->telefone }}</span>
                                    @endif
                                    @if($responsavel->email)
                                        <span class="text-xs text-gray-600">{{ $responsavel->email }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table-cell>
                        <x-table-cell :nowrap="true">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $responsavel->equipamentos_count }} equipamento(s)
                            </span>
                        </x-table-cell>
                        <x-table-cell :nowrap="true">
                            <div class="flex items-center gap-2">
                                @if(auth()->user()->canPermissao('equipamentos', 'ler'))
                                    <a href="{{ route('admin.responsaveis.show', $responsavel->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                @endif
                                @if(auth()->user()->canPermissao('equipamentos', 'alterar'))
                                    <a href="{{ route('admin.responsaveis.edit', $responsavel->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @endif
                                @if(auth()->user()->canPermissao('equipamentos', 'excluir'))
                                    <form action="{{ route('admin.responsaveis.destroy', $responsavel->id) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este responsável?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </x-table-cell>
                    </tr>
                    @endforeach
                </x-table>
                @else
                <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-medium text-gray-900">Nenhum responsável encontrado</h3>
                </div>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            // Verifica se há uma aba salva na URL (após filtro)
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'equipamentos';

            // Ativa a aba correta
            tabButtons.forEach(button => {
                if (button.getAttribute('data-tab') === activeTab) {
                    button.classList.add('active');
                }
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');

                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to clicked button and corresponding content
                    this.classList.add('active');
                    document.getElementById('tab-' + tabName).classList.add('active');
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
