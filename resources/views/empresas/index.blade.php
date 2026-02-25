<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Empresas']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <x-filter :action="route('empresas.index')" :show-clear-button="true" class="mb-4">
                <x-filter-field name="search" label="Pesquisar Empresa" placeholder="Razão social ou nome fantasia" colSpan="lg:col-span-6" />
                <x-filter-field name="status" label="Status" type="select" placeholder="Todas" colSpan="lg:col-span-3">
                    <option value="ativo" @selected(request('status')=='ativo')>Ativa</option>
                    <option value="inativo" @selected(request('status')=='inativo')>Inativa</option>
                </x-filter-field>
            </x-filter>

            {{-- ================= RESUMO (KPIs) ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-kpi-card title="Total de Empresas" :value="$totais['total']" color="blue" />
                <x-kpi-card title="Empresas Ativas" :value="$totais['ativos']" color="green" />
                <x-kpi-card title="Empresas Inativas" :value="$totais['inativos']" color="red" />
            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start mb-4">
                <x-button href="{{ route('empresas.create') }}" variant="success" size="sm" class="min-w-[130px]">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </x-button>
            </div>
            @endif

            {{-- ================= TABELA ================= --}}
            @php
                $columns = [
                    ['label' => 'ID'],
                    ['label' => 'Empresa'],
                    ['label' => 'CNPJ'],
                    ['label' => 'Status'],
                    ['label' => 'Ações'],
                ];
            @endphp

            @if($empresas->count())
            <x-table :columns="$columns" :data="$empresas" :actions="false" emptyMessage="Nenhuma empresa cadastrada" class="mb-4">
                @foreach($empresas as $empresa)
                <tr class="hover:bg-gray-50 transition">
                    <x-table-cell>{{ $empresa->id }}</x-table-cell>
                    <x-table-cell>
                        {{ $empresa->razao_social }}
                        @if($empresa->nome_fantasia)
                        <div class="text-xs text-gray-500">{{ $empresa->nome_fantasia }}</div>
                        @endif
                    </x-table-cell>
                    <x-table-cell type="muted">{{ $empresa->cnpj }}</x-table-cell>
                    <x-table-cell>
                        <x-badge type="{{ $empresa->ativo ? 'success' : 'danger' }}">
                            {{ $empresa->ativo ? 'Ativa' : 'Inativa' }}
                        </x-badge>
                    </x-table-cell>
                    <x-table-cell>
                        <x-actions 
                            :edit-url="route('empresas.edit', $empresa)" 
                            :delete-url="route('empresas.destroy', $empresa)"
                            :show-view="false"
                            confirm-delete-message="Deseja excluir esta Empresa?"
                        />
                    </x-table-cell>
                </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$empresas" label="empresas" />
            @else
            <x-card class="mb-4">
                <div class="p-12 text-center">
                    <h3 class="text-lg font-medium text-gray-900">Nenhuma empresa cadastrada</h3>
                </div>
            </x-card>
            @endif

        </div>
    </div>
</x-app-layout>
