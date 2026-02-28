<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'RH', 'url' => route('rh.dashboard')],
            ['label' => 'Cadastro de Feriados']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Novo Feriado</h2>
                <form method="POST" action="{{ route('rh.feriados.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    @csrf
                    <x-form-input name="nome" label="Nome" required placeholder="Ex.: Carnaval" />
                    <x-form-input name="data" type="date" label="Data" required />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                            <option value="nacional">Nacional</option>
                            <option value="estadual">Estadual</option>
                            <option value="municipal">Municipal</option>
                            <option value="interno">Interno</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-end gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="recorrente_anual" value="1" class="rounded border-gray-300">
                            Recorrente anual
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="ativo" value="1" checked class="rounded border-gray-300">
                            Ativo
                        </label>
                    </div>
                    <div class="md:col-span-6 flex justify-end">
                        <x-button type="submit" variant="primary" size="sm">Cadastrar Feriado</x-button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Feriados Cadastrados</h2>
                <div class="space-y-3">
                    @forelse($feriados as $feriado)
                        <form method="POST" action="{{ route('rh.feriados.update', $feriado) }}" class="grid grid-cols-1 md:grid-cols-8 gap-3 border border-gray-200 rounded p-3">
                            @csrf
                            @method('PUT')
                            <x-form-input name="nome" label="Nome" :value="$feriado->nome" required />
                            <x-form-input name="data" type="date" label="Data" :value="optional($feriado->data)->format('Y-m-d')" required />
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                    @foreach(['nacional' => 'Nacional', 'estadual' => 'Estadual', 'municipal' => 'Municipal', 'interno' => 'Interno'] as $valor => $label)
                                        <option value="{{ $valor }}" @selected($feriado->tipo === $valor)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2 flex items-end gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="recorrente_anual" value="1" @checked($feriado->recorrente_anual) class="rounded border-gray-300">
                                    Recorrente
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="ativo" value="1" @checked($feriado->ativo) class="rounded border-gray-300">
                                    Ativo
                                </label>
                            </div>
                            <div class="md:col-span-2 flex items-end justify-end gap-2">
                                <x-button type="submit" variant="secondary" size="sm">Salvar</x-button>
                        </form>
                                <form method="POST" action="{{ route('rh.feriados.destroy', $feriado) }}" onsubmit="return confirm('Excluir feriado?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">Excluir</x-button>
                                </form>
                            </div>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum feriado cadastrado.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
