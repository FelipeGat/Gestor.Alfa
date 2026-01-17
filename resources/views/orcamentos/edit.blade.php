<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ✏️ Editar Orçamento
        </h2>
    </x-slot>

    {{-- ================= AUTOCOMPLETE CLIENTE ================= --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {

        const inputNome = document.getElementById('cliente_nome');
        const inputClienteId = document.getElementById('cliente_id');
        const inputPreClienteId = document.getElementById('pre_cliente_id');
        const inputTipo = document.getElementById('cliente_tipo');
        const resultados = document.getElementById('cliente-resultados');
        const btnPreCadastro = document.getElementById('btn-pre-cadastro');

        let timeout = null;

        async function buscar(q = '') {
            const res = await fetch('/busca-clientes?q=' + encodeURIComponent(q));
            return await res.json();
        }

        function limparSelecao() {
            inputClienteId.value = '';
            inputPreClienteId.value = '';
            inputTipo.value = '';
        }

        function render(lista) {
            resultados.innerHTML = '';

            if (!lista.length) {
                resultados.classList.add('hidden');
                btnPreCadastro.classList.remove('hidden');
                return;
            }

            btnPreCadastro.classList.add('hidden');

            lista.forEach(item => {
                const nomeExibido = item.nome_fantasia || item.razao_social || '—';

                const div = document.createElement('div');
                div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';

                div.innerHTML = `
                    <strong>${nomeExibido}</strong><br>
                    <span class="text-xs text-gray-500">
                        ${item.cpf_cnpj} • ${item.tipo === 'cliente' ? 'Cliente' : 'Pré-Cliente'}
                    </span>
                `;

                div.onclick = () => {
                    inputNome.value = nomeExibido;
                    limparSelecao();

                    if (item.tipo === 'cliente') {
                        inputClienteId.value = item.id;
                        inputTipo.value = 'cliente';
                    }

                    if (item.tipo === 'pre_cliente') {
                        inputPreClienteId.value = item.id;
                        inputTipo.value = 'pre_cliente';
                    }

                    resultados.classList.add('hidden');
                };

                resultados.appendChild(div);
            });

            resultados.classList.remove('hidden');
        }

        inputNome.addEventListener('input', () => {
            clearTimeout(timeout);
            limparSelecao();

            const q = inputNome.value.trim();

            timeout = setTimeout(async () => {
                if (!q) {
                    resultados.classList.add('hidden');
                    btnPreCadastro.classList.add('hidden');
                    return;
                }

                const data = await buscar(q);
                render(data);
            }, 300);
        });

        document.addEventListener('click', e => {
            if (!e.target.closest('#cliente_nome')) {
                resultados.classList.add('hidden');
            }
        });

        btnPreCadastro?.addEventListener('click', () => {
            const nome = inputNome.value || '';
            const url = new URL('/pre-clientes/create', window.location.origin);
            url.searchParams.set('q', nome);
            url.searchParams.set('from', 'orcamento');
            window.location.href = url.toString();
        });

    });
    </script>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER --}}
            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 mb-6">
                <h1 class="text-2xl font-bold">Editar Orçamento</h1>
                <p class="text-sm text-gray-600">
                    Nº {{ $orcamento->numero_orcamento }}
                </p>
            </div>

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('orcamentos.update', $orcamento) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- ================= INFORMAÇÕES BÁSICAS ================= --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">📋 Informações Básicas</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- CLIENTE --}}
                        <div class="md:col-span-2 relative">
                            <label class="text-sm font-medium text-gray-700">Cliente</label>

                            <input type="text" id="cliente_nome" name="cliente_nome"
                                value="{{ $orcamento->nome_cliente }}" autocomplete="off"
                                class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">

                            <input type="hidden" name="cliente_id" id="cliente_id" value="{{ $orcamento->cliente_id }}">

                            <input type="hidden" name="pre_cliente_id" id="pre_cliente_id"
                                value="{{ $orcamento->pre_cliente_id }}">

                            <input type="hidden" name="cliente_tipo" id="cliente_tipo"
                                value="{{ $orcamento->cliente_id ? 'cliente' : ($orcamento->pre_cliente_id ? 'pre_cliente' : '') }}">

                            <div id="cliente-resultados"
                                class="absolute z-10 w-full bg-white border rounded-lg shadow mt-1 hidden">
                            </div>

                            <button type="button" id="btn-pre-cadastro"
                                class="text-sm text-blue-600 hover:underline mt-2 hidden">
                                ➕ Cliente não possui cadastro
                            </button>
                        </div>

                        {{-- DESCRIÇÃO --}}
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium">Descrição</label>
                            <input type="text" name="descricao" value="{{ old('descricao', $orcamento->descricao) }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        {{-- VALIDADE --}}
                        <div>
                            <label class="text-sm font-medium">Validade</label>
                            <input type="date" name="validade"
                                value="{{ old('validade', optional($orcamento->validade)->format('Y-m-d')) }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                    </div>
                </div>

                {{-- ================= FINANCEIRO ================= --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">💰 Financeiro</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div>
                            <label class="text-sm font-medium">Desconto</label>
                            <input type="number" step="0.01" name="desconto"
                                value="{{ old('desconto', $orcamento->desconto) }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="text-sm font-medium">Taxas</label>
                            <input type="number" step="0.01" name="taxas" value="{{ old('taxas', $orcamento->taxas) }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="text-sm font-medium">Forma de Pagamento</label>
                            <input type="text" name="forma_pagamento"
                                value="{{ old('forma_pagamento', $orcamento->forma_pagamento) }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                    </div>
                </div>

                {{-- ================= AÇÕES ================= --}}
                <div class="flex justify-end gap-3 bg-white shadow rounded-lg p-6">
                    <a href="{{ route('orcamentos.index') }}"
                        class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-red-50">
                        ❌ Cancelar
                    </a>

                    <button type="submit" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-green-50">
                        ✔️ Atualizar Orçamento
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-app-layout>