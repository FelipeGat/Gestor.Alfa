<x-app-layout>

    @php
        $routePrefix = request()->routeIs('rh.*') ? 'rh.funcionarios' : 'funcionarios';
        $isRhRoute = request()->routeIs('rh.*');
        $isRhAdmin = auth()->check() && auth()->user()->isAdmin();
        $canShowRh = $isRhRoute && $isRhAdmin;
        $breadcrumbBase = $isRhRoute
            ? ['label' => 'RH', 'url' => route('rh.dashboard')]
            : ['label' => 'Cadastros', 'url' => route('cadastros.index')];
    @endphp

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            $breadcrumbBase,
            ['label' => 'Funcionários', 'url' => route($routePrefix . '.index')],
            ['label' => 'Editar Funcionário']
        ]" />
    </x-slot>

    <x-page-title title="Editar Funcionário" :route="route($routePrefix . '.index')" />

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow">
                <h3 class="font-medium mb-2">Erros encontrados:</h3>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route($routePrefix . '.update', $funcionario) }}" class="space-y-6 mb-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO 1: DADOS DO FUNCIONÁRIO --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados do Funcionário
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <x-form-input name="nome" label="Nome" required placeholder="Nome completo do funcionário" :value="old('nome', $funcionario->nome)" />
                        <x-form-input name="email" label="Email de Acesso" type="email" required placeholder="email@empresa.com" :value="old('email', $funcionario->user->email)" />
                    </div>
                </div>

                {{-- SEÇÃO 2: STATUS --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Status
                    </h3>

                    <div class="max-w-xs">
                        <x-form-select
                            name="ativo"
                            label="Situação do Funcionário"
                            placeholder="Selecione"
                            :selected="old('ativo', $funcionario->ativo)"
                            :options="[1 => 'Ativo', 0 => 'Inativo']"
                        />
                    </div>
                </div>
                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <x-button href="{{ route($routePrefix . '.index') }}" variant="danger" size="md" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </x-button>

                    <x-button type="submit" variant="primary" size="md" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </x-button>
                </div>

            </form>

            @if($canShowRh)
            <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                    Gestão RH do Funcionário
                </h3>

                <div x-data="{ aba: localStorage.getItem('rh-funcionario-aba') || 'documentos' }" x-init="$watch('aba', valor => localStorage.setItem('rh-funcionario-aba', valor))" class="space-y-5">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="aba = 'documentos'" class="px-3 py-2 rounded border text-sm" :class="aba === 'documentos' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Documentos</button>
                        <button type="button" @click="aba = 'epis'" class="px-3 py-2 rounded border text-sm" :class="aba === 'epis' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">EPIs</button>
                        <button type="button" @click="aba = 'beneficios'" class="px-3 py-2 rounded border text-sm" :class="aba === 'beneficios' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Benefícios</button>
                        <button type="button" @click="aba = 'jornada'" class="px-3 py-2 rounded border text-sm" :class="aba === 'jornada' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Jornada</button>
                        <button type="button" @click="aba = 'ferias'" class="px-3 py-2 rounded border text-sm" :class="aba === 'ferias' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Férias</button>
                        <button type="button" @click="aba = 'advertencias'" class="px-3 py-2 rounded border text-sm" :class="aba === 'advertencias' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Advertências</button>
                    </div>

                    <div x-show="aba === 'documentos'" x-cloak class="space-y-3">
                        @php
                            $tiposDocumentoPadrao = [
                                'RG',
                                'CNH',
                                'CPF',
                                'CTPS',
                                'PIS',
                                'ASO',
                                'COMPROVANTE DE ENDEREÇO',
                                'TÍTULO ELEITOR',
                                'RESERVISTA',
                                'CERTIDÃO DE NASCIMENTO',
                                'CERTIDÃO DE CASAMENTO',
                                'CERTIDÃO DOS FILHOS (MENORES DE 14 ANOS)',
                            ];
                        @endphp
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.documentos.store', $funcionario) }}" enctype="multipart/form-data" x-data="{ tipoDocumento: '', nomeArquivoDocumento: '' }" class="border border-gray-200 rounded p-3 bg-gray-50 space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                    <select name="tipo" x-model="tipoDocumento" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                        <option value="">Selecione</option>
                                        @foreach($tiposDocumentoPadrao as $tipoDocumento)
                                            <option value="{{ $tipoDocumento }}">{{ $tipoDocumento }}</option>
                                        @endforeach
                                        <option value="OUTRO">NOVO (DIGITAR ABAIXO)</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2" x-show="tipoDocumento === 'OUTRO'" x-cloak>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Novo Tipo</label>
                                    <input type="text" name="tipo_customizado" x-bind:disabled="tipoDocumento !== 'OUTRO'" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Ex.: NR-10, TREINAMENTO">
                                </div>
                                <div class="md:col-span-2">
                                    <x-form-input name="numero" label="Número" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-form-input name="data_emissao" type="date" label="Emissão" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-form-input name="data_vencimento" type="date" label="Vencimento" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                        @foreach(['ativo' => 'Ativo', 'vencido' => 'Vencido', 'pendente' => 'Pendente'] as $valor => $rotulo)
                                            <option value="{{ $valor }}">{{ $rotulo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-4">
                                    <x-form-input name="arquivo" label="Referência (opcional)" placeholder="Ex.: Pasta física RH" />
                                </div>
                                <div class="md:col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Anexo do Documento</label>
                                    <label class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Anexar arquivo</span>
                                        <input type="file" name="arquivo_upload" accept=".pdf,.png,.jpg,.jpeg,.webp,.doc,.docx" class="sr-only" @change="nomeArquivoDocumento = $event.target.files?.[0]?.name || ''">
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500" x-text="nomeArquivoDocumento || 'Nenhum arquivo selecionado'"></p>
                                </div>
                                <div class="md:col-span-3 flex items-end md:justify-end">
                                    <x-button type="submit" variant="primary" size="sm" class="w-full md:w-auto">Adicionar Documento</x-button>
                                </div>
                            </div>
                        </form>
                        @endif
                        <div class="border border-gray-200 rounded p-3 bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700">Documentos adicionados</h4>
                                <span class="text-xs text-gray-500">{{ $funcionario->documentos->count() }} item(ns)</span>
                            </div>
                            <div class="hidden md:grid md:grid-cols-12 gap-2 px-3 py-2 rounded bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wide sticky top-0 z-10">
                                <div class="md:col-span-2">Tipo</div>
                                <div class="md:col-span-2">Número</div>
                                <div class="md:col-span-2">Emissão</div>
                                <div class="md:col-span-2">Vencimento</div>
                                <div class="md:col-span-2">Status</div>
                                <div class="md:col-span-2 text-right">Ações</div>
                            </div>
                            @forelse($funcionario->documentos as $documento)
                                @php
                                    $diasParaVencimento = $documento->data_vencimento
                                        ? now()->startOfDay()->diffInDays($documento->data_vencimento->copy()->startOfDay(), false)
                                        : null;
                                    $badgePrazo = null;
                                    $classeBadgePrazo = '';

                                    if (!is_null($diasParaVencimento)) {
                                        if ($diasParaVencimento < 0) {
                                            $badgePrazo = 'Vencido há ' . abs($diasParaVencimento) . ' dia(s)';
                                            $classeBadgePrazo = 'bg-red-100 text-red-700 border-red-200';
                                        } elseif ($diasParaVencimento <= 30) {
                                            $badgePrazo = 'Vence em ' . $diasParaVencimento . ' dia(s)';
                                            $classeBadgePrazo = 'bg-amber-100 text-amber-700 border-amber-200';
                                        }
                                    }
                                @endphp
                                <form id="update-documento-{{ $documento->id }}" method="POST" action="{{ route('rh.funcionarios.documentos.update', [$funcionario, $documento]) }}" enctype="multipart/form-data" x-data="{ tipoDocumentoEdit: '{{ in_array($documento->tipo, $tiposDocumentoPadrao, true) ? $documento->tipo : 'OUTRO' }}', nomeArquivoDocumentoEdit: '' }" class="border border-gray-200 rounded p-3 bg-gray-50 space-y-3">
                                    @csrf
                                    @method('PUT')
                                    @if($badgePrazo)
                                        <div>
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold border {{ $classeBadgePrazo }}">{{ $badgePrazo }}</span>
                                        </div>
                                    @endif
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                                        <div class="md:col-span-2">
                                            <select name="tipo" x-model="tipoDocumentoEdit" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" required>
                                                @foreach($tiposDocumentoPadrao as $tipoDocumento)
                                                    <option value="{{ $tipoDocumento }}" @selected($documento->tipo === $tipoDocumento)>{{ $tipoDocumento }}</option>
                                                @endforeach
                                                <option value="OUTRO">NOVO (DIGITAR ABAIXO)</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <input type="text" name="numero" value="{{ $documento->numero }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                        </div>
                                        <div class="md:col-span-2">
                                            <input type="date" name="data_emissao" value="{{ optional($documento->data_emissao)->format('Y-m-d') }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                        </div>
                                        <div class="md:col-span-2">
                                            <input type="date" name="data_vencimento" value="{{ optional($documento->data_vencimento)->format('Y-m-d') }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                        </div>
                                        <div class="md:col-span-2">
                                            <select name="status" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" required>
                                                @foreach(['ativo' => 'Ativo', 'vencido' => 'Vencido', 'pendente' => 'Pendente'] as $valor => $rotulo)
                                                    <option value="{{ $valor }}" @selected($documento->status === $valor)>{{ $rotulo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-2 flex md:justify-end gap-2">
                                            @if($isRhRoute)
                                            <x-button type="submit" form="update-documento-{{ $documento->id }}" variant="secondary" size="sm">Salvar</x-button>
                                            <x-button type="submit" form="delete-documento-{{ $documento->id }}" variant="danger" size="sm">Excluir</x-button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                                        <div class="md:col-span-3" x-show="tipoDocumentoEdit === 'OUTRO'" x-cloak>
                                            <input type="text" name="tipo_customizado" value="{{ in_array($documento->tipo, $tiposDocumentoPadrao, true) ? '' : $documento->tipo }}" x-bind:disabled="tipoDocumentoEdit !== 'OUTRO'" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Novo tipo">
                                        </div>
                                        <div class="md:col-span-4">
                                            <input type="text" name="arquivo" value="{{ $documento->arquivo }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Referência/URL do documento">
                                        </div>
                                        <div class="md:col-span-5">
                                            <label class="inline-flex items-center gap-2 px-2 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <span>Anexar</span>
                                                <input type="file" name="arquivo_upload" accept=".pdf,.png,.jpg,.jpeg,.webp,.doc,.docx" class="sr-only" @change="nomeArquivoDocumentoEdit = $event.target.files?.[0]?.name || ''">
                                            </label>
                                            <p class="mt-1 text-xs text-gray-500" x-text="nomeArquivoDocumentoEdit || 'Sem novo arquivo'"></p>
                                        </div>
                                    </div>
                                </form>
                                        @if($isRhRoute)
                                        <form id="delete-documento-{{ $documento->id }}" method="POST" action="{{ route('rh.funcionarios.documentos.destroy', [$funcionario, $documento]) }}" onsubmit="return confirm('Excluir documento?')" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        @endif
                                    @if($documento->arquivo_url)
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-xs text-gray-600">Anexo atual: {{ $documento->arquivo_nome ?? 'Documento' }}</span>
                                            <x-button href="{{ $documento->arquivo_url }}" variant="secondary" size="sm" target="_blank">Abrir Anexo</x-button>
                                            <x-button href="{{ $documento->arquivo_url }}" variant="primary" size="sm" target="_blank">Baixar Anexo</x-button>
                                        </div>
                                    @endif
                            @empty
                                <p class="text-sm text-gray-500">Sem documentos cadastrados.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="aba === 'epis'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.epis.store', $funcionario) }}" x-data="{ epiSelecionado: '' }" class="border border-gray-200 rounded p-3 bg-gray-50 space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">EPI</label>
                                    <select name="epi_id" x-model="epiSelecionado" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                        <option value="">Selecione</option>
                                        @foreach($epis as $epi)
                                            <option value="{{ $epi->id }}">{{ $epi->nome }} {{ $epi->ca ? '(CA ' . $epi->ca . ')' : '' }}</option>
                                        @endforeach
                                        <option value="__NOVO__">NOVO EPI (DIGITAR ABAIXO)</option>
                                    </select>
                                </div>
                                <div class="md:col-span-3" x-show="epiSelecionado === '__NOVO__'" x-cloak>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Novo EPI</label>
                                    <input type="text" name="epi_nome_customizado" x-bind:disabled="epiSelecionado !== '__NOVO__'" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Ex.: ÓCULOS DE PROTEÇÃO">
                                </div>
                                <div class="md:col-span-2"><x-form-input name="data_entrega" type="date" label="Entrega" required /></div>
                                <div class="md:col-span-2"><x-form-input name="data_vencimento" type="date" label="Vencimento" required /></div>
                                <div class="md:col-span-2"><x-form-input name="status" label="Status" :value="'ativo'" required /></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-3"><x-form-input name="marca" label="Marca" required /></div>
                                <div class="md:col-span-2"><x-form-input name="quantidade" type="number" min="1" step="1" label="Quantidade" :value="1" required /></div>
                                <div class="md:col-span-2"><x-form-input name="tamanho" label="Tamanho" required /></div>
                                <div class="md:col-span-2"><x-form-input name="numero_ca" label="Nº CA" required /></div>
                                <div class="md:col-span-3 flex items-end md:justify-end">
                                    <x-button type="submit" variant="primary" size="sm" class="w-full md:w-auto">Adicionar EPI</x-button>
                                </div>
                            </div>
                        </form>
                        @endif
                        <div class="border border-gray-200 rounded p-3 bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700">EPIs adicionados</h4>
                                <span class="text-xs text-gray-500">{{ $funcionario->episVinculos->count() }} item(ns)</span>
                            </div>
                            <div class="hidden md:grid md:grid-cols-12 gap-2 px-3 py-2 rounded bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                <div class="md:col-span-2">EPI</div>
                                <div class="md:col-span-2">Entrega</div>
                                <div class="md:col-span-2">Vencimento</div>
                                <div class="md:col-span-2">Status</div>
                                <div class="md:col-span-2">Marca</div>
                                <div class="md:col-span-2 text-right">Ações</div>
                            </div>
                            @forelse($funcionario->episVinculos as $epiVinculo)
                                @php
                                    $dataVencimentoEpi = $epiVinculo->data_vencimento ?: $epiVinculo->data_prevista_troca;
                                    $diasParaTroca = $dataVencimentoEpi
                                        ? now()->startOfDay()->diffInDays($dataVencimentoEpi->copy()->startOfDay(), false)
                                        : null;
                                    $badgeEpi = null;
                                    $classeBadgeEpi = '';

                                    if (!is_null($diasParaTroca)) {
                                        if ($diasParaTroca < 0) {
                                            $badgeEpi = 'EPI vencido há ' . abs($diasParaTroca) . ' dia(s)';
                                            $classeBadgeEpi = 'bg-red-100 text-red-700 border-red-200';
                                        } elseif ($diasParaTroca <= 30) {
                                            $badgeEpi = 'Troca prevista em ' . $diasParaTroca . ' dia(s)';
                                            $classeBadgeEpi = 'bg-amber-100 text-amber-700 border-amber-200';
                                        }
                                    }
                                @endphp
                                <form id="update-epi-{{ $epiVinculo->id }}" method="POST" action="{{ route('rh.funcionarios.epis.update', [$funcionario, $epiVinculo]) }}" x-data="{ epiSelecionadoEdit: '{{ (int) $epiVinculo->epi_id }}' }" class="border border-gray-200 rounded p-3 bg-gray-50 space-y-2">
                                    @csrf
                                    @method('PUT')
                                    @if($badgeEpi)
                                        <div>
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold border {{ $classeBadgeEpi }}">{{ $badgeEpi }}</span>
                                        </div>
                                    @endif
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                                        <div class="md:col-span-2">
                                            <select name="epi_id" x-model="epiSelecionadoEdit" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" required>
                                                @foreach($epis as $epi)
                                                    <option value="{{ $epi->id }}" @selected((int)$epiVinculo->epi_id === (int)$epi->id)>{{ $epi->nome }}</option>
                                                @endforeach
                                                <option value="__NOVO__">NOVO EPI</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2"><input type="date" name="data_entrega" value="{{ optional($epiVinculo->data_entrega)->format('Y-m-d') }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" required></div>
                                        <div class="md:col-span-2"><input type="date" name="data_vencimento" value="{{ optional($dataVencimentoEpi)->format('Y-m-d') }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" required></div>
                                        <div class="md:col-span-2"><input type="text" name="status" value="{{ $epiVinculo->status }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" required></div>
                                        <div class="md:col-span-2"><input type="text" name="marca" value="{{ $epiVinculo->marca }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" required></div>
                                        <div class="md:col-span-2 flex md:justify-end gap-2">
                                            @if($isRhRoute)
                                            <x-button type="submit" form="update-epi-{{ $epiVinculo->id }}" variant="secondary" size="sm">Salvar</x-button>
                                            <x-button type="submit" form="delete-epi-{{ $epiVinculo->id }}" variant="danger" size="sm">Excluir</x-button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                                        <div class="md:col-span-3" x-show="epiSelecionadoEdit === '__NOVO__'" x-cloak>
                                            <input type="text" name="epi_nome_customizado" x-bind:disabled="epiSelecionadoEdit !== '__NOVO__'" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Novo EPI">
                                        </div>
                                        <div class="md:col-span-3"><input type="number" name="quantidade" min="1" step="1" value="{{ $epiVinculo->quantidade }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Quantidade" required></div>
                                        <div class="md:col-span-3"><input type="text" name="tamanho" value="{{ $epiVinculo->tamanho }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Tamanho" required></div>
                                        <div class="md:col-span-3"><input type="text" name="numero_ca" value="{{ $epiVinculo->numero_ca }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Nº CA" required></div>
                                    </div>
                                </form>
                                @if($isRhRoute)
                                <form id="delete-epi-{{ $epiVinculo->id }}" method="POST" action="{{ route('rh.funcionarios.epis.destroy', [$funcionario, $epiVinculo]) }}" onsubmit="return confirm('Excluir vínculo de EPI?')" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            @empty
                                <p class="text-sm text-gray-500">Sem EPIs vinculados.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="aba === 'beneficios'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.beneficios.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 border border-gray-200 rounded p-3 bg-gray-50">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select name="tipo" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                                    <option value="VT">VT</option>
                                    <option value="VR">VR</option>
                                    <option value="VA">VA</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <x-form-input name="valor" type="number" step="0.01" min="0" label="Valor" required />
                            <x-form-input name="desconto_percentual" type="number" step="0.01" min="0" max="100" label="Desconto %" :value="0" />
                            <x-form-input name="data_inicio" type="date" label="Início" required />
                            <x-form-input name="data_fim" type="date" label="Fim" />
                            <div class="md:col-span-6 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar Benefício</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="border border-gray-200 rounded p-3 bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700">Benefícios adicionados</h4>
                                <span class="text-xs text-gray-500">{{ $funcionario->beneficios->count() }} item(ns)</span>
                            </div>
                            <div class="hidden md:grid md:grid-cols-6 gap-2 px-3 py-2 rounded bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                <div>Tipo</div><div>Valor</div><div>Desconto</div><div>Início</div><div>Fim</div><div class="text-right">Ações</div>
                            </div>
                            @forelse($funcionario->beneficios as $beneficio)
                                <form id="update-beneficio-{{ $beneficio->id }}" method="POST" action="{{ route('rh.funcionarios.beneficios.update', [$funcionario, $beneficio]) }}" class="grid grid-cols-1 md:grid-cols-6 gap-2 border border-gray-200 rounded p-3 bg-gray-50 items-end">
                                        @csrf
                                        @method('PUT')
                                        <select name="tipo" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                            @foreach(['VT','VR','VA','Outro'] as $tipo)
                                                <option value="{{ $tipo }}" @selected($beneficio->tipo === $tipo)>{{ $tipo }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" name="valor" step="0.01" min="0" value="{{ (float)$beneficio->valor }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="number" name="desconto_percentual" step="0.01" min="0" max="100" value="{{ (float)$beneficio->desconto_percentual }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <input type="date" name="data_inicio" value="{{ optional($beneficio->data_inicio)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="date" name="data_fim" value="{{ optional($beneficio->data_fim)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <div class="flex gap-2 justify-end">
                                            @if($isRhRoute)
                                            <x-button type="submit" form="update-beneficio-{{ $beneficio->id }}" variant="secondary" size="sm">Salvar</x-button>
                                            <x-button type="submit" form="delete-beneficio-{{ $beneficio->id }}" variant="danger" size="sm">Excluir</x-button>
                                            @endif
                                        </div>
                                </form>
                                @if($isRhRoute)
                                <form id="delete-beneficio-{{ $beneficio->id }}" method="POST" action="{{ route('rh.funcionarios.beneficios.destroy', [$funcionario, $beneficio]) }}" onsubmit="return confirm('Excluir benefício?')" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            @empty
                                <p class="text-sm text-gray-500">Sem benefícios cadastrados.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="aba === 'jornada'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.jornadas.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 border border-gray-200 rounded p-3 bg-gray-50">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jornada</label>
                                <select name="jornada_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                                    <option value="">Selecione</option>
                                    @foreach($jornadas as $jornada)
                                        <option value="{{ $jornada->id }}">{{ $jornada->nome }} ({{ $jornada->carga_horaria_semanal }}h)</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-form-input name="data_inicio" type="date" label="Iniciar a partir de" required />
                            <x-form-input name="data_fim" type="date" label="Até (opcional)" />
                            <div class="md:col-span-4 text-xs text-gray-600 bg-cyan-50 border border-cyan-200 rounded p-2">
                                A nova jornada será aplicada somente da data informada para frente. O histórico anterior será preservado automaticamente.
                            </div>
                            <div class="md:col-span-4 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Vincular Jornada</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="border border-gray-200 rounded p-3 bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700">Jornadas vinculadas</h4>
                                <span class="text-xs text-gray-500">{{ $funcionario->jornadasVinculos->count() }} item(ns)</span>
                            </div>
                            <div class="hidden lg:grid lg:grid-cols-12 gap-3 px-3 py-2 rounded bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                <div class="lg:col-span-4">Jornada</div><div class="lg:col-span-3">Início</div><div class="lg:col-span-3">Fim</div><div class="lg:col-span-2 text-right">Ações</div>
                            </div>
                            @forelse($funcionario->jornadasVinculos as $jornadaVinculo)
                                @php
                                    $inicio = optional($jornadaVinculo->data_inicio);
                                    $fim = optional($jornadaVinculo->data_fim);
                                    $hoje = now()->startOfDay();
                                    $jaIniciada = $inicio && $inicio->copy()->startOfDay()->lt($hoje);
                                @endphp
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 border border-gray-200 rounded p-3 items-end bg-gray-50">
                                    <div class="border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50 lg:col-span-3">{{ $jornadaVinculo->jornada?->nome ?? '—' }}</div>
                                    <div class="border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50 lg:col-span-3">Início: {{ optional($jornadaVinculo->data_inicio)->format('d/m/Y') ?? '—' }}</div>
                                    <div class="border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50 lg:col-span-3">Fim: {{ optional($jornadaVinculo->data_fim)->format('d/m/Y') ?? 'Em aberto' }}</div>
                                    <div class="lg:col-span-3">
                                        @if($isRhRoute && !$jaIniciada)
                                            <div class="flex flex-col gap-2 lg:items-end">
                                                <form method="POST" action="{{ route('rh.funcionarios.jornadas.update', [$funcionario, $jornadaVinculo]) }}" class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_auto] gap-2 w-full">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="date" name="data_fim" value="{{ optional($jornadaVinculo->data_fim)->format('Y-m-d') }}" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                                    <x-button type="submit" variant="secondary" size="sm">Salvar Fim</x-button>
                                                </form>
                                                <form method="POST" action="{{ route('rh.funcionarios.jornadas.destroy', [$funcionario, $jornadaVinculo]) }}" onsubmit="return confirm('Excluir vínculo de jornada futura?')" class="lg:self-end">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-button type="submit" variant="danger" size="sm">Excluir</x-button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500">Histórico bloqueado</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Sem jornada vinculada.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="aba === 'ferias'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.ferias.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 border border-gray-200 rounded p-3 bg-gray-50">
                            @csrf
                            <x-form-input name="periodo_aquisitivo_inicio" type="date" label="Aquisitivo Início" required />
                            <x-form-input name="periodo_aquisitivo_fim" type="date" label="Aquisitivo Fim" required />
                            <x-form-input name="periodo_gozo_inicio" type="date" label="Gozo Início" />
                            <x-form-input name="periodo_gozo_fim" type="date" label="Gozo Fim" />
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                                    @foreach(['pendente' => 'Pendente', 'programada' => 'Programada', 'gozo' => 'Em gozo', 'concluida' => 'Concluída'] as $valor => $rotulo)
                                        <option value="{{ $valor }}">{{ $rotulo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-5 text-xs text-gray-600 bg-cyan-50 border border-cyan-200 rounded p-2">
                                O prazo concessivo termina em até 12 meses após o fim do período aquisitivo. O sistema sinaliza férias próximas do vencimento.
                            </div>
                            <div class="md:col-span-5 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar Férias</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="border border-gray-200 rounded p-3 bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700">Registros de férias</h4>
                                <span class="text-xs text-gray-500">{{ $funcionario->ferias->count() }} item(ns)</span>
                            </div>
                            <div class="hidden md:grid md:grid-cols-6 gap-2 px-3 py-2 rounded bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                <div>Aquisitivo início</div><div>Aquisitivo fim</div><div>Gozo início</div><div>Gozo fim</div><div>Status</div><div class="text-right">Ações</div>
                            </div>
                            @forelse($funcionario->ferias as $ferias)
                                @php
                                    $prazoConcessivo = $ferias->periodo_aquisitivo_fim
                                        ? $ferias->periodo_aquisitivo_fim->copy()->addYear()->startOfDay()
                                        : null;
                                    $diasConcessivo = $prazoConcessivo
                                        ? now()->startOfDay()->diffInDays($prazoConcessivo, false)
                                        : null;
                                    $badgeFerias = null;
                                    $classeBadgeFerias = '';

                                    if (!is_null($diasConcessivo)) {
                                        if ($diasConcessivo < 0) {
                                            $badgeFerias = 'Prazo concessivo vencido há ' . abs($diasConcessivo) . ' dia(s)';
                                            $classeBadgeFerias = 'bg-red-100 text-red-700 border-red-200';
                                        } elseif ($diasConcessivo <= 60) {
                                            $badgeFerias = 'Prazo concessivo vence em ' . $diasConcessivo . ' dia(s)';
                                            $classeBadgeFerias = 'bg-amber-100 text-amber-700 border-amber-200';
                                        }
                                    }
                                @endphp
                                <div class="grid grid-cols-1 md:grid-cols-6 gap-2 border border-gray-200 rounded p-3 bg-gray-50">
                                    @if($prazoConcessivo)
                                        <div class="md:col-span-6 flex flex-wrap items-center gap-2 text-xs">
                                            <span class="text-gray-600">Prazo concessivo até {{ $prazoConcessivo->format('d/m/Y') }}</span>
                                            @if($badgeFerias)
                                                <span class="inline-flex items-center px-2 py-1 rounded font-semibold border {{ $classeBadgeFerias }}">{{ $badgeFerias }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('rh.funcionarios.ferias.update', [$funcionario, $ferias]) }}" class="contents">
                                        @csrf
                                        @method('PUT')
                                        <input type="date" name="periodo_aquisitivo_inicio" value="{{ optional($ferias->periodo_aquisitivo_inicio)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="date" name="periodo_aquisitivo_fim" value="{{ optional($ferias->periodo_aquisitivo_fim)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="date" name="periodo_gozo_inicio" value="{{ optional($ferias->periodo_gozo_inicio)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <input type="date" name="periodo_gozo_fim" value="{{ optional($ferias->periodo_gozo_fim)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <select name="status" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                            @foreach(['pendente' => 'Pendente', 'programada' => 'Programada', 'gozo' => 'Em gozo', 'concluida' => 'Concluída'] as $valor => $rotulo)
                                                <option value="{{ $valor }}" @selected($ferias->status === $valor)>{{ $rotulo }}</option>
                                            @endforeach
                                        </select>
                                        <div class="flex gap-2 justify-end">
                                            @if($isRhRoute)
                                            <x-button type="submit" variant="secondary" size="sm">Salvar</x-button>
                                            @endif
                                    </form>
                                            @if($isRhRoute)
                                            <form method="POST" action="{{ route('rh.funcionarios.ferias.destroy', [$funcionario, $ferias]) }}" onsubmit="return confirm('Excluir registro de férias?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" size="sm">Excluir</x-button>
                                            </form>
                                            @endif
                                        </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Sem registros de férias.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="aba === 'advertencias'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.advertencias.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 border border-gray-200 rounded p-3 bg-gray-50">
                            @csrf
                            <x-form-input name="data" type="date" label="Data" required />
                            <x-form-input name="tipo" label="Tipo" required />
                            <x-form-input name="descricao" label="Descrição" required />
                            <div class="md:col-span-1 flex items-end justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar Advertência</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="border border-gray-200 rounded p-3 bg-white space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700">Advertências registradas</h4>
                                <span class="text-xs text-gray-500">{{ $funcionario->advertencias->count() }} item(ns)</span>
                            </div>
                            <div class="hidden md:grid md:grid-cols-4 gap-2 px-3 py-2 rounded bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                <div>Data</div><div>Tipo</div><div>Descrição</div><div class="text-right">Ações</div>
                            </div>
                            @forelse($funcionario->advertencias as $advertencia)
                                <form id="update-advertencia-{{ $advertencia->id }}" method="POST" action="{{ route('rh.funcionarios.advertencias.update', [$funcionario, $advertencia]) }}" class="grid grid-cols-1 md:grid-cols-4 gap-2 border border-gray-200 rounded p-3 bg-gray-50 items-end">
                                        @csrf
                                        @method('PUT')
                                        <input type="date" name="data" value="{{ optional($advertencia->data)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="text" name="tipo" value="{{ $advertencia->tipo }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="text" name="descricao" value="{{ $advertencia->descricao }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <div class="flex gap-2 justify-end">
                                            @if($isRhRoute)
                                            <x-button type="submit" form="update-advertencia-{{ $advertencia->id }}" variant="secondary" size="sm">Salvar</x-button>
                                            <x-button type="submit" form="delete-advertencia-{{ $advertencia->id }}" variant="danger" size="sm">Excluir</x-button>
                                            @endif
                                        </div>
                                </form>
                                @if($isRhRoute)
                                <form id="delete-advertencia-{{ $advertencia->id }}" method="POST" action="{{ route('rh.funcionarios.advertencias.destroy', [$funcionario, $advertencia]) }}" onsubmit="return confirm('Excluir advertência?')" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            @empty
                                <p class="text-sm text-gray-500">Sem advertências registradas.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
