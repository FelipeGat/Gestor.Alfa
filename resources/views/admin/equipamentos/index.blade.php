<x-app-layout>
    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Equipamentos']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros --}}
            <div class="bg-white rounded-lg p-4" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <form action="{{ route('admin.equipamentos.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <x-input-label value="Pesquisar" />
                            <x-text-input type="text" name="search" :value="request('search')" placeholder="Nome, modelo, fabricante ou nº série" class="w-full" />
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

            {{-- Resumo --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
            </div>

            {{-- Botão Adicionar --}}
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

            {{-- Tabela --}}
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
                    <x-table-cell>
                        <x-status-badge :ativo="$equipamento->ativo" />
                    </x-table-cell>
                    <x-table-cell>
                        <x-actions
                            :edit-url="route('admin.equipamentos.edit', $equipamento->id)"
                            :delete-url="route('admin.equipamentos.destroy', $equipamento->id)"
                            :show-view="false"
                            confirm-delete-message="Tem certeza que deseja excluir este equipamento?"
                        />
                    </x-table-cell>
                </tr>
                @endforeach
            </x-table>
            @else
            <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Nenhum equipamento encontrado</h3>
                <p class="text-sm text-gray-500 mt-2">Cadastre um novo equipamento para começar</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
