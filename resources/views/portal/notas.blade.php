<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Minhas Notas Fiscais
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $cliente->nome_exibicao }}
                </p>
            </div>
            <a href="{{ route('portal.financeiro') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline">Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper">
        @if($notas->count() > 0)
        <div class="portal-table-card">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Notas Fiscais Disponíveis
                </h3>
            </div>

            {{-- Versão Desktop (Tabela) --}}
            <div class="portal-table-wrapper">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Data Emissão</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notas as $nota)
                        <tr>
                            <td class="portal-font-semibold">{{ $nota->numero ?? '—' }}</td>
                            <td>{{ $nota->created_at->format('d/m/Y') }}</td>
                            <td class="portal-font-semibold">
                                R$ {{ number_format($nota->valor_total ?? 0, 2, ',', '.') }}
                            </td>
                            <td>
                                @if($nota->status === 'emitida')
                                <span class="portal-badge portal-badge--success">Emitida</span>
                                @elseif($nota->status === 'cancelada')
                                <span class="portal-badge portal-badge--danger">Cancelada</span>
                                @else
                                <span class="portal-badge portal-badge--warning">{{ $nota->status ?? 'Pendente' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($nota->arquivo)
                                <a href="{{ route('portal.notas.download', $nota->id) }}"
                                    target="_blank"
                                    class="portal-btn portal-btn--primary portal-btn--sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Baixar
                                </a>
                                @else
                                <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Versão Mobile (Cards) --}}
            <div class="portal-mobile-cards px-4 pb-4">
                @foreach($notas as $nota)
                <div class="portal-mobile-card">
                    <div class="portal-mobile-card-header">
                        <div>
                            <div class="portal-mobile-card-title">
                                NF {{ $nota->numero ?? '—' }}
                            </div>
                            <div class="portal-mobile-card-subtitle">
                                Emitida em: {{ $nota->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        @if($nota->status === 'emitida')
                        <span class="portal-badge portal-badge--success">Emitida</span>
                        @elseif($nota->status === 'cancelada')
                        <span class="portal-badge portal-badge--danger">Cancelada</span>
                        @else
                        <span class="portal-badge portal-badge--warning">{{ $nota->status ?? 'Pendente' }}</span>
                        @endif
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Valor</span>
                        <span class="portal-mobile-card-value portal-font-bold">
                            R$ {{ number_format($nota->valor_total ?? 0, 2, ',', '.') }}
                        </span>
                    </div>
                    @if($nota->arquivo)
                    <div class="portal-mobile-card-actions">
                        <a href="{{ route('portal.notas.download', $nota->id) }}"
                            target="_blank"
                            class="portal-btn portal-btn--primary portal-btn--sm flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Baixar
                        </a>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="portal-empty-state-title">Nenhuma nota fiscal encontrada.</p>
            <p class="portal-empty-state-text">
                Suas notas fiscais aparecerão aqui quando forem emitidas.
            </p>
        </div>
        @endif
    </div>
</x-app-layout>
