<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Clientes']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FILTROS --}}
            <x-filter :action="route('clientes.index')" :show-clear-button="true">
                <x-filter-field name="search" label="Pesquisar Cliente" placeholder="Nome, CPF/CNPJ ou E-mail" colSpan="lg:col-span-6" />
                <x-filter-field name="status" label="Status" type="select" placeholder="Todos" colSpan="lg:col-span-3">
                    <option value="ativo" @selected(request('status')=='ativo')>Ativo</option>
                    <option value="inativo" @selected(request('status')=='inativo')>Inativo</option>
                </x-filter-field>
            </x-filter>

            {{-- RESUMO --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #3b82f6; border-top: 1px solid #3b82f6; border-right: 1px solid #3b82f6; border-bottom: 1px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total de Clientes</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalClientes }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #22c55e; border-top: 1px solid #22c55e; border-right: 1px solid #22c55e; border-bottom: 1px solid #22c55e; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Ativos</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $clientesAtivos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #ef4444; border-top: 1px solid #ef4444; border-right: 1px solid #ef4444; border-bottom: 1px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Inativos</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $clientesInativos }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg border-l-4" style="border-color: #eab308; border-top: 1px solid #eab308; border-right: 1px solid #eab308; border-bottom: 1px solid #eab308; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <p class="text-xs text-gray-600 uppercase tracking-wide">Receita Mensal</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">R$ {{ number_format($receitaMensal, 2, ',', '.') }}</p>
                </div>
            </div>

            @if(auth()->user()->canPermissao('clientes', 'incluir'))
            <div class="flex justify-start">
                <x-button href="{{ route('clientes.create') }}" variant="success" size="sm" class="min-w-[130px]">
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
                    ['label' => 'ID'],
                    ['label' => 'CPF/CNPJ'],
                    ['label' => 'Nome'],
                    ['label' => 'Telefone'],
                    ['label' => 'Status'],
                    ['label' => 'Ações'],
                ];
            @endphp

            @if($clientes->count())
            <x-table :columns="$columns" :data="$clientes" emptyMessage="Nenhum cliente encontrado">
                @foreach($clientes as $cliente)
                @php
                    $email = $cliente->emails->where('principal', true)->first();
                    $tel = $cliente->telefones->where('principal', true)->first();
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <x-table-cell :nowrap="true">{{ $cliente->id }}</x-table-cell>
                    <x-table-cell type="muted" :nowrap="true">{{ \App\Helpers\FormatHelper::cpfCnpj($cliente->cpf_cnpj) }}</x-table-cell>
                    <x-table-cell>{{ $cliente->nome }}</x-table-cell>
                    <x-table-cell :nowrap="true">
                        @if($tel)
                            <a href="tel:{{ preg_replace('/\D/', '', $tel->valor) }}" class="hover:text-blue-600 hover:underline">
                                {{ $tel->valor }}
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell align="left">
                        <x-badge type="{{ $cliente->ativo ? 'success' : 'danger' }}" :icon="true">
                            {{ $cliente->ativo ? 'Ativo' : 'Inativo' }}
                        </x-badge>
                    </x-table-cell>
                    <x-table-cell>
                        <x-actions 
                            :edit-url="route('clientes.edit', $cliente)" 
                            :delete-url="route('clientes.destroy', $cliente)"
                            :show-view="false"
                            confirm-delete-message="Tem certeza que deseja excluir este cliente?"
                        />
                    </x-table-cell>
                </tr>
                @endforeach
            </x-table>

            {{-- PAGINAÇÃO --}}
            <x-pagination :paginator="$clientes" label="clientes" />
            @else
            <x-table :columns="$columns" :data="$clientes" emptyMessage="Nenhum cliente encontrado" />
            @endif

        </div>
    </div>
</x-app-layout>
