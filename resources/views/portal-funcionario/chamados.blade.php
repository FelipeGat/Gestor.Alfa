<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📋 Painel de Chamados
            </h2>
            <a href="{{ route('portal-funcionario.index') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
        </div>
    </x-slot>

    <style>
        .chamados-container {
            padding: 1rem;
            max-width: 100%;
            margin: 0 auto;
        }

        .section-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
        }

        .section-count {
            background: rgba(255, 255, 255, 0.3);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .cards-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .chamado-card {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
            border-left: 4px solid;
            position: relative;
        }

        .chamado-card:active {
            transform: scale(0.98);
        }

        .chamado-card.alta {
            border-left-color: #ef4444;
        }

        .chamado-card.media {
            border-left-color: #f59e0b;
        }

        .chamado-card.baixa {
            border-left-color: #3b82f6;
        }

        .chamado-card.proximo {
            border: 3px solid #10b981;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .card-numero {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .priority-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-badge.alta {
            background: #fecaca;
            color: #991b1b;
        }

        .priority-badge.media {
            background: #fed7aa;
            color: #9a3412;
        }

        .priority-badge.baixa {
            background: #bfdbfe;
            color: #1e40af;
        }

        .card-info {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #4b5563;
        }

        .card-info svg {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        .card-cliente {
            font-weight: 600;
            color: #1f2937;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .card-descricao {
            font-size: 0.875rem;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .card-footer {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            flex: 1;
            min-width: 120px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
        }

        .btn-primary:active {
            transform: scale(0.95);
        }

        .btn-success {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .cronometro {
            background: #1f2937;
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            text-align: center;
            font-family: 'Courier New', monospace;
            margin-bottom: 1rem;
        }

        .cronometro.pausado {
            background: #f59e0b;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #9ca3af;
        }

        .empty-state svg {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
        }

        @media (min-width: 768px) {
            .chamados-container {
                max-width: 1200px;
                padding: 2rem;
            }

            .cards-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
        }
    </style>

    <div class="chamados-container">
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
        @endif

        <!-- EM ATENDIMENTO -->
        @if($emAtendimento->count() > 0)
        <div class="section-header" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
            <span class="section-title">
                <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                </svg>
                Em Atendimento
            </span>
            <span class="section-count">{{ $emAtendimento->count() }}</span>
        </div>

        <div class="cards-grid">
            @foreach($emAtendimento as $atendimento)
            <div class="chamado-card {{ $atendimento->prioridade }}">
                @if($atendimento->em_execucao)
                <div class="cronometro" data-iniciado="{{ $atendimento->iniciado_em->timestamp }}" data-tempo-base="{{ $atendimento->tempo_execucao_segundos }}">
                    <span class="tempo-display">00:00:00</span>
                </div>
                @elseif($atendimento->em_pausa)
                <div class="cronometro pausado">
                    ⏸️ PAUSADO - {{ $atendimento->tempo_execucao_formatado }}
                </div>
                @endif

                <div class="card-header">
                    <span class="card-numero">#{{ $atendimento->numero_atendimento }}</span>
                    <span class="priority-badge {{ $atendimento->prioridade }}">{{ strtoupper($atendimento->prioridade) }}</span>
                </div>

                <div class="card-cliente">
                    {{ $atendimento->cliente?->nome_fantasia ?? $atendimento->nome_solicitante ?? 'Sem cliente' }}
                </div>

                <div class="card-info">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    {{ $atendimento->empresa?->nome_fantasia ?? 'N/A' }}
                </div>

                <div class="card-info">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    {{ $atendimento->assunto?->nome ?? 'Sem assunto' }}
                </div>

                <div class="card-descricao">
                    {{ Str::limit($atendimento->descricao, 100) }}
                </div>

                <div class="card-footer">
                    <a href="{{ route('portal-funcionario.atendimento.show', $atendimento) }}" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Ver Detalhes
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- ABERTOS (FILA) -->
        <div class="section-header">
            <span class="section-title">
                <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9 4a1 1 0 10-2 0v5a1 1 0 102 0V9zm-3 0a1 1 0 10-2 0v5a1 1 0 102 0V9zm-3 0a1 1 0 10-2 0v5a1 1 0 102 0V9z" clip-rule="evenodd"></path>
                </svg>
                Fila de Atendimento
            </span>
            <span class="section-count">{{ $abertos->count() }}</span>
        </div>

        @if($abertos->count() > 0)
        <div class="cards-grid">
            @foreach($abertos as $index => $atendimento)
            <div class="chamado-card {{ $atendimento->prioridade }} {{ $index === 0 ? 'proximo' : '' }}">
                @if($index === 0)
                <div style="background: #10b981; color: white; padding: 0.5rem; text-align: center; border-radius: 0.5rem; margin-bottom: 1rem; font-weight: 700;">
                    ⭐ PRÓXIMO DA FILA
                </div>
                @endif

                <div class="card-header">
                    <span class="card-numero">#{{ $atendimento->numero_atendimento }}</span>
                    <span class="priority-badge {{ $atendimento->prioridade }}">{{ strtoupper($atendimento->prioridade) }}</span>
                </div>

                <div class="card-cliente">
                    {{ $atendimento->cliente?->nome_fantasia ?? $atendimento->nome_solicitante ?? 'Sem cliente' }}
                </div>

                <div class="card-info">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ $atendimento->data_atendimento->format('d/m/Y') }}
                </div>

                <div class="card-info">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    {{ $atendimento->assunto?->nome ?? 'Sem assunto' }}
                </div>

                <div class="card-descricao">
                    {{ Str::limit($atendimento->descricao, 100) }}
                </div>

                <div class="card-footer">
                    @if($index === 0)
                    <a href="{{ route('portal-funcionario.atendimento.show', $atendimento) }}" class="btn btn-success">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Iniciar Atendimento
                    </a>
                    @else
                    <a href="{{ route('portal-funcionario.atendimento.show', $atendimento) }}" class="btn btn-secondary">
                        Ver Detalhes
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p>Nenhum chamado aberto no momento</p>
        </div>
        @endif

        <!-- FINALIZADOS -->
        @if($finalizados->count() > 0)
        <div class="section-header" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
            <span class="section-title">
                <svg style="width: 1.25rem; height: 1.25rem; display: inline-block; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Finalizados Recentes
            </span>
            <span class="section-count">{{ $finalizados->count() }}</span>
        </div>

        <div class="cards-grid">
            @foreach($finalizados as $atendimento)
            <div class="chamado-card {{ $atendimento->prioridade }}" style="opacity: 0.7;">
                <div class="card-header">
                    <span class="card-numero">#{{ $atendimento->numero_atendimento }}</span>
                    @if($atendimento->status_atual === 'finalizacao')
                    <span style="color: #f59e0b; font-weight: 700;">⏳ AGUARDANDO APROVAÇÃO</span>
                    @else
                    <span style="color: #10b981; font-weight: 700;">✓ CONCLUÍDO</span>
                    @endif
                </div>

                <div class="card-cliente">
                    {{ $atendimento->cliente?->nome_fantasia ?? $atendimento->nome_solicitante ?? 'Sem cliente' }}
                </div>

                <div class="card-info">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Tempo: {{ $atendimento->tempo_execucao_formatado }}
                </div>

                <div class="card-footer">
                    <a href="{{ route('portal-funcionario.atendimento.show', $atendimento) }}" class="btn btn-secondary">
                        Ver Detalhes
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Cronômetros em tempo real
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
</x-app-layout>
