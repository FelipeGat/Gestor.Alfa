<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Equipamentos', 'url' => route('admin.equipamentos.index')],
            ['label' => 'Responsáveis']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FILTROS --}}
            <x-filter :action="route('admin.equipamentos.responsaveis.index')" :show-clear-button="true">
                <x-filter-field name="search" label="Pesquisar Responsável" placeholder="Nome ou cargo" colSpan="lg:col-span-6" />
                <x-filter-field name="cliente_id" label="Cliente" type="select" placeholder="Todos" colSpan="lg:col-span-6">
                    @foreach(\App\Models\Cliente::where('ativo', true)->orderBy('nome')->get() as $cliente)
                        <option value="{{ $cliente->id }}" @selected(request('cliente_id') == $cliente->id)>
                            {{ $cliente->nome_exibicao }}
                        </option>
                    @endforeach
                </x-filter-field>
            </x-filter>

            {{-- RESUMO --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4 border-blue-500 shadow-sm">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Responsáveis</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalResponsaveis }}</p>
                </div>
            </div>

            @if(auth()->user()->canPermissao('equipamentos', 'incluir'))
            <div class="flex justify-start gap-3">
                <x-button href="{{ route('admin.equipamentos.responsaveis.create') }}" variant="success" size="sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Novo Responsável
                </x-button>
                <a href="{{ route('admin.equipamentos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
            @endif

            {{-- TABELA --}}
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

            @if($responsaveis->count())
            <x-table :columns="$columns" :data="$responsaveis" emptyMessage="Nenhum responsável encontrado">
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
                                <a href="{{ route('admin.equipamentos.responsaveis.show', $responsavel->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            @endif
                            @if(auth()->user()->canPermissao('equipamentos', 'alterar'))
                                <a href="{{ route('admin.equipamentos.responsaveis.edit', $responsavel->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            @endif
                            @if(auth()->user()->canPermissao('equipamentos', 'excluir'))
                                <form action="{{ route('admin.equipamentos.responsaveis.destroy', $responsavel->id) }}" 
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">Nenhum responsável encontrado.</p>
            </div>
            @endif

            {{-- PAGINAÇÃO --}}
            @if($responsaveis->hasPages())
            <div class="mt-4">
                {{ $responsaveis->links() }}
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
