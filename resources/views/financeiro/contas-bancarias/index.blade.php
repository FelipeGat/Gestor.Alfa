<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro', 'url' => route('financeiro.home')],
            ['label' => 'Bancos']
        ]" />
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- FILTROS --}}
            <x-filter :action="route('financeiro.contas-financeiras.index')" :show-clear-button="true">
                <x-filter-field name="search" label="Pesquisar Conta" placeholder="Nome da conta" colSpan="md:col-span-4" />
                <x-filter-field name="empresa_id" label="Empresa" type="select" placeholder="Todas" colSpan="md:col-span-3">
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" @selected(request('empresa_id')==$empresa->id)>
                            {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                        </option>
                    @endforeach
                </x-filter-field>
                <x-filter-field name="tipo" label="Tipo" type="select" placeholder="Todos" colSpan="md:col-span-3">
                    <option value="banco">Banco</option>
                    <option value="pix">Pix</option>
                    <option value="caixa">Caixa</option>
                    <option value="credito">Crédito</option>
                </x-filter-field>
                <x-filter-field name="status" label="Status" type="select" placeholder="Todos" colSpan="md:col-span-2">
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </x-filter-field>
            </x-filter>

            {{-- RESUMO --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 mt-6">
                <x-kpi-card title="Total de Contas" :value="$totalContas" color="blue" />
                <x-kpi-card title="Contas Ativas" :value="$contasAtivas" color="green" />
                <x-kpi-card title="Contas Inativas" :value="$contasInativas" color="red" />
                <x-kpi-card title="Saldo Total" :value="'R$ ' . number_format($contas->sum('saldo'), 2, ',', '.')" color="yellow" />
            </div>

            {{-- BOTÃO ADICIONAR --}}
            <div class="flex justify-start mb-4">
                <x-button href="{{ route('financeiro.contas-financeiras.create') }}" variant="success" size="sm" class="min-w-[130px]">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Adicionar
                </x-button>
            </div>

            {{-- TABELA --}}
            @php
                $columns = [
                    ['label' => 'ID'],
                    ['label' => 'Empresa'],
                    ['label' => 'Conta'],
                    ['label' => 'Tipo'],
                    ['label' => 'Saldo'],
                    ['label' => 'Disponível'],
                    ['label' => 'Status'],
                ];
            @endphp

            <x-table :columns="$columns" :data="$contas" :actions="false" emptyMessage="Nenhuma conta cadastrada">
                @foreach($contas as $conta)
                @php
                    $onclickAdjust = "abrirModalAjusteUnificado({$conta->id}, '" . addslashes($conta->nome) . "', {$conta->saldo})";
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <x-table-cell :nowrap="true">{{ $conta->id }}</x-table-cell>
                    <x-table-cell>{{ $conta->empresa->nome_fantasia ?? '—' }}</x-table-cell>
                    <x-table-cell>{{ $conta->nome }}</x-table-cell>
                    <x-table-cell>
                        <x-badge type="{{ $conta->tipo === 'credito' ? 'primary' : 'default' }}" :icon="true">
                            {{ $conta->tipo }}
                        </x-badge>
                    </x-table-cell>
                    <x-table-cell>R$ {{ number_format($conta->saldo, 2, ',', '.') }}</x-table-cell>
                    <x-table-cell>R$ {{ number_format($conta->saldo_total, 2, ',', '.') }}</x-table-cell>
                    <x-table-cell>
                        <x-badge type="{{ $conta->ativo ? 'success' : 'danger' }}" :icon="true">
                            {{ $conta->ativo ? 'Ativo' : 'Inativo' }}
                        </x-badge>
                    </x-table-cell>
                    <x-table-cell>
                        <x-actions 
                            :edit-url="route('financeiro.contas-financeiras.edit', $conta)" 
                            :delete-url="route('financeiro.contas-financeiras.destroy', $conta)"
                            :show-view="false"
                            :show-adjust="true"
                            :onclick-adjust="$onclickAdjust"
                            confirm-delete-message="Deseja remover esta conta?"
                        />
                    </x-table-cell>
                </tr>
                @endforeach
            </x-table>

            {{-- PAGINAÇÃO --}}
            <x-pagination :paginator="$contas" label="contas" />

        </div>
    </div>

    {{-- Modal Ajuste --}}
    @include('financeiro.partials.modal-ajuste-unificado')

</x-app-layout>
