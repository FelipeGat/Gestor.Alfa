<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">Meus Ativos</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $cliente->nome_exibicao }}</p>
            </div>
            <a href="{{ route('portal.equipamentos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline">Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper">
        @if($ativosTecnicos->count())
        <div class="portal-table-card overflow-hidden">
            <div class="portal-table-header">
                <h3 class="portal-table-title">Portal > Meus Ativos</h3>
            </div>
            <div class="portal-table-wrapper">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Modelo</th>
                            <th>Localização</th>
                            <th>Status</th>
                            <th>Última manutenção</th>
                            <th>Próxima manutenção</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ativosTecnicos as $equipamento)
                        <tr>
                            <td>{{ $equipamento->nome }}</td>
                            <td>{{ $equipamento->modelo ?: '-' }}</td>
                            <td>{{ $equipamento->localizacao_resumo }}</td>
                            <td>{{ $equipamento->status_ativo ? str_replace('_', ' ', ucfirst($equipamento->status_ativo)) : 'Não informado' }}</td>
                            <td>{{ $equipamento->ultima_manutencao?->format('d/m/Y') ?: '-' }}</td>
                            <td>{{ $equipamento->proxima_manutencao?->format('d/m/Y') ?: '-' }}</td>
                            <td>
                                <a href="{{ route('portal.ativos.show', $equipamento) }}" class="portal-btn portal-btn--primary">Ver Detalhes</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum ativo técnico cadastrado.</p>
            <p class="portal-empty-state-text">Os ativos aparecerão aqui quando forem vinculados ao seu cliente.</p>
        </div>
        @endif
    </div>
</x-app-layout>
