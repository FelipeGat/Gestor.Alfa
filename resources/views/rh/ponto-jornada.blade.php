<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'RH', 'url' => route('rh.dashboard')],
            ['label' => 'Ponto & Jornada']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
             x-data="{
                modalAjusteAberto: false,
                modalLoteAberto: false,
                     modalRelatorioAjustesAberto: false,
                     modalDetalhesBatidaAberto: false,
                ajusteSecao1: {
                    funcionario_id: '',
                    funcionario_nome: '',
                    data_referencia: '',
                    campo_batida: '',
                    campo_label: '',
                    horario_batida: ''
                },
                loteAjuste: {
                    funcionario_id: '',
                    funcionario_nome: '',
                    data_inicio: '',
                    data_fim: '',
                    tipo_lote: 'atestado',
                    tipo_ajuste: 'atestado_medico',
                    horario_entrada: '',
                    horario_intervalo_inicio: '',
                    horario_intervalo_fim: '',
                    horario_saida: '',
                    sobrescrever_campos: false
                },
                detalhesBatida: {
                    funcionario_nome: '',
                    data_referencia: '',
                    entrada: { horario: '—', foto_url: null, latitude: null, longitude: null },
                    intervalo_inicio: { horario: '—', foto_url: null, latitude: null, longitude: null },
                    intervalo_fim: { horario: '—', foto_url: null, latitude: null, longitude: null },
                    saida: { horario: '—', foto_url: null, latitude: null, longitude: null }
                },
                abrirModalAjuste(payload) {
                    this.ajusteSecao1 = payload;
                    this.modalAjusteAberto = true;
                },
                fecharModalAjuste() {
                    this.modalAjusteAberto = false;
                },
                abrirModalLote(payload) {
                    this.loteAjuste = {
                        funcionario_id: payload.funcionario_id || '',
                        funcionario_nome: payload.funcionario_nome || '',
                        data_inicio: payload.data_inicio || '',
                        data_fim: payload.data_fim || '',
                        tipo_lote: payload.tipo_lote || 'atestado',
                        tipo_ajuste: payload.tipo_ajuste || 'atestado_medico',
                        horario_entrada: payload.horario_entrada || '',
                        horario_intervalo_inicio: payload.horario_intervalo_inicio || '',
                        horario_intervalo_fim: payload.horario_intervalo_fim || '',
                        horario_saida: payload.horario_saida || '',
                        sobrescrever_campos: false
                    };
                    this.modalLoteAberto = true;
                },
                fecharModalLote() {
                    this.modalLoteAberto = false;
                },
                abrirModalRelatorioAjustes() {
                    this.modalRelatorioAjustesAberto = true;
                },
                fecharModalRelatorioAjustes() {
                    this.modalRelatorioAjustesAberto = false;
                },
                abrirModalDetalhesBatida(payload) {
                    this.detalhesBatida = payload;
                    this.modalDetalhesBatidaAberto = true;
                },
                fecharModalDetalhesBatida() {
                    this.modalDetalhesBatidaAberto = false;
                }
             }">
            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                @php
                    $dataAtualFiltro = !empty($filtros['inicio'])
                        ? \Carbon\Carbon::parse($filtros['inicio'])
                        : now();
                    $mesAnterior = $dataAtualFiltro->copy()->subMonth();
                    $proximoMes = $dataAtualFiltro->copy()->addMonth();
                    $mesesNomes = [
                        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
                    ];
                    $mesAtualNome = ($mesesNomes[$dataAtualFiltro->month] ?? $dataAtualFiltro->format('m')) . '/' . $dataAtualFiltro->year;
                    $periodoLabel = \Carbon\Carbon::parse($filtros['inicio'])->format('d/m/Y') . ' até ' . \Carbon\Carbon::parse($filtros['fim'])->format('d/m/Y');
                @endphp

                <form method="GET" action="{{ route('rh.ponto-jornada.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Navegação Rápida</label>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('rh.ponto-jornada.index', array_filter([
                                    'inicio' => $mesAnterior->startOfMonth()->format('Y-m-d'),
                                    'fim' => $mesAnterior->endOfMonth()->format('Y-m-d'),
                                    'funcionario_id' => $filtros['funcionario_id'] ?? null,
                                ])) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                                    title="Mês anterior">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>

                                <div class="flex-1 text-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm">
                                    {{ $mesAtualNome }}
                                </div>

                                <a href="{{ route('rh.ponto-jornada.index', array_filter([
                                    'inicio' => $proximoMes->startOfMonth()->format('Y-m-d'),
                                    'fim' => $proximoMes->endOfMonth()->format('Y-m-d'),
                                    'funcionario_id' => $filtros['funcionario_id'] ?? null,
                                ])) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 bg-white hover:bg-gray-50 text-gray-600 rounded-lg transition border border-gray-300 shadow-sm"
                                    title="Próximo mês">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Funcionário</label>
                            <div class="flex items-center gap-2">
                                <select name="funcionario_id" class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm">
                                    <option value="">Todos</option>
                                    @foreach($funcionarios as $funcionario)
                                        <option value="{{ $funcionario->id }}" @selected((string) ($filtros['funcionario_id'] ?? '') === (string) $funcionario->id)>
                                            {{ $funcionario->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-button type="submit" variant="primary" size="sm">Filtrar</x-button>
                                <x-button href="{{ route('rh.ponto-jornada.index') }}" variant="secondary" size="sm">Limpar</x-button>
                            </div>
                        </div>

                        <div class="text-sm text-gray-600 md:text-right">
                            Período: <span class="font-semibold">{{ $periodoLabel }}</span>
                        </div>
                    </div>

                    <div x-data="{ mostrarPeriodo: false }">
                        <button type="button" @click="mostrarPeriodo = !mostrarPeriodo" class="text-sm text-[#3f9cae] hover:text-[#2d7a8a] font-medium flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="mostrarPeriodo ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span x-text="mostrarPeriodo ? 'Ocultar período personalizado' : 'Escolher período personalizado'"></span>
                        </button>

                        <div x-show="mostrarPeriodo" x-transition class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data início</label>
                                <input type="date" name="inicio" value="{{ $filtros['inicio'] }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data fim</label>
                                <input type="date" name="fim" value="{{ $filtros['fim'] }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div class="flex sm:items-end gap-2">
                                <x-button type="submit" variant="primary" size="sm">Filtrar</x-button>
                                <x-button href="{{ route('rh.ponto-jornada.index') }}" variant="secondary" size="sm">Limpar</x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if(!empty($fechamentoDisponivel))
                <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Fechamento de Ponto</h2>
                    <form method="POST" action="{{ route('rh.ponto-jornada.fechamentos.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        <input type="hidden" name="competencia" value="{{ $competenciaAtualFechamento }}">
                        <input type="hidden" name="inicio" value="{{ $filtros['inicio'] }}">
                        <input type="hidden" name="fim" value="{{ $filtros['fim'] }}">
                        <input type="hidden" name="funcionario_id_filtro" value="{{ $filtros['funcionario_id'] ?? '' }}">

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Funcionário para fechamento</label>
                            <select name="funcionario_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                                <option value="">Selecione</option>
                                @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}" @selected((string) ($filtros['funcionario_id'] ?? '') === (string) $funcionario->id)>{{ $funcionario->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                            <input type="text" value="{{ \Carbon\Carbon::parse($competenciaAtualFechamento)->format('m/Y') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" readonly>
                        </div>

                        <div class="flex">
                            <x-button type="submit" variant="primary" size="sm">Marcar Fechamento</x-button>
                        </div>
                    </form>

                    @if($fechamentosCompetencia->isNotEmpty())
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            <p class="text-sm font-semibold text-gray-800 mb-2">Funcionários já fechados nesta competência</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($funcionarios as $funcionario)
                                    @php $fechamento = $fechamentosCompetencia->get((int) $funcionario->id); @endphp
                                    @if($fechamento)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            {{ $funcionario->nome }} · {{ optional($fechamento->fechado_em)->format('d/m/Y H:i') }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">SEÇÃO 1 – Jornada Legal (Registro de Ponto do Portal)</h2>
                @php
                    $columnsLegal = [
                        ['label' => 'Funcionário'],
                        ['label' => 'Data'],
                        ['label' => 'Dia'],
                        ['label' => 'Entrada'],
                        ['label' => 'Início Intervalo'],
                        ['label' => 'Fim Intervalo'],
                        ['label' => 'Saída'],
                        ['label' => 'Total'],
                        ['label' => 'Extra 50%'],
                        ['label' => 'Extra 100%'],
                        ['label' => 'Atraso'],
                        ['label' => 'Ações'],
                    ];
                @endphp

                <x-table :columns="$columnsLegal" :data="$jornadaLegal['rows']" emptyMessage="Sem registros de jornada legal no período.">
                    @foreach($jornadaLegal['rows'] as $linha)
                        @php
                            $estiloLinha = '';
                            $ehFalta = ($linha['status'] ?? '') === 'Falta';
                            $tipoCelula = $ehFalta ? 'danger' : 'default';
                            $ehDomingoOuFeriado = !empty($linha['eh_domingo']) || !empty($linha['eh_feriado']);
                            $edicaoBloqueada = !empty($linha['edicao_bloqueada']);

                            if (!empty($linha['eh_feriado'])) {
                                $estiloLinha = 'background-color: #fff7ed;';
                            } elseif (!empty($linha['eh_domingo'])) {
                                $estiloLinha = 'background-color: #fef2f2;';
                            }
                        @endphp
                        <tr @if($estiloLinha) style="{{ $estiloLinha }}" @endif>
                            @php
                                $partesNome = collect(explode(' ', trim((string) ($linha['funcionario'] ?? ''))))
                                    ->filter(fn ($p) => $p !== '')
                                    ->values();
                                $primeiroNome = $partesNome->first() ?? '—';
                                $ultimoSobrenome = $partesNome->count() > 1 ? $partesNome->last() : '';
                                $nomeExibicao = trim($primeiroNome . ' ' . $ultimoSobrenome);

                                if (mb_strlen($nomeExibicao) > 18 && $ultimoSobrenome !== '') {
                                    $inicialPrimeiro = mb_substr($primeiroNome, 0, 1);
                                    $nomeExibicao = $inicialPrimeiro . '. ' . $ultimoSobrenome;
                                }
                            @endphp
                            <x-table-cell :type="$tipoCelula">
                                <span class="whitespace-nowrap" title="{{ $linha['funcionario'] }}">{{ $nomeExibicao }}</span>
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">
                                @php
                                    $dataCurta = (strlen((string) ($linha['data'] ?? '')) >= 5)
                                        ? substr((string) $linha['data'], 0, 5)
                                        : ($linha['data'] ?? '—');
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span>{{ $dataCurta }}</span>
                                </div>
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">
                                <div class="flex items-center gap-2">
                                    <span>{{ $linha['dia'] ?? '—' }}</span>
                                </div>
                                @if(!empty($linha['eh_feriado']))
                                    <div class="text-xs text-amber-800 mt-1">Feriado</div>
                                @endif
                            </x-table-cell>
                            @php
                                $classeEntrada = !empty($linha['entrada_corrigida_manual'])
                                    ? 'text-blue-700 font-semibold'
                                    : ($ehDomingoOuFeriado ? 'text-red-700 font-semibold' : 'text-gray-800');
                                $classeIntervaloInicio = !empty($linha['intervalo_inicio_corrigida_manual'])
                                    ? 'text-blue-700 font-semibold'
                                    : ($ehDomingoOuFeriado ? 'text-red-700 font-semibold' : 'text-gray-800');
                                $classeIntervaloFim = !empty($linha['intervalo_fim_corrigida_manual'])
                                    ? 'text-blue-700 font-semibold'
                                    : ($ehDomingoOuFeriado ? 'text-red-700 font-semibold' : 'text-gray-800');
                                $classeSaida = !empty($linha['saida_corrigida_manual'])
                                    ? 'text-blue-700 font-semibold'
                                    : ($ehDomingoOuFeriado ? 'text-red-700 font-semibold' : 'text-gray-800');
                            @endphp
                            <x-table-cell :type="$tipoCelula">
                                @php $podeEditarEntrada = (!$edicaoBloqueada) && (($linha['entrada'] ?? '—') === '—' || !empty($linha['entrada_corrigida_manual'])); @endphp
                                @if($podeEditarEntrada)
                                    @php
                                        $classeEntradaEditavel = !empty($linha['entrada_corrigida_manual'])
                                            ? 'text-blue-700 font-semibold'
                                            : ($ehDomingoOuFeriado ? 'text-red-700 font-semibold' : 'text-blue-700 font-semibold');
                                    @endphp
                                    <button type="button"
                                            class="{{ $classeEntradaEditavel }} hover:underline underline-offset-2"
                                            @click="abrirModalAjuste({ funcionario_id: {{ (int) ($linha['funcionario_id'] ?? 0) }}, funcionario_nome: {{ \Illuminate\Support\Js::from($linha['funcionario'] ?? '') }}, data_referencia: {{ \Illuminate\Support\Js::from($linha['data_iso'] ?? '') }}, campo_batida: 'entrada_em', campo_label: 'Entrada', horario_batida: {{ \Illuminate\Support\Js::from($linha['entrada_sugerida'] ?? '08:00') }} })">{{ $linha['entrada'] ?? '—' }}</button>
                                @else
                                    <span class="{{ $classeEntrada }}">{{ $linha['entrada'] }}</span>
                                @endif
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">
                                @php $podeEditarIntervaloInicio = (!$edicaoBloqueada) && (($linha['intervalo_inicio'] ?? '—') === '—' || !empty($linha['intervalo_inicio_corrigida_manual'])); @endphp
                                @if($podeEditarIntervaloInicio)
                                    @php
                                        $classeIntervaloInicioEditavel = !empty($linha['intervalo_inicio_corrigida_manual'])
                                            ? 'text-blue-700 font-semibold'
                                            : ($ehDomingoOuFeriado ? 'text-red-700 font-semibold' : 'text-blue-700 font-semibold');
                                    @endphp
                                    <button type="button"
                                            class="{{ $classeIntervaloInicioEditavel }} hover:underline underline-offset-2"
                                            @click="abrirModalAjuste({ funcionario_id: {{ (int) ($linha['funcionario_id'] ?? 0) }}, funcionario_nome: {{ \Illuminate\Support\Js::from($linha['funcionario'] ?? '') }}, data_referencia: {{ \Illuminate\Support\Js::from($linha['data_iso'] ?? '') }}, campo_batida: 'intervalo_inicio_em', campo_label: 'Saída almoço', horario_batida: {{ \Illuminate\Support\Js::from($linha['intervalo_inicio_sugerida'] ?? '12:00') }} })">{{ $linha['intervalo_inicio'] ?? '—' }}</button>
                                @else
                                    <span class="{{ $classeIntervaloInicio }}">{{ $linha['intervalo_inicio'] }}</span>
                                @endif
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">
                                @php $podeEditarIntervaloFim = (!$edicaoBloqueada) && (($linha['intervalo_fim'] ?? '—') === '—' || !empty($linha['intervalo_fim_corrigida_manual'])); @endphp
                                @if($podeEditarIntervaloFim)
                                    @php
                                        $classeIntervaloFimEditavel = !empty($linha['intervalo_fim_corrigida_manual'])
                                            ? 'text-blue-700 font-semibold'
                                            : ($ehDomingoOuFeriado ? 'text-red-700 font-semibold' : 'text-blue-700 font-semibold');
                                    @endphp
                                    <button type="button"
                                            class="{{ $classeIntervaloFimEditavel }} hover:underline underline-offset-2"
                                            @click="abrirModalAjuste({ funcionario_id: {{ (int) ($linha['funcionario_id'] ?? 0) }}, funcionario_nome: {{ \Illuminate\Support\Js::from($linha['funcionario'] ?? '') }}, data_referencia: {{ \Illuminate\Support\Js::from($linha['data_iso'] ?? '') }}, campo_batida: 'intervalo_fim_em', campo_label: 'Retorno almoço', horario_batida: {{ \Illuminate\Support\Js::from($linha['intervalo_fim_sugerida'] ?? '13:00') }} })">{{ $linha['intervalo_fim'] ?? '—' }}</button>
                                @else
                                    <span class="{{ $classeIntervaloFim }}">{{ $linha['intervalo_fim'] }}</span>
                                @endif
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">
                                @php $podeEditarSaida = (!$edicaoBloqueada) && (($linha['saida'] ?? '—') === '—' || !empty($linha['saida_corrigida_manual'])); @endphp
                                @if($podeEditarSaida)
                                    @php
                                        $classeSaidaEditavel = !empty($linha['saida_corrigida_manual'])
                                            ? 'text-blue-700 font-semibold'
                                            : ($ehDomingoOuFeriado ? 'text-red-700 font-semibold' : 'text-blue-700 font-semibold');
                                    @endphp
                                    <button type="button"
                                            class="{{ $classeSaidaEditavel }} hover:underline underline-offset-2"
                                            @click="abrirModalAjuste({ funcionario_id: {{ (int) ($linha['funcionario_id'] ?? 0) }}, funcionario_nome: {{ \Illuminate\Support\Js::from($linha['funcionario'] ?? '') }}, data_referencia: {{ \Illuminate\Support\Js::from($linha['data_iso'] ?? '') }}, campo_batida: 'saida_em', campo_label: 'Saída', horario_batida: {{ \Illuminate\Support\Js::from($linha['saida_sugerida'] ?? '18:00') }} })">{{ $linha['saida'] ?? '—' }}</button>
                                @else
                                    <span class="{{ $classeSaida }}">{{ $linha['saida'] }}</span>
                                @endif
                            </x-table-cell>
                            @php
                                $saldoSegundos = (int) ($linha['saldo_segundos'] ?? 0);
                                $toleranciaSegundos = (int) ($linha['tolerancia_segundos'] ?? 0);
                                $destacarAtrasoTotal = $saldoSegundos < 0 && abs($saldoSegundos) > $toleranciaSegundos;
                                $destacarAcrescimoTotal = $saldoSegundos > 0 && $saldoSegundos > $toleranciaSegundos;
                            @endphp
                            <x-table-cell :type="$tipoCelula">
                                <span class="{{ $destacarAtrasoTotal ? 'text-red-700 font-semibold' : ($destacarAcrescimoTotal ? 'text-green-700 font-semibold' : '') }}">{{ $linha['total'] }}</span>
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">{{ $linha['extra_50'] ?? '—' }}</x-table-cell>
                            <x-table-cell :type="$tipoCelula">{{ $linha['extra_100'] ?? '—' }}</x-table-cell>
                            @php
                                $saldoSegundos = (int) ($linha['saldo_segundos'] ?? 0);
                                $toleranciaSegundos = (int) ($linha['tolerancia_segundos'] ?? 0);
                                $destacarAtrasoColuna = $saldoSegundos < 0 && abs($saldoSegundos) > $toleranciaSegundos;
                            @endphp
                            <x-table-cell :type="$tipoCelula">
                                <span class="text-xs font-semibold {{ $destacarAtrasoColuna ? 'text-red-700' : 'text-gray-700' }}">{{ $linha['atraso'] ?? '—' }}</span>
                            </x-table-cell>
                            <x-table-cell :type="$tipoCelula">
                                @php
                                    $semBatidasNoDia = (($linha['entrada'] ?? '—') === '—')
                                        && (($linha['intervalo_inicio'] ?? '—') === '—')
                                        && (($linha['intervalo_fim'] ?? '—') === '—')
                                        && (($linha['saida'] ?? '—') === '—');
                                @endphp
                                <div class="flex items-center gap-1">
                                    @if($semBatidasNoDia)
                                        <button type="button"
                                                class="p-2 rounded-full inline-flex items-center justify-center text-[#3f9cae] hover:bg-[#3f9cae]/10 transition"
                                                title="Ajuste"
                                                aria-label="Ajuste"
                                                @click="abrirModalLote({
                                                    funcionario_id: {{ (int) ($linha['funcionario_id'] ?? 0) }},
                                                    funcionario_nome: {{ \Illuminate\Support\Js::from($linha['funcionario'] ?? '') }},
                                                    data_inicio: {{ \Illuminate\Support\Js::from($linha['data_iso'] ?? '') }},
                                                    data_fim: {{ \Illuminate\Support\Js::from($linha['data_iso'] ?? '') }},
                                                    tipo_lote: 'atestado',
                                                    tipo_ajuste: 'atestado_medico'
                                                })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M17.414 2.586a2 2 0 010 2.828l-9.9 9.9a1 1 0 01-.45.263l-4 1a1 1 0 01-1.212-1.212l1-4a1 1 0 01.263-.45l9.9-9.9a2 2 0 012.828 0z" />
                                            </svg>
                                        </button>
                                    @else
                                        <span class="p-2 rounded-full inline-flex items-center justify-center text-gray-300 bg-gray-100" title="Ajuste indisponível" aria-label="Ajuste indisponível">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M17.414 2.586a2 2 0 010 2.828l-9.9 9.9a1 1 0 01-.45.263l-4 1a1 1 0 01-1.212-1.212l1-4a1 1 0 01.263-.45l9.9-9.9a2 2 0 012.828 0z" />
                                            </svg>
                                        </span>
                                    @endif

                                    <button type="button"
                                            class="p-2 rounded-full inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 transition"
                                            title="Visualizar ajustes"
                                            aria-label="Visualizar ajustes"
                                            @click="abrirModalRelatorioAjustes()">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 3C5 3 1.73 7.11 1.18 7.83a1 1 0 000 1.34C1.73 9.89 5 14 10 14s8.27-4.11 8.82-4.83a1 1 0 000-1.34C18.27 7.11 15 3 10 3zm0 9a3 3 0 110-6 3 3 0 010 6z" />
                                        </svg>
                                    </button>

                                    <button type="button"
                                            class="p-2 rounded-full inline-flex items-center justify-center text-gray-600 hover:bg-gray-100 transition"
                                            title="Detalhes da batida"
                                            aria-label="Detalhes da batida"
                                            @click="abrirModalDetalhesBatida({
                                                funcionario_nome: {{ \Illuminate\Support\Js::from($linha['funcionario'] ?? '') }},
                                                data_referencia: {{ \Illuminate\Support\Js::from($linha['data'] ?? '') }},
                                                entrada: {{ \Illuminate\Support\Js::from($linha['detalhes_batida']['entrada'] ?? ['horario' => '—', 'foto_url' => null, 'latitude' => null, 'longitude' => null]) }},
                                                intervalo_inicio: {{ \Illuminate\Support\Js::from($linha['detalhes_batida']['intervalo_inicio'] ?? ['horario' => '—', 'foto_url' => null, 'latitude' => null, 'longitude' => null]) }},
                                                intervalo_fim: {{ \Illuminate\Support\Js::from($linha['detalhes_batida']['intervalo_fim'] ?? ['horario' => '—', 'foto_url' => null, 'latitude' => null, 'longitude' => null]) }},
                                                saida: {{ \Illuminate\Support\Js::from($linha['detalhes_batida']['saida'] ?? ['horario' => '—', 'foto_url' => null, 'latitude' => null, 'longitude' => null]) }}
                                            })">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-9-3a1 1 0 112 0 1 1 0 01-2 0zm2 7a1 1 0 10-2 0 1 1 0 002 0zm-2-5a1 1 0 000 2v2a1 1 0 102 0v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </x-table-cell>
                        </tr>
                    @endforeach
                </x-table>

                @php
                    $totaisSecao1 = $jornadaLegal['totais_secao_1'] ?? [];
                    $faltasQtd = (int) ($totaisSecao1['faltas_qtd'] ?? 0);
                    $atrasosQtd = (int) ($totaisSecao1['atrasos_qtd'] ?? 0);
                    $extras50Segundos = (int) ($totaisSecao1['extras_50_segundos'] ?? 0);
                    $extras100Segundos = (int) ($totaisSecao1['extras_100_segundos'] ?? 0);
                    $atrasosSegundos = (int) ($totaisSecao1['atrasos_segundos'] ?? 0);
                    $formatarSegundosTotal = function (int $segundos): string {
                        $valor = max(0, $segundos);
                        $horas = intdiv($valor, 3600);
                        $minutos = intdiv($valor % 3600, 60);

                        return sprintf('%02d:%02d', $horas, $minutos);
                    };
                @endphp

                <div class="mt-4 border-t border-gray-200 pt-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Totalizador da Seção 1</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Quantidade de Faltas</p>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ $faltasQtd }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Quantidade de Atrasos</p>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ $atrasosQtd }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Total Horas Extras 50%</p>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ $formatarSegundosTotal($extras50Segundos) }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Total Horas Extras 100%</p>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ $formatarSegundosTotal($extras100Segundos) }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-white">
                            <p class="text-xs text-gray-600 uppercase">Total de Atrasos</p>
                            <p class="text-xl font-bold text-red-700 mt-1">{{ $formatarSegundosTotal($atrasosSegundos) }}</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">SEÇÃO 2 – Indicadores de Produtividade (Atendimentos)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-4">
                    <div class="bg-white p-4 rounded border" style="border-color:#3b82f6; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Tempo em Atendimento</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ gmdate('H:i', max(0, $indicadores['total_tempo_atendimento_segundos'])) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded border" style="border-color:#22c55e; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Tempo Médio / Atendimento</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ gmdate('H:i', max(0, $indicadores['tempo_medio_segundos'])) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded border" style="border-color:#8b5cf6; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Atendimentos no Período</p>
                        <p class="text-2xl font-bold text-violet-600 mt-1">{{ $indicadores['total_atendimentos'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded border" style="border-color:#f59e0b; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Produtividade (%)</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($indicadores['produtividade_percentual'], 2, ',', '.') }}%</p>
                    </div>
                    <div class="bg-white p-4 rounded border" style="border-color:#ef4444; border-width:1px; border-left-width:4px;">
                        <p class="text-xs text-gray-600 uppercase">Tempo Ocioso</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ gmdate('H:i', max(0, $indicadores['tempo_ocioso_segundos'])) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-600 uppercase">Assiduidade Mensal</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($indicadores['assiduidade_mensal'], 2, ',', '.') }}%</p>
                    </div>
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-600 uppercase">Pontualidade Mensal</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($indicadores['pontualidade_mensal'], 2, ',', '.') }}%</p>
                    </div>
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-600 uppercase">Horas Extras no Período</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ gmdate('H:i', max(0, $indicadores['horas_extras_segundos'])) }}</p>
                    </div>
                    <div class="border border-gray-200 rounded p-4">
                        <p class="text-xs text-gray-600 uppercase">Banco de Horas Acumulado</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ ($indicadores['banco_horas_acumulado_segundos'] < 0 ? '-' : '+') . gmdate('H:i', abs($indicadores['banco_horas_acumulado_segundos'])) }}</p>
                    </div>
                </div>
            </div>

            <div x-show="modalAjusteAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="fecharModalAjuste()"></div>
                <div class="relative bg-white rounded-lg w-full max-w-xl p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Lançamento Manual - Seção 1</h3>

                    <form method="POST" action="{{ route('rh.ponto-jornada.ajustes-secao-um.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="funcionario_id" :value="ajusteSecao1.funcionario_id">
                        <input type="hidden" name="data_referencia" :value="ajusteSecao1.data_referencia">
                        <input type="hidden" name="campo_batida" :value="ajusteSecao1.campo_batida">
                        <input type="hidden" name="inicio" value="{{ $filtros['inicio'] }}">
                        <input type="hidden" name="fim" value="{{ $filtros['fim'] }}">
                        <input type="hidden" name="funcionario_id_filtro" value="{{ $filtros['funcionario_id'] ?? '' }}">

                        <div class="text-sm text-gray-700 bg-blue-50 border border-blue-100 rounded p-3">
                            <p><span class="font-semibold">Funcionário:</span> <span x-text="ajusteSecao1.funcionario_nome"></span></p>
                            <p><span class="font-semibold">Data:</span> <span x-text="ajusteSecao1.data_referencia"></span></p>
                            <p><span class="font-semibold">Campo:</span> <span x-text="ajusteSecao1.campo_label"></span></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horário da Batida</label>
                            <input type="time" name="horario_batida" :value="ajusteSecao1.horario_batida" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            <p class="text-xs text-gray-500 mt-1">Preenchido com o horário previsto da jornada, mas você pode alterar.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo do Ajuste</label>
                            <select name="tipo_ajuste" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                <option value="">Selecione</option>
                                <option value="esquecimento">Esquecimento</option>
                                <option value="batida_duplicidade">Batida em Duplicidade</option>
                                <option value="atestado_medico">Atestado Médico</option>
                                <option value="acompanhamento_medico">Acompanhamento Médico</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo do lançamento Manual</label>
                            <textarea name="motivo_lancamento_manual" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" required></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quem Autorizou</label>
                            <select name="autorizado_por_user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                <option value="">Selecione</option>
                                @foreach($autorizadores as $usuario)
                                    <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <x-button type="button" variant="secondary" size="sm" @click="fecharModalAjuste()">Cancelar</x-button>
                            <x-button type="submit" variant="primary" size="sm">Lançar Ajuste</x-button>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="modalLoteAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="fecharModalLote()"></div>
                <div class="relative bg-white rounded-lg w-full max-w-3xl p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Lançamento em Lote</h3>
                    <p class="text-sm text-gray-600 mb-4">Aplique atestado ou batidas para um período sem preencher dia a dia.</p>

                    <form method="POST" action="{{ route('rh.ponto-jornada.ajustes-lote.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="inicio" value="{{ $filtros['inicio'] }}">
                        <input type="hidden" name="fim" value="{{ $filtros['fim'] }}">
                        <input type="hidden" name="funcionario_id_filtro" value="{{ $filtros['funcionario_id'] ?? '' }}">
                        <input type="hidden" name="funcionario_id" :value="loteAjuste.funcionario_id">

                        <div class="text-sm text-gray-700 bg-blue-50 border border-blue-100 rounded p-3">
                            <p><span class="font-semibold">Funcionário:</span> <span x-text="loteAjuste.funcionario_nome"></span></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Lançamento</label>
                                <select name="tipo_lote" x-model="loteAjuste.tipo_lote" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                                    <option value="atestado">Atestado</option>
                                    <option value="batidas">Batidas</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data início</label>
                                <input type="date" name="data_inicio" x-model="loteAjuste.data_inicio" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data fim</label>
                                <input type="date" name="data_fim" x-model="loteAjuste.data_fim" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo do Ajuste</label>
                                <select name="tipo_ajuste" x-model="loteAjuste.tipo_ajuste" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                                    <option value="">Selecione</option>
                                    <option value="atestado_medico">Atestado Médico</option>
                                    <option value="acompanhamento_medico">Acompanhamento Médico</option>
                                    <option value="esquecimento">Esquecimento</option>
                                    <option value="batida_duplicidade">Batida em Duplicidade</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quem Autorizou</label>
                                <select name="autorizado_por_user_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                                    <option value="">Selecione</option>
                                    @foreach($autorizadores as $usuario)
                                        <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div x-show="loteAjuste.tipo_lote === 'batidas'" x-transition class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Entrada</label>
                                <input type="time" name="horario_entrada" x-model="loteAjuste.horario_entrada" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Saída almoço</label>
                                <input type="time" name="horario_intervalo_inicio" x-model="loteAjuste.horario_intervalo_inicio" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Retorno almoço</label>
                                <input type="time" name="horario_intervalo_fim" x-model="loteAjuste.horario_intervalo_fim" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Saída</label>
                                <input type="time" name="horario_saida" x-model="loteAjuste.horario_saida" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="sobrescrever_campos" value="1" x-model="loteAjuste.sobrescrever_campos" class="rounded border-gray-300 text-[#3f9cae] focus:ring-[#3f9cae]">
                                    Sobrescrever
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo do lançamento Manual</label>
                            <textarea name="motivo_lancamento_manual" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" required></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <x-button type="button" variant="secondary" size="sm" @click="fecharModalLote()">Cancelar</x-button>
                            <x-button type="submit" variant="primary" size="sm">Aplicar Lançamento em Lote</x-button>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="modalRelatorioAjustesAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="fecharModalRelatorioAjustes()"></div>
                <div class="relative bg-white rounded-lg w-full max-w-4xl p-6 shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Relatório de Ajustes Manuais</h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700" @click="fecharModalRelatorioAjustes()">✕</button>
                    </div>

                    <div class="space-y-3 max-h-[520px] overflow-auto pr-1">
                        @forelse($ajustes as $ajuste)
                            <div class="border border-gray-200 rounded p-3">
                                <p class="font-semibold text-gray-800">{{ $ajuste->funcionario?->nome ?? '—' }}</p>
                                <p class="text-sm text-gray-600">Tipo: {{ $tiposAjuste[$ajuste->tipo_ajuste] ?? $ajuste->tipo_ajuste }}</p>
                                <p class="text-sm text-gray-600">Ajuste: {{ $ajuste->minutos_ajuste }} min</p>
                                <p class="text-sm text-gray-600">Autorizado por: {{ $ajuste->autorizadoPor?->name ?? '—' }}</p>
                                <p class="text-sm text-gray-600">Registrado por: {{ $ajuste->ajustadoPor?->name ?? '—' }} em {{ optional($ajuste->ajustado_em)->format('d/m/Y H:i') }}</p>
                                <p class="text-sm text-gray-700 mt-1">{{ $ajuste->justificativa }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Nenhum ajuste registrado no período.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div x-show="modalDetalhesBatidaAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" @click="fecharModalDetalhesBatida()"></div>
                <div class="relative bg-white rounded-lg w-full max-w-4xl p-6 shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Detalhes do Batimento de Ponto</h3>
                            <p class="text-sm text-gray-600"><span x-text="detalhesBatida.funcionario_nome"></span> · <span x-text="detalhesBatida.data_referencia"></span></p>
                        </div>
                        <button type="button" class="text-gray-500 hover:text-gray-700" @click="fecharModalDetalhesBatida()">✕</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-sm font-semibold text-gray-800 mb-2">Entrada</p>
                            <p class="text-sm text-gray-700">Horário: <span x-text="detalhesBatida.entrada.horario || '—'"></span></p>
                            <p class="text-sm text-gray-700">Localização: <span x-text="(detalhesBatida.entrada.latitude ?? '—') + ', ' + (detalhesBatida.entrada.longitude ?? '—')"></span></p>
                            <template x-if="detalhesBatida.entrada.foto_url">
                                <a :href="detalhesBatida.entrada.foto_url" target="_blank" rel="noopener" class="inline-block mt-2">
                                    <img :src="detalhesBatida.entrada.foto_url" alt="Foto entrada" class="h-28 w-28 object-cover rounded border border-gray-200">
                                </a>
                            </template>
                        </div>

                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-sm font-semibold text-gray-800 mb-2">Saída almoço</p>
                            <p class="text-sm text-gray-700">Horário: <span x-text="detalhesBatida.intervalo_inicio.horario || '—'"></span></p>
                            <p class="text-sm text-gray-700">Localização: <span x-text="(detalhesBatida.intervalo_inicio.latitude ?? '—') + ', ' + (detalhesBatida.intervalo_inicio.longitude ?? '—')"></span></p>
                        </div>

                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-sm font-semibold text-gray-800 mb-2">Retorno almoço</p>
                            <p class="text-sm text-gray-700">Horário: <span x-text="detalhesBatida.intervalo_fim.horario || '—'"></span></p>
                            <p class="text-sm text-gray-700">Localização: <span x-text="(detalhesBatida.intervalo_fim.latitude ?? '—') + ', ' + (detalhesBatida.intervalo_fim.longitude ?? '—')"></span></p>
                        </div>

                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-sm font-semibold text-gray-800 mb-2">Saída</p>
                            <p class="text-sm text-gray-700">Horário: <span x-text="detalhesBatida.saida.horario || '—'"></span></p>
                            <p class="text-sm text-gray-700">Localização: <span x-text="(detalhesBatida.saida.latitude ?? '—') + ', ' + (detalhesBatida.saida.longitude ?? '—')"></span></p>
                            <template x-if="detalhesBatida.saida.foto_url">
                                <a :href="detalhesBatida.saida.foto_url" target="_blank" rel="noopener" class="inline-block mt-2">
                                    <img :src="detalhesBatida.saida.foto_url" alt="Foto saída" class="h-28 w-28 object-cover rounded border border-gray-200">
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
