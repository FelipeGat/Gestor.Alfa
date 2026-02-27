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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-kpi-card title="Total de Usuários" :value="$totalUsuarios" color="blue" />
                <x-kpi-card title="Ativos" :value="$usuariosAtivos" color="green" />
                <x-kpi-card title="Primeiro Acesso" :value="$usuariosInativos" color="red" />
            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start">
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
                    <x-table-cell>{{ $row->email }}</x-table-cell>
                    <x-table-cell>{{ ucfirst($row->tipo) }}</x-table-cell>
                    <x-table-cell :nowrap="true">
                        @if(!$row->primeiro_acesso)
                            <x-badge type="success" :icon="true">Ativo</x-badge>
                        @else
                            <x-badge type="warning" :icon="true">Primeiro Acesso</x-badge>
                        @endif
                    </x-table-cell>
                    <td class="px-4 py-3 text-center">
                        <div class="flex gap-1 items-center justify-center">
                            <a href="{{ route('usuarios.edit', $row) }}" class="btn btn-sm btn-edit" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Editar">
                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </a>

                            <form action="{{ route('usuarios.destroy', $row) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este usuário?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-delete" style="padding: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;" title="Excluir">
                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </x-table>

            {{-- PAGINAÇÃO --}}
            <x-pagination :paginator="$usuarios" label="usuários" />

        </div>
    </div>
</x-app-layout>
