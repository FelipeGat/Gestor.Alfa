<x-app-layout>

    @push('styles')
    <style>
        input[name="search"] {
            border-color: #d1d5db !important;
        }
        input[name="search"]:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        select[name="ativas"] {
            border-color: #d1d5db !important;
        }
        select[name="ativas"]:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        .tabela-categorias thead th {
            color: rgb(17, 24, 39) !important;
        }
        .tabela-categorias tbody td {
            font-weight: 400 !important;
        }
        .tabela-categorias tbody td.font-medium {
            font-weight: 400 !important;
        }
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
        .tab-btn.active {
            background-color: #3f9cae;
            color: white;
            border-color: #3f9cae;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
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
                            @if(auth()->user()->canPermissao('categorias', 'incluir'))
                            <button type="button" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onclick="openModal('modal-categoria')">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4 mr-1">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Nova Categoria
                            </button>
                            @endif
                        </div>

                        @if($categorias->count())
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto tabela-categorias">
                                <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                                    <tr>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">ID</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Nome</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Tipo</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Subcategorias</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Status</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($categorias as $categoria)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $categoria->id }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 min-w-[200px] max-w-[280px] truncate font-medium">{{ $categoria->nome }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $categoria->tipo ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $categoria->subcategorias->count() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <x-status-badge :ativo="$categoria->ativo" />
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <div class="flex gap-1 items-center">
                                                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                                <button type="button" class="btn btn-sm btn-edit" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" onclick="editCategoria({{ $categoria->id }}, '{{ $categoria->nome }}', '{{ $categoria->tipo }}', {{ $categoria->ativo }})">
                                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                </button>
                                                @endif
                                                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'excluir'))
                                                <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta Categoria? Isso excluirá todas as subcategorias e contas vinculadas.')" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-delete" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">Nenhuma categoria encontrada.</div>
                        @endif
                    </div>

                    <div id="tab-subcategorias" class="tab-content">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Subcategorias</h3>
                            @if(auth()->user()->canPermissao('categorias', 'incluir'))
                            <button type="button" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onclick="openModal('modal-subcategoria')">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4 mr-1">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Nova Subcategoria
                            </button>
                            @endif
                        </div>

                        @if($subcategorias->count())
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto tabela-categorias">
                                <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                                    <tr>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">ID</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Categoria</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Nome</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Contas</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Status</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($subcategorias as $subcategoria)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $subcategoria->id }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $subcategoria->categoria->nome ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 min-w-[200px] max-w-[280px] truncate font-medium">{{ $subcategoria->nome }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $subcategoria->contas->count() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <x-status-badge :ativo="$subcategoria->ativo" />
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <div class="flex gap-1 items-center">
                                                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                                <button type="button" class="btn btn-sm btn-edit" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" onclick="editSubcategoria({{ $subcategoria->id }}, {{ $subcategoria->categoria_id }}, '{{ $subcategoria->nome }}', {{ $subcategoria->ativo }})">
                                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                </button>
                                                @endif
                                                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'excluir'))
                                                <form action="{{ route('subcategorias.destroy', $subcategoria) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta Subcategoria? Isso excluirá todas as contas vinculadas.')" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-delete" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">Nenhuma subcategoria encontrada.</div>
                        @endif
                    </div>

                    <div id="tab-contas" class="tab-content">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Contas</h3>
                            @if(auth()->user()->canPermissao('categorias', 'incluir'))
                            <button type="button" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px; box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);" onclick="openModal('modal-conta')">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4 mr-1">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Nova Conta
                            </button>
                            @endif
                        </div>

                        @if($contas->count())
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto tabela-categorias">
                                <thead style="background-color: rgba(63, 156, 174, 0.05); border-bottom: 1px solid #3f9cae;">
                                    <tr>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">ID</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Categoria</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Subcategoria</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Nome</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Status</th>
                                        <th class="px-4 py-3 text-left uppercase" style="font-size: 14px; font-weight: 600;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($contas as $conta)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $conta->id }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $conta->subcategoria->categoria->nome ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $conta->subcategoria->nome ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 min-w-[200px] max-w-[280px] truncate font-medium">{{ $conta->nome }}</td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <x-status-badge :ativo="$conta->ativo" />
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <div class="flex gap-1 items-center">
                                                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'editar'))
                                                <button type="button" class="btn btn-sm btn-edit" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" onclick="editConta({{ $conta->id }}, {{ $conta->subcategoria_id }}, {{ $conta->subcategoria->categoria_id ?? 0 }}, '{{ $conta->nome }}', {{ $conta->ativo }})">
                                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                </button>
                                                @endif
                                                @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('categorias', 'excluir'))
                                                <form action="{{ route('contas.destroy', $conta) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta Conta?')" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-delete" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">Nenhuma conta encontrada.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-categoria" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('modal-categoria')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form id="form-categoria" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-categoria-title">Nova Categoria</h3>
                        <input type="hidden" name="_method" id="categoria-method" value="POST">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <input type="text" name="nome" id="categoria-nome" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo (opcional)</label>
                                <input type="text" name="tipo" id="categoria-tipo" placeholder="Ex: Receita, Despesa" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="ativo" id="categoria-ativo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#3f9cae] text-base font-medium text-white hover:bg-[#35858e] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button" onclick="closeModal('modal-categoria')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-subcategoria" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('modal-subcategoria')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form id="form-subcategoria" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-subcategoria-title">Nova Subcategoria</h3>
                        <input type="hidden" name="_method" id="subcategoria-method" value="POST">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                                <select name="categoria_id" id="subcategoria-categoria_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($todasCategorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <input type="text" name="nome" id="subcategoria-nome" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="ativo" id="subcategoria-ativo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#3f9cae] text-base font-medium text-white hover:bg-[#35858e] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button" onclick="closeModal('modal-subcategoria')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-conta" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('modal-conta')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form id="form-conta" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-conta-title">Nova Conta</h3>
                        <input type="hidden" name="_method" id="conta-method" value="POST">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                                <select name="categoria_id" id="conta-categoria_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]" onchange="loadSubcategoriasForConta()">
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($todasCategorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subcategoria</label>
                                <select name="subcategoria_id" id="conta-subcategoria_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                                    <option value="">Selecione uma subcategoria</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <input type="text" name="nome" id="conta-nome" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="ativo" id="conta-ativo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#3f9cae]">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#3f9cae] text-base font-medium text-white hover:bg-[#35858e] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button" onclick="closeModal('modal-conta')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
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

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            if (id === 'modal-categoria') {
                document.getElementById('form-categoria').reset();
                document.getElementById('categoria-method').value = 'POST';
                document.getElementById('modal-categoria-title').textContent = 'Nova Categoria';
                document.getElementById('form-categoria').action = '{{ route("categorias.store") }}';
            } else if (id === 'modal-subcategoria') {
                document.getElementById('form-subcategoria').reset();
                document.getElementById('subcategoria-method').value = 'POST';
                document.getElementById('modal-subcategoria-title').textContent = 'Nova Subcategoria';
                document.getElementById('form-subcategoria').action = '{{ route("subcategorias.store") }}';
            } else if (id === 'modal-conta') {
                document.getElementById('form-conta').reset();
                document.getElementById('conta-method').value = 'POST';
                document.getElementById('modal-conta-title').textContent = 'Nova Conta';
                document.getElementById('form-conta').action = '{{ route("contas.store") }}';
                document.getElementById('conta-subcategoria_id').innerHTML = '<option value="">Selecione uma subcategoria</option>';
            }
        }

        function editCategoria(id, nome, tipo, ativo) {
            document.getElementById('categoria-nome').value = nome;
            document.getElementById('categoria-tipo').value = tipo || '';
            document.getElementById('categoria-ativo').value = ativo ? '1' : '0';
            document.getElementById('categoria-method').value = 'PUT';
            document.getElementById('modal-categoria-title').textContent = 'Editar Categoria';
            document.getElementById('form-categoria').action = '/categorias/' + id;
            openModal('modal-categoria');
        }

        function editSubcategoria(id, categoriaId, nome, ativo) {
            document.getElementById('subcategoria-categoria_id').value = categoriaId;
            document.getElementById('subcategoria-nome').value = nome;
            document.getElementById('subcategoria-ativo').value = ativo ? '1' : '0';
            document.getElementById('subcategoria-method').value = 'PUT';
            document.getElementById('modal-subcategoria-title').textContent = 'Editar Subcategoria';
            document.getElementById('form-subcategoria').action = '/subcategorias/' + id;
            openModal('modal-subcategoria');
        }

        function editConta(id, subcategoriaId, categoriaId, nome, ativo) {
            const categoriaSelect = document.getElementById('conta-categoria_id');
            const subcategoriaSelect = document.getElementById('conta-subcategoria_id');
            
            categoriaSelect.value = categoriaId;
            
            fetch('/financeiro/api/subcategorias/' + categoriaId)
                .then(r => r.json())
                .then(data => {
                    subcategoriaSelect.innerHTML = '<option value="">Selecione uma subcategoria</option>';
                    data.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.textContent = sub.nome;
                        if (sub.id === subcategoriaId) option.selected = true;
                        subcategoriaSelect.appendChild(option);
                    });
                });
            
            document.getElementById('conta-nome').value = nome;
            document.getElementById('conta-ativo').value = ativo ? '1' : '0';
            document.getElementById('conta-method').value = 'PUT';
            document.getElementById('modal-conta-title').textContent = 'Editar Conta';
            document.getElementById('form-conta').action = '/contas/' + id;
            openModal('modal-conta');
        }

        function loadSubcategoriasForConta() {
            const categoriaId = document.getElementById('conta-categoria_id').value;
            const subcategoriaSelect = document.getElementById('conta-subcategoria_id');
            
            if (!categoriaId) {
                subcategoriaSelect.innerHTML = '<option value="">Selecione uma subcategoria</option>';
                return;
            }

            fetch('/financeiro/api/subcategorias/' + categoriaId)
                .then(r => r.json())
                .then(data => {
                    subcategoriaSelect.innerHTML = '<option value="">Selecione uma subcategoria</option>';
                    data.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.textContent = sub.nome;
                        subcategoriaSelect.appendChild(option);
                    });
                });
        }

        document.getElementById('form-categoria').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const method = document.getElementById('categoria-method').value;
            const url = form.action;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(new FormData(form)) + '&_method=' + method
            }).then(r => {
                if (r.ok) {
                    window.location.reload();
                } else {
                    alert('Erro ao salvar categoria');
                }
            });
        });

        document.getElementById('form-subcategoria').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const method = document.getElementById('subcategoria-method').value;
            const url = form.action;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(new FormData(form)) + '&_method=' + method
            }).then(r => {
                if (r.ok) {
                    window.location.reload();
                } else {
                    alert('Erro ao salvar subcategoria');
                }
            });
        });

        document.getElementById('form-conta').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const method = document.getElementById('conta-method').value;
            const url = form.action;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(new FormData(form)) + '&_method=' + method
            }).then(r => {
                if (r.ok) {
                    window.location.reload();
                } else {
                    alert('Erro ao salvar conta');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
