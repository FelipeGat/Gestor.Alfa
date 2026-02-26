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

            <form method="GET" class="bg-white rounded-lg p-4" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="flex items-center gap-4">
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-1">Exibir</label>
                        <select name="ativas" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#3f9cae]" onchange="this.form.submit()">
                            <option value="todas" {{ $ativas === 'todas' ? 'selected' : '' }}>Todas</option>
                            <option value="ativas" {{ $ativas === 'ativas' ? 'selected' : '' }}>Apenas Ativas</option>
                            <option value="inativas" {{ $ativas === 'inativas' ? 'selected' : '' }}>Apenas Inativas</option>
                        </select>
                    </div>
                </div>
            </form>

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

                <div class="p-4">
                    <div id="tab-categorias" class="tab-content active">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Categorias</h3>
                            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'incluir'))
                            <x-button type="button" variant="success" size="sm" class="min-w-[130px]" onclick="openModal('modal-categoria')">
                                <x-slot name="iconLeft">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                </x-slot>
                                Adicionar
                            </x-button>
                            @endif
                        </div>

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
                                <x-table-cell>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $categoria->subcategorias->count() }}
                                    </span>
                                </x-table-cell>
                                <x-table-cell>
                                    <x-status-badge :ativo="$categoria->ativo" />
                                </x-table-cell>
                                <x-table-cell>
                                    <div class="flex items-center gap-1">
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                        <button type="button" class="p-2 rounded-full inline-flex items-center justify-center text-[#3f9cae] hover:bg-[#3f9cae]/10 transition" onclick="editCategoria({{ $categoria->id }}, '{{ $categoria->nome }}', '{{ $categoria->tipo }}', {{ $categoria->ativo }})">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        @endif
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'excluir'))
                                        <form id="form-categoria-delete-{{ $categoria->id }}" action="{{ route('categorias.destroy', $categoria) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta Categoria? Isso excluirá todas as subcategorias e contas vinculadas.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-full inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
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
                        <div class="text-center py-8 text-gray-500">Nenhuma categoria encontrada.</div>
                        @endif
                    </div>

                    <div id="tab-subcategorias" class="tab-content">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Subcategorias</h3>
                            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'incluir'))
                            <x-button type="button" variant="success" size="sm" class="min-w-[130px]" onclick="openModal('modal-subcategoria')">
                                <x-slot name="iconLeft">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                </x-slot>
                                Adicionar
                            </x-button>
                            @endif
                        </div>

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
                                <x-table-cell>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $subcategoria->contas->count() }}
                                    </span>
                                </x-table-cell>
                                <x-table-cell>
                                    <x-status-badge :ativo="$subcategoria->ativo" />
                                </x-table-cell>
                                <x-table-cell>
                                    <div class="flex items-center gap-1">
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                        <button type="button" class="p-2 rounded-full inline-flex items-center justify-center text-[#3f9cae] hover:bg-[#3f9cae]/10 transition" onclick="editSubcategoria({{ $subcategoria->id }}, {{ $subcategoria->categoria_id }}, '{{ $subcategoria->nome }}', {{ $subcategoria->ativo }})">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        @endif
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'excluir'))
                                        <form id="form-subcategoria-delete-{{ $subcategoria->id }}" action="{{ route('subcategorias.destroy', $subcategoria) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta Subcategoria? Isso excluirá todas as contas vinculadas.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-full inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
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
                        <div class="text-center py-8 text-gray-500">Nenhuma subcategoria encontrada.</div>
                        @endif
                    </div>

                    <div id="tab-contas" class="tab-content">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Contas</h3>
                            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'incluir'))
                            <x-button type="button" variant="success" size="sm" class="min-w-[130px]" onclick="openModal('modal-conta')">
                                <x-slot name="iconLeft">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                </x-slot>
                                Adicionar
                            </x-button>
                            @endif
                        </div>

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
                                    <div class="flex items-center gap-1">
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                        <button type="button" class="p-2 rounded-full inline-flex items-center justify-center text-[#3f9cae] hover:bg-[#3f9cae]/10 transition" onclick="editConta({{ $conta->id }}, {{ $conta->subcategoria_id }}, {{ $conta->subcategoria->categoria_id ?? 0 }}, '{{ $conta->nome }}', {{ $conta->ativo }})">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        @endif
                                        @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'excluir'))
                                        <form id="form-conta-delete-{{ $conta->id }}" action="{{ route('contas.destroy', $conta) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta Conta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-full inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
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
                        <div class="text-center py-8 text-gray-500">Nenhuma conta encontrada.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
