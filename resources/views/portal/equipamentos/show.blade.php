<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    {{ $equipamento->nome }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $cliente->nome_exibicao }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.equipamento.chamado', $equipamento->qrcode_token) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="hidden sm:inline">Abrir Chamado</span>
                </a>
                <a href="{{ route('portal.equipamentos.qrcode', $equipamento->id) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span class="hidden sm:inline">QR Code</span>
                </a>
                <a href="{{ route('portal.equipamentos.lista') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="hidden sm:inline">Voltar</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="portal-wrapper">

        <!-- Dados do Equipamento -->
        <div class="portal-section">
            <div class="portal-table-card">
                <div class="portal-table-header">
                    <h3 class="portal-table-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                        Dados do Equipamento
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <p class="portal-mobile-card-label">Nome</p>
                            <p class="portal-font-semibold text-gray-900">{{ $equipamento->nome }}</p>
                        </div>
                        <div>
                            <p class="portal-mobile-card-label">Modelo</p>
                            <p class="portal-font-semibold text-gray-900">{{ $equipamento->modelo ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="portal-mobile-card-label">Fabricante</p>
                            <p class="portal-font-semibold text-gray-900">{{ $equipamento->fabricante ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="portal-mobile-card-label">Número de Série</p>
                            <p class="portal-font-semibold text-gray-900">{{ $equipamento->numero_serie ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="portal-mobile-card-label">Setor</p>
                            <p class="portal-font-semibold text-gray-900">{{ $equipamento->setor->nome ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="portal-mobile-card-label">Responsável</p>
                            <p class="portal-font-semibold text-gray-900">{{ $equipamento->responsavel->nome ?? 'Não informado' }}</p>
                        </div>
                    </div>

                    @if($equipamento->observacoes)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="portal-mobile-card-label">Observações</p>
                        <p class="text-gray-700 mt-1">{{ $equipamento->observacoes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Status de Manutenção e Limpeza -->
        <div class="portal-section">
            <h2 class="portal-section-title">
                <svg class="w-6 h-6 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Status de Manutenção e Limpeza
            </h2>

            <div class="portal-stats-grid">
                <!-- Status Manutenção -->
                <div class="portal-stat-card portal-stat-card--blue">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Manutenção</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $equipamento->status_manutencao['classe'] }}">
                            {{ $equipamento->status_manutencao['mensagem'] }}
                        </span>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="portal-text-muted">Última:</span>
                            <span class="portal-font-semibold">{{ $equipamento->ultima_manutencao?->format('d/m/Y') ?? 'Nunca' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="portal-text-muted">Próxima:</span>
                            <span class="portal-font-semibold">{{ $equipamento->proxima_manutencao?->format('d/m/Y') ?? 'Não calculada' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="portal-text-muted">Periodicidade:</span>
                            <span class="portal-font-semibold">{{ $equipamento->periodicidade_manutencao_meses }} meses</span>
                        </div>
                    </div>

                    <!-- Botão Registrar Manutenção -->
                    <x-button 
                        type="button" 
                        variant="primary" 
                        size="md" 
                        class="w-full justify-center"
                        onclick="document.getElementById('modal-manutencao').classList.remove('hidden')"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Registrar Manutenção
                    </x-button>
                </div>

                <!-- Status Limpeza -->
                <div class="portal-stat-card portal-stat-card--green">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Limpeza</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $equipamento->status_limpeza['classe'] }}">
                            {{ $equipamento->status_limpeza['mensagem'] }}
                        </span>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="portal-text-muted">Última:</span>
                            <span class="portal-font-semibold">{{ $equipamento->ultima_limpeza?->format('d/m/Y') ?? 'Nunca' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="portal-text-muted">Próxima:</span>
                            <span class="portal-font-semibold">{{ $equipamento->proxima_limpeza?->format('d/m/Y') ?? 'Não calculada' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="portal-text-muted">Periodicidade:</span>
                            <span class="portal-font-semibold">{{ $equipamento->periodicidade_limpeza_meses }} {{ $equipamento->periodicidade_limpeza_meses == 1 ? 'mês' : 'meses' }}</span>
                        </div>
                    </div>

                    <!-- Botão Registrar Limpeza -->
                    <x-button 
                        type="button" 
                        variant="primary" 
                        size="md" 
                        class="w-full justify-center"
                        onclick="document.getElementById('modal-limpeza').classList.remove('hidden')"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Registrar Limpeza
                    </x-button>
                </div>
            </div>
        </div>

        <!-- Histórico de Manutenções -->
        <div class="portal-section">
            <div class="portal-table-card">
                <div class="portal-table-header">
                    <h3 class="portal-table-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2zM5 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5zm0 8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2zm8-8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zm0 8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Histórico de Manutenções
                    </h3>
                </div>

                @if($equipamento->manutencoes->count() > 0)
                <div class="portal-table-wrapper">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>Realizado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipamento->manutencoes as $manutencao)
                            <tr>
                                <td class="portal-font-semibold">
                                    {{ $manutencao->data->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if($manutencao->tipo === 'preventiva')
                                    <span class="portal-badge portal-badge--success">Preventiva</span>
                                    @elseif($manutencao->tipo === 'correctiva')
                                    <span class="portal-badge portal-badge--info">Corretiva</span>
                                    @else
                                    <span class="portal-badge portal-badge--danger">Emergencial</span>
                                    @endif
                                </td>
                                <td>{{ $manutencao->descricao ?? '—' }}</td>
                                <td>{{ $manutencao->realizado_por ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="portal-empty-state">
                    <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2zM5 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5zm0 8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2zm8-8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zm0 8a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <p class="portal-empty-state-title">Nenhuma manutenção registrada.</p>
                    <p class="portal-empty-state-text">
                        As manutenções realizadas aparecerão aqui.
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Histórico de Limpezas -->
        <div class="portal-section">
            <div class="portal-table-card">
                <div class="portal-table-header">
                    <h3 class="portal-table-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Histórico de Limpezas
                    </h3>
                </div>

                @if($equipamento->limpezas->count() > 0)
                <div class="portal-table-wrapper">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Realizado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipamento->limpezas as $limpeza)
                            <tr>
                                <td class="portal-font-semibold">
                                    {{ $limpeza->data->format('d/m/Y') }}
                                </td>
                                <td>{{ $limpeza->descricao ?? '—' }}</td>
                                <td>{{ $limpeza->realizado_por ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="portal-empty-state">
                    <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <p class="portal-empty-state-title">Nenhuma limpeza registrada.</p>
                    <p class="portal-empty-state-text">
                        As limpezas realizadas aparecerão aqui.
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="portal-section">
            <div class="portal-table-card">
                <div class="portal-table-header">
                    <h3 class="portal-table-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        QR Code para Abrir Chamado
                    </h3>
                </div>
                <div class="p-6 text-center">
                    <p class="portal-text-muted mb-6">
                        Escaneie o QR Code abaixo para abrir rapidamente um chamado para este equipamento.
                    </p>
                    <div class="flex justify-center mb-4">
                        <img src="https://quickchart.io/qr?size=200x200&text={{ urlencode(route('portal.equipamento.chamado', $equipamento->qrcode_token)) }}"
                             alt="QR Code"
                             class="w-48 h-48 border-4 border-[#3f9cae] rounded-lg shadow-md">
                    </div>
                    <p class="portal-text-sm portal-text-muted">
                        Ao escanear, você será direcionado para abrir um chamado vinculado a este equipamento.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Registro Manutenção -->
    <div id="modal-manutencao" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('modal-manutencao').classList.add('hidden')"></div>

            <div class="relative bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Registrar Manutenção</h3>
                    <button type="button" onclick="document.getElementById('modal-manutencao').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('portal.equipamentos.manutencao.store', $equipamento->id) }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Data</label>
                            <input type="date" name="data" value="{{ date('Y-m-d') }}" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#3f9cae] focus:border-[#3f9cae]">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" required class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#3f9cae] focus:border-[#3f9cae]">
                                <option value="preventiva">Preventiva</option>
                                <option value="correctiva">Corretiva</option>
                                <option value="emergencial">Emergencial</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Descrição</label>
                            <textarea name="descricao" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#3f9cae] focus:border-[#3f9cae]"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Realizado por</label>
                            <input type="text" name="realizado_por"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#3f9cae] focus:border-[#3f9cae]">
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('modal-manutencao').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white font-semibold rounded-lg transition-all">
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Registro Limpeza -->
    <div id="modal-limpeza" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('modal-limpeza').classList.add('hidden')"></div>

            <div class="relative bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Registrar Limpeza</h3>
                    <button type="button" onclick="document.getElementById('modal-limpeza').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('portal.equipamentos.limpeza.store', $equipamento->id) }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Data</label>
                            <input type="date" name="data" value="{{ date('Y-m-d') }}" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#3f9cae] focus:border-[#3f9cae]">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Descrição</label>
                            <textarea name="descricao" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#3f9cae] focus:border-[#3f9cae]"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Realizado por</label>
                            <input type="text" name="realizado_por"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#3f9cae] focus:border-[#3f9cae]">
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('modal-limpeza').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white font-semibold rounded-lg transition-all">
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
