<x-app-layout>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const empresaSelect = document.querySelector('[name="empresa_id"]');
        const numeroInput = document.querySelector('[name="numero_orcamento"]');

        if (!empresaSelect || !numeroInput) return;

        empresaSelect.addEventListener('change', async () => {
            const empresaId = empresaSelect.value;
            numeroInput.value = '';

            if (!empresaId) return;

            try {
                const response = await fetch(`/orcamentos/gerar-numero/${empresaId}`);
                const data = await response.json();

                numeroInput.value = data.numero ?? '';
            } catch (e) {
                console.error('Erro ao gerar número do orçamento', e);
            }
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {

        const inputCliente = document.getElementById('cliente_nome');
        const inputClienteId = document.getElementById('cliente_id');
        const resultados = document.getElementById('cliente-resultados');

        if (!inputCliente) {
            console.error('Campo cliente_nome NÃO encontrado');
            return;
        }

        let timeout = null;

        async function buscarClientes(q = '') {
            const res = await fetch('/clientes/buscar?q=' + encodeURIComponent(q));
            return await res.json();
        }

        function renderResultados(clientes) {
            resultados.innerHTML = '';

            if (!clientes.length) {
                resultados.classList.add('hidden');
                return;
            }

            clientes.forEach(cliente => {
                const nomeExibido = cliente.nome_fantasia || cliente.razao_social;

                const item = document.createElement('div');
                item.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                item.innerHTML = `
                <strong>${nomeExibido}</strong><br>
                <span class="text-xs text-gray-500">${cliente.cpf_cnpj}</span>
            `;

                item.onclick = () => {
                    inputCliente.value = nomeExibido;
                    inputClienteId.value = cliente.id;
                    resultados.classList.add('hidden');
                };

                resultados.appendChild(item);
            });

            resultados.classList.remove('hidden');
        }

        /* 👉 BUSCA AO CLICAR */
        inputCliente.addEventListener('focus', async () => {
            const data = await buscarClientes();
            renderResultados(data);
        });

        /* 👉 BUSCA AO DIGITAR */
        inputCliente.addEventListener('input', () => {
            clearTimeout(timeout);
            const q = inputCliente.value.trim();

            timeout = setTimeout(async () => {
                const data = await buscarClientes(q);
                renderResultados(data);
            }, 300);
        });

        /* 👉 FECHAR AO CLICAR FORA */
        document.addEventListener('click', e => {
            if (!e.target.closest('#cliente_nome')) {
                resultados.classList.add('hidden');
            }
        });

    });
    </script>


    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ➕ Novo Orçamento
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= HEADER DO FORMULÁRIO ================= --}}
            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 mb-6">
                <h1 class="text-2xl font-bold text-black">Cadastro de Orçamento</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Preencha os dados abaixo para criar um novo orçamento
                </p>
            </div>

            {{-- ================= ERROS ================= --}}
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

            <form action="{{ route('orcamentos.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- ================= INFORMAÇÕES BÁSICAS ================= --}}
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        📋 Informações Básicas
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- EMPRESA --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Empresa <span
                                    class="text-red-500">*</span></label>
                            <select name="empresa_id" required
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">Selecione</option>
                                @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}">
                                    {{ $empresa->nome_fantasia ?? $empresa->nome }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Nº ORÇAMENTO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Nº do Orçamento</label>
                            <input type="text" name="numero_orcamento" readonly placeholder="Gerado automaticamente"
                                class="mt-1 w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>

                        {{-- DESCRIÇÃO / REFERÊNCIA --}}
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Descrição / Referência <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="descricao" required
                                placeholder="Ex: Manutenção preventiva, Instalação de câmeras..."
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>

                        {{-- CLIENTE (DIGITÁVEL) --}}
                        <div class="md:col-span-2 relative">
                            <label class="text-sm font-medium text-gray-700">Cliente</label>
                            <input type="text" name="cliente_nome" id="cliente_nome" autocomplete="off"
                                placeholder="Digite nome ou CPF/CNPJ"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">

                            <input type="hidden" name="cliente_id" id="cliente_id">

                            <div id="cliente-resultados"
                                class="absolute z-10 w-full bg-white border rounded-lg shadow mt-1 hidden">
                            </div>
                        </div>

                        {{-- VALIDADE --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Validade do Orçamento</label>
                            <input type="date" name="validade" value="{{ now()->addDays(5)->format('Y-m-d') }}"
                                min="{{ now()->addDays(5)->format('Y-m-d') }}"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>

                        {{-- OBSERVAÇÕES --}}
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-700">Observações</label>
                            <textarea name="observacoes" rows="3" placeholder="Observações gerais do orçamento..."
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
                        </div>

                    </div>
                </div>

                {{-- ================= INFORMAÇÕES DO PEDIDO ================= --}}
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        🧾 Informações do Pedido
                    </h3>

                    <div class="space-y-4">

                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h4 class="font-medium text-gray-800 mb-2">Serviços a serem executados</h4>
                            <p class="text-sm text-gray-500">
                                ⚙️ Em breve será possível adicionar serviços detalhados.
                            </p>
                        </div>

                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h4 class="font-medium text-gray-800 mb-2">Materiais</h4>
                            <p class="text-sm text-gray-500">
                                🧱 Em breve será possível adicionar materiais ao orçamento.
                            </p>
                        </div>

                    </div>
                </div>

                {{-- ================= VALORES E PAGAMENTO ================= --}}
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        💰 Valores e Pagamento
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- DESCONTO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Desconto</label>
                            <input type="number" step="0.01" name="desconto" placeholder="0,00"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>

                        {{-- TAXAS --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Taxas Adicionais</label>
                            <input type="number" step="0.01" name="taxas" placeholder="0,00"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>

                        {{-- FORMA DE PAGAMENTO --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Forma de Pagamento</label>
                            <select name="forma_pagamento"
                                class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">Selecione</option>
                                <option value="pix">PIX</option>
                                <option value="boleto">Boleto</option>
                                <option value="credito">Cartão de Crédito</option>
                                <option value="debito">Cartão de Débito</option>
                                <option value="transferencia">Transferência</option>
                            </select>
                        </div>

                    </div>
                </div>




                {{-- ================= OBSERVAÇÕES ================= --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        📝 Observações
                    </h3>
                    <textarea name="observacoes" rows="4" class="w-full rounded-lg border border-gray-300 shadow-sm
                        focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                        placeholder="Observações importantes sobre o orçamento..."></textarea>
                </div>

                {{-- ================= AÇÕES ================= --}}
                <div class="flex justify-end gap-3 bg-white shadow rounded-lg p-6">
                    <a href="{{ route('orcamentos.index') }}" class="px-6 py-2 rounded-lg border border-gray-300
                              text-gray-700 hover:bg-red-50 transition">
                        ❌ Cancelar
                    </a>

                    <button type="submit" class="px-6 py-2 rounded-lg border border-gray-300
                               text-gray-700 hover:bg-green-50 transition">
                        ✔️ Salvar Orçamento
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-app-layout>