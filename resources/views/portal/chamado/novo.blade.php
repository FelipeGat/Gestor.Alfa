<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <div class="portal-wrapper">
        <div class="portal-table-card overflow-hidden">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Abrir Chamado de Forma Rápida
                </h3>
                <p class="text-sm text-gray-600 mt-2">Preencha os dados abaixo e, se desejar, escaneie o QR Code do equipamento para vincular o chamado automaticamente.</p>
            </div>

            <form id="form-novo-chamado" action="{{ route('portal.chamado.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 space-y-6">
                        <div class="portal-filter-group">
                            <label for="assunto" class="portal-filter-label">Assunto</label>
                            <input
                                id="assunto"
                                name="assunto"
                                type="text"
                                required
                                maxlength="255"
                                value="{{ old('assunto') }}"
                                class="portal-filter-input w-full"
                                placeholder="Ex.: Ar-condicionado parou de funcionar"
                            >
                            @error('assunto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="portal-filter-group">
                            <label class="portal-filter-label">Descrição do Problema</label>
                            <textarea name="descricao" rows="6" required
                                class="portal-filter-input w-full"
                                placeholder="Descreva detalhadamente o problema...">{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="portal-filter-group">
                                <label for="melhor_horario_contato" class="portal-filter-label">Melhor horário para contato</label>
                                <input
                                    id="melhor_horario_contato"
                                    name="melhor_horario_contato"
                                    type="text"
                                    maxlength="100"
                                    value="{{ old('melhor_horario_contato') }}"
                                    class="portal-filter-input w-full"
                                    placeholder="Ex.: 09:00 às 11:00"
                                >
                                @error('melhor_horario_contato')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="portal-filter-group">
                                <label for="foto_problema" class="portal-filter-label">Foto do problema</label>
                                <input
                                    id="foto_problema"
                                    name="foto_problema"
                                    type="file"
                                    accept="image/*"
                                    class="portal-filter-input w-full file:mr-3 file:px-3 file:py-2 file:border-0 file:rounded-md file:bg-gray-100 file:text-gray-700"
                                >
                                <p class="text-xs text-gray-500 mt-1">Opcional. Formatos: JPG, PNG, WEBP. Máx. 5MB.</p>
                                @error('foto_problema')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                            <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                </svg>
                                Equipamento (QR, Código ou TAG)
                            </h4>
                            <p class="text-sm text-gray-600 mt-1">Escaneie o QR ou busque pelo token, Código do ativo ou TAG patrimonial.</p>

                            <div class="mt-4 space-y-3">
                                <div>
                                    <label for="qr_payload" class="portal-filter-label">Identificador do equipamento</label>
                                    <input id="qr_payload" type="text" class="portal-filter-input w-full" placeholder="Link/token QR, Código do ativo (ex.: BYD-AC-016) ou TAG (ex.: TAG-BYD-016)">
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" id="btn-resolver-qr" class="portal-btn border border-gray-300 text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-lg">Buscar equipamento</button>
                                    <button type="button" id="btn-start-scan" class="portal-btn border border-gray-300 text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-lg">Ler com câmera</button>
                                    <button type="button" id="btn-stop-scan" class="portal-btn border border-gray-300 text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-lg hidden">Parar câmera</button>
                                </div>

                                <div id="scan-area" class="hidden">
                                    <video id="qr-video" class="w-full rounded-lg border border-gray-200" autoplay playsinline muted></video>
                                    <p class="text-xs text-gray-500 mt-2">A leitura depende do suporte do navegador à API de detecção de QR.</p>
                                </div>
                            </div>

                            <input type="hidden" name="equipamento_id" id="equipamento_id" value="{{ old('equipamento_id', $equipamentoSelecionado?->id) }}">

                            <div id="equipamento-card" class="mt-4 rounded-lg border border-gray-200 bg-white p-3 {{ old('equipamento_id', $equipamentoSelecionado?->id) ? '' : 'hidden' }}">
                                <p class="text-sm font-semibold text-gray-800" id="eq-nome">{{ $equipamentoSelecionado?->nome }}</p>
                                <div class="mt-2 text-xs text-gray-600 space-y-1">
                                    <p id="eq-modelo">{{ $equipamentoSelecionado?->modelo ? 'Modelo: '.$equipamentoSelecionado->modelo : '' }}</p>
                                    <p id="eq-codigo">{{ $equipamentoSelecionado?->codigo_ativo ? 'Código do ativo: '.$equipamentoSelecionado->codigo_ativo : '' }}</p>
                                    <p id="eq-patrimonio">{{ $equipamentoSelecionado?->tag_patrimonial ? 'Patrimônio: '.$equipamentoSelecionado->tag_patrimonial : '' }}</p>
                                    <p id="eq-localizacao">{{ $equipamentoSelecionado?->localizacao_resumo ? 'Localização: '.$equipamentoSelecionado->localizacao_resumo : '' }}</p>
                                    <p id="eq-responsavel">{{ $equipamentoSelecionado?->responsavel?->nome ? 'Responsável: '.$equipamentoSelecionado->responsavel->nome : '' }}</p>
                                </div>
                            </div>

                            <p id="qr-feedback" class="text-xs text-gray-600 mt-3"></p>

                            <div id="equipamentos-resultados" class="mt-3 space-y-2 hidden"></div>

                            @error('equipamento_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="portal-filter-group">
                    <label class="portal-filter-label">Prioridade</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="prioridade-group">
                        @php
                            $prioridadeSelecionada = old('prioridade', 'media');
                        @endphp
                        @foreach(['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente'] as $valor => $label)
                            <label class="prioridade-option flex items-center justify-center p-3 border-2 rounded-full cursor-pointer transition-colors {{ $prioridadeSelecionada === $valor ? 'border-[#3f9cae] bg-[#3f9cae]/10' : 'border-gray-200 hover:border-[#3f9cae] hover:bg-[#3f9cae]/5' }}">
                                <input type="radio" name="prioridade" value="{{ $valor }}" class="sr-only" {{ $prioridadeSelecionada === $valor ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('prioridade')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 relative">
                    <button id="btn-submit-chamado" type="submit" class="portal-btn portal-btn--primary w-full justify-center py-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="btn-submit-label">Abrir Chamado</span>
                    </button>

                    <div id="submit-loading" class="hidden absolute inset-0 rounded-lg bg-[#2d7a8a] text-white flex items-center justify-center gap-2 font-semibold">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Criando chamado...
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const equipamentoBuscaEndpoint = @json(route('portal.equipamento.buscar'));
        const qrPayloadInput = document.getElementById('qr_payload');
        const qrFeedback = document.getElementById('qr-feedback');
        const equipamentoCard = document.getElementById('equipamento-card');
        const equipamentoIdInput = document.getElementById('equipamento_id');
        const equipamentosResultados = document.getElementById('equipamentos-resultados');

        const eqNome = document.getElementById('eq-nome');
        const eqModelo = document.getElementById('eq-modelo');
        const eqCodigo = document.getElementById('eq-codigo');
        const eqPatrimonio = document.getElementById('eq-patrimonio');
        const eqLocalizacao = document.getElementById('eq-localizacao');
        const eqResponsavel = document.getElementById('eq-responsavel');

        const btnResolverQr = document.getElementById('btn-resolver-qr');
        const btnStartScan = document.getElementById('btn-start-scan');
        const btnStopScan = document.getElementById('btn-stop-scan');
        const formNovoChamado = document.getElementById('form-novo-chamado');
        const btnSubmitChamado = document.getElementById('btn-submit-chamado');
        const submitLoading = document.getElementById('submit-loading');

        const scanArea = document.getElementById('scan-area');
        const qrVideo = document.getElementById('qr-video');

        let cameraStream = null;
        let scannerInterval = null;
        let debounceBuscaEquipamento = null;

        function setFeedback(message, type = 'info') {
            qrFeedback.textContent = message;
            qrFeedback.classList.remove('text-gray-600', 'text-red-600', 'text-green-700');
            qrFeedback.classList.add(type === 'error' ? 'text-red-600' : (type === 'success' ? 'text-green-700' : 'text-gray-600'));
        }

        function preencherEquipamento(data) {
            equipamentoIdInput.value = data.id || '';
            eqNome.textContent = data.nome || '-';
            eqModelo.textContent = data.modelo ? `Modelo: ${data.modelo}` : '';
            eqCodigo.textContent = data.codigo_ativo ? `Código do ativo: ${data.codigo_ativo}` : '';
            eqPatrimonio.textContent = data.tag_patrimonial ? `Patrimônio: ${data.tag_patrimonial}` : '';
            eqLocalizacao.textContent = data.localizacao ? `Localização: ${data.localizacao}` : '';
            eqResponsavel.textContent = data.responsavel ? `Responsável: ${data.responsavel}` : '';
            equipamentoCard.classList.remove('hidden');
        }

        function limparResultadosEquipamentos() {
            equipamentosResultados.innerHTML = '';
            equipamentosResultados.classList.add('hidden');
        }

        function renderizarResultadosEquipamentos(items) {
            if (!Array.isArray(items) || items.length <= 1) {
                limparResultadosEquipamentos();
                return;
            }

            const html = items.map((item) => {
                const codigo = item.codigo_ativo || '—';
                const tag = item.tag_patrimonial || '—';
                const setor = item.setor || '—';
                const responsavel = item.responsavel || '—';

                return `
                    <button type="button"
                        class="w-full text-left rounded-lg border border-gray-200 bg-white p-3 hover:border-[#3f9cae] hover:bg-[#3f9cae]/5 transition"
                        data-equipamento='${JSON.stringify(item).replace(/'/g, "&#39;")}'
                    >
                        <p class="text-sm font-semibold text-gray-800">${item.nome || '-'}</p>
                        <p class="text-xs text-gray-600 mt-1">Código: ${codigo} • TAG: ${tag}</p>
                        <p class="text-xs text-gray-500 mt-1">Setor: ${setor} • Responsável: ${responsavel}</p>
                    </button>
                `;
            }).join('');

            equipamentosResultados.innerHTML = html;
            equipamentosResultados.classList.remove('hidden');

            equipamentosResultados.querySelectorAll('button[data-equipamento]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    const payload = botao.getAttribute('data-equipamento');
                    if (!payload) return;
                    const item = JSON.parse(payload);
                    preencherEquipamento(item);
                    limparResultadosEquipamentos();
                    setFeedback('Equipamento selecionado com sucesso.', 'success');
                });
            });
        }

        async function resolverQr(payload) {
            const identificador = (payload || qrPayloadInput.value || '').trim();
            if (!identificador) {
                setFeedback('Informe um identificador para busca: token, Código do ativo ou TAG patrimonial.', 'error');
                return;
            }

            setFeedback('Buscando equipamento...');
            const endpoint = `${equipamentoBuscaEndpoint}?identificador=${encodeURIComponent(identificador)}`;

            try {
                const response = await fetch(endpoint, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                const data = await response.json();
                if (!response.ok) {
                    limparResultadosEquipamentos();
                    setFeedback(data.message || 'Não foi possível carregar o equipamento.', 'error');
                    return;
                }

                const items = Array.isArray(data.items) ? data.items : [];
                if (items.length === 0) {
                    limparResultadosEquipamentos();
                    setFeedback('Nenhum equipamento encontrado para esse identificador.', 'error');
                    return;
                }

                if (items.length === 1) {
                    preencherEquipamento(items[0]);
                    limparResultadosEquipamentos();
                    setFeedback('Equipamento vinculado com sucesso ao chamado.', 'success');
                    return;
                }

                renderizarResultadosEquipamentos(items);
                setFeedback(`Foram encontrados ${items.length} equipamentos. Selecione um da lista abaixo.`, 'info');
            } catch (error) {
                limparResultadosEquipamentos();
                setFeedback('Erro ao consultar equipamento pelo QR. Tente novamente.', 'error');
            }
        }

        btnResolverQr?.addEventListener('click', function() {
            resolverQr();
        });

        qrPayloadInput?.addEventListener('input', function() {
            const valor = (this.value || '').trim();

            if (debounceBuscaEquipamento) {
                clearTimeout(debounceBuscaEquipamento);
            }

            if (valor.length < 2) {
                limparResultadosEquipamentos();
                if (!valor.length) {
                    setFeedback('');
                }

                return;
            }

            debounceBuscaEquipamento = setTimeout(() => {
                resolverQr(valor);
            }, 300);
        });

        qrPayloadInput?.addEventListener('keydown', function(event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();

            if (debounceBuscaEquipamento) {
                clearTimeout(debounceBuscaEquipamento);
            }

            resolverQr(this.value || '');
        });

        async function iniciarLeituraCamera() {
            if (!('BarcodeDetector' in window)) {
                setFeedback('Seu navegador não suporta leitura direta por câmera. Cole o token, Código do ativo ou TAG para continuar.', 'error');
                return;
            }

            try {
                const detector = new BarcodeDetector({ formats: ['qr_code'] });
                cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                qrVideo.srcObject = cameraStream;
                scanArea.classList.remove('hidden');
                btnStopScan.classList.remove('hidden');
                setFeedback('Câmera ativa. Aponte para o QR Code do equipamento.');

                scannerInterval = setInterval(async () => {
                    if (!qrVideo || qrVideo.readyState < 2) return;

                    try {
                        const barcodes = await detector.detect(qrVideo);
                        if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                            qrPayloadInput.value = barcodes[0].rawValue;
                            await resolverQr(barcodes[0].rawValue);
                            pararLeituraCamera();
                        }
                    } catch (_) {
                    }
                }, 500);
            } catch (_) {
                setFeedback('Não foi possível acessar a câmera. Verifique permissões do navegador.', 'error');
                pararLeituraCamera();
            }
        }

        function pararLeituraCamera() {
            if (scannerInterval) {
                clearInterval(scannerInterval);
                scannerInterval = null;
            }

            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }

            if (qrVideo) {
                qrVideo.srcObject = null;
            }

            scanArea.classList.add('hidden');
            btnStopScan.classList.add('hidden');
        }

        btnStartScan?.addEventListener('click', iniciarLeituraCamera);
        btnStopScan?.addEventListener('click', pararLeituraCamera);

        formNovoChamado?.addEventListener('submit', function() {
            btnSubmitChamado?.setAttribute('disabled', 'disabled');
            btnSubmitChamado?.classList.add('opacity-70', 'cursor-not-allowed');
            submitLoading?.classList.remove('hidden');
        });

        window.addEventListener('beforeunload', pararLeituraCamera);

        document.querySelectorAll('input[name="prioridade"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.prioridade-option').forEach(function(label) {
                    label.classList.remove('border-[#3f9cae]', 'bg-[#3f9cae]/10');
                    label.classList.add('border-gray-200');
                });

                this.closest('.prioridade-option')?.classList.remove('border-gray-200');
                this.closest('.prioridade-option')?.classList.add('border-[#3f9cae]', 'bg-[#3f9cae]/10');
            });
        });
    </script>
</x-app-layout>
