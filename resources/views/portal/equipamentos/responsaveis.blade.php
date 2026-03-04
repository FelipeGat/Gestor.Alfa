<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <div class="portal-wrapper">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">Responsáveis</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $cliente->nome_exibicao }}</p>
            </div>
            <a href="{{ route('portal.equipamentos.index') }}"
                class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Voltar para Dashboard Ativos Técnicos</span>
            </a>
        </div>

        <div class="portal-table-card p-4 mb-4">
            <h3 class="portal-table-title mb-3">Cadastrar Responsável</h3>
            <form action="{{ route('portal.equipamentos.responsaveis.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <input type="text" name="nome" required placeholder="Nome" class="rounded border-gray-300">
                <input type="text" name="cargo" placeholder="Cargo" class="rounded border-gray-300">
                <input type="text" name="telefone" placeholder="Telefone" class="rounded border-gray-300">
                <input type="email" name="email" placeholder="E-mail" class="rounded border-gray-300">
                <div class="md:col-span-4">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all">Salvar Responsável</button>
                </div>
            </form>
        </div>

        @if($responsaveis->count())
        <div class="portal-table-card overflow-hidden">
            <div class="portal-table-header">
                <h3 class="portal-table-title">Portal > Responsáveis <span class="text-sm font-normal text-gray-600">({{ $responsaveis->total() }})</span></h3>
            </div>
            @include('portal.partials.live-table-filter', [
                'inputId' => 'filtroResponsaveis',
                'tableId' => 'tabelaResponsaveis',
                'placeholder' => 'Digite nome, cargo, telefone ou e-mail',
                'mode' => 'server',
                'action' => route('portal.equipamentos.responsaveis'),
                'queryParam' => 'q'
            ])
            <div class="portal-table-wrapper">
                <table id="tabelaResponsaveis" class="portal-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Cargo</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Total de ativos</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($responsaveis as $responsavel)
                        <tr>
                            <td>{{ $responsavel->nome }}</td>
                            <td>{{ $responsavel->cargo ?: '-' }}</td>
                            <td>{{ $responsavel->telefone ?: '-' }}</td>
                            <td>{{ $responsavel->email ?: '-' }}</td>
                            <td>{{ $responsavel->equipamentos_count }}</td>
                            <td>
                                <a href="{{ route('portal.equipamentos.responsaveis.edit', $responsavel) }}" class="portal-btn portal-btn--primary">Editar</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $responsaveis->links() }}
            </div>
        </div>
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum responsável cadastrado.</p>
            <p class="portal-empty-state-text">
                Os responsáveis aparecerão aqui quando forem cadastrados.
            </p>
        </div>
        @endif

    </div>
</x-app-layout>
