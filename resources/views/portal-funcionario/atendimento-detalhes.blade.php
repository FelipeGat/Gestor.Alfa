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

        <!-- Cronômetro -->
        @if($atendimento->status_atual === 'em_atendimento')
        <div class="cronometro-principal {{ $atendimento->em_pausa ? 'pausado' : '' }}" 
             data-iniciado="{{ $atendimento->iniciado_em->timestamp }}" 
             data-tempo-base="{{ $atendimento->tempo_execucao_segundos }}">
            <div class="cronometro-label">
                @if($atendimento->em_pausa)
                    ⏸️ PAUSADO
                @else
                    ⏱️ TEMPO DE EXECUÇÃO
                @endif
            </div>
            <div class="cronometro-display">00:00:00</div>
        </div>
        @endif

        @if($atendimento->finalizado_em)
        <div class="info-card">
            <div class="info-label">Tempo Total de Execução</div>
            <div class="info-value" style="font-size: 2rem; color: #10b981;">
                ⏱️ {{ $atendimento->tempo_execucao_formatado }}
            </div>
            @if($atendimento->tempo_pausa_segundos > 0)
            <div class="info-label" style="margin-top: 0.5rem;">Tempo de Pausas</div>
            <div class="info-value" style="color: #f59e0b;">
                {{ $atendimento->tempo_pausa_formatado }}
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
        @if($atendimento->pausas->count() > 0)
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

            @if($atendimento->status_atual === 'em_atendimento' && $atendimento->em_execucao)
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
                
                if (!pausado) {
                    function atualizar() {
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
                    
                    atualizar();
                    setInterval(atualizar, 1000);
                } else {
                    const horas = Math.floor(tempoBase / 3600);
                    const minutos = Math.floor((tempoBase % 3600) / 60);
                    const segundos = tempoBase % 60;
                    
                    display.textContent = 
                        String(horas).padStart(2, '0') + ':' +
                        String(minutos).padStart(2, '0') + ':' +
                        String(segundos).padStart(2, '0');
                }
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
