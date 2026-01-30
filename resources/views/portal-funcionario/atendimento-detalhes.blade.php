<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                #{{ $atendimento->numero_atendimento }}
            </h2>
            <a href="{{ route('portal-funcionario.chamados') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
        </div>
    </x-slot>

    <style>
        .detalhes-container {
            padding: 1rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .status-banner {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
            font-size: 1.125rem;
        }

        .status-banner.aberto {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-banner.em_atendimento {
            background: #fef3c7;
            color: #92400e;
        }

        .status-banner.finalizacao {
            background: #fed7aa;
            color: #9a3412;
        }

        .status-banner.concluido {
            background: #d1fae5;
            color: #065f46;
        }

        .cronometro-principal {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .cronometro-label {
            font-size: 0.875rem;
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }

        .cronometro-display {
            font-size: 3rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.1em;
        }

        .cronometro-principal.pausado {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .info-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .info-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
            color: #1f2937;
            font-weight: 600;
        }

        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .btn-action {
            width: 100%;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .btn-action:active {
            transform: scale(0.97);
        }

        .btn-iniciar {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-pausar {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-retomar {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-finalizar {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            padding: 1rem;
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .foto-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .foto-preview img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .pausas-list {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }

        .pausa-item {
            background: white;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            border-left: 3px solid #f59e0b;
        }

        @media (min-width: 768px) {
            .detalhes-container {
                padding: 2rem;
            }
        }
    </style>

    <div class="detalhes-container">
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

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Status Banner -->
        <div class="status-banner {{ $atendimento->status_atual }}">
            {{ strtoupper(str_replace('_', ' ', $atendimento->status_atual)) }}
        </div>

        <!-- Cronômetro - Só mostra se realmente foi iniciado -->
        @if($atendimento->status_atual === 'em_atendimento' && $atendimento->iniciado_em)
        @php
            $pausaAtiva = $atendimento->em_pausa ? $atendimento->pausaAtiva() : null;
        @endphp
        <div class="cronometro-principal {{ $atendimento->em_pausa ? 'pausado' : '' }}" 
             data-iniciado="{{ $atendimento->em_pausa && $pausaAtiva ? $pausaAtiva->iniciada_em->timestamp : $atendimento->iniciado_em->timestamp }}" 
             data-tempo-base="{{ $atendimento->em_pausa ? 0 : ($atendimento->tempo_execucao_segundos ?? 0) }}">
            @if($atendimento->em_pausa)
                @php
                    $tipoPausaLabel = $pausaAtiva ? $pausaAtiva->tipo_pausa_label : 'Pausa';
                @endphp
                <div class="cronometro-label">⏸️ PAUSA PARA {{ strtoupper($tipoPausaLabel) }}</div>
                <div style="font-size: 0.875rem; opacity: 0.9; margin-top: 0.25rem;">
                    Iniciada às {{ $pausaAtiva ? $pausaAtiva->iniciada_em->format('H:i') : '' }}
                </div>
            @else
                <div class="cronometro-label">⏱️ EXECUÇÃO EM ANDAMENTO</div>
                <div style="font-size: 0.875rem; opacity: 0.9; margin-top: 0.25rem;">
                    Iniciado às {{ $atendimento->iniciado_em->format('H:i') }} - Bom trabalho!
                </div>
            @endif
            <div class="cronometro-display" style="margin-top: 0.5rem;">00:00:00</div>
        </div>
        @endif

        <!-- Aviso para atendimentos antigos em_atendimento sem iniciado_em -->
        @if($atendimento->status_atual === 'em_atendimento' && !$atendimento->iniciado_em)
        <div class="info-card" style="background: #fef3c7; border-left: 4px solid #f59e0b;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 1.5rem; height: 1.5rem; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <div style="font-weight: 700; color: #92400e;">Atendimento Antigo</div>
                    <div style="font-size: 0.875rem; color: #92400e; margin-top: 0.25rem;">
                        Este atendimento foi marcado como "em atendimento" pelo sistema antigo. 
                        Use os botões abaixo para <strong>iniciar</strong> o controle de tempo com o novo sistema.
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($atendimento->status_atual === 'finalizacao')
        <div class="info-card" style="background: #fffbeb; border-left: 4px solid #f59e0b;">
            <div style="padding: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.5rem;">⏳</span>
                    <div style="font-weight: 700; color: #92400e; font-size: 1.125rem;">Aguardando Aprovação do Gerente</div>
                </div>
                <div style="font-size: 0.875rem; color: #92400e; line-height: 1.5;">
                    Este atendimento foi finalizado pelo técnico e está aguardando revisão e aprovação do gerente de suporte para conclusão final.
                </div>
            </div>
        </div>
        @endif

        @if($atendimento->finalizado_em)
        <div class="info-card">
            <div class="info-label">Tempo Total de Execução</div>
            <div class="info-value" style="font-size: 2rem; color: #10b981;">
                ⏱️ {{ $atendimento->tempo_execucao_formatado }}
            </div>
            
            <!-- Fotos de Início e Finalização -->
            @php
                $andamentoInicio = $atendimento->andamentos->where('descricao', 'Atendimento iniciado pelo técnico')->first();
                $andamentoFinal = $atendimento->andamentos->where('descricao', '!=', 'Atendimento iniciado pelo técnico')->sortByDesc('created_at')->first();
            @endphp
            
            @if($andamentoInicio && $andamentoInicio->fotos->count() > 0)
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid #e5e7eb;">
                <div class="info-label">📸 Fotos do Início do Atendimento</div>
                @if($atendimento->iniciadoPor)
                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                    Iniciado por: <strong>{{ $atendimento->iniciadoPor->name }}</strong> em {{ $atendimento->iniciado_em->format('d/m/Y H:i') }}
                </div>
                @endif
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem; margin-top: 0.75rem;">
                    @foreach($andamentoInicio->fotos as $foto)
                    <div>
                        <img src="{{ asset('storage/' . $foto->arquivo) }}" 
                             alt="Foto início" 
                             style="width: 100%; height: 100px; object-fit: cover; border-radius: 0.5rem; cursor: pointer; border: 2px solid #10b981;"
                             onclick="window.open(this.src, '_blank')">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if($andamentoFinal && $andamentoFinal->fotos->count() > 0)
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid #e5e7eb;">
                <div class="info-label">📸 Fotos da Finalização do Atendimento</div>
                @if($andamentoFinal->descricao && $andamentoFinal->descricao != 'Atendimento finalizado pelo técnico')
                <div style="background: #f0f9ff; padding: 0.75rem; border-radius: 0.5rem; margin-top: 0.5rem; border-left: 3px solid #3b82f6;">
                    <div style="font-size: 0.75rem; color: #1e40af; font-weight: 600; margin-bottom: 0.25rem;">Observações:</div>
                    <div style="font-size: 0.875rem; color: #1e40af;">{{ $andamentoFinal->descricao }}</div>
                </div>
                @endif
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem; margin-top: 0.75rem;">
                    @foreach($andamentoFinal->fotos as $foto)
                    <div>
                        <img src="{{ asset('storage/' . $foto->arquivo) }}" 
                             alt="Foto finalização" 
                             style="width: 100%; height: 100px; object-fit: cover; border-radius: 0.5rem; cursor: pointer; border: 2px solid #8b5cf6;"
                             onclick="window.open(this.src, '_blank')">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if($atendimento->pausas->count() > 0)
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid #e5e7eb;">
                <div class="info-label">Detalhamento de Pausas</div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.75rem;">
                    @foreach($atendimento->pausas as $index => $pausa)
                    <div style="background: #fef3c7; padding: 0.75rem; border-radius: 0.5rem; border-left: 3px solid #f59e0b;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <div>
                                <div style="font-weight: 700; color: #92400e; font-size: 0.875rem;">
                                    {{ $pausa->tipo_pausa_label }}
                                </div>
                                <div style="font-size: 0.75rem; color: #92400e; margin-top: 0.25rem;">
                                    {{ $pausa->iniciada_em->format('d/m/Y H:i') }}
                                    @if($pausa->encerrada_em)
                                        → {{ $pausa->encerrada_em->format('H:i') }}
                                    @endif
                                </div>
                                @if($pausa->user)
                                <div style="font-size: 0.7rem; color: #78716c; margin-top: 0.25rem;">
                                    Pausado por: <strong>{{ $pausa->user->name }}</strong>
                                </div>
                                @endif
                                @if($pausa->retomadoPor)
                                <div style="font-size: 0.7rem; color: #78716c;">
                                    Retomado por: <strong>{{ $pausa->retomadoPor->name }}</strong>
                                </div>
                                @endif
                            </div>
                            <div style="font-weight: 700; color: #92400e; font-size: 1.125rem;">
                                {{ gmdate('H:i:s', $pausa->tempo_segundos ?? 0) }}
                            </div>
                        </div>
                        
                        <!-- Fotos da Pausa -->
                        @if($pausa->foto_inicio_path || $pausa->foto_retorno_path)
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;">
                            @if($pausa->foto_inicio_path)
                            <div>
                                <div style="font-size: 0.65rem; color: #92400e; margin-bottom: 0.25rem; font-weight: 600;">📸 Início</div>
                                <img src="{{ asset('storage/' . $pausa->foto_inicio_path) }}" 
                                     alt="Foto início pausa" 
                                     style="width: 100%; height: 80px; object-fit: cover; border-radius: 0.5rem; cursor: pointer; border: 2px solid #f59e0b;"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                            @endif
                            @if($pausa->foto_retorno_path)
                            <div>
                                <div style="font-size: 0.65rem; color: #92400e; margin-bottom: 0.25rem; font-weight: 600;">📸 Retorno</div>
                                <img src="{{ asset('storage/' . $pausa->foto_retorno_path) }}" 
                                     alt="Foto retorno" 
                                     style="width: 100%; height: 80px; object-fit: cover; border-radius: 0.5rem; cursor: pointer; border: 2px solid #f59e0b;"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: between; align-items: center;">
                        <div class="info-label">Tempo Total de Pausas</div>
                        <div class="info-value" style="color: #f59e0b; font-size: 1.5rem;">
                            {{ $atendimento->tempo_pausa_formatado }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Informações do Atendimento -->
        <div class="info-card">
            <div class="info-row">
                <div>
                    <div class="info-label">Cliente</div>
                    <div class="info-value">{{ $atendimento->cliente?->nome_fantasia ?? $atendimento->nome_solicitante ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="info-label">Prioridade</div>
                    <div class="info-value" style="color: {{ $atendimento->prioridade === 'alta' ? '#ef4444' : ($atendimento->prioridade === 'media' ? '#f59e0b' : '#3b82f6') }};">
                        {{ strtoupper($atendimento->prioridade) }}
                    </div>
                </div>
            </div>

            <div class="info-row">
                <div>
                    <div class="info-label">Empresa</div>
                    <div class="info-value">{{ $atendimento->empresa?->nome_fantasia ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="info-label">Data</div>
                    <div class="info-value">{{ $atendimento->data_atendimento->format('d/m/Y') }}</div>
                </div>
            </div>

            <div>
                <div class="info-label">Assunto</div>
                <div class="info-value">{{ $atendimento->assunto?->nome ?? 'N/A' }}</div>
            </div>

            <div style="margin-top: 1rem;">
                <div class="info-label">Descrição</div>
                <div class="info-value" style="font-weight: 400; white-space: pre-wrap;">{{ $atendimento->descricao }}</div>
            </div>
        </div>

        <!-- Pausas Ativas -->
        @if($atendimento->pausas->count() > 0 && !$atendimento->finalizado_em)
        <div class="info-card">
            <div class="info-label">Histórico de Pausas</div>
            <div class="pausas-list">
                @foreach($atendimento->pausas as $pausa)
                <div class="pausa-item">
                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;">
                        {{ $pausa->tipo_pausa_label }}
                    </div>
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        Início: {{ $pausa->iniciada_em->format('d/m/Y H:i') }}
                        @if($pausa->encerrada_em)
                        <br>Término: {{ $pausa->encerrada_em->format('d/m/Y H:i') }}
                        <br>Duração: {{ gmdate('H:i:s', $pausa->tempo_segundos) }}
                        @else
                        <br><span style="color: #f59e0b; font-weight: 600;">Em andamento...</span>
                        @endif
                    </div>
                    @if($pausa->user)
                    <div style="font-size: 0.75rem; color: #78716c; margin-top: 0.25rem;">
                        Pausado por: <strong>{{ $pausa->user->name }}</strong>
                    </div>
                    @endif
                    @if($pausa->retomadoPor)
                    <div style="font-size: 0.75rem; color: #78716c;">
                        Retomado por: <strong>{{ $pausa->retomadoPor->name }}</strong>
                    </div>
                    @endif
                    
                    <!-- Fotos da Pausa -->
                    @if($pausa->foto_inicio_path || $pausa->foto_retorno_path)
                    <div style="margin-top: 0.75rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem;">
                        @if($pausa->foto_inicio_path)
                        <div>
                            <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 0.25rem;">📸 Início da Pausa</div>
                            <img src="{{ asset('storage/' . $pausa->foto_inicio_path) }}" 
                                 alt="Foto início pausa" 
                                 style="width: 100%; height: 100px; object-fit: cover; border-radius: 0.5rem; cursor: pointer;"
                                 onclick="window.open(this.src, '_blank')">
                        </div>
                        @endif
                        @if($pausa->foto_retorno_path)
                        <div>
                            <div style="font-size: 0.7rem; color: #6b7280; margin-bottom: 0.25rem;">📸 Retorno</div>
                            <img src="{{ asset('storage/' . $pausa->foto_retorno_path) }}" 
                                 alt="Foto retorno" 
                                 style="width: 100%; height: 100px; object-fit: cover; border-radius: 0.5rem; cursor: pointer;"
                                 onclick="window.open(this.src, '_blank')">
                        </div>
                        @endif
                    </div>
                    @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Botões de Ação -->
        <div style="margin-top: 1.5rem;">
            @if($atendimento->status_atual === 'aberto')
            <button onclick="abrirModalIniciar()" class="btn-action btn-iniciar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Iniciar Atendimento
            </button>
            @endif

            @if($atendimento->status_atual === 'em_atendimento' && !$atendimento->iniciado_em)
            <button onclick="abrirModalIniciar()" class="btn-action btn-iniciar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Iniciar Controle de Tempo
            </button>
            @endif

            @if($atendimento->status_atual === 'em_atendimento' && $atendimento->iniciado_em && $atendimento->em_execucao)
            <button onclick="abrirModalPausar()" class="btn-action btn-pausar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Pausar Atendimento
            </button>

            <button onclick="abrirModalFinalizar()" class="btn-action btn-finalizar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Finalizar Atendimento
            </button>
            @endif

            @if($atendimento->status_atual === 'em_atendimento' && $atendimento->em_pausa)
            <button onclick="abrirModalRetomar()" class="btn-action btn-retomar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Retomar Atendimento
            </button>
            @endif
        </div>
    </div>

    <!-- Modal Iniciar -->
    <div id="modalIniciar" class="modal">
        <div class="modal-content">
            <div class="modal-header">📸 Enviar 3 Fotos Iniciais</div>
            <form action="{{ route('portal-funcionario.atendimento.iniciar', $atendimento) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Selecione 3 Fotos</label>
                    <input type="file" name="fotos[]" accept="image/*" capture="environment" multiple required class="form-input" onchange="previewFotos(this, 'previewIniciar')">
                    <div id="previewIniciar" class="foto-preview"></div>
                </div>
                <button type="submit" class="btn-action btn-iniciar">Iniciar</button>
                <button type="button" onclick="fecharModal('modalIniciar')" class="btn-action" style="background: #e5e7eb; color: #374151;">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal Pausar -->
    <div id="modalPausar" class="modal">
        <div class="modal-content">
            <div class="modal-header">⏸️ Pausar Atendimento</div>
            <form action="{{ route('portal-funcionario.atendimento.pausar', $atendimento) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Tipo de Pausa</label>
                    <select name="tipo_pausa" required class="form-select">
                        <option value="">Selecione...</option>
                        <option value="almoco">🍽️ Almoço</option>
                        <option value="deslocamento">🚗 Deslocamento entre Clientes</option>
                        <option value="material">🛒 Compra de Material</option>
                        <option value="fim_dia">🌙 Encerramento do Dia</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Enviar 1 Foto</label>
                    <input type="file" name="foto" accept="image/*" capture="environment" required class="form-input" onchange="previewFotos(this, 'previewPausar')">
                    <div id="previewPausar" class="foto-preview"></div>
                </div>
                <button type="submit" class="btn-action btn-pausar">Pausar</button>
                <button type="button" onclick="fecharModal('modalPausar')" class="btn-action" style="background: #e5e7eb; color: #374151;">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal Retomar -->
    <div id="modalRetomar" class="modal">
        <div class="modal-content">
            <div class="modal-header">▶️ Retomar Atendimento</div>
            <form action="{{ route('portal-funcionario.atendimento.retomar', $atendimento) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Enviar 1 Foto</label>
                    <input type="file" name="foto" accept="image/*" capture="environment" required class="form-input" onchange="previewFotos(this, 'previewRetomar')">
                    <div id="previewRetomar" class="foto-preview"></div>
                </div>
                <button type="submit" class="btn-action btn-retomar">Retomar</button>
                <button type="button" onclick="fecharModal('modalRetomar')" class="btn-action" style="background: #e5e7eb; color: #374151;">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal Finalizar -->
    <div id="modalFinalizar" class="modal">
        <div class="modal-content">
            <div class="modal-header">✅ Finalizar Atendimento</div>
            <form action="{{ route('portal-funcionario.atendimento.finalizar', $atendimento) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Observações Finais (Opcional)</label>
                    <textarea name="observacao" rows="3" class="form-textarea" placeholder="Descreva o que foi feito..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Enviar 3 Fotos Finais</label>
                    <input type="file" name="fotos[]" accept="image/*" capture="environment" multiple required class="form-input" onchange="previewFotos(this, 'previewFinalizar')">
                    <div id="previewFinalizar" class="foto-preview"></div>
                </div>
                <button type="submit" class="btn-action btn-finalizar">Finalizar</button>
                <button type="button" onclick="fecharModal('modalFinalizar')" class="btn-action" style="background: #e5e7eb; color: #374151;">Cancelar</button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Cronômetro em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const crono = document.querySelector('.cronometro-principal[data-iniciado]');
            
            if (crono) {
                const iniciadoTimestamp = parseInt(crono.dataset.iniciado);
                const tempoBase = parseInt(crono.dataset.tempoBase || 0);
                const display = crono.querySelector('.cronometro-display');
                const pausado = crono.classList.contains('pausado');
                
                function atualizar() {
                    const agora = Math.floor(Date.now() / 1000);
                    const segundosDecorridos = agora - iniciadoTimestamp;
                    const totalSegundos = Math.max(0, tempoBase + segundosDecorridos); // Garante nunca negativo
                    
                    const horas = Math.floor(totalSegundos / 3600);
                    const minutos = Math.floor((totalSegundos % 3600) / 60);
                    const segundos = totalSegundos % 60;
                    
                    display.textContent = 
                        String(horas).padStart(2, '0') + ':' +
                        String(minutos).padStart(2, '0') + ':' +
                        String(segundos).padStart(2, '0');
                }
                
                atualizar();
                setInterval(atualizar, 1000);
            }
        });

        function abrirModalIniciar() {
            document.getElementById('modalIniciar').classList.add('active');
        }

        function abrirModalPausar() {
            document.getElementById('modalPausar').classList.add('active');
        }

        function abrirModalRetomar() {
            document.getElementById('modalRetomar').classList.add('active');
        }

        function abrirModalFinalizar() {
            document.getElementById('modalFinalizar').classList.add('active');
        }

        function fecharModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function previewFotos(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        // Fechar modal ao clicar fora
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
