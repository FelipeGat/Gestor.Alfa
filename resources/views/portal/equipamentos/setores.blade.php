<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <div class="portal-wrapper">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">Setores</h2>
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

        @if($setores->count())
        <div class="portal-table-card overflow-hidden">
            <div class="portal-table-header">
                <h3 class="portal-table-title">Portal > Setores <span class="text-sm font-normal text-gray-600">({{ $setores->total() }})</span></h3>
            </div>
            <div class="portal-table-wrapper">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Setor</th>
                            <th>Descrição</th>
                            <th>Total de ativos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($setores as $setor)
                        <tr>
                            <td>{{ $setor->nome }}</td>
                            <td>{{ $setor->descricao ?: '-' }}</td>
                            <td>{{ $setor->equipamentos_count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $setores->links() }}
            </div>
        </div>
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum setor cadastrado.</p>
            <p class="portal-empty-state-text">
                Os setores aparecerão aqui quando forem cadastrados.
            </p>
        </div>
        @endif

    </div>
</x-app-layout>
