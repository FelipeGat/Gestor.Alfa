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
            <div class="grid grid-cols-4 gap-3 mb-5">
                <div class="p-3 rounded border border-gray-200">
                    <div class="text-xs text-gray-500 uppercase">Entrada</div>
                    <div class="text-lg font-semibold text-gray-900">{{ optional($registroHoje?->entrada_em)->format('H:i') ?? '—' }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200">
                    <div class="text-xs text-gray-500 uppercase">Saída almoço</div>
                    <div class="text-lg font-semibold text-gray-900">{{ optional($registroHoje?->intervalo_inicio_em)->format('H:i') ?? '—' }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200">
                    <div class="text-xs text-gray-500 uppercase">Retorno almoço</div>
                    <div class="text-lg font-semibold text-gray-900">{{ optional($registroHoje?->intervalo_fim_em)->format('H:i') ?? '—' }}</div>
                </div>
                <div class="p-3 rounded border border-gray-200">
                    <div class="text-xs text-gray-500 uppercase">Saída</div>
                    <div class="text-lg font-semibold text-gray-900">{{ optional($registroHoje?->saida_em)->format('H:i') ?? '—' }}</div>
                </div>
            </div>

            <form id="form-ponto-unico" method="POST" action="{{ route('portal-funcionario.ponto.store') }}" enctype="multipart/form-data" class="space-y-3">
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

                <div id="bloco-foto" class="{{ $eventoPrecisaFoto ? '' : 'hidden' }}">
                    <label for="ponto-foto" class="block text-sm font-medium text-gray-700 mb-1">Foto obrigatória para {{ $eventos[$proximoEvento] ?? 'registro' }}</label>
                    <input id="ponto-foto" type="file" name="foto" accept="image/*" capture="user" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <div class="flex items-center justify-center gap-3">
                    <x-button id="btn-registrar-ponto" type="submit" variant="primary" size="sm" :disabled="$jornadaConcluida || (($bloqueioMinutosRestantes ?? 0) > 0)">
                        {{ $jornadaConcluida ? 'Jornada concluída' : 'Registrar ' . ($eventos[$proximoEvento] ?? 'Ponto') }}
                    </x-button>
                    <span id="ponto-loading" class="text-sm text-gray-500 hidden">Obtendo localização...</span>
                </div>
            </form>

            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <div class="font-semibold mb-2">Como liberar GPS e câmera no Chrome</div>
                <ol class="list-decimal pl-5 space-y-1">
                    <li>Toque no ícone ao lado da URL (cadeado ou <strong>ⓘ</strong>).</li>
                    <li>Abra <strong>Permissões</strong> do site.</li>
                    <li>Defina <strong>Localização</strong> e <strong>Câmera</strong> como <strong>Permitir</strong>.</li>
                    <li>Atualize a página e tente registrar novamente.</li>
                </ol>
                <div class="mt-2 text-xs text-amber-800">
                    Se ainda bloquear no celular: Configurações do aparelho → Apps → Chrome → Permissões → habilite Localização e Câmera.
                </div>
                <div class="mt-1 text-xs text-amber-800">
                    Em ambiente de testes, prefira acesso por <strong>localhost</strong> ou <strong>HTTPS</strong>.
                </div>
            </div>
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
                const mensagemErroGeolocalizacao = (erro) => {
                    if (!window.isSecureContext) {
                        return 'Geolocalização bloqueada no Chrome em conexão não segura. Use HTTPS (ou localhost) para liberar o GPS. Veja o guia "Como liberar GPS e câmera no Chrome" abaixo.';
                    }

                    if (!erro || typeof erro.code !== 'number') {
                        return 'Não foi possível obter sua localização. Verifique GPS e permissões do navegador. Veja o guia "Como liberar GPS e câmera no Chrome" abaixo.';
                    }

                    if (erro.code === 1) {
                        return 'Permissão de localização negada. Libere o acesso ao GPS para este site e tente novamente. Veja o guia "Como liberar GPS e câmera no Chrome" abaixo.';
                    }

                    if (erro.code === 2) {
                        return 'Não foi possível determinar sua localização. Ative o GPS e tente novamente. Veja o guia "Como liberar GPS e câmera no Chrome" abaixo.';
                    }

                    if (erro.code === 3) {
                        return 'Tempo esgotado ao obter localização. Tente novamente em local com melhor sinal.';
                    }

                    return 'Não foi possível obter sua localização. Verifique GPS e permissões do navegador. Veja o guia "Como liberar GPS e câmera no Chrome" abaixo.';
                };

                const statusPermissaoGeolocalizacao = async () => {
                    if (!navigator.permissions || typeof navigator.permissions.query !== 'function') {
                        return null;
                    }

                    try {
                        const permissao = await navigator.permissions.query({ name: 'geolocation' });
                        return permissao?.state || null;
                    } catch (erro) {
                        return null;
                    }
                };

                const obterPosicaoAtual = async () => {
                    const tentarObter = (opcoes) => new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, opcoes);
                    });

                    try {
                        return await tentarObter({
                            enableHighAccuracy: true,
                            timeout: 12000,
                            maximumAge: 0,
                        });
                    } catch (erroAltaPrecisao) {
                        return await tentarObter({
                            enableHighAccuracy: false,
                            timeout: 20000,
                            maximumAge: 60000,
                        });
                    }
                };

                form.addEventListener('submit', async function(event) {
                    event.preventDefault();

                    if (!navigator.geolocation) {
                        alert('Seu navegador não suporta geolocalização.');
                        return;
                    }

                    if (!window.isSecureContext) {
                        alert('No Chrome, o GPS só funciona em contexto seguro (HTTPS) ou localhost. A URL atual não está em contexto seguro. Veja o guia "Como liberar GPS e câmera no Chrome" abaixo.');
                        return;
                    }

                    const permissaoGeolocalizacao = await statusPermissaoGeolocalizacao();
                    if (permissaoGeolocalizacao === 'denied') {
                        alert('A localização está bloqueada para este site no Chrome. Abra as permissões do site, permita "Localização" e recarregue a página.');
                        return;
                    }

                    btn?.setAttribute('disabled', 'disabled');
                    loading?.classList.remove('hidden');

                    const resultadoGeolocalizacao = await obterPosicaoAtual()
                      .then((posicao) => ({ posicao, erro: null }))
                      .catch((erro) => ({ posicao: null, erro }));

                    if (!resultadoGeolocalizacao.posicao) {
                        btn?.removeAttribute('disabled');
                        loading?.classList.add('hidden');
                        alert(mensagemErroGeolocalizacao(resultadoGeolocalizacao.erro));
                        return;
                    }

                    const posicao = resultadoGeolocalizacao.posicao;

                    const latitude = Number(posicao.coords.latitude);
                    const longitude = Number(posicao.coords.longitude);

                    latInput.value = latitude;
                    lngInput.value = longitude;
                    foraAtendInput.value = '0';
                    distanciaInput.value = '';
                    justificativaInput.value = '';

                    form.submit();
                });
            }
        });
    </script>
    @endpush
</x-portal-funcionario-layout>
