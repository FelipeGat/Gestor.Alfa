<x-app-layout>

    @push('styles')
    @vite('resources/css/contas-bancarias/contas-bancarias.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏦 Contas Bancárias
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- FILTROS --}}
            <form method="GET" class="filters-card">
                <div class="filters-grid">

                    <div class="filter-group">
                        <label>Pesquisar Conta</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome da conta">
                    </div>

                    <div class="filter-group">
                        <label>Empresa</label>
                        <select name="empresa_id">
                            <option value="">Todas</option>
                            @foreach($empresas as $empresa)
                            <option value="{{ $empresa->id }}" @selected(request('empresa_id')==$empresa->id)>
                                {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Tipo</label>
                        <select name="tipo">
                            <option value="">Todos</option>
                            <option value="banco">Banco</option>
                            <option value="pix">Pix</option>
                            <option value="caixa">Caixa</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">Todos</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>

                <div class="filters-actions">
                    <button class="btn btn-primary">Filtrar</button>

                    <a href="{{ route('financeiro.contas-financeiras.create') }}"
                        class="btn btn-success">
                        + Nova Conta
                    </a>
                </div>
            </form>

            {{-- TABELA --}}
            @if($contas->count())
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Conta</th>
                                <th>Tipo</th>
                                <th>Saldo</th>
                                <th>Disponível</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contas as $conta)
                            <tr>
                                <td>
                                    <span class="table-number">{{ $conta->id }}</span>
                                </td>
                                <td>{{ $conta->empresa->nome_fantasia ?? '—' }}</td>
                                <td class="font-semibold">{{ $conta->nome }}</td>
                                <td>
                                    <span class="table-badge badge-{{ $conta->tipo }}">
                                        {{ strtoupper($conta->tipo) }}
                                    </span>
                                </td>
                                <td>R$ {{ number_format($conta->saldo, 2, ',', '.') }}</td>
                                <td class="font-semibold">
                                    R$ {{ number_format($conta->saldo_total, 2, ',', '.') }}
                                </td>
                                <td>
                                    <span class="table-badge {{ $conta->ativo ? 'badge-baixa' : 'badge-alta' }}">
                                        {{ $conta->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('financeiro.contas-financeiras.edit', $conta) }}"
                                            class="btn btn-sm btn-edit">Editar</a>

                                        <form method="POST"
                                            action="{{ route('financeiro.contas-financeiras.destroy', $conta) }}"
                                            onsubmit="return confirm('Deseja remover esta conta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-delete">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $contas->links() }}
            @else
            <div class="empty-state">
                <h3 class="empty-state-title">Nenhuma conta cadastrada</h3>
            </div>
            @endif

        </div>
    </div>

</x-app-layout>