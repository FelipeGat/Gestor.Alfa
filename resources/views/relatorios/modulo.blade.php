<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Relatórios', 'url' => route('relatorios.index')],
            ['label' => 'Módulo de Relatórios']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Módulo de Relatórios</h2>

                <form method="GET" action="{{ route('relatorios.modulo') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Empresa</label>
                        <select name="empresa_id" class="w-full border rounded-lg px-3 py-2" required>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected((int)$filtros['empresa_id'] === (int)$empresa->id)>
                                    {{ $empresa->nome_fantasia ?: $empresa->razao_social }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Data início</label>
                        <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] }}" class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Data fim</label>
                        <input type="date" name="data_fim" value="{{ $filtros['data_fim'] }}" class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Centro de custo</label>
                        <select name="centro_custo_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Todos</option>
                            @foreach($centrosCusto as $centro)
                                <option value="{{ $centro->id }}" @selected((int)($filtros['centro_custo_id'] ?? 0) === (int)$centro->id)>
                                    {{ $centro->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Relatório</label>
                        <select name="tipo" class="w-full border rounded-lg px-3 py-2">
                            <option value="financeiro" @selected($filtros['tipo'] === 'financeiro')>Financeiro</option>
                            <option value="tecnico" @selected($filtros['tipo'] === 'tecnico')>Técnico</option>
                            <option value="comercial" @selected($filtros['tipo'] === 'comercial')>Comercial</option>
                            <option value="rh" @selected($filtros['tipo'] === 'rh')>RH</option>
                            <option value="painel-executivo" @selected($filtros['tipo'] === 'painel-executivo')>Painel Executivo</option>
                        </select>
                    </div>

                    <div class="md:col-span-6 flex gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg text-white" style="background-color:#3f9cae;">Gerar relatório</button>
                        <a href="{{ route('relatorios.modulo.imprimir', [
                            'empresa_id' => $filtros['empresa_id'],
                            'data_inicio' => $filtros['data_inicio'],
                            'data_fim' => $filtros['data_fim'],
                            'centro_custo_id' => $filtros['centro_custo_id'],
                            'tipo' => $filtros['tipo'],
                        ]) }}"
                           target="_blank"
                           class="px-4 py-2 rounded-lg border border-[#3f9cae] text-[#3f9cae]">
                            Imprimir
                        </a>
                    </div>
                </form>
            </div>

            @if($dados !== null)
                <div class="bg-white rounded-lg p-6" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);">
                    <h3 class="text-base font-bold text-gray-800 mb-3">Resultado: {{ strtoupper($filtros['tipo']) }}</h3>
                    <pre class="bg-gray-50 rounded-lg p-4 text-xs overflow-auto">{{ json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
