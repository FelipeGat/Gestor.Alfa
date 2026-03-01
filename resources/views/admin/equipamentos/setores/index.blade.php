<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Setores']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FILTROS --}}
            <x-filter :action="route('admin.equipamentos.setores.index')" :show-clear-button="true">
                <x-filter-field name="search" label="Pesquisar Setor" placeholder="Nome do setor" colSpan="lg:col-span-6" />
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
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Setores</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalSetores }}</p>
                </div>
            </div>

            @if(auth()->user()->canPermissao('equipamentos', 'incluir'))
            <div class="flex justify-start gap-3">
                <x-button href="{{ route('admin.setores.create') }}" variant="success" size="sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Novo Setor
                </x-button>
            </div>
            @endif

            {{-- TABELA --}}
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

            @if($setores->count())
            <x-table :columns="$columns" :data="$setores" emptyMessage="Nenhum setor encontrado">
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">Nenhum setor encontrado.</p>
            </div>
            @endif

            {{-- PAGINAÇÃO --}}
            @if($setores->hasPages())
            <div class="mt-4">
                {{ $setores->links() }}
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
