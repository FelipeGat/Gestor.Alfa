<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Minhas Ordens de Serviço
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Unidade: <span class="font-semibold text-[#3f9cae]">{{ $cliente->nome_exibicao }}</span>
                </p>
            </div>
            <a href="{{ route('portal.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg shadow transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline">Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper">

        @if($atendimentos->count() > 0)
        <div class="portal-table-card">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Meus Atendimentos
                </h3>
            </div>

            {{-- Versão Desktop (Tabela) --}}
            <div class="portal-table-wrapper">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Empresa</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($atendimentos as $atendimento)
                        <tr>
                            <td class="portal-font-bold text-[#3f9cae]">#{{ $atendimento->id }}</td>
                            <td>{{ $atendimento->empresa->nome_fantasia ?? '—' }}</td>
                            <td>{{ $atendimento->assunto->nome ?? '—' }}</td>
                            <td>
                                <span class="portal-badge portal-badge--info">
                                    {{ $atendimento->status_atual ?? 'Indefinido' }}
                                </span>
                            </td>
                            <td>{{ $atendimento->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button type="button"
                                    onclick="window.showHistorico{{ $atendimento->id }}.showModal()"
                                    class="portal-btn portal-btn--primary portal-btn--sm">
                                    Ver Detalhes
                                </button>

                                <dialog id="showHistorico{{ $atendimento->id }}"
                                    class="rounded-xl shadow-xl p-0 w-full max-w-2xl backdrop:bg-gray-900/50">
                                    <form method="dialog" class="p-6">
                                        <h3 class="text-lg font-bold mb-4 text-gray-900">
                                            Histórico do Atendimento #{{ $atendimento->id }}
                                        </h3>
                                        @if($atendimento->andamentos && $atendimento->andamentos->count())
                                        <div class="space-y-3 max-h-96 overflow-y-auto">
                                            @foreach($atendimento->andamentos as $andamento)
                                            <div class="border-b pb-2 last:border-0">
                                                <div class="text-sm text-gray-800">
                                                    {{ $andamento->descricao ?? '—' }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $andamento->created_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <div class="text-gray-500 text-center py-8">
                                            Nenhum andamento disponível.
                                        </div>
                                        @endif
                                        <div class="mt-6 text-right">
                                            <button type="button"
                                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg"
                                                onclick="window.showHistorico{{ $atendimento->id }}.close()">
                                                Fechar
                                            </button>
                                        </div>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Versão Mobile (Cards) --}}
            <div class="portal-mobile-cards px-4 pb-4">
                @foreach($atendimentos as $atendimento)
                <div class="portal-mobile-card">
                    <div class="portal-mobile-card-header">
                        <div>
                            <div class="portal-mobile-card-title">
                                Atendimento #{{ $atendimento->id }}
                            </div>
                            <div class="portal-mobile-card-subtitle">
                                {{ $atendimento->empresa->nome_fantasia ?? '—' }}
                            </div>
                        </div>
                        <span class="portal-badge portal-badge--info">
                            {{ $atendimento->status_atual ?? 'Indefinido' }}
                        </span>
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Assunto</span>
                        <span class="portal-mobile-card-value">
                            {{ $atendimento->assunto->nome ?? '—' }}
                        </span>
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Criado em</span>
                        <span class="portal-mobile-card-value">
                            {{ $atendimento->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div class="portal-mobile-card-actions">
                        <button type="button"
                            onclick="window.showHistorico{{ $atendimento->id }}.showModal()"
                            class="portal-btn portal-btn--primary portal-btn--sm flex-1">
                            Ver Detalhes
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum atendimento encontrado.</p>
            <p class="portal-empty-state-text">
                Suas ordens de serviço aparecerão aqui quando forem cadastradas.
            </p>
        </div>
        @endif

    </div>
</x-app-layout>
