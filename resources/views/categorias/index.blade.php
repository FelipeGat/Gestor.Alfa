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
            ['label' => 'Categorias Financeiras']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Card 1: Abas/Navegação --}}
            <div class="bg-white rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex gap-2 p-4" aria-label="Tabs">
                        <button type="button" class="tab-btn active px-4 py-2 text-sm font-medium rounded-full border border-gray-300 hover:bg-gray-50 transition" data-tab="categorias">
                            Categorias ({{ $categorias->count() }})
                        </button>
                        <button type="button" class="tab-btn px-4 py-2 text-sm font-medium rounded-full border border-gray-300 hover:bg-gray-50 transition" data-tab="subcategorias">
                            Subcategorias ({{ $subcategorias->count() }})
                        </button>
                        <button type="button" class="tab-btn px-4 py-2 text-sm font-medium rounded-full border border-gray-300 hover:bg-gray-50 transition" data-tab="contas">
                            Contas ({{ $contas->count() }})
                        </button>
                    </nav>
                </div>
            </div>

            {{-- Aba: Categorias --}}
            <div id="tab-categorias" class="tab-content active space-y-6">
                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'incluir'))
                <div class="flex justify-start">
                    <x-button href="{{ route('categorias.create') }}" variant="success" size="sm" class="min-w-[130px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Adicionar
                    </x-button>
                </div>
                @endif

                @if($categorias->count())
                @php
                    $columns = [
                        ['label' => 'ID'],
                        ['label' => 'Nome'],
                        ['label' => 'Tipo'],
                        ['label' => 'Subcategorias'],
                        ['label' => 'Status'],
                        ['label' => 'Ações'],
                    ];
                @endphp
                <x-table :columns="$columns" :data="$categorias" :actions="false">
                    @foreach($categorias as $categoria)
                    <tr class="hover:bg-gray-50 transition">
                        <x-table-cell>{{ $categoria->id }}</x-table-cell>
                        <x-table-cell>{{ $categoria->nome }}</x-table-cell>
                        <x-table-cell>{{ $categoria->tipo ?? '—' }}</x-table-cell>
                        <x-table-cell>{{ $categoria->subcategorias->count() }}</x-table-cell>
                        <x-table-cell>
                            <x-status-badge :ativo="$categoria->ativo" />
                        </x-table-cell>
                        <x-table-cell>
                            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                <x-actions 
                                    :edit-url="route('categorias.edit', $categoria)" 
                                    :delete-url="route('categorias.destroy', $categoria)"
                                    :show-view="false"
                                    confirm-delete-message="Tem certeza que deseja excluir esta Categoria? Isso excluirá todas as subcategorias e contas vinculadas."
                                />
                            @endif
                        </x-table-cell>
                    </tr>
                    @endforeach
                </x-table>
                @else
                <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-medium text-gray-900">Nenhuma categoria encontrada</h3>
                </div>
                @endif
            </div>

            {{-- Aba: Subcategorias --}}
            <div id="tab-subcategorias" class="tab-content space-y-6">
                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'incluir'))
                <div class="flex justify-start">
                    <x-button href="{{ route('subcategorias.create') }}" variant="success" size="sm" class="min-w-[130px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Adicionar
                    </x-button>
                </div>
                @endif

                @if($subcategorias->count())
                @php
                    $columns = [
                        ['label' => 'ID'],
                        ['label' => 'Categoria'],
                        ['label' => 'Nome'],
                        ['label' => 'Contas'],
                        ['label' => 'Status'],
                        ['label' => 'Ações'],
                    ];
                @endphp
                <x-table :columns="$columns" :data="$subcategorias" :actions="false">
                    @foreach($subcategorias as $subcategoria)
                    <tr class="hover:bg-gray-50 transition">
                        <x-table-cell>{{ $subcategoria->id }}</x-table-cell>
                        <x-table-cell>{{ $subcategoria->categoria->nome ?? '—' }}</x-table-cell>
                        <x-table-cell>{{ $subcategoria->nome }}</x-table-cell>
                        <x-table-cell>{{ $subcategoria->contas->count() }}</x-table-cell>
                        <x-table-cell>
                            <x-status-badge :ativo="$subcategoria->ativo" />
                        </x-table-cell>
                        <x-table-cell>
                            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                <x-actions 
                                    :edit-url="route('subcategorias.edit', $subcategoria)" 
                                    :delete-url="route('subcategorias.destroy', $subcategoria)"
                                    :show-view="false"
                                    confirm-delete-message="Tem certeza que deseja excluir esta Subcategoria? Isso excluirá todas as contas vinculadas."
                                />
                            @endif
                        </x-table-cell>
                    </tr>
                    @endforeach
                </x-table>
                @else
                <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-medium text-gray-900">Nenhuma subcategoria encontrada</h3>
                </div>
                @endif
            </div>

            {{-- Aba: Contas --}}
            <div id="tab-contas" class="tab-content space-y-6">
                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'incluir'))
                <div class="flex justify-start">
                    <x-button href="{{ route('contas.create') }}" variant="success" size="sm" class="min-w-[130px]">
                        <x-slot name="iconLeft">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>
                        Adicionar
                    </x-button>
                </div>
                @endif

                @if($contas->count())
                @php
                    $columns = [
                        ['label' => 'ID'],
                        ['label' => 'Categoria'],
                        ['label' => 'Subcategoria'],
                        ['label' => 'Nome'],
                        ['label' => 'Status'],
                        ['label' => 'Ações'],
                    ];
                @endphp
                <x-table :columns="$columns" :data="$contas" :actions="false">
                    @foreach($contas as $conta)
                    <tr class="hover:bg-gray-50 transition">
                        <x-table-cell>{{ $conta->id }}</x-table-cell>
                        <x-table-cell>{{ $conta->subcategoria->categoria->nome ?? '—' }}</x-table-cell>
                        <x-table-cell>{{ $conta->subcategoria->nome ?? '—' }}</x-table-cell>
                        <x-table-cell>{{ $conta->nome }}</x-table-cell>
                        <x-table-cell>
                            <x-status-badge :ativo="$conta->ativo" />
                        </x-table-cell>
                        <x-table-cell>
                            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                <x-actions 
                                    :edit-url="route('contas.edit', $conta)" 
                                    :delete-url="route('contas.destroy', $conta)"
                                    :show-view="false"
                                    confirm-delete-message="Tem certeza que deseja excluir esta Conta?"
                                />
                            @endif
                        </x-table-cell>
                    </tr>
                    @endforeach
                </x-table>
                @else
                <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-medium text-gray-900">Nenhuma conta encontrada</h3>
                </div>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                btn.classList.add('active');
                document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            });
        });
    </script>
    @endpush
</x-app-layout>
