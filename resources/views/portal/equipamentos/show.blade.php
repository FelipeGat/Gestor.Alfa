<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
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
                    Abrir Chamado
                </a>
                <a href="{{ route('portal.equipamentos.qrcode', $equipamento->id) }}" 
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    QR Code
                </a>
                <a href="{{ route('portal.equipamentos.lista') }}" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-green-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-8">

            <!-- Dados do Equipamento -->
            <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    Dados do Equipamento
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Nome</p>
                        <p class="text-gray-900 font-medium">{{ $equipamento->nome }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Modelo</p>
                        <p class="text-gray-900 font-medium">{{ $equipamento->modelo ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Fabricante</p>
                        <p class="text-gray-900 font-medium">{{ $equipamento->fabricante ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Número de Série</p>
                        <p class="text-gray-900 font-medium">{{ $equipamento->numero_serie ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Setor</p>
                        <p class="text-gray-900 font-medium">{{ $equipamento->setor->nome ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Responsável</p>
                        <p class="text-gray-900 font-medium">{{ $equipamento->responsavel->nome ?? 'Não informado' }}</p>
                    </div>
                </div>

                @if($equipamento->observacoes)
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Observações</p>
                    <p class="text-gray-600">{{ $equipamento->observacoes }}</p>
                </div>
                @endif
            </div>

            <!-- Status de Manutenção e Limpeza -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status Manutenção -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Manutenção</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $equipamento->status_manutencao['classe'] }}">
                            {{ $equipamento->status_manutencao['mensagem'] }}
                        </span>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Última:</span>
                            <span class="font-medium">{{ $equipamento->ultima_manutencao?->format('d/m/Y') ?? 'Nunca' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Próxima:</span>
                            <span class="font-medium">{{ $equipamento->proxima_manutencao?->format('d/m/Y') ?? 'Não calculada' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Periodicidade:</span>
                            <span class="font-medium">{{ $equipamento->periodicidade_manutencao_meses }} meses</span>
                        </div>
                    </div>

                    <!-- Botão Registrar Manutenção -->
                    <button type="button" 
                        onclick="document.getElementById('modal-manutencao').classList.remove('hidden')"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Registrar Manutenção
                    </button>
                </div>

                <!-- Status Limpeza -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Limpeza</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $equipamento->status_limpeza['classe'] }}">
                            {{ $equipamento->status_limpeza['mensagem'] }}
                        </span>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Última:</span>
                            <span class="font-medium">{{ $equipamento->ultima_limpeza?->format('d/m/Y') ?? 'Nunca' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Próxima:</span>
                            <span class="font-medium">{{ $equipamento->proxima_limpeza?->format('d/m/Y') ?? 'Não calculada' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Periodicidade:</span>
                            <span class="font-medium">{{ $equipamento->periodicidade_limpeza_meses }} {{ $equipamento->periodicidade_limpeza_meses == 1 ? 'mês' : 'meses' }}</span>
                        </div>
                    </div>

                    <!-- Botão Registrar Limpeza -->
                    <button type="button" 
                        onclick="document.getElementById('modal-limpeza').classList.remove('hidden')"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Registrar Limpeza
                    </button>
                </div>
            </div>

            <!-- Histórico de Manutenções -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Histórico de Manutenções</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Realizado por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($equipamento->manutencoes as $manutencao)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $manutencao->data->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($manutencao->tipo === 'preventiva')
                                    <span class="inline-flex px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Preventiva</span>
                                    @elseif($manutencao->tipo === 'correctiva')
                                    <span class="inline-flex px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">Corretiva</span>
                                    @else
                                    <span class="inline-flex px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Emergencial</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $manutencao->descricao ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $manutencao->realizado_por ?? '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    Nenhum registro de manutenção encontrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Histórico de Limpezas -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Histórico de Limpezas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Realizado por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($equipamento->limpezas as $limpeza)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $limpeza->data->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $limpeza->descricao ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $limpeza->realizado_por ?? '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    Nenhum registro de limpeza encontrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- QR Code Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    QR Code para Abrir Chamado
                </h3>
                <p class="text-gray-600 mb-6">
                    Escaneie o QR Code abaixo para abrir rapidamente um chamado para este equipamento.
                </p>
                <div class="flex justify-center">
                    <img src="https://quickchart.io/qr?size=200x200&text={{ urlencode(route('portal.equipamento.chamado', $equipamento->qrcode_token)) }}" 
                         alt="QR Code" 
                         class="w-48 h-48 border-2 border-gray-200 rounded-lg">
                </div>
                <p class="text-center text-sm text-gray-500 mt-4">
                    Ao escanear, você será direcionado para abrir um chamado vinculado a este equipamento.
                </p>
            </div>

        </div>
    </div>

    <!-- Modal Registro Manutenção -->
    <div id="modal-manutencao" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('modal-manutencao').classList.add('hidden')"></div>
            
            <div class="relative bg-white rounded-2xl shadow-xl p-8 max-w-md w-full">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Registrar Manutenção</h3>
                
                <form action="{{ route('portal.equipamentos.manutencao.store', $equipamento->id) }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                            <input type="date" name="data" value="{{ date('Y-m-d') }}" required
                                class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" required class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="preventiva">Preventiva</option>
                                <option value="correctiva">Corretiva</option>
                                <option value="emergencial">Emergencial</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <textarea name="descricao" rows="3" class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Realizado por</label>
                            <input type="text" name="realizado_por" 
                                class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('modal-manutencao').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all">
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
            
            <div class="relative bg-white rounded-2xl shadow-xl p-8 max-w-md w-full">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Registrar Limpeza</h3>
                
                <form action="{{ route('portal.equipamentos.limpeza.store', $equipamento->id) }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                            <input type="date" name="data" value="{{ date('Y-m-d') }}" required
                                class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <textarea name="descricao" rows="3" class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Realizado por</label>
                            <input type="text" name="realizado_por" 
                                class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('modal-limpeza').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all">
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
