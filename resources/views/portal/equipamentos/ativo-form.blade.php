<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <div class="portal-wrapper">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    {{ $isEdit ? 'Editar Ativo Técnico' : 'Cadastrar Ativo Técnico' }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $cliente->nome_exibicao }}</p>
            </div>
            <a href="{{ route('portal.ativos.index') }}" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                Voltar para Lista de Ativos
            </a>
        </div>

        <div class="portal-table-card p-6">
            <form action="{{ $isEdit ? route('portal.ativos.update', $equipamento) : route('portal.ativos.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" required value="{{ old('nome', $equipamento->nome) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                    <input type="text" name="modelo" value="{{ old('modelo', $equipamento->modelo) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fabricante</label>
                    <input type="text" name="fabricante" value="{{ old('fabricante', $equipamento->fabricante) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de série</label>
                    <input type="text" name="numero_serie" value="{{ old('numero_serie', $equipamento->numero_serie) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código do ativo</label>
                    <input type="text" name="codigo_ativo" value="{{ old('codigo_ativo', $equipamento->codigo_ativo) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tag patrimonial</label>
                    <input type="text" name="tag_patrimonial" value="{{ old('tag_patrimonial', $equipamento->tag_patrimonial) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
                    <select name="setor_id" class="w-full rounded border-gray-300">
                        <option value="">Selecione</option>
                        @foreach($setores as $setor)
                            <option value="{{ $setor->id }}" @selected(old('setor_id', $equipamento->setor_id) == $setor->id)>{{ $setor->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                    <select name="responsavel_id" class="w-full rounded border-gray-300">
                        <option value="">Selecione</option>
                        @foreach($responsaveis as $responsavel)
                            <option value="{{ $responsavel->id }}" @selected(old('responsavel_id', $equipamento->responsavel_id) == $responsavel->id)>{{ $responsavel->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status_ativo" class="w-full rounded border-gray-300">
                        <option value="operando" @selected(old('status_ativo', $equipamento->status_ativo) === 'operando')>Operando</option>
                        <option value="em_manutencao" @selected(old('status_ativo', $equipamento->status_ativo) === 'em_manutencao')>Em manutenção</option>
                        <option value="inativo" @selected(old('status_ativo', $equipamento->status_ativo) === 'inativo')>Inativo</option>
                        <option value="aguardando_peca" @selected(old('status_ativo', $equipamento->status_ativo) === 'aguardando_peca')>Aguardando peça</option>
                        <option value="descartado" @selected(old('status_ativo', $equipamento->status_ativo) === 'descartado')>Descartado</option>
                        <option value="substituido" @selected(old('status_ativo', $equipamento->status_ativo) === 'substituido')>Substituído</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Criticidade</label>
                    <select name="criticidade" class="w-full rounded border-gray-300">
                        <option value="">Selecione</option>
                        <option value="baixa" @selected(old('criticidade', $equipamento->criticidade) === 'baixa')>Baixa</option>
                        <option value="media" @selected(old('criticidade', $equipamento->criticidade) === 'media')>Média</option>
                        <option value="alta" @selected(old('criticidade', $equipamento->criticidade) === 'alta')>Alta</option>
                        <option value="critica" @selected(old('criticidade', $equipamento->criticidade) === 'critica')>Crítica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacidade</label>
                    <input type="text" name="capacidade" value="{{ old('capacidade', $equipamento->capacidade) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Potência</label>
                    <input type="text" name="potencia" value="{{ old('potencia', $equipamento->potencia) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Voltagem</label>
                    <input type="text" name="voltagem" value="{{ old('voltagem', $equipamento->voltagem) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periodicidade manutenção (meses)</label>
                    <input type="number" min="1" max="120" name="periodicidade_manutencao_meses" value="{{ old('periodicidade_manutencao_meses', $equipamento->periodicidade_manutencao_meses ?? 6) }}" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periodicidade limpeza (meses)</label>
                    <input type="number" min="1" max="120" name="periodicidade_limpeza_meses" value="{{ old('periodicidade_limpeza_meses', $equipamento->periodicidade_limpeza_meses ?? 1) }}" class="w-full rounded border-gray-300">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                    <textarea name="observacoes" rows="4" class="w-full rounded border-gray-300">{{ old('observacoes', $equipamento->observacoes) }}</textarea>
                </div>

                <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                    <a href="{{ route('portal.ativos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Cancelar</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg">
                        {{ $isEdit ? 'Salvar Alterações' : 'Cadastrar Ativo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
