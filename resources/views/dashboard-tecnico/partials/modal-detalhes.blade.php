<div class="detalhes-atendimento">
    <!-- Cabeçalho do Atendimento -->
    <div class="detalhes-header">
        <div class="header-info">
            <h4 class="cliente-nome">
                <svg class="icon-building" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ $atendimento->cliente->nome_fantasia ?? 'Cliente não informado' }}
            </h4>
            <p class="empresa-nome">{{ $atendimento->empresa->nome_fantasia ?? 'N/D' }}</p>
        </div>
        <div class="header-badges">
            <span class="prioridade-badge prioridade-{{ $atendimento->prioridade }}">
                {{ strtoupper($atendimento->prioridade) }}
            </span>
            @if($atendimento->status_atual === 'em_atendimento')
                @if($atendimento->em_pausa)
                    <span class="status-badge badge-pausa">EM PAUSA</span>
                @else
                    <span class="status-badge badge-execucao">EM EXECUÇÃO</span>
                @endif
            @elseif($atendimento->status_atual === 'finalizacao')
                <span class="status-badge badge-finalizacao">AGUARDANDO APROVAÇÃO</span>
            @elseif($atendimento->status_atual === 'concluido')
                <span class="status-badge badge-concluido">CONCLUÍDO</span>
            @else
                <span class="status-badge badge-livre">{{ strtoupper($atendimento->status_atual) }}</span>
            @endif
        </div>
    </div>

    <!-- Informações Gerais -->
    <div class="detalhes-section">
        <h5 class="section-subtitle">
            <svg class="icon-info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Informações Gerais
        </h5>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-item-label">Técnico Responsável:</span>
                <span class="info-item-value">{{ $atendimento->funcionario->user->name ?? 'N/D' }}</span>
            </div>
            <div class="info-item">
                <span class="info-item-label">Assunto:</span>
                <span class="info-item-value">{{ $atendimento->assunto->nome ?? 'N/D' }}</span>
            </div>
            <div class="info-item">
                <span class="info-item-label">Data Agendada:</span>
                <span class="info-item-value">
                    {{ $atendimento->data_atendimento ? \Carbon\Carbon::parse($atendimento->data_atendimento)->format('d/m/Y H:i') : 'N/D' }}
                </span>
            </div>
            @if($atendimento->iniciado_em)
            <div class="info-item">
                <span class="info-item-label">Iniciado Em:</span>
                <span class="info-item-value">{{ $atendimento->iniciado_em->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="info-item">
                <span class="info-item-label">Iniciado Por:</span>
                <span class="info-item-value">{{ $atendimento->iniciadoPor->name ?? 'N/D' }}</span>
            </div>
            @endif
            @if($atendimento->finalizado_em)
            <div class="info-item">
                <span class="info-item-label">Finalizado Em:</span>
                <span class="info-item-value">{{ $atendimento->finalizado_em->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="info-item">
                <span class="info-item-label">Finalizado Por:</span>
                <span class="info-item-value">{{ $atendimento->finalizadoPor->name ?? 'N/D' }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Tempos -->
    @if($atendimento->iniciado_em)
    <div class="detalhes-section">
        <h5 class="section-subtitle">
            <svg class="icon-info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Tempos
        </h5>
        <div class="tempos-grid">
            <div class="tempo-card tempo-execucao">
                <p class="tempo-card-label">Tempo Trabalhado</p>
                <p class="tempo-card-valor">{{ $atendimento->tempo_execucao_formatado }}</p>
            </div>
            <div class="tempo-card tempo-pausa">
                <p class="tempo-card-label">Tempo em Pausas</p>
                <p class="tempo-card-valor">{{ $atendimento->tempo_pausa_formatado }}</p>
            </div>
            @if($atendimento->iniciado_em && $atendimento->finalizado_em)
            <div class="tempo-card tempo-total">
                <p class="tempo-card-label">Tempo Total</p>
                <p class="tempo-card-valor">
                    @php
                        $totalSegundos = $atendimento->iniciado_em->diffInSeconds($atendimento->finalizado_em);
                        echo gmdate('H:i:s', $totalSegundos);
                    @endphp
                </p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Fotos de Início -->
    @if($atendimento->iniciado_em)
    <div class="detalhes-section">
        <h5 class="section-subtitle">
            <svg class="icon-info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Fotos de Início ({{ $atendimento->foto_inicio_1_path ? 1 : 0 }}{{ $atendimento->foto_inicio_2_path ? '+1' : '' }}{{ $atendimento->foto_inicio_3_path ? '+1' : '' }})
        </h5>
        <div class="fotos-grid">
            @if($atendimento->foto_inicio_1_path)
                <a href="{{ asset('storage/' . $atendimento->foto_inicio_1_path) }}" target="_blank" class="foto-link">
                    <img src="{{ asset('storage/' . $atendimento->foto_inicio_1_path) }}" alt="Foto 1" class="foto-thumbnail">
                    <span class="foto-label">Foto 1</span>
                </a>
            @endif
            @if($atendimento->foto_inicio_2_path)
                <a href="{{ asset('storage/' . $atendimento->foto_inicio_2_path) }}" target="_blank" class="foto-link">
                    <img src="{{ asset('storage/' . $atendimento->foto_inicio_2_path) }}" alt="Foto 2" class="foto-thumbnail">
                    <span class="foto-label">Foto 2</span>
                </a>
            @endif
            @if($atendimento->foto_inicio_3_path)
                <a href="{{ asset('storage/' . $atendimento->foto_inicio_3_path) }}" target="_blank" class="foto-link">
                    <img src="{{ asset('storage/' . $atendimento->foto_inicio_3_path) }}" alt="Foto 3" class="foto-thumbnail">
                    <span class="foto-label">Foto 3</span>
                </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Histórico de Pausas -->
    @if($atendimento->pausas->count() > 0)
    <div class="detalhes-section">
        <h5 class="section-subtitle">
            <svg class="icon-info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Histórico de Pausas ({{ $atendimento->pausas->count() }})
        </h5>
        <div class="pausas-timeline">
            @foreach($atendimento->pausas as $pausa)
            <div class="pausa-item">
                <div class="pausa-header">
                    <div class="pausa-info">
                        <span class="pausa-tipo-badge">{{ $pausa->tipo_pausa_label }}</span>
                        <span class="pausa-duracao">
                            <svg class="icon-clock-small" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ gmdate('H:i:s', $pausa->tempo_segundos ?? 0) }}
                        </span>
                    </div>
                    <span class="pausa-status {{ $pausa->encerrada_em ? 'pausa-encerrada' : 'pausa-ativa' }}">
                        {{ $pausa->encerrada_em ? 'Encerrada' : 'Em Andamento' }}
                    </span>
                </div>
                
                <div class="pausa-detalhes">
                    <div class="pausa-detail-item">
                        <span class="detail-label">Pausado por:</span>
                        <span class="detail-value">{{ $pausa->user->name ?? 'N/D' }}</span>
                    </div>
                    <div class="pausa-detail-item">
                        <span class="detail-label">Iniciada em:</span>
                        <span class="detail-value">{{ $pausa->iniciada_em ? $pausa->iniciada_em->format('d/m/Y H:i:s') : 'N/D' }}</span>
                    </div>
                    @if($pausa->encerrada_em)
                    <div class="pausa-detail-item">
                        <span class="detail-label">Retomado por:</span>
                        <span class="detail-value">{{ $pausa->retomadoPor->name ?? 'N/D' }}</span>
                    </div>
                    <div class="pausa-detail-item">
                        <span class="detail-label">Encerrada em:</span>
                        <span class="detail-value">{{ $pausa->encerrada_em->format('d/m/Y H:i:s') }}</span>
                    </div>
                    @endif
                </div>

                <!-- Fotos da Pausa -->
                @if($pausa->foto_inicio_path || $pausa->foto_retorno_path)
                <div class="pausa-fotos">
                    @if($pausa->foto_inicio_path)
                    <a href="{{ asset('storage/' . $pausa->foto_inicio_path) }}" target="_blank" class="pausa-foto-link">
                        <img src="{{ asset('storage/' . $pausa->foto_inicio_path) }}" alt="Foto Pausa" class="pausa-foto-thumb">
                        <span class="pausa-foto-label">Início da Pausa</span>
                    </a>
                    @endif
                    @if($pausa->foto_retorno_path)
                    <a href="{{ asset('storage/' . $pausa->foto_retorno_path) }}" target="_blank" class="pausa-foto-link">
                        <img src="{{ asset('storage/' . $pausa->foto_retorno_path) }}" alt="Foto Retorno" class="pausa-foto-thumb">
                        <span class="pausa-foto-label">Retorno</span>
                    </a>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Fotos de Finalização -->
    @if($atendimento->finalizado_em)
    <div class="detalhes-section">
        <h5 class="section-subtitle">
            <svg class="icon-info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Fotos de Finalização
        </h5>
        <div class="fotos-grid">
            @if($atendimento->foto_finalizacao_1_path)
                <a href="{{ asset('storage/' . $atendimento->foto_finalizacao_1_path) }}" target="_blank" class="foto-link">
                    <img src="{{ asset('storage/' . $atendimento->foto_finalizacao_1_path) }}" alt="Finalização 1" class="foto-thumbnail">
                    <span class="foto-label">Finalização 1</span>
                </a>
            @endif
            @if($atendimento->foto_finalizacao_2_path)
                <a href="{{ asset('storage/' . $atendimento->foto_finalizacao_2_path) }}" target="_blank" class="foto-link">
                    <img src="{{ asset('storage/' . $atendimento->foto_finalizacao_2_path) }}" alt="Finalização 2" class="foto-thumbnail">
                    <span class="foto-label">Finalização 2</span>
                </a>
            @endif
            @if($atendimento->foto_finalizacao_3_path)
                <a href="{{ asset('storage/' . $atendimento->foto_finalizacao_3_path) }}" target="_blank" class="foto-link">
                    <img src="{{ asset('storage/' . $atendimento->foto_finalizacao_3_path) }}" alt="Finalização 3" class="foto-thumbnail">
                    <span class="foto-label">Finalização 3</span>
                </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Observações de Finalização -->
    @if($atendimento->observacoes_finalizacao)
    <div class="detalhes-section">
        <h5 class="section-subtitle">
            <svg class="icon-info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Observações de Finalização
        </h5>
        <div class="observacoes-box">
            {{ $atendimento->observacoes_finalizacao }}
        </div>
    </div>
    @endif
</div>

<style>
.detalhes-atendimento {
    font-size: 14px;
}

.detalhes-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 20px;
    margin-bottom: 24px;
    border-bottom: 2px solid #e5e7eb;
}

.cliente-nome {
    font-size: 20px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.icon-building {
    width: 24px;
    height: 24px;
    color: #2563eb;
}

.empresa-nome {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
}

.header-badges {
    display: flex;
    gap: 8px;
}

.badge-finalizacao {
    background: #fef3c7;
    color: #b45309;
}

.badge-concluido {
    background: #d1fae5;
    color: #047857;
}

.detalhes-section {
    margin-bottom: 32px;
}

.section-subtitle {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.icon-info {
    width: 20px;
    height: 20px;
    color: #2563eb;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
}

.info-item {
    background: #f9fafb;
    padding: 12px 16px;
    border-radius: 8px;
    border-left: 3px solid #2563eb;
}

.info-item-label {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
    font-weight: 500;
}

.info-item-value {
    display: block;
    font-size: 14px;
    color: #111827;
    font-weight: 600;
}

.tempos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.tempo-card {
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.tempo-execucao {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
}

.tempo-pausa {
    background: linear-gradient(135deg, #fed7aa 0%, #fcd34d 100%);
}

.tempo-total {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
}

.tempo-card-label {
    font-size: 12px;
    color: #374151;
    margin: 0 0 8px 0;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tempo-card-valor {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin: 0;
    font-family: 'Courier New', monospace;
}

.fotos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
}

.foto-link {
    display: block;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
}

.foto-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.foto-thumbnail {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.foto-label {
    display: block;
    padding: 8px;
    background: #f9fafb;
    text-align: center;
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.pausas-timeline {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.pausa-item {
    background: #f9fafb;
    border-radius: 10px;
    padding: 16px;
    border-left: 4px solid #f59e0b;
}

.pausa-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.pausa-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pausa-tipo-badge {
    padding: 6px 12px;
    background: #fef3c7;
    color: #b45309;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.pausa-duracao {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    font-family: 'Courier New', monospace;
}

.icon-clock-small {
    width: 16px;
    height: 16px;
}

.pausa-status {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.pausa-encerrada {
    background: #d1fae5;
    color: #047857;
}

.pausa-ativa {
    background: #fed7aa;
    color: #b45309;
}

.pausa-detalhes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 12px;
}

.pausa-detail-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.detail-label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
}

.detail-value {
    font-size: 13px;
    color: #111827;
    font-weight: 600;
}

.pausa-fotos {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
}

.pausa-foto-link {
    display: block;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    transition: transform 0.2s;
    text-decoration: none;
}

.pausa-foto-link:hover {
    transform: scale(1.05);
}

.pausa-foto-thumb {
    width: 100%;
    height: 100px;
    object-fit: cover;
}

.pausa-foto-label {
    display: block;
    padding: 6px;
    background: white;
    text-align: center;
    font-size: 10px;
    color: #6b7280;
    font-weight: 500;
}

.observacoes-box {
    background: #f9fafb;
    padding: 16px;
    border-radius: 8px;
    border-left: 3px solid #2563eb;
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
    white-space: pre-wrap;
}
</style>
