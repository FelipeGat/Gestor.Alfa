<x-app-layout>
    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Ativos Técnicos']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <x-filter :action="route('admin.equipamentos.index')" :show-clear-button="true" class="mb-4">
                <x-filter-field name="search" label="Pesquisar Ativo Técnico" placeholder="Nome, modelo, fabricante ou nº série" colSpan="lg:col-span-6" />
                <x-filter-field name="cliente_id" label="Cliente" type="select" placeholder="Todos" colSpan="lg:col-span-3">
                    <option value="">Todos</option>
                    @foreach(\App\Models\Cliente::where('ativo', true)->orderBy('nome')->get() as $cliente)
                        <option value="{{ $cliente->id }}" @selected(request('cliente_id') == $cliente->id)>
                            {{ $cliente->nome_exibicao }}
                        </option>
                    @endforeach
                </x-filter-field>
                <x-filter-field name="status" label="Status" type="select" placeholder="Todos" colSpan="lg:col-span-3">
                    <option value="">Todos</option>
                    <option value="ativo" @selected(request('status')=='ativo')>Ativo</option>
                    <option value="inativo" @selected(request('status')=='inativo')>Inativo</option>
                </x-filter-field>
            </x-filter>

            {{-- ================= RESUMO (KPIs) ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #3b82f6; border-top: 1px solid #3b82f6; border-right: 1px solid #3b82f6; border-bottom: 1px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Ativos Técnicos</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalAtivosTecnicos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #22c55e; border-top: 1px solid #22c55e; border-right: 1px solid #22c55e; border-bottom: 1px solid #22c55e; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos Técnicos Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $ativosTecnicosAtivos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #ef4444; border-top: 1px solid #ef4444; border-right: 1px solid #ef4444; border-bottom: 1px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos Técnicos Inativos</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $ativosTecnicosInativos }}</p>
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
            @if($ativosTecnicos->count())
            @php
                $columns = [
                    ['label' => 'ID'],
                    ['label' => 'Cliente'],
                    ['label' => 'Ativo Técnico'],
                    ['label' => 'Modelo'],
                    ['label' => 'Setor'],
                    ['label' => 'Responsável'],
                    ['label' => 'Status'],
                    ['label' => 'Ações'],
                ];
            @endphp
            <x-table :columns="$columns" :data="$ativosTecnicos" :actions="false">
                @foreach($ativosTecnicos as $equipamento)
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
                            confirm-delete-message="Tem certeza que deseja excluir este ativo técnico?"
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
                <h3 class="text-lg font-medium text-gray-900 mt-4">Nenhum ativo técnico encontrado</h3>
                <p class="text-sm text-gray-500 mt-2">Cadastre um novo ativo técnico para começar</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
