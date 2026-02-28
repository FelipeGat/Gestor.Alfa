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

                <div x-data="{ aba: 'documentos' }" class="space-y-5">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="aba = 'documentos'" class="px-3 py-2 rounded border text-sm" :class="aba === 'documentos' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Documentos</button>
                        <button type="button" @click="aba = 'epis'" class="px-3 py-2 rounded border text-sm" :class="aba === 'epis' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">EPIs</button>
                        <button type="button" @click="aba = 'beneficios'" class="px-3 py-2 rounded border text-sm" :class="aba === 'beneficios' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Benefícios</button>
                        <button type="button" @click="aba = 'jornada'" class="px-3 py-2 rounded border text-sm" :class="aba === 'jornada' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Jornada</button>
                        <button type="button" @click="aba = 'ferias'" class="px-3 py-2 rounded border text-sm" :class="aba === 'ferias' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Férias</button>
                        <button type="button" @click="aba = 'advertencias'" class="px-3 py-2 rounded border text-sm" :class="aba === 'advertencias' ? 'bg-gray-100 border-gray-400 font-semibold' : 'bg-white border-gray-200'">Advertências</button>
                    </div>

                    <div x-show="aba === 'documentos'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.documentos.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 border border-gray-200 rounded p-3">
                            @csrf
                            <x-form-input name="tipo" label="Tipo" required />
                            <x-form-input name="numero" label="Número" />
                            <x-form-input name="data_emissao" type="date" label="Emissão" />
                            <x-form-input name="data_vencimento" type="date" label="Vencimento" />
                            <x-form-input name="arquivo" label="Arquivo" />
                            <x-form-input name="status" label="Status" :value="'ativo'" />
                            <div class="md:col-span-6 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar Documento</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="space-y-2">
                            @forelse($funcionario->documentos as $documento)
                                <form method="POST" action="{{ route('rh.funcionarios.documentos.update', [$funcionario, $documento]) }}" class="grid grid-cols-1 md:grid-cols-7 gap-2 border border-gray-200 rounded p-3">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="tipo" value="{{ $documento->tipo }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                    <input type="text" name="numero" value="{{ $documento->numero }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                    <input type="date" name="data_emissao" value="{{ optional($documento->data_emissao)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                    <input type="date" name="data_vencimento" value="{{ optional($documento->data_vencimento)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                    <input type="text" name="arquivo" value="{{ $documento->arquivo }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                    <input type="text" name="status" value="{{ $documento->status }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                    <div class="flex gap-2 justify-end">
                                        @if($isRhRoute)
                                        <x-button type="submit" variant="secondary" size="sm">Salvar</x-button>
                                        @endif
                                </form>
                                        @if($isRhRoute)
                                        <form method="POST" action="{{ route('rh.funcionarios.documentos.destroy', [$funcionario, $documento]) }}" onsubmit="return confirm('Excluir documento?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="danger" size="sm">Excluir</x-button>
                                        </form>
                                        @endif
                                    </div>
                            @empty
                                <p class="text-sm text-gray-500">Sem documentos cadastrados.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="aba === 'epis'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.epis.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 border border-gray-200 rounded p-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">EPI</label>
                                <select name="epi_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    <option value="">Selecione</option>
                                    @foreach($epis as $epi)
                                        <option value="{{ $epi->id }}">{{ $epi->nome }} {{ $epi->ca ? '(CA ' . $epi->ca . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-form-input name="data_entrega" type="date" label="Data de Entrega" required />
                            <x-form-input name="data_prevista_troca" type="date" label="Troca Prevista" />
                            <x-form-input name="status" label="Status" :value="'ativo'" required />
                            <div class="md:col-span-4 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar EPI</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="space-y-2">
                            @forelse($funcionario->episVinculos as $epiVinculo)
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-2 border border-gray-200 rounded p-3">
                                    <form method="POST" action="{{ route('rh.funcionarios.epis.update', [$funcionario, $epiVinculo]) }}" class="contents">
                                        @csrf
                                        @method('PUT')
                                        <select name="epi_id" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                            @foreach($epis as $epi)
                                                <option value="{{ $epi->id }}" @selected((int)$epiVinculo->epi_id === (int)$epi->id)>{{ $epi->nome }}</option>
                                            @endforeach
                                        </select>
                                        <input type="date" name="data_entrega" value="{{ optional($epiVinculo->data_entrega)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="date" name="data_prevista_troca" value="{{ optional($epiVinculo->data_prevista_troca)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <input type="text" name="status" value="{{ $epiVinculo->status }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <div class="flex gap-2 justify-end">
                                            @if($isRhRoute)
                                            <x-button type="submit" variant="secondary" size="sm">Salvar</x-button>
                                            @endif
                                    </form>
                                            @if($isRhRoute)
                                            <form method="POST" action="{{ route('rh.funcionarios.epis.destroy', [$funcionario, $epiVinculo]) }}" onsubmit="return confirm('Excluir vínculo de EPI?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" size="sm">Excluir</x-button>
                                            </form>
                                            @endif
                                        </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Sem EPIs vinculados.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="aba === 'beneficios'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.beneficios.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 border border-gray-200 rounded p-3">
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
                            <div class="md:col-span-5 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar Benefício</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="space-y-2">
                            @forelse($funcionario->beneficios as $beneficio)
                                <div class="grid grid-cols-1 md:grid-cols-6 gap-2 border border-gray-200 rounded p-3">
                                    <form method="POST" action="{{ route('rh.funcionarios.beneficios.update', [$funcionario, $beneficio]) }}" class="contents">
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
                                            <x-button type="submit" variant="secondary" size="sm">Salvar</x-button>
                                            @endif
                                    </form>
                                            @if($isRhRoute)
                                            <form method="POST" action="{{ route('rh.funcionarios.beneficios.destroy', [$funcionario, $beneficio]) }}" onsubmit="return confirm('Excluir benefício?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" size="sm">Excluir</x-button>
                                            </form>
                                            @endif
                                        </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Sem benefícios cadastrados.</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="aba === 'jornada'" x-cloak class="space-y-3">
                        @if($isRhRoute)
                        <form method="POST" action="{{ route('rh.funcionarios.jornadas.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-200 rounded p-3">
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
                            <x-form-input name="data_inicio" type="date" label="Data Início" required />
                            <x-form-input name="data_fim" type="date" label="Data Fim" />
                            <div class="md:col-span-3 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar Jornada</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="space-y-2">
                            @forelse($funcionario->jornadasVinculos as $jornadaVinculo)
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 border border-gray-200 rounded p-3">
                                    <form method="POST" action="{{ route('rh.funcionarios.jornadas.update', [$funcionario, $jornadaVinculo]) }}" class="contents">
                                        @csrf
                                        @method('PUT')
                                        <select name="jornada_id" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                            @foreach($jornadas as $jornada)
                                                <option value="{{ $jornada->id }}" @selected((int)$jornadaVinculo->jornada_id === (int)$jornada->id)>{{ $jornada->nome }}</option>
                                            @endforeach
                                        </select>
                                        <input type="date" name="data_inicio" value="{{ optional($jornadaVinculo->data_inicio)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="date" name="data_fim" value="{{ optional($jornadaVinculo->data_fim)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <div class="flex gap-2 justify-end">
                                            @if($isRhRoute)
                                            <x-button type="submit" variant="secondary" size="sm">Salvar</x-button>
                                            @endif
                                    </form>
                                            @if($isRhRoute)
                                            <form method="POST" action="{{ route('rh.funcionarios.jornadas.destroy', [$funcionario, $jornadaVinculo]) }}" onsubmit="return confirm('Excluir vínculo de jornada?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" size="sm">Excluir</x-button>
                                            </form>
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
                        <form method="POST" action="{{ route('rh.funcionarios.ferias.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 border border-gray-200 rounded p-3">
                            @csrf
                            <x-form-input name="periodo_aquisitivo_inicio" type="date" label="Aquisitivo Início" required />
                            <x-form-input name="periodo_aquisitivo_fim" type="date" label="Aquisitivo Fim" required />
                            <x-form-input name="periodo_gozo_inicio" type="date" label="Gozo Início" />
                            <x-form-input name="periodo_gozo_fim" type="date" label="Gozo Fim" />
                            <x-form-input name="status" label="Status" :value="'pendente'" required />
                            <div class="md:col-span-5 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar Férias</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="space-y-2">
                            @forelse($funcionario->ferias as $ferias)
                                <div class="grid grid-cols-1 md:grid-cols-6 gap-2 border border-gray-200 rounded p-3">
                                    <form method="POST" action="{{ route('rh.funcionarios.ferias.update', [$funcionario, $ferias]) }}" class="contents">
                                        @csrf
                                        @method('PUT')
                                        <input type="date" name="periodo_aquisitivo_inicio" value="{{ optional($ferias->periodo_aquisitivo_inicio)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="date" name="periodo_aquisitivo_fim" value="{{ optional($ferias->periodo_aquisitivo_fim)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="date" name="periodo_gozo_inicio" value="{{ optional($ferias->periodo_gozo_inicio)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <input type="date" name="periodo_gozo_fim" value="{{ optional($ferias->periodo_gozo_fim)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <input type="text" name="status" value="{{ $ferias->status }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
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
                        <form method="POST" action="{{ route('rh.funcionarios.advertencias.store', $funcionario) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-200 rounded p-3">
                            @csrf
                            <x-form-input name="data" type="date" label="Data" required />
                            <x-form-input name="tipo" label="Tipo" required />
                            <x-form-input name="descricao" label="Descrição" required />
                            <div class="md:col-span-3 flex justify-end">
                                <x-button type="submit" variant="primary" size="sm">Adicionar Advertência</x-button>
                            </div>
                        </form>
                        @endif
                        <div class="space-y-2">
                            @forelse($funcionario->advertencias as $advertencia)
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 border border-gray-200 rounded p-3">
                                    <form method="POST" action="{{ route('rh.funcionarios.advertencias.update', [$funcionario, $advertencia]) }}" class="contents">
                                        @csrf
                                        @method('PUT')
                                        <input type="date" name="data" value="{{ optional($advertencia->data)->format('Y-m-d') }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="text" name="tipo" value="{{ $advertencia->tipo }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <input type="text" name="descricao" value="{{ $advertencia->descricao }}" class="border border-gray-300 rounded px-2 py-1 text-sm" required>
                                        <div class="flex gap-2 justify-end">
                                            @if($isRhRoute)
                                            <x-button type="submit" variant="secondary" size="sm">Salvar</x-button>
                                            @endif
                                    </form>
                                            @if($isRhRoute)
                                            <form method="POST" action="{{ route('rh.funcionarios.advertencias.destroy', [$funcionario, $advertencia]) }}" onsubmit="return confirm('Excluir advertência?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" size="sm">Excluir</x-button>
                                            </form>
                                            @endif
                                        </div>
                                </div>
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
