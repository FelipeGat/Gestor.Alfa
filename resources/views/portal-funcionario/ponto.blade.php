<x-portal-funcionario-layout>
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('portal-funcionario.index') }}" class="hover:text-[#3f9cae]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </a>
            <span>/</span>
            <span class="font-medium text-gray-900">Ponto</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Card de Registro de Hoje -->
        <x-card>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Registro de Hoje ({{ now()->format('d/m/Y') }})</h3>
                <p class="text-sm text-gray-600 mt-1">Próximo evento esperado: <span class="font-semibold text-[#3f9cae]">{{ $eventos[$proximoEvento] ?? '—' }}</span></p>
            </div>

            @if(($bloqueioMinutosRestantes ?? 0) > 0)
            <x-badge type="warning" class="mb-4">
                Aguarde {{ $bloqueioMinutosRestantes }} minuto(s) para registrar o próximo ponto.
            </x-badge>
            @endif

            <!-- Marcadores de Horário -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                    <div class="text-xs text-gray-500 uppercase font-medium">Entrada</div>
                    <div class="text-lg font-bold text-gray-900 mt-1">{{ optional($registroHoje?->entrada_em)->format('H:i') ?? '—' }}</div>
                </div>
                <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                    <div class="text-xs text-gray-500 uppercase font-medium">Saída almoço</div>
                    <div class="text-lg font-bold text-gray-900 mt-1">{{ optional($registroHoje?->intervalo_inicio_em)->format('H:i') ?? '—' }}</div>
                </div>
                <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                    <div class="text-xs text-gray-500 uppercase font-medium">Retorno almoço</div>
                    <div class="text-lg font-bold text-gray-900 mt-1">{{ optional($registroHoje?->intervalo_fim_em)->format('H:i') ?? '—' }}</div>
                </div>
                <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                    <div class="text-xs text-gray-500 uppercase font-medium">Saída</div>
                    <div class="text-lg font-bold text-gray-900 mt-1">{{ optional($registroHoje?->saida_em)->format('H:i') ?? '—' }}</div>
                </div>
            </div>

            <!-- Formulário de Registro -->
            <form id="form-ponto-unico" method="POST" action="{{ route('portal-funcionario.ponto.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="tipo" value="{{ $proximoEvento }}">
                <input type="hidden" name="latitude" id="ponto-latitude">
                <input type="hidden" name="longitude" id="ponto-longitude">
                <input type="hidden" name="fora_atendimento" id="fora-atendimento" value="0">
                <input type="hidden" name="fora_atendimento_confirmado" id="fora-atendimento-confirmado" value="0">
                <input type="hidden" name="distancia_atendimento_metros" id="distancia-atendimento-metros">
                <input type="hidden" name="justificativa_fora_atendimento" id="justificativa-fora-atendimento" value="">

                @php
                    $eventoPrecisaFoto = in_array($proximoEvento, ['entrada', 'saida'], true);
                    $jornadaConcluida = $registroHoje?->saida_em;
                @endphp

                @if($eventoPrecisaFoto && !$jornadaConcluida)
                <div>
                    <label for="ponto-foto" class="block text-sm font-medium text-gray-700 mb-2">
                        📸 Foto obrigatória para {{ $eventos[$proximoEvento] ?? 'registro' }}
                    </label>
                    <input id="ponto-foto" type="file" name="foto" accept="image/*" capture="user" required 
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#3f9cae] focus:border-[#3f9cae]">
                </div>
                @endif

                <div class="flex items-center justify-center gap-3 pt-2">
                    <x-button id="btn-registrar-ponto" type="submit" variant="primary" size="md" 
                              :disabled="$jornadaConcluida || (($bloqueioMinutosRestantes ?? 0) > 0)"
                              :iconLeft="$jornadaConcluida ? null : '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\'></path></svg>'">
                        {{ $jornadaConcluida ? '✅ Jornada concluída' : 'Registrar ' . ($eventos[$proximoEvento] ?? 'Ponto') }}
                    </x-button>
                    <span id="ponto-loading" class="text-sm text-gray-500 hidden">📍 Obtendo localização...</span>
                </div>
            </form>
        </x-card>

        <!-- Card de Histórico -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Histórico (últimos 10 dias)</h3>
            
            @php
                $itensHistorico = collect($historico->items());
                $mesesHistorico = $itensHistorico
                    ->map(fn ($linha) => optional($linha->data_referencia)->format('m/Y'))
                    ->filter()
                    ->unique()
                    ->values();
            @endphp

            @if($mesesHistorico->isNotEmpty())
            <div class="mb-4 space-y-2">
                @foreach($mesesHistorico as $mesAno)
                    @php
                        $saldoMes = $saldoBancoHorasMes[$mesAno] ?? null;
                        $saldoPositivo = $saldoMes['positivo'] ?? true;
                    @endphp
                    <div class="flex items-center justify-between text-sm p-2 rounded bg-gray-50">
                        <span class="text-gray-600">📅 Mês: <strong>{{ $mesAno }}</strong></span>
                        @if($saldoMes)
                            <span class="font-semibold {{ $saldoPositivo ? 'text-green-700' : 'text-red-700' }}">
                                Banco: {{ $saldoMes['formatado'] }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Tabela de Histórico -->
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600 border-b border-gray-200">
                            <th class="py-3 px-2 font-semibold">Dia</th>
                            <th class="py-3 px-2 font-semibold">Entrada</th>
                            <th class="py-3 px-2 font-semibold">Saída almoço</th>
                            <th class="py-3 px-2 font-semibold">Retorno</th>
                            <th class="py-3 px-2 font-semibold">Saída</th>
                            <th class="py-3 px-2 font-semibold">Total</th>
                            <th class="py-3 px-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historico as $linha)
                            @php
                                $estiloLinha = '';
                                if (!empty($linha->eh_feriado)) {
                                    $estiloLinha = 'background-color: #fef3c7;';
                                } elseif (!empty($linha->eh_domingo)) {
                                    $estiloLinha = 'background-color: #fee2e2;';
                                }

                                $ehFalta = ($linha->status ?? '') === 'Falta';
                                $statusClass = $ehFalta
                                    ? 'text-red-700 bg-red-50'
                                    : (in_array($linha->status ?? '', ['Extra', 'Extra feriado'], true)
                                        ? 'text-amber-700 bg-amber-50'
                                        : ((!empty($linha->eh_domingo) || !empty($linha->eh_feriado))
                                            ? 'text-red-700'
                                            : 'text-gray-900'));
                            @endphp
                            <tr class="border-b border-gray-100 hover:bg-gray-50" @if($estiloLinha) style="{{ $estiloLinha }}" @endif>
                                <td class="py-2 px-2 font-medium">{{ optional($linha->data_referencia)->format('d/m') }}</td>
                                <td class="py-2 px-2">{{ optional($linha->entrada_em)->format('H:i') ?? '—' }}</td>
                                <td class="py-2 px-2">{{ optional($linha->intervalo_inicio_em)->format('H:i') ?? '—' }}</td>
                                <td class="py-2 px-2">{{ optional($linha->intervalo_fim_em)->format('H:i') ?? '—' }}</td>
                                <td class="py-2 px-2">{{ optional($linha->saida_em)->format('H:i') ?? '—' }}</td>
                                <td class="py-2 px-2 font-mono">{{ $linha->total_formatado ?? '00:00' }}</td>
                                <td class="py-2 px-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusClass }}">
                                            {{ $linha->status ?? '' }}
                                        </span>
                                        @if(!empty($linha->ponto_fora_atendimento))
                                            <x-badge type="danger" size="xs">Fora do atendimento</x-badge>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-gray-500">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($historico->hasPages())
            <div class="mt-4">
                {{ $historico->links() }}
            </div>
            @endif
        </x-card>

        <!-- Card de Instruções -->
        <x-card>
            <h4 class="font-semibold text-gray-900 mb-3">ℹ️ Informações Importantes</h4>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start gap-2">
                    <span class="text-[#3f9cae] font-bold">•</span>
                    <span>O registro de ponto deve ser feito dentro do horário de trabalho.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-[#3f9cae] font-bold">•</span>
                    <span>Para entrada e saída, é obrigatória a captura de foto.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-[#3f9cae] font-bold">•</span>
                    <span>O sistema captura automaticamente sua localização no momento do registro.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-[#3f9cae] font-bold">•</span>
                    <span>Registros fora do endereço do atendimento serão sinalizados.</span>
                </li>
            </ul>
        </x-card>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-ponto-unico');
            const btn = document.getElementById('btn-registrar-ponto');
            const loading = document.getElementById('ponto-loading');
            const latInput = document.getElementById('ponto-latitude');
            const lngInput = document.getElementById('ponto-longitude');
            const foraAtendInput = document.getElementById('fora-atendimento');
            const distanciaInput = document.getElementById('distancia-atendimento-metros');
            const justificativaInput = document.getElementById('justificativa-fora-atendimento');

            if (form && btn) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (btn.disabled) return;

                    loading.classList.remove('hidden');
                    btn.disabled = true;

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                latInput.value = position.coords.latitude;
                                lngInput.value = position.coords.longitude;

                                // Verificar distância do atendimento (se disponível)
                                form.submit();
                            },
                            function(error) {
                                alert('Não foi possível obter sua localização. Verifique as permissões do navegador.');
                                loading.classList.add('hidden');
                                btn.disabled = false;
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    } else {
                        alert('Seu navegador não suporta geolocalização.');
                        loading.classList.add('hidden');
                        btn.disabled = false;
                    }
                });
            }
        });
    </script>
    @endpush
</x-portal-funcionario-layout>
