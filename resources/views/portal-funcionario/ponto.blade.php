<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registro de Ponto
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Registro de Hoje ({{ now()->format('d/m/Y') }})</h3>
                    <p class="text-sm text-gray-600 mb-4">Próximo evento esperado: <span class="font-semibold">{{ $eventos[$proximoEvento] ?? '—' }}</span></p>

                    @if(($bloqueioMinutosRestantes ?? 0) > 0)
                        <div class="mb-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded p-3">
                            Aguarde {{ $bloqueioMinutosRestantes }} minuto(s) para registrar o próximo ponto.
                        </div>
                    @endif

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
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Histórico (últimos 10 dias)</h3>
                    @php
                        $itensHistorico = collect($historico->items());
                        $mesesHistorico = $itensHistorico
                            ->map(fn ($linha) => optional($linha->data_referencia)->format('m/Y'))
                            ->filter()
                            ->unique()
                            ->values();
                    @endphp
                    @if($mesesHistorico->isNotEmpty())
                        <div class="mb-3 space-y-1">
                            @foreach($mesesHistorico as $mesAno)
                                @php
                                    $saldoMes = $saldoBancoHorasMes[$mesAno] ?? null;
                                    $saldoClass = ($saldoMes['positivo'] ?? true) ? 'text-green-700' : 'text-red-700';
                                @endphp
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <span>Mês: {{ $mesAno }}</span>
                                    @if($saldoMes)
                                        <span class="font-semibold {{ $saldoClass }}">Banco: {{ $saldoMes['formatado'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600 border-b border-gray-200">
                                    <th class="py-2 pr-4">Dia</th>
                                    <th class="py-2 pr-4">Entrada</th>
                                    <th class="py-2 pr-4">Saída almoço</th>
                                    <th class="py-2 pr-4">Retorno almoço</th>
                                    <th class="py-2 pr-4">Saída</th>
                                    <th class="py-2 pr-4">Total</th>
                                    <th class="py-2 pr-4">Status</th>
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
                                            ? 'text-red-700'
                                            : (in_array($linha->status ?? '', ['Extra', 'Extra feriado'], true)
                                                ? 'text-amber-700'
                                                : ((!empty($linha->eh_domingo) || !empty($linha->eh_feriado))
                                                    ? 'text-red-700'
                                                    : 'text-gray-900'));
                                        $cellClass = $ehFalta ? 'text-red-700' : 'text-gray-900';
                                    @endphp
                                    <tr class="border-b border-gray-100" @if($estiloLinha) style="{{ $estiloLinha }}" @endif>
                                        <td class="py-2 pr-4">{{ optional($linha->data_referencia)->format('d') }}</td>
                                        <td class="py-2 pr-4 {{ $cellClass }}">{{ optional($linha->entrada_em)->format('H:i') ?? '—' }}</td>
                                        <td class="py-2 pr-4 {{ $cellClass }}">{{ optional($linha->intervalo_inicio_em)->format('H:i') ?? '—' }}</td>
                                        <td class="py-2 pr-4 {{ $cellClass }}">{{ optional($linha->intervalo_fim_em)->format('H:i') ?? '—' }}</td>
                                        <td class="py-2 pr-4 {{ $cellClass }}">{{ optional($linha->saida_em)->format('H:i') ?? '—' }}</td>
                                        <td class="py-2 pr-4 {{ $cellClass }}">{{ $linha->total_formatado ?? '00:00' }}</td>
                                        <td class="py-2 pr-4">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-semibold {{ $statusClass }}">{{ $linha->status ?? '' }}</span>
                                                @if(!empty($linha->ponto_fora_atendimento))
                                                    <span class="text-[11px] font-semibold text-red-700 bg-red-50 border border-red-200 rounded px-2 py-0.5">Fora do atendimento</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-4 text-gray-500">Nenhum registro encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <x-pagination :paginator="$historico" label="registros" />
                    </div>
                </div>
            </div>

            <div>
                <x-button href="{{ route('portal-funcionario.index') }}" variant="secondary" size="sm">Voltar ao Portal</x-button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('form-ponto-unico');
            if (!form) {
                return;
            }

            const btn = document.getElementById('btn-registrar-ponto');
            const loading = document.getElementById('ponto-loading');
            const latInput = document.getElementById('ponto-latitude');
            const lngInput = document.getElementById('ponto-longitude');
            const foraInput = document.getElementById('fora-atendimento');
            const foraConfirmadoInput = document.getElementById('fora-atendimento-confirmado');
            const distanciaInput = document.getElementById('distancia-atendimento-metros');
            const justificativaInput = document.getElementById('justificativa-fora-atendimento');
            const fotoInput = document.getElementById('ponto-foto');
            const tipoInput = form.querySelector('input[name="tipo"]');

            const tipoEventoAtual = (tipoInput?.value || '').toLowerCase();
            const precisaFoto = ['entrada', 'saida'].includes(tipoEventoAtual);
            const possuiAtendimentoHoje = @json((bool) $atendimentoHoje);
            const enderecoAtendimento = @json($enderecoAtendimento);

            const haversine = (lat1, lon1, lat2, lon2) => {
                const toRad = (value) => value * Math.PI / 180;
                const R = 6371000;
                const dLat = toRad(lat2 - lat1);
                const dLon = toRad(lon2 - lon1);
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
                    + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return Math.round(R * c);
            };

            const buscarCoordenadaEndereco = async (endereco) => {
                const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(endereco)}`;
                const resposta = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!resposta.ok) {
                    return null;
                }
                const dados = await resposta.json();
                if (!Array.isArray(dados) || dados.length === 0) {
                    return null;
                }
                return {
                    latitude: Number(dados[0].lat),
                    longitude: Number(dados[0].lon),
                };
            };

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                if (precisaFoto && fotoInput && !fotoInput.files.length) {
                    alert('Para entrada e saída é obrigatório enviar uma foto.');
                    return;
                }

                if (!navigator.geolocation) {
                    alert('Seu navegador não suporta geolocalização.');
                    return;
                }

                btn?.setAttribute('disabled', 'disabled');
                loading?.classList.remove('hidden');

                const posicao = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 12000,
                        maximumAge: 0,
                    });
                }).catch(() => null);

                if (!posicao) {
                    btn?.removeAttribute('disabled');
                    loading?.classList.add('hidden');
                    alert('Não foi possível obter sua localização. Ative o GPS e tente novamente.');
                    return;
                }

                const latitude = Number(posicao.coords.latitude);
                const longitude = Number(posicao.coords.longitude);

                latInput.value = latitude;
                lngInput.value = longitude;
                foraInput.value = '0';
                foraConfirmadoInput.value = '0';
                distanciaInput.value = '';
                justificativaInput.value = '';

                if (tipoEventoAtual === 'entrada' && possuiAtendimentoHoje && enderecoAtendimento) {
                    let confirmarFora = false;
                    let distancia = null;

                    try {
                        const pontoAtendimento = await buscarCoordenadaEndereco(enderecoAtendimento);
                        if (pontoAtendimento) {
                            distancia = haversine(latitude, longitude, pontoAtendimento.latitude, pontoAtendimento.longitude);
                            distanciaInput.value = String(distancia);
                            if (distancia > 150) {
                                confirmarFora = !confirm(`Você está aproximadamente ${distancia}m do endereço do atendimento. Este registro poderá ser auditado. Deseja continuar mesmo assim?`);
                                if (!confirmarFora) {
                                    foraInput.value = '1';
                                    foraConfirmadoInput.value = '1';
                                    justificativaInput.value = 'Funcionário confirmou registro fora do endereço do atendimento agendado.';
                                }
                            }
                        } else {
                            const prosseguirSemValidacao = confirm('Não foi possível validar sua distância do endereço do atendimento. O registro poderá ser auditado. Deseja continuar?');
                            if (prosseguirSemValidacao) {
                                foraInput.value = '1';
                                foraConfirmadoInput.value = '1';
                                justificativaInput.value = 'Registro confirmado sem validação automática de distância.';
                            } else {
                                confirmarFora = true;
                            }
                        }
                    } catch (error) {
                        const prosseguirComErro = confirm('Falha ao validar localização do atendimento. O registro poderá ser auditado. Deseja continuar?');
                        if (prosseguirComErro) {
                            foraInput.value = '1';
                            foraConfirmadoInput.value = '1';
                            justificativaInput.value = 'Registro confirmado após falha de validação de geolocalização.';
                        } else {
                            confirmarFora = true;
                        }
                    }

                    if (confirmarFora) {
                        btn?.removeAttribute('disabled');
                        loading?.classList.add('hidden');
                        return;
                    }
                }

                form.submit();
            });
        })();
    </script>
</x-app-layout>
