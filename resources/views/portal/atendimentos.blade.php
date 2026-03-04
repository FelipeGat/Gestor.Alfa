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
        <div class="rounded-lg overflow-hidden" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Meus Atendimentos
                </h3>
            </div>
            @include('portal.partials.live-table-filter', [
                'inputId' => 'filtroAtendimentos',
                'tableId' => 'tabelaAtendimentos',
                'placeholder' => 'Digite código, empresa, assunto ou status'
            ])

            {{-- Versão Desktop (Tabela) --}}
            <div class="overflow-x-auto">
                <table id="tabelaAtendimentos" class="portal-table w-full">
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
                                @php
                                    $status = $atendimento->status_atual;
                                    $badgeClass = 'portal-badge--gray';
                                    
                                    if(in_array($status, ['aberto', 'em_atendimento'])) {
                                        $badgeClass = 'portal-badge--info';
                                    } elseif(in_array($status, ['pendente_cliente', 'pendente_fornecedor', 'garantia', 'finalizacao'])) {
                                        $badgeClass = 'portal-badge--warning';
                                    } elseif($status === 'concluido') {
                                        $badgeClass = 'portal-badge--success';
                                    }
                                @endphp
                                <span class="portal-badge {{ $badgeClass }}">
                                    {{ strtoupper(str_replace('_', ' ', $status ?? 'Indefinido')) }}
                                </span>
                            </td>
                            <td>{{ $atendimento->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button type="button"
                                    onclick="openModal('showHistorico{{ $atendimento->id }}')"
                                    class="p-2 rounded-full inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 transition"
                                    title="Ver Detalhes">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                </button>

                                <dialog id="showHistorico{{ $atendimento->id }}"
                                    class="rounded-xl shadow-xl p-0 w-full max-w-2xl backdrop:bg-gray-900/50">
                                    <form method="dialog" class="p-0">
                                        {{-- Header --}}
                                        <div class="px-6 py-4 border-b border-gray-200" style="background-color: rgba(63, 156, 174, 0.05);">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                Histórico do Atendimento #{{ $atendimento->id }}
                                            </h3>
                                        </div>

                                        {{-- Corpo --}}
                                        <div class="p-6">
                                            @if($atendimento->andamentos && $atendimento->andamentos->count())
                                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                                @foreach($atendimento->andamentos as $andamento)
                                                <div class="border-b border-gray-200 pb-3 last:border-0">
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
                                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm font-medium">Nenhum andamento registrado.</p>
                                            </div>
                                            @endif
                                        </div>

                                        {{-- Footer com Botões --}}
                                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                                            <x-button variant="danger" size="sm" class="min-w-[130px]" onclick="closeModal('showHistorico{{ $atendimento->id }}')">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                                Fechar
                                            </x-button>
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
                        @php
                            $status = $atendimento->status_atual;
                            $badgeClass = 'portal-badge--gray';
                            
                            if(in_array($status, ['aberto', 'em_atendimento'])) {
                                $badgeClass = 'portal-badge--info';
                            } elseif(in_array($status, ['pendente_cliente', 'pendente_fornecedor', 'garantia', 'finalizacao'])) {
                                $badgeClass = 'portal-badge--warning';
                            } elseif($status === 'concluido') {
                                $badgeClass = 'portal-badge--success';
                            }
                        @endphp
                        <span class="portal-badge {{ $badgeClass }}">
                            {{ strtoupper(str_replace('_', ' ', $status ?? 'Indefinido')) }}
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
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 
                                   bg-[#3f9cae] hover:bg-[#2d7a8a] 
                                   text-white text-sm font-semibold 
                                   rounded-lg border-0 shadow transition-all flex-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
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

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.showModal();
            // Remove foco do botão para evitar borda dupla
            setTimeout(function() {
                const button = modal.querySelector('button');
                if (button) button.blur();
            }, 0);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.close();
        }
    </script>
</x-app-layout>
