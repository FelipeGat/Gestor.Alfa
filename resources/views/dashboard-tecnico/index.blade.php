<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 Dashboard Técnico - Monitoramento em Tempo Real
            </h2>
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="timestampAtualizacao">{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Cards de Indicadores -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 mb-10">
                <!-- Agendados Hoje -->
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-blue-500 hover:shadow-xl transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Agendados Hoje</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-agendados">{{ $indicadores['agendados_hoje'] }}</p>
                </div>

                <!-- Em Execução -->
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-green-500 hover:shadow-xl transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Em Execução</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-execucao">{{ $indicadores['em_execucao'] }}</p>
                </div>

                <!-- Em Pausa -->
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-orange-500 hover:shadow-xl transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Em Pausa</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-pausa">{{ $indicadores['em_pausa'] }}</p>
                </div>

                <!-- Finalizados Hoje -->
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-purple-500 hover:shadow-xl transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Finalizados Hoje</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-finalizados">{{ $indicadores['finalizados_hoje'] }}</p>
                </div>

                <!-- Técnicos Ativos -->
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-teal-500 hover:shadow-xl transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Técnicos Ativos</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-tecnicos-ativos">{{ $indicadores['tecnicos_ativos'] }}</p>
                </div>

                <!-- Técnicos em Pausa -->
                <div class="bg-white shadow rounded-xl p-6 border-l-4 border-gray-500 hover:shadow-xl transition-all">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Técnicos em Pausa</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="ind-tecnicos-pausados">{{ $indicadores['tecnicos_pausados'] }}</p>
                </div>
            </div>

            <!-- Painel de Acompanhamento -->
            <div class="bg-white shadow rounded-xl p-6 mb-8">
                <h3 class="text-base font-semibold text-gray-700 mb-6">Acompanhamento em Tempo Real</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" id="tecnicosGrid">
            @forelse($tecnicos as $tecnicoData)
                @php
                    $funcionario = $tecnicoData['funcionario'];
                    $atendimentoAtual = $tecnicoData['atendimento_atual'];
                    $pausaAtiva = $tecnicoData['pausa_ativa'];
                @endphp

                <div class="tecnico-card {{ $atendimentoAtual ? ($atendimentoAtual->em_pausa ? 'status-pausa' : 'status-execucao') : 'status-livre' }}" 
                     data-tecnico-id="{{ $funcionario->id }}">
                    
                    <!-- Header do Card -->
                    <div class="tecnico-header">
                        <div class="tecnico-info">
                            <div class="tecnico-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="tecnico-nome">{{ $funcionario->user->name }}</h3>
                                <p class="tecnico-stats">
                                    {{ $tecnicoData['atendimentos_finalizados_hoje'] }}/{{ $tecnicoData['atendimentos_total_hoje'] }} finalizados
                                </p>
                            </div>
                        </div>

                        @if($atendimentoAtual)
                            @if($atendimentoAtual->em_pausa)
                                <span class="status-badge badge-pausa">
                                    <svg class="badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    EM PAUSA
                                </span>
                            @else
                                <span class="status-badge badge-execucao">
                                    <svg class="badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                    </svg>
                                    EM EXECUÇÃO
                                </span>
                            @endif
                        @else
                            <span class="status-badge badge-livre">
                                <svg class="badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                LIVRE
                            </span>
                        @endif
                    </div>

                    <!-- Conteúdo do Card -->
                    <div class="tecnico-body">
                        @if($atendimentoAtual)
                            <!-- Cliente Atual -->
                            <div class="info-row">
                                <span class="info-label">
                                    <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Cliente:
                                </span>
                                <span class="info-value">{{ $atendimentoAtual->cliente->nome_fantasia ?? 'N/D' }}</span>
                            </div>

                            <!-- Prioridade -->
                            <div class="info-row">
                                <span class="info-label">
                                    <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
                                    </svg>
                                    Prioridade:
                                </span>
                                <span class="prioridade-badge prioridade-{{ $atendimentoAtual->prioridade }}">
                                    {{ strtoupper($atendimentoAtual->prioridade) }}
                                </span>
                            </div>

                            @if($pausaAtiva)
                                <!-- Tipo de Pausa -->
                                <div class="info-row">
                                    <span class="info-label">
                                        <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Tipo de Pausa:
                                    </span>
                                    <span class="pausa-tipo">{{ $pausaAtiva->tipo_pausa_label }}</span>
                                </div>
                            @endif

                            <!-- Horário de Início -->
                            <div class="info-row">
                                <span class="info-label">
                                    <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Início:
                                </span>
                                <span class="info-value">{{ $atendimentoAtual->iniciado_em ? $atendimentoAtual->iniciado_em->format('H:i:s') : 'N/D' }}</span>
                            </div>

                            <!-- Tempo Trabalhado -->
                            <div class="tempo-row">
                                <div class="tempo-item">
                                    <p class="tempo-label">Tempo Trabalhado</p>
                                    <p class="tempo-valor tempo-trabalhado" 
                                       data-segundos="{{ $tecnicoData['total_tempo_trabalhado'] }}"
                                       data-em-execucao="{{ $atendimentoAtual->em_execucao ? 'true' : 'false' }}">
                                        {{ gmdate('H:i:s', $tecnicoData['total_tempo_trabalhado']) }}
                                    </p>
                                </div>
                                <div class="tempo-item">
                                    <p class="tempo-label">Tempo Pausas</p>
                                    <p class="tempo-valor tempo-pausas"
                                       data-segundos="{{ $tecnicoData['total_tempo_pausas'] }}"
                                       data-em-pausa="{{ $pausaAtiva ? 'true' : 'false' }}">
                                        {{ gmdate('H:i:s', $tecnicoData['total_tempo_pausas'] + $tecnicoData['tempo_pausa_atual']) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Botão Ver Detalhes -->
                            <button class="btn-ver-detalhes" onclick="abrirDetalhes({{ $atendimentoAtual->id }})">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver Detalhes Completos
                            </button>
                        @else
                            <div class="tecnico-livre-msg">
                                <svg class="libre-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <p>Sem atendimento em andamento</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p>Nenhum técnico com atendimento hoje</p>
                </div>
            @endforelse
        </div>
    </div>

            <!-- Atendimentos Não Iniciados -->
            @if($atendimentosNaoIniciados->count() > 0)
            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-base font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Atendimentos Agendados Não Iniciados ({{ $atendimentosNaoIniciados->count() }})
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3">Técnico</th>
                                <th class="px-4 py-3">Cliente</th>
                                <th class="px-4 py-3">Assunto</th>
                                <th class="px-4 py-3">Prioridade</th>
                                <th class="px-4 py-3">Agendado Para</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atendimentosNaoIniciados as $atendimento)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $atendimento->funcionario->user->name ?? 'N/D' }}</td>
                                <td class="px-4 py-3">{{ $atendimento->cliente->nome_fantasia ?? 'N/D' }}</td>
                                <td class="px-4 py-3">{{ $atendimento->assunto->nome ?? 'N/D' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $atendimento->prioridade === 'alta' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $atendimento->prioridade === 'media' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $atendimento->prioridade === 'baixa' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ strtoupper($atendimento->prioridade) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $atendimento->data_atendimento ? \Carbon\Carbon::parse($atendimento->data_atendimento)->format('d/m/Y H:i') : 'N/D' }}</td>
                                <td class="px-4 py-3">
                                    @if($atendimento->data_atendimento < now())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ATRASADO</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">AGUARDANDO</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="modalDetalhes" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Detalhes do Atendimento</h3>
                <button onclick="fecharDetalhes()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 overflow-y-auto" id="modalDetalhesConteudo">
                <!-- Conteúdo carregado via AJAX -->
            </div>
        </div>
    </div>

    <style>
    .tecnico-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid #cbd5e1;
        transition: all 0.3s;
    }

    .tecnico-card.status-execucao {
        border-left-color: #059669;
        background: linear-gradient(to right, rgba(5, 150, 105, 0.05) 0%, white 100%);
    }

    .tecnico-card.status-pausa {
        border-left-color: #f59e0b;
        background: linear-gradient(to right, rgba(245, 158, 11, 0.05) 0%, white 100%);
    }

    .tecnico-card.status-livre {
        border-left-color: #cbd5e1;
    }

    .tempo-valor {
        font-family: 'Courier New', monospace;
    }
    </style>

    <script>
    // Atualizar cronômetros a cada segundo
    setInterval(function() {
        // Atualizar tempos trabalhados
        document.querySelectorAll('.tempo-trabalhado[data-em-execucao="true"]').forEach(el => {
            let segundos = parseInt(el.dataset.segundos) + 1;
            el.dataset.segundos = segundos;
            el.textContent = formatarTempo(segundos);
        });
        
        // Atualizar tempos de pausa
        document.querySelectorAll('.tempo-pausas[data-em-pausa="true"]').forEach(el => {
            let segundos = parseInt(el.dataset.segundos) + 1;
            el.dataset.segundos = segundos;
            el.textContent = formatarTempo(segundos);
        });
    }, 1000);

    // Atualizar dados via AJAX a cada 30 segundos
    setInterval(function() {
        fetch('{{ route('dashboard.tecnico.atualizar') }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar timestamp
                    document.getElementById('timestampAtualizacao').textContent = data.timestamp;
                    
                    // Atualizar indicadores
                    document.getElementById('ind-agendados').textContent = data.indicadores.agendados_hoje;
                    document.getElementById('ind-execucao').textContent = data.indicadores.em_execucao;
                    document.getElementById('ind-pausa').textContent = data.indicadores.em_pausa;
                    document.getElementById('ind-finalizados').textContent = data.indicadores.finalizados_hoje;
                    document.getElementById('ind-tecnicos-ativos').textContent = data.indicadores.tecnicos_ativos;
                    document.getElementById('ind-tecnicos-pausados').textContent = data.indicadores.tecnicos_pausados;
                    
                    // Atualizar status dos técnicos
                    data.tecnicos.forEach(tecnico => {
                        let card = document.querySelector(`.tecnico-card[data-tecnico-id="${tecnico.funcionario_id}"]`);
                        if (card) {
                            // Atualizar classes de status
                            card.classList.remove('status-execucao', 'status-pausa', 'status-livre');
                            if (tecnico.status === 'execucao') {
                                card.classList.add('status-execucao');
                            } else if (tecnico.status === 'pausa') {
                                card.classList.add('status-pausa');
                            } else {
                                card.classList.add('status-livre');
                            }
                        }
                    });
                }
            })
            .catch(error => console.error('Erro ao atualizar:', error));
    }, 30000); // 30 segundos

    // Abrir modal de detalhes
    function abrirDetalhes(atendimentoId) {
        const modal = document.getElementById('modalDetalhes');
        const conteudo = document.getElementById('modalDetalhesConteudo');
        
        conteudo.innerHTML = '<div class="text-center py-10"><p>Carregando...</p></div>';
        modal.style.display = 'flex';
        
        fetch(`/dashboard-tecnico/atendimento/${atendimentoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    conteudo.innerHTML = data.html;
                } else {
                    conteudo.innerHTML = '<p class="text-center text-red-500">Erro ao carregar detalhes</p>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                conteudo.innerHTML = '<p class="text-center text-red-500">Erro ao carregar detalhes</p>';
            });
    }

    // Fechar modal
    function fecharDetalhes() {
        document.getElementById('modalDetalhes').style.display = 'none';
    }

    // Fechar modal ao clicar fora
    document.getElementById('modalDetalhes')?.addEventListener('click', function(e) {
        if (e.target === this) {
            fecharDetalhes();
        }
    });

    // Função auxiliar para formatar tempo
    function formatarTempo(segundos) {
        const h = Math.floor(segundos / 3600);
        const m = Math.floor((segundos % 3600) / 60);
        const s = segundos % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }
    </script>
</x-app-layout>
