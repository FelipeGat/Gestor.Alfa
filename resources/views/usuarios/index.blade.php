<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    <style>
        .form-section {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        input[type="text"],
        input[type="email"],
        select {
            font-family: Inter, sans-serif !important;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: #3f9cae !important;
            box-shadow: 0 0 0 1px rgba(63, 156, 174, 0.2) !important;
        }
        .pagination-link {
            border-radius: 9999px !important;
            min-width: 40px;
            text-align: center;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Usuários']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FILTROS --}}
            <x-filter :action="route('usuarios.index')" :show-clear-button="true">
                <x-filter-field name="search" label="Pesquisar" placeholder="Nome ou E-mail" colSpan="md:col-span-6" />
                <x-filter-field name="status" label="Status" type="select" placeholder="Todos" colSpan="md:col-span-3">
                    <option value="ativo" @selected(request('status')=='ativo')>Ativo</option>
                    <option value="primeiro acesso" @selected(request('status')=='primeiro acesso')>Primeiro Acesso</option>
                </x-filter-field>
            </x-filter>

            {{-- RESUMO --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <x-kpi-card title="Total de Usuários" :value="$totalUsuarios" color="blue" />
                <x-kpi-card title="Ativos" :value="$usuariosAtivos" color="green" />
                <x-kpi-card title="Primeiro Acesso" :value="$usuariosInativos" color="red" />
            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start" style="margin-bottom: -1rem;">
                <x-button href="{{ route('usuarios.create') }}" variant="success" size="sm" class="min-w-[130px]">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </x-button>
            </div>
            @endif

            {{-- TABELA --}}
            @php
                $columns = [
                    ['label' => 'Nome'],
                    ['label' => 'Email'],
                    ['label' => 'Tipo'],
                    ['label' => 'Status'],
                ];
            @endphp

            <x-table :columns="$columns" :data="$usuarios" :actions="true" emptyMessage="Nenhum usuário encontrado">
                @foreach($usuarios as $row)
                <tr class="hover:bg-gray-50 transition">
                    <x-table-cell>{{ $row->name }}</x-table-cell>
                    <x-table-cell color="gray-600">{{ $row->email }}</x-table-cell>
                    <x-table-cell color="gray-600">{{ ucfirst($row->tipo) }}</x-table-cell>
                    <x-table-cell :nowrap="true">
                        @if(!$row->primeiro_acesso)
                            <x-badge type="success" :icon="true">Ativo</x-badge>
                        @else
                            <x-badge type="warning" :icon="true">Primeiro Acesso</x-badge>
                        @endif
                    </x-table-cell>
                    <td class="px-4 py-3">
                        <x-actions :edit-url="route('usuarios.edit', $row)" :delete-url="route('usuarios.destroy', $row)" :show-view="false" />
                    </td>
                </tr>
                @endforeach
            </x-table>

            {{-- PAGINAÇÃO --}}
            <x-pagination :paginator="$usuarios" label="usuários" />

        </div>
    </div>
</x-app-layout>
