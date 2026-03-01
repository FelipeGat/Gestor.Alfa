<x-portal-funcionario-layout>
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('portal-funcionario.index') }}" class="hover:text-[#3f9cae]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </a>
            <span>/</span>
            <span class="font-medium text-gray-900">Chamados</span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Notificações -->
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 0L6 8.586l-.707-.707a1 1 0 00-1.414 1.414L5.172 10.586l-1.293 1.293a1 1 0 101.414 1.414L6.586 12l.707.707a1 1 0 001.414 0L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
        @endif

        <!-- EM ATENDIMENTO -->
        @if($emAtendimento->count() > 0)
        <section class="mb-8">
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 text-white px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">Em Atendimento</span>
                </div>
                <x-badge :label="$emAtendimento->count()" class="bg-white/20 text-white" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($emAtendimento as $atendimento)
                <x-card class="group border-l-4 {{ $atendimento->prioridade === 'alta' ? 'border-l-red-500' : ($atendimento->prioridade === 'media' ? 'border-l-amber-500' : 'border-l-teal-500') }}">
                    @if($atendimento->em_execucao)
                    <div class="cronometro bg-gray-800 text-white px-4 py-3 rounded-lg mb-3 text-center font-mono font-bold text-lg" 
                         data-iniciado="{{ $atendimento->iniciado_em->timestamp }}" 
                         data-tempo-base="{{ $atendimento->tempo_execucao_segundos }}">
                        <span class="tempo-display">00:00:00</span>
                    </div>
                    @elseif($atendimento->em_pausa)
                    <div class="bg-amber-500 text-white px-4 py-3 rounded-lg mb-3 text-center font-bold">
                        ⏸️ PAUSADO - {{ $atendimento->tempo_execucao_formatado }}
                    </div>
                    @endif

                    <div class="flex items-start justify-between mb-3">
                        <span class="text-2xl font-bold text-gray-900">#{{ $atendimento->numero_atendimento }}</span>
                        <x-badge :label="strtoupper($atendimento->prioridade)" 
                                 :type="$atendimento->prioridade === 'alta' ? 'danger' : ($atendimento->prioridade === 'media' ? 'warning' : 'primary')" 
                                 size="sm" />
                    </div>

                    <h4 class="font-semibold text-gray-900 mb-3">
                        {{ $atendimento->cliente?->nome_fantasia ?? $atendimento->nome_solicitante ?? 'Sem cliente' }}
                    </h4>

                    <div class="space-y-2 text-sm text-gray-600 mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            @php
                                $data = $atendimento->finalizado_em ?? $atendimento->iniciado_em ?? $atendimento->data_atendimento;
                            @endphp
                            {{ $data ? $data->format('d/m/Y H:i') : '-' }}
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            {{ $atendimento->assunto?->nome ?? 'Sem assunto' }}
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                        {{ Str::limit($atendimento->descricao, 100) }}
                    </p>

                    <x-button href="{{ route('portal-funcionario.atendimento.show', $atendimento) }}" variant="primary" size="sm" class="w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Ver Detalhes
                    </x-button>
                </x-card>
                @endforeach
            </div>
        </section>
        @endif

        <!-- ABERTOS (FILA) -->
        <section class="mb-8">
            <div class="bg-gradient-to-r from-[#3f9cae] to-[#327d8c] text-white px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9 4a1 1 0 10-2 0v5a1 1 0 102 0V9zm-3 0a1 1 0 10-2 0v5a1 1 0 102 0V9zm-3 0a1 1 0 10-2 0v5a1 1 0 102 0V9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">Fila de Atendimento</span>
                </div>
                <x-badge :label="$abertos->count()" class="bg-white/20 text-white" />
            </div>

            @if($abertos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($abertos as $index => $atendimento)
                <x-card class="group border-l-4 {{ $atendimento->prioridade === 'alta' ? 'border-l-red-500' : ($atendimento->prioridade === 'media' ? 'border-l-amber-500' : 'border-l-teal-500') }} {{ $index === 0 ? 'ring-2 ring-green-500 bg-gradient-to-br from-green-50 to-white' : '' }}">
                    @if($index === 0)
                    <div class="bg-green-500 text-white px-3 py-2 rounded-lg mb-3 text-center font-bold text-sm">
                        ⭐ PRÓXIMO DA FILA
                    </div>
                    @endif

                    <div class="flex items-start justify-between mb-3">
                        <span class="text-2xl font-bold text-gray-900">#{{ $atendimento->numero_atendimento }}</span>
                        <x-badge :label="strtoupper($atendimento->prioridade)" 
                                 :type="$atendimento->prioridade === 'alta' ? 'danger' : ($atendimento->prioridade === 'media' ? 'warning' : 'primary')" 
                                 size="sm" />
                    </div>

                    <h4 class="font-semibold text-gray-900 mb-3">
                        {{ $atendimento->cliente?->nome_fantasia ?? $atendimento->nome_solicitante ?? 'Sem cliente' }}
                    </h4>

                    <div class="space-y-2 text-sm text-gray-600 mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            @php
                                $data = $atendimento->finalizado_em ?? $atendimento->iniciado_em ?? $atendimento->data_atendimento;
                            @endphp
                            {{ $data ? $data->format('d/m/Y H:i') : '-' }}
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            {{ $atendimento->assunto?->nome ?? 'Sem assunto' }}
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                        {{ Str::limit($atendimento->descricao, 100) }}
                    </p>

                    @if($index === 0)
                    <x-button href="{{ route('portal-funcionario.atendimento.show', $atendimento) }}" variant="success" size="sm" class="w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Iniciar Atendimento
                    </x-button>
                    @else
                    <x-button href="{{ route('portal-funcionario.atendimento.show', $atendimento) }}" variant="light" size="sm" class="w-full">
                        Ver Detalhes
                    </x-button>
                    @endif
                </x-card>
                @endforeach
            </div>
            @else
            <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-600 font-medium">Nenhum chamado aberto no momento</p>
            </div>
            @endif
        </section>

        <!-- FINALIZADOS -->
        @if($finalizados->count() > 0)
        <section class="mb-8">
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">Finalizados Recentes</span>
                </div>
                <x-badge :label="$finalizados->count()" class="bg-white/20 text-white" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($finalizados as $atendimento)
                <x-card class="group border-l-4 {{ $atendimento->prioridade === 'alta' ? 'border-l-red-500' : ($atendimento->prioridade === 'media' ? 'border-l-amber-500' : 'border-l-teal-500') }} opacity-90">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-2xl font-bold text-gray-900">#{{ $atendimento->numero_atendimento }}</span>
                        @if($atendimento->status_atual === 'finalizacao')
                        <x-badge label="⏳ Aguardando" type="warning" size="sm" />
                        @else
                        <x-badge label="✓ Concluído" type="success" size="sm" />
                        @endif
                    </div>

                    <h4 class="font-semibold text-gray-900 mb-3">
                        {{ $atendimento->cliente?->nome_fantasia ?? $atendimento->nome_solicitante ?? 'Sem cliente' }}
                    </h4>

                    <div class="space-y-2 text-sm text-gray-600 mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            @php
                                $data = $atendimento->finalizado_em ?? $atendimento->iniciado_em ?? $atendimento->data_atendimento;
                            @endphp
                            {{ $data ? $data->format('d/m/Y H:i') : '-' }}
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Tempo: {{ $atendimento->tempo_execucao_formatado }}
                        </div>
                    </div>

                    <x-button href="{{ route('portal-funcionario.atendimento.show', $atendimento) }}" variant="light" size="sm" class="w-full">
                        Abrir
                    </x-button>
                </x-card>
                @endforeach
            </div>
        </section>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cronometros = document.querySelectorAll('.cronometro[data-iniciado]');

            cronometros.forEach(crono => {
                const iniciadoTimestamp = parseInt(crono.dataset.iniciado);
                const tempoBase = parseInt(crono.dataset.tempoBase || 0);
                const display = crono.querySelector('.tempo-display');

                function atualizarCronometro() {
                    const agora = Math.floor(Date.now() / 1000);
                    const segundosDecorridos = agora - iniciadoTimestamp;
                    const totalSegundos = tempoBase + segundosDecorridos;

                    const horas = Math.floor(totalSegundos / 3600);
                    const minutos = Math.floor((totalSegundos % 3600) / 60);
                    const segundos = totalSegundos % 60;

                    display.textContent =
                        String(horas).padStart(2, '0') + ':' +
                        String(minutos).padStart(2, '0') + ':' +
                        String(segundos).padStart(2, '0');
                }

                atualizarCronometro();
                setInterval(atualizarCronometro, 1000);
            });
        });
    </script>
    @endpush
</x-portal-funcionario-layout>
