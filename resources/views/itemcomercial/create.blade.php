<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ➕ Novo Serviço / Produto
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <ul class="text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('itemcomercial.store') }}" class="space-y-6">
                @csrf

                {{-- ================= INFORMAÇÕES BÁSICAS ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-blue-500">
                    <h3 class="font-semibold mb-4 text-lg">📋 Informações Básicas</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- TIPO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Tipo <span
                                    class="text-red-500">*</span></label>
                            <select name="tipo" id="tipo" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione</option>
                                <option value="produto" @selected(old('tipo')=='produto' )>Produto</option>
                                <option value="servico" @selected(old('tipo')=='servico' )>Serviço</option>
                            </select>
                            @error('tipo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- NOME --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Nome <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome') }}" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('nome')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- SKU / REFERÊNCIA --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">SKU / Referência</label>
                            <input type="text" name="sku_ou_referencia" value="{{ old('sku_ou_referencia') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('sku_ou_referencia')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CÓDIGO DE BARRAS / EAN --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Código de Barras / EAN</label>
                            <input type="text" name="codigo_barras_ean" value="{{ old('codigo_barras_ean') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('codigo_barras_ean')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- MARCA --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Marca</label>
                            <input type="text" name="marca" value="{{ old('marca') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('marca')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- MODELO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Modelo</label>
                            <input type="text" name="modelo" value="{{ old('modelo') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('modelo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ================= CATEGORIA E ASSUNTO ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-purple-500">
                    <h3 class="font-semibold mb-4 text-lg">🏷️ Categoria / Assunto</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- CATEGORIA --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Categoria / Assunto</label>
                            <select name="categoria_id"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                            <label class="text-sm font-medium text-gray-700">Estado</label>
                            <select name="estado"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="novo" @selected(old('estado', 'novo' )=='novo' )>Novo</option>
                                <option value="usado" @selected(old('estado')=='usado' )>Usado</option>
                            </select>
                            @error('estado')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ================= PRECIFICAÇÃO ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-green-500">
                    <h3 class="font-semibold mb-4 text-lg">💰 Precificação</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- PREÇO CUSTO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Preço de Custo</label>
                            <input type="number" step="0.01" name="preco_custo" value="{{ old('preco_custo') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('preco_custo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- PREÇO VENDA --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Preço de Venda <span
                                    class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="preco_venda" value="{{ old('preco_venda') }}"
                                required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('preco_venda')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- MARGEM DE LUCRO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Margem de Lucro (%)</label>
                            <input type="number" step="0.01" name="margem_lucro" value="{{ old('margem_lucro') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('margem_lucro')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- UNIDADE --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Unidade de Medida <span
                                    class="text-red-500">*</span></label>
                            <select name="unidade_medida" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione</option>
                                <option value="unidade" @selected(old('unidade_medida')=='unidade' )>Unidade</option>
                                <option value="hora" @selected(old('unidade_medida')=='hora' )>Hora</option>
                                <option value="dia" @selected(old('unidade_medida')=='dia' )>Dia</option>
                                <option value="semana" @selected(old('unidade_medida')=='semana' )>Semana</option>
                                <option value="m2" @selected(old('unidade_medida')=='m2' )>m²</option>
                                <option value="kg" @selected(old('unidade_medida')=='kg' )>Kg</option>
                                <option value="litro" @selected(old('unidade_medida')=='litro' )>Litro</option>
                            </select>
                            @error('unidade_medida')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CUSTO FRETE --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Custo de Frete</label>
                            <input type="number" step="0.01" name="custo_frete" value="{{ old('custo_frete') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('custo_frete')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ================= ESTOQUE (PRODUTO) ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-orange-500">
                    <h3 class="font-semibold mb-4 text-lg">📦 Estoque (somente para produtos)</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- GERENCIAR ESTOQUE --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Gerenciar Estoque</label>
                            <select name="gerencia_estoque"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="0" @selected(old('gerencia_estoque')==0)>Não</option>
                                <option value="1" @selected(old('gerencia_estoque')==1)>Sim</option>
                            </select>
                            @error('gerencia_estoque')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ESTOQUE ATUAL --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Estoque Atual</label>
                            <input type="number" name="estoque_atual" value="{{ old('estoque_atual') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('estoque_atual')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ESTOQUE MÍNIMO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Estoque Mínimo</label>
                            <input type="number" name="estoque_minimo" value="{{ old('estoque_minimo') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('estoque_minimo')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- FINALIDADE --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Finalidade</label>
                            <select name="finalidade"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione</option>
                                <option value="uso_consumo" @selected(old('finalidade')=='uso_consumo' )>Uso e Consumo
                                </option>
                                <option value="venda" @selected(old('finalidade')=='venda' )>Venda</option>
                                <option value="aluguel" @selected(old('finalidade')=='aluguel' )>Aluguel</option>
                                <option value="materia_prima" @selected(old('finalidade')=='materia_prima' )>Matéria
                                    Prima</option>
                            </select>
                            @error('finalidade')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ================= DADOS FISCAIS ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-red-500">
                    <h3 class="font-semibold mb-4 text-lg">📄 Dados Fiscais</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- NCM --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">NCM</label>
                            <input type="text" name="ncm" value="{{ old('ncm') }}" placeholder="Ex: 12345678"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('ncm')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CFOP PADRÃO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">CFOP Padrão</label>
                            <input type="text" name="cfop_padrao" value="{{ old('cfop_padrao') }}"
                                placeholder="Ex: 5102"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('cfop_padrao')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- CÓDIGO SERVIÇO ISS --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Código Serviço ISS</label>
                            <input type="text" name="codigo_servico_iss" value="{{ old('codigo_servico_iss') }}"
                                placeholder="Ex: 0101"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('codigo_servico_iss')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ALÍQUOTA ICMS --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Alíquota ICMS (%)</label>
                            <input type="number" step="0.01" name="aliquota_icms" value="{{ old('aliquota_icms') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('aliquota_icms')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ALÍQUOTA ISS --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Alíquota ISS (%)</label>
                            <input type="number" step="0.01" name="aliquota_iss" value="{{ old('aliquota_iss') }}"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('aliquota_iss')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="bg-white shadow rounded-lg p-6 border-t-4 border-indigo-500">
                    <h3 class="font-semibold mb-4 text-lg">✅ Status</h3>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                        {{-- ATIVO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Status</label>
                            <select name="ativo"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3
                            bg-white shadow rounded-lg p-6 sm:p-8">

                    <a href="{{ route('itemcomercial.index') }}"
                        class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition duration-200">

                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>

                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Salvar Item
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>