<x-app-layout>
    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    <style>
        /* Cards de Seção */
        .section-card {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
        }
        .section-card > h3,
        .section-card .card-header h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .section-card h4 {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: #111827;
        }
        /* Labels */
        .filter-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
            display: block;
        }
        /* Inputs e Selects */
        .filter-select,
        input[type="text"],
        input[type="date"],
        input[type="number"],
        textarea,
        select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }
        .filter-select:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #3f9cae !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #3f9cae !important;
        }
        /* Botões */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-success {
            background: #22c55e !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3);
            transition: all 0.2s;
            color: white !important;
        }
        .btn-success:hover {
            box-shadow: 0 4px 6px rgba(34, 197, 94, 0.4);
        }
        .btn-cancel {
            background: #ef4444 !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
            transition: all 0.2s;
            color: white !important;
        }
        .btn-cancel:hover {
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.4);
        }
        .btn-save {
            background: #3f9cae !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 4px rgba(63, 156, 174, 0.3);
            transition: all 0.2s;
            color: white !important;
        }
        .btn-save:hover {
            box-shadow: 0 4px 6px rgba(63, 156, 174, 0.4);
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Comercial', 'url' => route('comercial.index')],
            ['label' => 'Serviços e Produtos', 'url' => route('itemcomercial.index')],
            ['label' => 'Novo']
        ]" />
    </x-slot>

    <x-back-button :route="route('itemcomercial.index')" />

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= ERROS ================= --}}
            @if ($errors->any())
            <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-5 rounded-xl shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 h-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg></div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800 uppercase tracking-wide">Erros encontrados:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $erro) <li>{{ $erro }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('itemcomercial.store') }}" class="space-y-6">
                @csrf

                {{-- ================= INFORMAÇÕES BÁSICAS ================= --}}
                <div class="section-card p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Informações Básicas</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- TIPO --}}
                        <div>
                            <label class="filter-label">Tipo <span class="text-red-500">*</span></label>
                            <select name="tipo" id="tipo" required class="filter-select w-full">
                                <option value="">Selecione</option>
                                <option value="produto" @selected(old('tipo')=='produto')>Produto</option>
                                <option value="servico" @selected(old('tipo')=='servico')>Serviço</option>
                            </select>
                            @error('tipo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- NOME --}}
                        <div>
                            <label class="filter-label">Nome <span class="text-red-500">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome') }}" required class="filter-select w-full">
                            @error('nome')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- SKU / REFERÊNCIA --}}
                        <div>
                            <label class="filter-label">SKU / Referência</label>
                            <input type="text" name="sku_ou_referencia" value="{{ old('sku_ou_referencia') }}" class="filter-select w-full">
                            @error('sku_ou_referencia')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CÓDIGO DE BARRAS / EAN --}}
                        <div>
                            <label class="filter-label">Código de Barras / EAN</label>
                            <input type="text" name="codigo_barras_ean" value="{{ old('codigo_barras_ean') }}" class="filter-select w-full">
                            @error('codigo_barras_ean')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- MARCA --}}
                        <div>
                            <label class="filter-label">Marca</label>
                            <input type="text" name="marca" value="{{ old('marca') }}" class="filter-select w-full">
                            @error('marca')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- MODELO --}}
                        <div>
                            <label class="filter-label">Modelo</label>
                            <input type="text" name="modelo" value="{{ old('modelo') }}" class="filter-select w-full">
                            @error('modelo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= CATEGORIA E ASSUNTO ================= --}}
                <div class="section-card p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Categoria / Assunto</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- CATEGORIA --}}
                        <div>
                            <label class="filter-label">Categoria / Assunto</label>
                            <select name="categoria_id" class="filter-select w-full">
                                <option value="">Selecione uma categoria</option>
                                @foreach ($assuntos ?? [] as $assunto)
                                <option value="{{ $assunto->id }}" @selected(old('categoria_id')==$assunto->id)>
                                    {{ $assunto->nome }}
                                </option>
                                @endforeach
                            </select>
                            @error('categoria_id')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ESTADO --}}
                        <div>
                            <label class="filter-label">Estado</label>
                            <select name="estado" class="filter-select w-full">
                                <option value="novo" @selected(old('estado', 'novo')=='novo')>Novo</option>
                                <option value="usado" @selected(old('estado')=='usado')>Usado</option>
                            </select>
                            @error('estado')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= PRECIFICAÇÃO ================= --}}
                <div class="section-card p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Precificação</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- PREÇO CUSTO --}}
                        <div>
                            <label class="filter-label">Preço de Custo</label>
                            <input type="number" step="0.01" name="preco_custo" value="{{ old('preco_custo') }}" class="filter-select w-full">
                            @error('preco_custo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- PREÇO VENDA --}}
                        <div>
                            <label class="filter-label">Preço de Venda <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="preco_venda" value="{{ old('preco_venda') }}" required class="filter-select w-full">
                            @error('preco_venda')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- MARGEM DE LUCRO --}}
                        <div>
                            <label class="filter-label">Margem de Lucro (%)</label>
                            <input type="number" step="0.01" name="margem_lucro" value="{{ old('margem_lucro') }}" class="filter-select w-full">
                            @error('margem_lucro')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- UNIDADE --}}
                        <div>
                            <label class="filter-label">Unidade de Medida <span class="text-red-500">*</span></label>
                            <select name="unidade_medida" required class="filter-select w-full">
                                <option value="">Selecione</option>
                                <option value="unidade" @selected(old('unidade_medida')=='unidade')>Unidade</option>
                                <option value="hora" @selected(old('unidade_medida')=='hora')>Hora</option>
                                <option value="dia" @selected(old('unidade_medida')=='dia')>Dia</option>
                                <option value="semana" @selected(old('unidade_medida')=='semana')>Semana</option>
                                <option value="m2" @selected(old('unidade_medida')=='m2')>m²</option>
                                <option value="kg" @selected(old('unidade_medida')=='kg')>Kg</option>
                                <option value="litro" @selected(old('unidade_medida')=='litro')>Litro</option>
                            </select>
                            @error('unidade_medida')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CUSTO FRETE --}}
                        <div>
                            <label class="filter-label">Custo de Frete</label>
                            <input type="number" step="0.01" name="custo_frete" value="{{ old('custo_frete') }}" class="filter-select w-full">
                            @error('custo_frete')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= ESTOQUE (PRODUTO) ================= --}}
                <div class="section-card p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Estoque (somente para produtos)</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- GERENCIAR ESTOQUE --}}
                        <div>
                            <label class="filter-label">Gerenciar Estoque</label>
                            <select name="gerencia_estoque" class="filter-select w-full">
                                <option value="0" @selected(old('gerencia_estoque')==0)>Não</option>
                                <option value="1" @selected(old('gerencia_estoque')==1)>Sim</option>
                            </select>
                            @error('gerencia_estoque')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ESTOQUE ATUAL --}}
                        <div>
                            <label class="filter-label">Estoque Atual</label>
                            <input type="number" name="estoque_atual" value="{{ old('estoque_atual') }}" class="filter-select w-full">
                            @error('estoque_atual')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ESTOQUE MÍNIMO --}}
                        <div>
                            <label class="filter-label">Estoque Mínimo</label>
                            <input type="number" name="estoque_minimo" value="{{ old('estoque_minimo') }}" class="filter-select w-full">
                            @error('estoque_minimo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- FINALIDADE --}}
                        <div>
                            <label class="filter-label">Finalidade</label>
                            <select name="finalidade" class="filter-select w-full">
                                <option value="">Selecione</option>
                                <option value="uso_consumo" @selected(old('finalidade')=='uso_consumo')>Uso e Consumo</option>
                                <option value="venda" @selected(old('finalidade')=='venda')>Venda</option>
                                <option value="aluguel" @selected(old('finalidade')=='aluguel')>Aluguel</option>
                                <option value="materia_prima" @selected(old('finalidade')=='materia_prima')>Matéria Prima</option>
                            </select>
                            @error('finalidade')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= DADOS FISCAIS ================= --}}
                <div class="section-card p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Dados Fiscais</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- NCM --}}
                        <div>
                            <label class="filter-label">NCM</label>
                            <input type="text" name="ncm" value="{{ old('ncm') }}" placeholder="Ex: 12345678" class="filter-select w-full">
                            @error('ncm')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CFOP PADRÃO --}}
                        <div>
                            <label class="filter-label">CFOP Padrão</label>
                            <input type="text" name="cfop_padrao" value="{{ old('cfop_padrao') }}" placeholder="Ex: 5102" class="filter-select w-full">
                            @error('cfop_padrao')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CÓDIGO SERVIÇO ISS --}}
                        <div>
                            <label class="filter-label">Código Serviço ISS</label>
                            <input type="text" name="codigo_servico_iss" value="{{ old('codigo_servico_iss') }}" placeholder="Ex: 0101" class="filter-select w-full">
                            @error('codigo_servico_iss')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ALÍQUOTA ICMS --}}
                        <div>
                            <label class="filter-label">Aliquota ICMS (%)</label>
                            <input type="number" step="0.01" name="aliquota_icms" value="{{ old('aliquota_icms') }}" class="filter-select w-full">
                            @error('aliquota_icms')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ALÍQUOTA ISS --}}
                        <div>
                            <label class="filter-label">Aliquota ISS (%)</label>
                            <input type="number" step="0.01" name="aliquota_iss" value="{{ old('aliquota_iss') }}" class="filter-select w-full">
                            @error('aliquota_iss')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="section-card p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Status</h3>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        {{-- ATIVO --}}
                        <div>
                            <label class="filter-label">Status</label>
                            <select name="ativo" class="filter-select w-full">
                                <option value="1" @selected(old('ativo', 1) == 1)>Ativo</option>
                                <option value="0" @selected(old('ativo', 1) == 0)>Inativo</option>
                            </select>
                            @error('ativo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= AÇÕES ================= --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3" style="margin-top: 1.5rem;">
                    <a href="{{ route('itemcomercial.index') }}" class="btn btn-cancel" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center;">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-save" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center;">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar Item
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
