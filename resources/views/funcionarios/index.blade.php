<x-app-layout>

    @php
        $routePrefix = request()->routeIs('rh.*') ? 'rh.funcionarios' : 'funcionarios';
        $isRhRoute = request()->routeIs('rh.*');
        $breadcrumbBase = $isRhRoute
            ? ['label' => 'RH', 'url' => route('rh.dashboard')]
            : ['label' => 'Cadastros', 'url' => route('cadastros.index')];
    @endphp

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            $breadcrumbBase,
            ['label' => 'Funcionários']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ================= FILTROS ================= --}}
            <x-filter :action="route($routePrefix . '.index')" :show-clear-button="true">
                <x-filter-field name="search" label="Pesquisar Funcionário" placeholder="Nome do funcionário" colSpan="lg:col-span-6" />
                <x-filter-field name="status" label="Status" type="select" placeholder="Todos" colSpan="lg:col-span-3">
                    <option value="ativo" @selected(request('status')=='ativo')>Ativo</option>
                    <option value="inativo" @selected(request('status')=='inativo')>Inativo</option>
                </x-filter-field>
            </x-filter>

            {{-- ================= RESUMO ================= --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #3b82f6; border-top: 1px solid #3b82f6; border-right: 1px solid #3b82f6; border-bottom: 1px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Funcionários</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totais['total'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #22c55e; border-top: 1px solid #22c55e; border-right: 1px solid #22c55e; border-bottom: 1px solid #22c55e; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $totais['ativos'] }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #ef4444; border-top: 1px solid #ef4444; border-right: 1px solid #ef4444; border-bottom: 1px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Inativos</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $totais['inativos'] }}</p>
                </div>
            </div>

            @if(auth()->user()->isAdminPanel() || auth()->user()->canPermissao('funcionarios','incluir'))
            <div class="flex justify-start">
                <x-button href="{{ route($routePrefix . '.create') }}" variant="success" size="sm" class="min-w-[130px]">
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
                    ['label' => 'Nome'],
                    ['label' => 'Email'],
                    ['label' => 'Status'],
                    ['label' => 'Ações'],
                ];
            @endphp

            <x-table :columns="$columns" :data="$funcionarios" emptyMessage="Nenhum funcionário cadastrado">
                @foreach($funcionarios as $funcionario)
                <tr class="hover:bg-gray-50 transition">
                    <x-table-cell>{{ $funcionario->id }}</x-table-cell>
                    <x-table-cell>{{ $funcionario->nome }}</x-table-cell>
                    <x-table-cell>{{ $funcionario->user->email ?? '—' }}</x-table-cell>
                    <x-table-cell align="left">
                        <x-badge type="{{ $funcionario->ativo ? 'success' : 'danger' }}" :icon="true">
                            {{ $funcionario->ativo ? 'Ativo' : 'Inativo' }}
                        </x-badge>
                    </x-table-cell>
                    <x-table-cell>
                        <x-actions
                            :edit-url="route($routePrefix . '.edit', $funcionario)"
                            :delete-url="route($routePrefix . '.destroy', $funcionario)"
                            :show-view="false"
                            confirm-delete-message="Deseja excluir este Funcionário?"
                        />
                    </x-table-cell>
                </tr>
                @endforeach
            </x-table>

            {{-- PAGINAÇÃO --}}
            <x-pagination :paginator="$funcionarios" label="funcionários" />

        </div>
    </div>
</x-app-layout>
