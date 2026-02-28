<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'RH', 'url' => route('rh.dashboard')],
            ['label' => 'Cadastro de Jornada']
        ]" />
    </x-slot>

    @php
        $diasSemana = [
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
    @endphp

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Nova Jornada</h2>
                <form method="POST" action="{{ route('rh.jornadas.store') }}" class="space-y-5" data-jornada-form>
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-form-input name="nome" label="Nome da Jornada" required placeholder="Ex.: Administrativo Seg-Sex" />
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo_jornada" class="w-full border border-gray-300 rounded-lg px-3 py-2 js-tipo-jornada" required>
                                <option value="fixa">Fixa</option>
                                <option value="escala">Escala</option>
                            </select>
                        </div>
                        <x-form-input name="hora_entrada_padrao" type="time" label="Entrada padrão" />
                        <x-form-input name="hora_saida_padrao" type="time" label="Saída padrão" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dias trabalhados (jornada fixa)</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($diasSemana as $diaNumero => $diaLabel)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="dias_trabalhados[]" value="{{ $diaNumero }}" class="rounded border-gray-300" @checked(in_array($diaNumero, [1,2,3,4,5]))>
                                    {{ $diaLabel }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-form-input name="intervalo_minutos" type="number" label="Intervalo (min)" :value="60" required />
                        <x-form-input name="carga_horaria_semanal" type="number" step="0.01" min="0" label="Carga semanal (h)" :value="44" required />
                        <x-form-input name="tolerancia_entrada_min" type="number" label="Tolerância atraso (min)" :value="10" required />
                        <x-form-input name="tolerancia_saida_min" type="number" label="Tolerância saída (min)" :value="10" required />
                        <x-form-input name="tolerancia_intervalo_min" type="number" label="Tolerância intervalo (min)" :value="5" required />
                        <x-form-input name="minimo_horas_para_extra" type="number" label="Mín. para hora extra (min)" :value="30" required />
                    </div>

                    <div class="js-escala-section hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Escala personalizada por dia (preencha apenas para tipo Escala)</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($diasSemana as $diaNumero => $diaLabel)
                                <div class="border border-gray-200 rounded p-3">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">{{ $diaLabel }}</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <x-form-input :name="'escala['.$diaNumero.'][hora_entrada]'" type="time" label="Entrada" />
                                        <x-form-input :name="'escala['.$diaNumero.'][hora_saida]'" type="time" label="Saída" />
                                        <x-form-input :name="'escala['.$diaNumero.'][intervalo_minutos]'" type="number" label="Intervalo" :value="60" />
                                        <x-form-input :name="'escala['.$diaNumero.'][carga_horaria_dia]'" type="number" step="0.01" min="0" label="Carga dia" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Feriados atrelados à jornada</label>
                        <div class="feriado-picker grid grid-cols-1 md:grid-cols-2 gap-3" data-feriado-picker>
                            <select name="feriado_ids[]" multiple class="hidden js-feriados-hidden">
                                @foreach($feriadosNacionaisIds as $feriadoNacionalId)
                                    <option value="{{ $feriadoNacionalId }}" selected>{{ $feriadoNacionalId }}</option>
                                @endforeach
                            </select>

                            <div class="border border-gray-200 rounded-lg p-3">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Feriados disponíveis</p>
                                <p class="text-xs text-gray-500 mb-2">Dica: clique duplo para mover ou arraste para o card ao lado.</p>
                                <ul class="js-feriados-disponiveis min-h-[180px] max-h-[220px] overflow-auto space-y-1 bg-gray-50 rounded p-2" data-drop-target="disponiveis">
                                    @foreach($feriados as $feriado)
                                        @if(!in_array((int) $feriado->id, $feriadosNacionaisIds, true))
                                            <li class="js-feriado-item cursor-pointer select-none border border-gray-200 bg-white rounded px-2 py-1 text-sm" draggable="true" data-id="{{ $feriado->id }}">
                                                {{ optional($feriado->data)->format('d/m') }} - {{ $feriado->nome }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-3">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Feriados selecionados</p>
                                <p class="text-xs text-gray-500 mb-2">Feriados nacionais já vêm selecionados automaticamente.</p>
                                <ul class="js-feriados-selecionados min-h-[180px] max-h-[220px] overflow-auto space-y-1 bg-cyan-50 rounded p-2" data-drop-target="selecionados">
                                    @foreach($feriados as $feriado)
                                        @if(in_array((int) $feriado->id, $feriadosNacionaisIds, true))
                                            <li class="js-feriado-item cursor-pointer select-none border border-cyan-300 bg-white rounded px-2 py-1 text-sm" draggable="true" data-id="{{ $feriado->id }}" data-fixed="1">
                                                {{ optional($feriado->data)->format('d/m') }} - {{ $feriado->nome }} <span class="text-[11px] text-cyan-700">(Nacional)</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-6">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="permitir_ponto_fora_horario" value="1" checked class="rounded border-gray-300">
                            Permitir bater ponto fora do horário
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="ativo" value="1" checked class="rounded border-gray-300">
                            Jornada ativa
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <x-button type="submit" variant="primary" size="sm">Cadastrar Jornada</x-button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Jornadas Cadastradas</h2>
                <div class="space-y-4">
                    @forelse($jornadas as $jornada)
                        @php
                            $diasSelecionados = collect($jornada->dias_trabalhados ?? [])->map(fn($d) => (int) $d)->all();
                            $escalas = $jornada->escalas->keyBy('dia_semana');
                            $feriadosSelecionados = collect(array_merge(
                                $jornada->feriados->pluck('id')->map(fn($id) => (int) $id)->all(),
                                $feriadosNacionaisIds
                            ))->unique()->values()->all();
                        @endphp
                        <form method="POST" action="{{ route('rh.jornadas.update', $jornada) }}" class="border border-gray-200 rounded p-4 space-y-4" data-jornada-form>
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <x-form-input name="nome" label="Nome" :value="$jornada->nome" required />
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                    <select name="tipo_jornada" class="w-full border border-gray-300 rounded-lg px-3 py-2 js-tipo-jornada" required>
                                        <option value="fixa" @selected($jornada->tipo_jornada === 'fixa')>Fixa</option>
                                        <option value="escala" @selected($jornada->tipo_jornada === 'escala')>Escala</option>
                                    </select>
                                </div>
                                <x-form-input name="hora_entrada_padrao" type="time" label="Entrada padrão" :value="$jornada->hora_entrada_padrao" />
                                <x-form-input name="hora_saida_padrao" type="time" label="Saída padrão" :value="$jornada->hora_saida_padrao" />
                            </div>

                            <div class="flex flex-wrap gap-3">
                                @foreach($diasSemana as $diaNumero => $diaLabel)
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="dias_trabalhados[]" value="{{ $diaNumero }}" class="rounded border-gray-300" @checked(in_array($diaNumero, $diasSelecionados, true))>
                                        {{ $diaLabel }}
                                    </label>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <x-form-input name="intervalo_minutos" type="number" label="Intervalo (min)" :value="$jornada->intervalo_minutos" required />
                                <x-form-input name="carga_horaria_semanal" type="number" step="0.01" min="0" label="Carga semanal (h)" :value="$jornada->carga_horaria_semanal" required />
                                <x-form-input name="tolerancia_entrada_min" type="number" label="Tol. atraso" :value="$jornada->tolerancia_entrada_min" required />
                                <x-form-input name="tolerancia_saida_min" type="number" label="Tol. saída" :value="$jornada->tolerancia_saida_min" required />
                                <x-form-input name="tolerancia_intervalo_min" type="number" label="Tol. intervalo" :value="$jornada->tolerancia_intervalo_min" required />
                                <x-form-input name="minimo_horas_para_extra" type="number" label="Mín. extra" :value="$jornada->minimo_horas_para_extra" required />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 js-escala-section @if($jornada->tipo_jornada !== 'escala') hidden @endif">
                                @foreach($diasSemana as $diaNumero => $diaLabel)
                                    @php $escalaDia = $escalas->get($diaNumero); @endphp
                                    <div class="border border-gray-200 rounded p-3">
                                        <p class="text-sm font-semibold text-gray-700 mb-2">{{ $diaLabel }}</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <x-form-input :name="'escala['.$diaNumero.'][hora_entrada]'" type="time" label="Entrada" :value="$escalaDia?->hora_entrada" />
                                            <x-form-input :name="'escala['.$diaNumero.'][hora_saida]'" type="time" label="Saída" :value="$escalaDia?->hora_saida" />
                                            <x-form-input :name="'escala['.$diaNumero.'][intervalo_minutos]'" type="number" label="Intervalo" :value="$escalaDia?->intervalo_minutos" />
                                            <x-form-input :name="'escala['.$diaNumero.'][carga_horaria_dia]'" type="number" step="0.01" min="0" label="Carga dia" :value="$escalaDia?->carga_horaria_dia" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Feriados atrelados</label>
                                <div class="feriado-picker grid grid-cols-1 md:grid-cols-2 gap-3" data-feriado-picker>
                                    <select name="feriado_ids[]" multiple class="hidden js-feriados-hidden">
                                        @foreach($feriadosSelecionados as $feriadoSelecionado)
                                            <option value="{{ $feriadoSelecionado }}" selected>{{ $feriadoSelecionado }}</option>
                                        @endforeach
                                    </select>

                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <p class="text-sm font-semibold text-gray-700 mb-2">Feriados disponíveis</p>
                                        <p class="text-xs text-gray-500 mb-2">Dica: clique duplo para mover ou arraste para o card ao lado.</p>
                                        <ul class="js-feriados-disponiveis min-h-[180px] max-h-[220px] overflow-auto space-y-1 bg-gray-50 rounded p-2" data-drop-target="disponiveis">
                                            @foreach($feriados as $feriado)
                                                @if(!in_array($feriado->id, $feriadosSelecionados, true))
                                                    <li class="js-feriado-item cursor-pointer select-none border border-gray-200 bg-white rounded px-2 py-1 text-sm" draggable="true" data-id="{{ $feriado->id }}">
                                                        {{ optional($feriado->data)->format('d/m') }} - {{ $feriado->nome }}
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <p class="text-sm font-semibold text-gray-700 mb-2">Feriados selecionados</p>
                                        <p class="text-xs text-gray-500 mb-2">Somente os itens deste card serão salvos na jornada.</p>
                                        <ul class="js-feriados-selecionados min-h-[180px] max-h-[220px] overflow-auto space-y-1 bg-cyan-50 rounded p-2" data-drop-target="selecionados">
                                            @foreach($feriados as $feriado)
                                                @if(in_array($feriado->id, $feriadosSelecionados, true))
                                                    <li class="js-feriado-item cursor-pointer select-none border border-cyan-300 bg-white rounded px-2 py-1 text-sm" draggable="true" data-id="{{ $feriado->id }}" @if(in_array((int) $feriado->id, $feriadosNacionaisIds, true)) data-fixed="1" @endif>
                                                        {{ optional($feriado->data)->format('d/m') }} - {{ $feriado->nome }}
                                                        @if(in_array((int) $feriado->id, $feriadosNacionaisIds, true))
                                                            <span class="text-[11px] text-cyan-700">(Nacional)</span>
                                                        @endif
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <div class="flex flex-wrap gap-6">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="permitir_ponto_fora_horario" value="1" @checked($jornada->permitir_ponto_fora_horario) class="rounded border-gray-300">
                                        Permitir ponto fora do horário
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="ativo" value="1" @checked($jornada->ativo) class="rounded border-gray-300">
                                        Ativa
                                    </label>
                                </div>
                                <div class="flex gap-2">
                                    <x-button type="submit" variant="secondary" size="sm">Salvar</x-button>
                        </form>
                                    <form method="POST" action="{{ route('rh.jornadas.ativo', $jornada) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-button type="submit" variant="danger" size="sm">{{ $jornada->ativo ? 'Desativar' : 'Ativar' }}</x-button>
                                    </form>
                                </div>
                            </div>
                    @empty
                        <p class="text-sm text-gray-500">Nenhuma jornada cadastrada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-jornada-form]').forEach((form) => {
        const selectTipo = form.querySelector('.js-tipo-jornada');
        const secoesEscala = form.querySelectorAll('.js-escala-section');

        const atualizarVisibilidadeEscala = () => {
            const mostrar = selectTipo && selectTipo.value === 'escala';
            secoesEscala.forEach((secao) => {
                secao.classList.toggle('hidden', !mostrar);
            });
        };

        if (selectTipo) {
            selectTipo.addEventListener('change', atualizarVisibilidadeEscala);
            atualizarVisibilidadeEscala();
        }
    });

    const pickers = document.querySelectorAll('[data-feriado-picker]');

    const syncHidden = (picker) => {
        const hidden = picker.querySelector('.js-feriados-hidden');
        const selecionados = picker.querySelector('.js-feriados-selecionados');
        hidden.innerHTML = '';

        [...selecionados.querySelectorAll('.js-feriado-item')].forEach((item) => {
            const option = document.createElement('option');
            option.value = item.dataset.id;
            option.selected = true;
            option.textContent = item.textContent.trim();
            hidden.appendChild(option);
        });
    };

    const marcarSelecionado = (lista, item) => {
        lista.querySelectorAll('.js-feriado-item').forEach((it) => {
            it.classList.remove('ring-2', 'ring-cyan-400');
        });
        item.classList.add('ring-2', 'ring-cyan-400');
    };

    const moverItem = (picker, item, destinoLista) => {
        if (!item || !destinoLista || destinoLista.contains(item)) {
            return;
        }

        if (item.dataset.fixed === '1' && destinoLista.classList.contains('js-feriados-disponiveis')) {
            return;
        }

        item.classList.remove('ring-2', 'ring-cyan-400');
        if (destinoLista.classList.contains('js-feriados-selecionados')) {
            item.classList.add('border-cyan-300');
        } else {
            item.classList.remove('border-cyan-300');
        }
        destinoLista.appendChild(item);
        syncHidden(picker);
    };

    pickers.forEach((picker) => {
        const disponiveis = picker.querySelector('.js-feriados-disponiveis');
        const selecionados = picker.querySelector('.js-feriados-selecionados');

        picker.querySelectorAll('.js-feriado-item').forEach((item) => {
            item.addEventListener('click', () => marcarSelecionado(item.parentElement, item));

            item.addEventListener('dblclick', () => {
                const destino = item.parentElement === disponiveis ? selecionados : disponiveis;
                moverItem(picker, item, destino);
            });

            item.addEventListener('dragstart', (event) => {
                event.dataTransfer.setData('text/plain', item.dataset.id);
                event.dataTransfer.effectAllowed = 'move';
                item.classList.add('opacity-60');
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('opacity-60');
            });
        });

        [disponiveis, selecionados].forEach((lista) => {
            lista.addEventListener('dragover', (event) => {
                event.preventDefault();
            });

            lista.addEventListener('drop', (event) => {
                event.preventDefault();
                const id = event.dataTransfer.getData('text/plain');
                const item = picker.querySelector(`.js-feriado-item[data-id="${id}"]`);
                moverItem(picker, item, lista);
            });
        });

        syncHidden(picker);

        const form = picker.closest('form');
        if (form) {
            form.addEventListener('submit', () => syncHidden(picker));
        }
    });
});
</script>
