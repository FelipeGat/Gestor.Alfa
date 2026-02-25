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
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #3b82f6; border-top: 1px solid #3b82f6; border-right: 1px solid #3b82f6; border-bottom: 1px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Empresas</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totais['total'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #22c55e; border-top: 1px solid #22c55e; border-right: 1px solid #22c55e; border-bottom: 1px solid #22c55e; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Empresas Ativas</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $totais['ativos'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #ef4444; border-top: 1px solid #ef4444; border-right: 1px solid #ef4444; border-bottom: 1px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Empresas Inativas</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $totais['inativos'] }}</p>
                </div>
            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start">
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
                ];
            @endphp

            @if($empresas->count())
            <x-table :columns="$columns" :data="$empresas" :actions="false">
                @foreach($empresas as $empresa)
                <tr class="hover:bg-gray-50 transition">
                    <x-table-cell>{{ $empresa->id }}</x-table-cell>
                    <x-table-cell>
                        {{ $empresa->razao_social }}
                        @if($empresa->nome_fantasia)
                        <div class="text-xs text-gray-500"> {{ $empresa->nome_fantasia }}</div>
                        @endif
                    </x-table-cell>
                    <x-table-cell type="muted">{{ $empresa->cnpj }}</x-table-cell>
                    <x-table-cell>
                        <x-badge type="{{ $empresa->ativo ? 'success' : 'danger' }}" :icon="true">
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

            @if($empresas->hasPages())
            <div class="bg-white rounded-lg p-4 flex justify-between items-center" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="text-sm text-gray-500">
                    Mostrando {{ $empresas->count() }} de {{ $empresas->total() }} empresas
                </div>
                <div class="flex gap-2">
                    @if($empresas->onFirstPage())
                        <span class="px-3 py-1 text-gray-400">Anterior</span>
                    @else
                        <a href="{{ $empresas->previousPageUrl() }}" class="px-3 py-1 text-[#3f9cae]">Anterior</a>
                    @endif
                    @if($empresas->hasMorePages())
                        <a href="{{ $empresas->nextPageUrl() }}" class="px-3 py-1 text-[#3f9cae]">Próximo</a>
                    @else
                        <span class="px-3 py-1 text-gray-400">Próximo</span>
                    @endif
                </div>
            </div>
            @endif
            @else
            <div class="bg-white rounded-lg p-12 text-center" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <h3 class="text-lg font-medium text-gray-900">Nenhuma empresa cadastrada</h3>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
