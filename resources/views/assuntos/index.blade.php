<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Assuntos']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-filter :action="route('assuntos.index')" :show-clear-button="true" class="mb-4">
                <x-filter-field name="search" label="Pesquisar Assunto" placeholder="Nome do assunto" colSpan="lg:col-span-4" />
                
                <x-filter-field name="empresa_id" label="Empresa" type="select" placeholder="Todas" colSpan="lg:col-span-3">
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" @selected(request('empresa_id') == $empresa->id)>
                            {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                        </option>
                    @endforeach
                </x-filter-field>
                
                <x-filter-field name="status" label="Status" type="select" placeholder="Todos" colSpan="lg:col-span-2">
                    <option value="ativo" @selected(request('status') == 'ativo')>Ativo</option>
                    <option value="inativo" @selected(request('status') == 'inativo')>Inativo</option>
                </x-filter-field>
            </x-filter>

            @if(auth()->user()->canPermissao('assuntos', 'incluir'))
            <div class="flex justify-start">
                <x-button href="{{ route('assuntos.create') }}" variant="success" size="sm" class="min-w-[130px]">
                    <x-slot name="iconLeft">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </x-slot>
                    Adicionar
                </x-button>
            </div>
            @endif

            @php
                $columns = [
                    ['label' => 'ID'],
                    ['label' => 'Empresa'],
                    ['label' => 'Assunto'],
                    ['label' => 'Tipo'],
                    ['label' => 'Categoria'],
                    ['label' => 'Status'],
                    ['label' => 'Ações'],
                ];
            @endphp

            @if($assuntos->count())
                <x-table :columns="$columns" :data="$assuntos" :actions="false">
                    @foreach($assuntos as $assunto)
                    <tr class="hover:bg-gray-50 transition">
                        <x-table-cell>{{ $assunto->id }}</x-table-cell>
                        <x-table-cell>{{ $assunto->empresa->nome_fantasia ?? '—' }}</x-table-cell>
                        <x-table-cell>{{ $assunto->nome }}</x-table-cell>
                        <x-table-cell>{{ $assunto->tipo }}</x-table-cell>
                        <x-table-cell>
                            <div class="font-medium">{{ $assunto->categoria }}</div>
                            <div class="text-xs text-gray-500">{{ $assunto->subcategoria }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            <x-status-badge :ativo="$assunto->ativo" />
                        </x-table-cell>
                        <x-table-cell>
                            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('assuntos', 'incluir'))
                                <x-actions 
                                    :edit-url="route('assuntos.edit', $assunto)" 
                                    :delete-url="route('assuntos.destroy', $assunto)"
                                    :show-view="false"
                                    confirm-delete-message="Tem certeza que deseja excluir este Assunto?"
                                />
                            @endif
                        </x-table-cell>
                    </tr>
                    @endforeach
                </x-table>

                <x-pagination :paginator="$assuntos" label="assuntos" />
            @else
                <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-medium text-gray-900">Nenhum assunto encontrado</h3>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
