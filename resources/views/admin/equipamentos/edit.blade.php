<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Ativos Técnicos', 'url' => route('admin.equipamentos.index')],
            ['label' => 'Editar Ativo Técnico']
        ]" />
    </x-slot>

    <x-page-title title="Editar Ativo Técnico" :route="route('admin.equipamentos.index')" />

    <div class="pb-8" x-data="{ tab: 'dados' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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

            <div class="mb-6 border-b border-gray-200">
                <nav class="-mb-px flex gap-6">
                    <button type="button" @click="tab='dados'" :class="tab === 'dados' ? 'border-[#3f9cae] text-[#3f9cae]' : 'border-transparent text-gray-500'" class="py-3 px-1 border-b-2 font-medium text-sm">Dados do Ativo</button>
                    <button type="button" @click="tab='historico'" :class="tab === 'historico' ? 'border-[#3f9cae] text-[#3f9cae]' : 'border-transparent text-gray-500'" class="py-3 px-1 border-b-2 font-medium text-sm">Histórico de Manutenções</button>
                    <button type="button" @click="tab='documentos'" :class="tab === 'documentos' ? 'border-[#3f9cae] text-[#3f9cae]' : 'border-transparent text-gray-500'" class="py-3 px-1 border-b-2 font-medium text-sm">Documentos</button>
                </nav>
            </div>

            <div x-show="tab === 'dados'">
                <form action="{{ route('admin.equipamentos.update', $equipamento->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Dados Básicos</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <x-form-input name="nome" label="Nome do Ativo Técnico *" required :value="old('nome', $equipamento->nome)" />
                            <x-form-input name="modelo" label="Modelo" :value="old('modelo', $equipamento->modelo)" />
                            <x-form-input name="fabricante" label="Fabricante" :value="old('fabricante', $equipamento->fabricante)" />
                            <x-form-input name="numero_serie" label="Número de Série" :value="old('numero_serie', $equipamento->numero_serie)" />
                            <x-form-input name="codigo_ativo" label="Código do Ativo" :value="old('codigo_ativo', $equipamento->codigo_ativo)" />
                            <x-form-input name="tag_patrimonial" label="TAG Patrimonial" :value="old('tag_patrimonial', $equipamento->tag_patrimonial)" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Localização</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="sm:col-span-2">
                                <x-form-select name="cliente_id" label="Cliente *" required>
                                    @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(old('cliente_id', $equipamento->cliente_id) == $cliente->id)>{{ $cliente->nome_exibicao }}</option>
                                    @endforeach
                                </x-form-select>
                            </div>
                            <x-form-input name="unidade" label="Unidade" :value="old('unidade', $equipamento->unidade)" />
                            <x-form-input name="andar" label="Andar" :value="old('andar', $equipamento->andar)" />
                            <x-form-input name="sala" label="Sala" :value="old('sala', $equipamento->sala)" />
                            <x-form-input name="setor_nome" label="Setor" :value="old('setor_nome', $equipamento->setor->nome ?? '')" />
                            <x-form-input name="responsavel_nome" label="Responsável" :value="old('responsavel_nome', $equipamento->responsavel->nome ?? '')" />
                            <div class="sm:col-span-2">
                                <label for="localizacao_detalhada" class="block text-sm font-medium text-gray-700 mb-1">Localização Detalhada</label>
                                <textarea id="localizacao_detalhada" name="localizacao_detalhada" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]">{{ old('localizacao_detalhada', $equipamento->localizacao_detalhada) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Especificações Técnicas</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <x-form-input name="capacidade" label="Capacidade" :value="old('capacidade', $equipamento->capacidade)" />
                            <x-form-input name="potencia" label="Potência" :value="old('potencia', $equipamento->potencia)" />
                            <x-form-input name="voltagem" label="Voltagem" :value="old('voltagem', $equipamento->voltagem)" />
                            <x-form-input name="vida_util_anos" label="Vida útil estimada (anos)" type="number" min="1" max="100" :value="old('vida_util_anos', $equipamento->vida_util_anos)" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Aquisição</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <x-form-input name="data_aquisicao" label="Data de aquisição" type="date" :value="old('data_aquisicao', optional($equipamento->data_aquisicao)->format('Y-m-d'))" />
                            <x-form-input name="data_instalacao" label="Data de instalação" type="date" :value="old('data_instalacao', optional($equipamento->data_instalacao)->format('Y-m-d'))" />
                            <x-form-input name="valor_aquisicao" label="Valor aquisição" type="number" step="0.01" min="0" :value="old('valor_aquisicao', $equipamento->valor_aquisicao)" />
                            <x-form-select name="fornecedor_id" label="Fornecedor">
                                <option value="">Selecione</option>
                                @foreach($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}" @selected(old('fornecedor_id', $equipamento->fornecedor_id) == $fornecedor->id)>
                                    {{ $fornecedor->nome_fantasia ?: ($fornecedor->razao_social ?: $fornecedor->nome) }}
                                </option>
                                @endforeach
                            </x-form-select>
                            <div class="sm:col-span-2 flex items-center gap-3">
                                <input type="checkbox" id="possui_garantia" name="possui_garantia" value="1" @checked(old('possui_garantia', $equipamento->possui_garantia)) class="rounded border-gray-300 text-[#3f9cae] shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]">
                                <label for="possui_garantia" class="text-sm font-medium text-gray-700">Possui garantia</label>
                            </div>
                            <x-form-input name="garantia_inicio" label="Garantia início" type="date" :value="old('garantia_inicio', optional($equipamento->garantia_inicio)->format('Y-m-d'))" />
                            <x-form-input name="garantia_fim" label="Garantia fim" type="date" :value="old('garantia_fim', optional($equipamento->garantia_fim)->format('Y-m-d'))" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Manutenção</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <x-form-input name="ultima_manutencao" label="Última manutenção" type="date" :value="old('ultima_manutencao', optional($equipamento->ultima_manutencao)->format('Y-m-d'))" />
                            <x-form-input name="ultima_limpeza" label="Última limpeza" type="date" :value="old('ultima_limpeza', optional($equipamento->ultima_limpeza)->format('Y-m-d'))" />
                            <x-form-input name="periodicidade_manutencao_meses" label="Periodicidade manutenção (meses)" type="number" min="1" max="120" :value="old('periodicidade_manutencao_meses', $equipamento->periodicidade_manutencao_meses)" />
                            <x-form-input name="periodicidade_limpeza_meses" label="Periodicidade limpeza (meses)" type="number" min="1" max="120" :value="old('periodicidade_limpeza_meses', $equipamento->periodicidade_limpeza_meses)" />
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Próxima manutenção (calculada)</label>
                                <input type="text" readonly value="{{ $equipamento->proxima_manutencao?->format('d/m/Y') ?? '-' }}" class="w-full rounded-md bg-gray-100 border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Status</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <x-form-select name="status_ativo" label="Status do ativo">
                                <option value="">Selecione</option>
                                @foreach(['operando' => 'Operando', 'em_manutencao' => 'Em manutenção', 'inativo' => 'Inativo', 'aguardando_peca' => 'Aguardando peça', 'descartado' => 'Descartado', 'substituido' => 'Substituído'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status_ativo', $equipamento->status_ativo) === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form-select>
                            <x-form-select name="criticidade" label="Criticidade">
                                <option value="">Selecione</option>
                                @foreach(['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'critica' => 'Crítica'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('criticidade', $equipamento->criticidade) === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form-select>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="ativo" name="ativo" value="1" @checked(old('ativo', $equipamento->ativo)) class="rounded border-gray-300 text-[#3f9cae] shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]">
                                <label for="ativo" class="text-sm font-medium text-gray-700">Ativo técnico ativo</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Financeiro</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor aquisição</label>
                                <input type="text" readonly value="R$ {{ $equipamento->valor_aquisicao ? number_format((float) $equipamento->valor_aquisicao, 2, ',', '.') : '0,00' }}" class="w-full rounded-md bg-gray-100 border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Custo total de manutenção (calculado)</label>
                                <input type="text" readonly value="R$ {{ number_format((float) $equipamento->custo_total_manutencao, 2, ',', '.') }}" class="w-full rounded-md bg-gray-100 border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Fotos</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="foto_principal" class="block text-sm font-medium text-gray-700 mb-1">Foto principal do ativo</label>
                                <input type="file" id="foto_principal" name="foto_principal" accept="image/*" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]">
                            </div>
                            @if($equipamento->foto_principal)
                            <div>
                                <p class="block text-sm font-medium text-gray-700 mb-1">Foto atual</p>
                                <img src="{{ Storage::disk('public')->url($equipamento->foto_principal) }}" alt="Foto do ativo" class="h-24 rounded-md border border-gray-200">
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Observações</h3>
                        <textarea id="observacoes" name="observacoes" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]">{{ old('observacoes', $equipamento->observacoes) }}</textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                        <x-button href="{{ route('admin.equipamentos.index') }}" variant="danger" size="md" class="min-w-[130px]">Cancelar</x-button>
                        <x-button type="submit" variant="primary" size="md" class="min-w-[130px]">Salvar Alterações</x-button>
                    </div>
                </form>
            </div>

            <div x-show="tab === 'historico'" x-cloak>
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Histórico de Manutenções</h3>
                        <x-button type="button" variant="primary" size="sm" @click="document.getElementById('modal-historico').classList.remove('hidden')">Adicionar Manutenção</x-button>
                    </div>

                    @if($equipamento->historicoManutencoes->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Data</th>
                                    <th class="px-4 py-2 text-left">Tipo</th>
                                    <th class="px-4 py-2 text-left">Descrição</th>
                                    <th class="px-4 py-2 text-left">Técnico</th>
                                    <th class="px-4 py-2 text-left">Custo</th>
                                    <th class="px-4 py-2 text-left">Peças</th>
                                    <th class="px-4 py-2 text-left">Tempo parado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($equipamento->historicoManutencoes as $item)
                                <tr>
                                    <td class="px-4 py-2">{{ $item->data_manutencao?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($item->tipo) }}</td>
                                    <td class="px-4 py-2">{{ $item->descricao ?: '-' }}</td>
                                    <td class="px-4 py-2">{{ $item->tecnico_responsavel ?: '-' }}</td>
                                    <td class="px-4 py-2">{{ $item->custo ? 'R$ '.number_format((float) $item->custo, 2, ',', '.') : '-' }}</td>
                                    <td class="px-4 py-2">{{ $item->pecas_trocadas ?: '-' }}</td>
                                    <td class="px-4 py-2">{{ $item->tempo_parado_horas ? $item->tempo_parado_horas.'h' : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-500">Nenhum histórico de manutenção cadastrado.</p>
                    @endif
                </div>
            </div>

            <div x-show="tab === 'documentos'" x-cloak>
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Documentos</h3>

                    <form action="{{ route('admin.equipamentos.documentos.store', $equipamento) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        @csrf
                        <x-form-input name="nome_documento" label="Nome do documento" required />
                        <x-form-select name="tipo_documento" label="Tipo" required>
                            <option value="manual">Manual</option>
                            <option value="nota_fiscal">Nota Fiscal</option>
                            <option value="garantia">Garantia</option>
                            <option value="pmoc">PMOC</option>
                            <option value="foto">Foto</option>
                        </x-form-select>
                        <div>
                            <label for="arquivo" class="block text-sm font-medium text-gray-700 mb-1">Arquivo</label>
                            <input type="file" id="arquivo" name="arquivo" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]">
                        </div>
                        <div class="sm:col-span-3">
                            <x-button type="submit" variant="primary" size="sm">Enviar documento</x-button>
                        </div>
                    </form>

                    @if($equipamento->documentos->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Nome</th>
                                    <th class="px-4 py-2 text-left">Tipo</th>
                                    <th class="px-4 py-2 text-left">Data</th>
                                    <th class="px-4 py-2 text-left">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($equipamento->documentos as $documento)
                                <tr>
                                    <td class="px-4 py-2">{{ $documento->nome_documento }}</td>
                                    <td class="px-4 py-2">{{ strtoupper(str_replace('_', ' ', $documento->tipo_documento)) }}</td>
                                    <td class="px-4 py-2">{{ $documento->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2 flex gap-2">
                                        <a href="{{ route('admin.equipamentos.documentos.download', [$equipamento, $documento]) }}" class="text-[#3f9cae] font-medium">Download</a>
                                        <form action="{{ route('admin.equipamentos.documentos.destroy', [$equipamento, $documento]) }}" method="POST" onsubmit="return confirm('Excluir documento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 font-medium">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-500">Nenhum documento anexado.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="modal-historico" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('modal-historico').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Adicionar Manutenção</h3>
                <form action="{{ route('admin.equipamentos.historico.store', $equipamento) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @csrf
                    <x-form-input name="data_manutencao" label="Data" type="date" required />
                    <x-form-select name="tipo" label="Tipo" required>
                        <option value="preventiva">Preventiva</option>
                        <option value="corretiva">Corretiva</option>
                        <option value="limpeza">Limpeza</option>
                    </x-form-select>
                    <x-form-input name="tecnico_responsavel" label="Técnico responsável" />
                    <x-form-input name="custo" label="Custo" type="number" step="0.01" min="0" />
                    <x-form-input name="tempo_parado_horas" label="Tempo parado (horas)" type="number" min="0" />
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Peças trocadas</label>
                        <textarea name="pecas_trocadas" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae]"></textarea>
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-3 mt-2">
                        <x-button type="button" variant="danger" size="sm" onclick="document.getElementById('modal-historico').classList.add('hidden')">Cancelar</x-button>
                        <x-button type="submit" variant="primary" size="sm">Salvar</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
