<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Equipamentos']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FILTROS --}}
            <x-filter :action="route('admin.equipamentos.index')" :show-clear-button="true">
                <x-filter-field name="search" label="Pesquisar Equipamento" placeholder="Nome, modelo, fabricante ou nº série" colSpan="lg:col-span-6" />
                <x-filter-field name="cliente_id" label="Cliente" type="select" placeholder="Todos" colSpan="lg:col-span-3">
                    @foreach(\App\Models\Cliente::where('ativo', true)->orderBy('nome')->get() as $cliente)
                        <option value="{{ $cliente->id }}" @selected(request('cliente_id') == $cliente->id)>
                            {{ $cliente->nome_exibicao }}
                        </option>
                    @endforeach
                </x-filter-field>
                <x-filter-field name="status" label="Status" type="select" placeholder="Todos" colSpan="lg:col-span-3">
                    <option value="ativo" @selected(request('status')=='ativo')>Ativo</option>
                    <option value="inativo" @selected(request('status')=='inativo')>Inativo</option>
                </x-filter-field>
            </x-filter>

            {{-- RESUMO --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4 border-blue-500 shadow-sm">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Equipamentos</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalEquipamentos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4 border-green-500 shadow-sm">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $equipamentosAtivos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4 border-red-500 shadow-sm">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Inativos</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $equipamentosInativos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4 border-purple-500 shadow-sm">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">QR Code</p>
                    <p class="text-sm text-gray-500 mt-2">Gere etiquetas para identificação</p>
                </div>
            </div>

            @if(auth()->user()->canPermissao('equipamentos', 'incluir'))
            <div class="flex justify-start gap-3">
                <x-button href="{{ route('admin.equipamentos.create') }}" variant="success" size="sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Novo Equipamento
                </x-button>
                <x-button href="{{ route('admin.equipamentos.setores.index') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Setores
                </x-button>
                <x-button href="{{ route('admin.equipamentos.responsaveis.index') }}" variant="secondary" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Responsáveis
                </x-button>
            </div>
            @endif

            {{-- TABELA --}}
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

            @if($equipamentos->count())
            <x-table :columns="$columns" :data="$equipamentos" emptyMessage="Nenhum equipamento encontrado">
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">Nenhum equipamento encontrado.</p>
            </div>
            @endif

            {{-- PAGINAÇÃO --}}
            @if($equipamentos->hasPages())
            <div class="mt-4">
                {{ $equipamentos->links() }}
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
