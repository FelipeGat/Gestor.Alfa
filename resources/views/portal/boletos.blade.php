<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <div class="portal-wrapper">
        @if($boletos->count() > 0)
        <div class="portal-table-card">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Boletos Disponíveis
                </h3>
            </div>
            @include('portal.partials.live-table-filter', [
                'inputId' => 'filtroBoletos',
                'tableId' => 'tabelaBoletos',
                'placeholder' => 'Digite número, status ou valor'
            ])

            {{-- Versão Desktop (Tabela) --}}
            <div class="portal-table-wrapper">
                <table id="tabelaBoletos" class="portal-table">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($boletos as $boleto)
                        <tr>
                            <td class="portal-font-semibold">{{ $boleto->numero }}</td>
                            <td class="portal-font-semibold">
                                R$ {{ number_format($boleto->valor, 2, ',', '.') }}
                            </td>
                            <td>{{ $boleto->data_vencimento->format('d/m/Y') }}</td>
                            <td>
                                @if($boleto->foiBaixado())
                                <span class="portal-badge portal-badge--success">Pago</span>
                                @elseif($boleto->estaVencido())
                                <span class="portal-badge portal-badge--danger">Vencido</span>
                                @else
                                <span class="portal-badge portal-badge--warning">Pendente</span>
                                @endif
                            </td>
                            <td>
                                @if($boleto->arquivo)
                                <a href="{{ route('portal.boletos.download', $boleto->id) }}"
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
                @foreach($boletos as $boleto)
                <div class="portal-mobile-card">
                    <div class="portal-mobile-card-header">
                        <div>
                            <div class="portal-mobile-card-title">
                                Boleto Nº {{ $boleto->numero }}
                            </div>
                            <div class="portal-mobile-card-subtitle">
                                Vencimento: {{ $boleto->data_vencimento->format('d/m/Y') }}
                            </div>
                        </div>
                        @if($boleto->foiBaixado())
                        <span class="portal-badge portal-badge--success">Pago</span>
                        @elseif($boleto->estaVencido())
                        <span class="portal-badge portal-badge--danger">Vencido</span>
                        @else
                        <span class="portal-badge portal-badge--warning">Pendente</span>
                        @endif
                    </div>
                    <div class="portal-mobile-card-row">
                        <span class="portal-mobile-card-label">Valor</span>
                        <span class="portal-mobile-card-value portal-font-bold">
                            R$ {{ number_format($boleto->valor, 2, ',', '.') }}
                        </span>
                    </div>
                    @if($boleto->arquivo)
                    <div class="portal-mobile-card-actions">
                        <a href="{{ route('portal.boletos.download', $boleto->id) }}"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum boleto encontrado.</p>
            <p class="portal-empty-state-text">
                Seus boletos aparecerão aqui quando forem disponibilizados.
            </p>
        </div>
        @endif
    </div>
</x-app-layout>
