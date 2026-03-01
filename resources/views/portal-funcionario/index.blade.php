<x-portal-funcionario-layout>
    <div class="py-6">
        <!-- Boas-vindas -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Olá, {{ Auth::user()->name }}!
                </h1>
                <p class="text-gray-600 mt-1">Selecione uma opção abaixo</p>
            </div>

            <!-- Estatísticas (KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <x-kpi-card 
                    title="Chamados Abertos" 
                    :value="$totalAbertos" 
                    color="blue"
                    iconPosition="right"
                >
                    <div class="mt-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                        Em aberto
                    </div>
                </x-kpi-card>

                @if($temPausado && $totalEmAtendimento > 0)
                <x-kpi-card 
                    title="Em Atendimento" 
                    :value="$totalEmAtendimento" 
                    color="yellow"
                    iconPosition="right"
                >
                    <div class="mt-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        Em execução
                    </div>
                </x-kpi-card>
                @endif

                <x-kpi-card 
                    title="Finalizados" 
                    :value="$totalFinalizados" 
                    color="green"
                    iconPosition="right"
                >
                    <div class="mt-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Concluídos
                    </div>
                </x-kpi-card>
            </div>

            <!-- Botões de Acesso Rápido -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-4xl mx-auto">
                <!-- Registro de Ponto -->
                <x-card href="{{ route('portal-funcionario.ponto') }}" clickable class="group">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center group-hover:bg-teal-100 transition-colors">
                            <svg class="w-8 h-8 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">Registro de Ponto</h3>
                            <p class="text-sm text-gray-600 mt-0.5">Registrar entrada, almoço e saída</p>
                            <x-badge type="{{ ($pontoStatus['concluido'] ?? false) ? 'success' : 'warning' }}" size="xs" class="mt-1.5">
                                {{ $pontoStatus['label'] ?? 'Pendente hoje' }}
                            </x-badge>
                            @if(isset($pontoStatus['detalhe']))
                                <p class="text-xs text-gray-500 mt-1">{{ $pontoStatus['detalhe'] }}</p>
                            @endif
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-[#3f9cae] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </x-card>

                <!-- Painel de Chamados -->
                <x-card href="{{ route('portal-funcionario.chamados') }}" clickable class="group">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center group-hover:bg-teal-100 transition-colors">
                            <svg class="w-8 h-8 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">Painel de Chamados</h3>
                            <p class="text-sm text-gray-600 mt-0.5">Gerencie seus atendimentos</p>
                            <x-badge type="primary" size="xs" class="mt-1.5">
                                {{ $totalAbertos }} na fila
                            </x-badge>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-[#3f9cae] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </x-card>

                <!-- Agenda Técnica -->
                <x-card href="{{ route('portal-funcionario.agenda') }}" clickable class="group">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center group-hover:bg-teal-100 transition-colors">
                            <svg class="w-8 h-8 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">Agenda Técnica</h3>
                            <p class="text-sm text-gray-600 mt-0.5">Visualize sua agenda</p>
                            <x-badge type="primary" size="xs" class="mt-1.5">
                                Calendário
                            </x-badge>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-[#3f9cae] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </x-card>

                <!-- Documentos -->
                <x-card href="{{ route('portal-funcionario.documentos') }}" clickable class="group">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center group-hover:bg-teal-100 transition-colors">
                            <svg class="w-8 h-8 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">Documentos</h3>
                            <p class="text-sm text-gray-600 mt-0.5">Em breve</p>
                            <x-badge type="default" size="xs" class="mt-1.5">
                                Indisponível
                            </x-badge>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-[#3f9cae] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-portal-funcionario-layout>
