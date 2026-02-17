<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush


    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Novo Cliente
        </h2>
    </x-slot>


    {{-- ================= JS ================= --}}
    <script>
        /*    ADIÇÃO DINÂICA DE EMAILS  */

        function addEmail() {
            document.getElementById('emails').insertAdjacentHTML(
                'beforeend',
                `<div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                <input type="email" name="emails[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2" required>
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <input type="radio" name="email_principal" value="1" class="rounded text-blue-600">
                    <span class="text-sm text-gray-600">Principal</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">
                    Remover
                </button>
            </div>`
            );
        }

        /* =========================
        ADIÇÃO DINÂICA DE TELEFONES
        ========================= */
        function addTelefone() {
            document.getElementById('telefones').insertAdjacentHTML(
                'beforeend',
                `<div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                <input type="text" name="telefones[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <input type="radio" name="telefone_principal" value="1" class="rounded text-blue-600">
                    <span class="text-sm text-gray-600">Principal</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">
                    Remover
                </button>
            </div>`
            );
        }

        /* =========================
        MÁSCARAS (INPUT)
        ========================= */
        document.addEventListener('input', function(e) {

            // Telefone
            if (e.target.classList.contains('telefone')) {
                let v = e.target.value.replace(/\D/g, '');
                e.target.value = v.length <= 10 ?
                    v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3') :
                    v.replace(/(\d{2})(\d{1})(\d{4})(\d{0,4})/, '($1) $2.$3-$4');
            }

            // CPF / CNPJ
            if (e.target.name === 'cpf_cnpj') {
                let v = e.target.value.replace(/\D/g, '');
                e.target.value = v.length <= 11 ?
                    v.replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2') :
                    v.replace(/(\d{2})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1/$2')
                    .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            }

            // CEP
            if (e.target.name === 'cep') {
                let v = e.target.value.replace(/\D/g, '');
                e.target.value = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
            }
        });

        /* =========================
        TOGGLE CONTRATO
        ========================= */
        function toggleContrato() {
            const tipo = document.querySelector('[name="tipo_cliente"]')?.value;
            const bloco = document.getElementById('bloco-contrato');
            if (!bloco) return;

            bloco.style.display = tipo === 'AVULSO' ? 'none' : 'block';
        }

        document.addEventListener('DOMContentLoaded', toggleContrato);
        document.addEventListener('change', e => {
            if (e.target.name === 'tipo_cliente') toggleContrato();
        });


        /* =========================
        STATUS VISUAL CNPJ
        ========================= */
        const cnpjStatusEl = () => document.getElementById('cnpj-status');

        function setCnpjLoading() {
            const el = cnpjStatusEl();
            if (!el) return;

            el.classList.remove('hidden');
            el.innerHTML = `<div class="cnpj-spinner"></div>`;
        }

        function setCnpjSuccess() {
            const el = cnpjStatusEl();
            if (!el) return;

            el.classList.remove('hidden');
            el.innerHTML = `
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7"/>
                </svg>
            `;
        }

        function setCnpjError() {
            const el = cnpjStatusEl();
            if (!el) return;

            el.classList.remove('hidden');
            el.innerHTML = `
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            `;
        }

        function clearCnpjStatus() {
            const el = cnpjStatusEl();
            if (!el) return;

            el.classList.add('hidden');
            el.innerHTML = '';
        }


        /* =========================
        BUSCAR CNPJ (RECEITA)
        ========================= */
        async function buscarCNPJ(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');

            // ignora CPF
            if (cnpj.length !== 14) {
                clearCnpjStatus();
                return;
            }

            setCnpjLoading();

            try {
                const response = await fetch(`/api/cnpj/${cnpj}`);
                const data = await response.json();

                if (data.status === 'ERROR') {
                    setCnpjError();
                    return;
                }

                document.querySelector('[name="razao_social"]').value = data.nome || '';
                document.querySelector('[name="nome_fantasia"]').value = data.fantasia || '';
                document.querySelector('[name="cep"]').value = data.cep || '';
                document.querySelector('[name="logradouro"]').value = data.logradouro || '';
                document.querySelector('[name="numero"]').value = data.numero || '';
                document.querySelector('[name="bairro"]').value = data.bairro || '';
                document.querySelector('[name="cidade"]').value = data.municipio || '';
                document.querySelector('[name="estado"]').value = data.uf || '';

                if (data.cep) {
                    buscarCEP(data.cep.replace(/\D/g, ''));
                }

                setCnpjSuccess();

            } catch (error) {
                console.error(error);
                setCnpjError();
            }
        }


        /* =========================
        BUSCAR CEP (VIACEP)
        ========================= */
        async function buscarCEP(cep) {
            cep = cep.replace(/\D/g, '');

            if (cep.length !== 8) return;

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (data.erro) {
                    console.warn('CEP não encontrado');
                    return;
                }

                document.querySelector('[name="logradouro"]').value = data.logradouro || '';
                document.querySelector('[name="bairro"]').value = data.bairro || '';
                document.querySelector('[name="cidade"]').value = data.localidade || '';
                document.querySelector('[name="estado"]').value = data.uf || '';

            } catch (error) {
                console.error('Erro ao consultar CEP', error);
            }
        }

        /* =========================
        DISPARO AUTOMÁTICO (BLUR)
        ========================= */
        document.addEventListener('blur', function(e) {
            if (e.target.name === 'cpf_cnpj') buscarCNPJ(e.target.value);
            if (e.target.name === 'cep') buscarCEP(e.target.value);
        }, true);

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector(
                "form[action='{{ route('clientes.store') }}']"
            );

            if (!form) return;

            form.addEventListener('submit', function() {
                const tipo = document.querySelector('[name="tipo_pessoa"]')?.value;
                const razao = document.querySelector('[name="razao_social"]');
                const fantasia = document.querySelector('[name="nome_fantasia"]');
                const nome = document.querySelector('[name="nome"]');

                if (!nome || !razao) return;

                if (tipo === 'PF') {
                    nome.value = razao.value.trim();
                } else {
                    nome.value = fantasia && fantasia.value.trim() !== '' ?
                        fantasia.value.trim() :
                        razao.value.trim();
                }
            });
        });
    </script>



    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER DO FORMULÁRIO --}}
            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 sm:px-8 sm:py-6 mb-6">
                <h1 class="text-2xl font-bold text-black">Cadastro de Cliente</h1>
                <p class="text-sm text-gray-600 mt-1">Preencha os dados abaixo para criar um novo cliente</p>
            </div>

            {{-- ERROS --}}
            @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-medium text-red-800 mb-2">Erros encontrados:</h3>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form action="{{ route('clientes.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="nome" value="">


                {{-- SEÇÃO 1: DADOS BÁSICOS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados Básicos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pessoa <span
                                    class="text-red-500">*</span></label>
                            <select name="tipo_pessoa"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-gray-900"
                                required>
                                <option value="">Selecione</option>
                                <option value="PF">Pessoa Física</option>
                                <option value="PJ">Pessoa Jurídica</option>
                            </select>
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                CPF / CNPJ <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="cpf_cnpj" class="w-full rounded-lg border border-gray-300 shadow-sm
                            focus:border-blue-500 focus:ring-blue-500 px-3 py-2 pr-10" placeholder="000.000.000-00"
                                required>

                            {{-- Loading / Status --}}
                            <div id="cnpj-status"
                                class="absolute inset-0 flex items-center justify-center pointer-events-none hidden">
                                <!-- conteúdo dinâmico -->
                            </div>
                        </div>


                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Cadastro <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="data_cadastro" value="{{ date('Y-m-d') }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome / Razão Social <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="razao_social"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                placeholder="Digite o nome completo" required>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                placeholder="Digite o nome fantasia">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 2: CONTATOS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Contatos
                    </h3>

                    <div class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700">Emails <span
                                        class="text-red-500">*</span></label>
                                <button type="button" onclick="addEmail()"
                                    class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Adicionar
                                </button>
                            </div>
                            <div id="emails" class="space-y-3">
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <input type="email" name="emails[]"
                                        class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                        placeholder="seu.email@exemplo.com" required>
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <input type="radio" name="email_principal" checked
                                            class="rounded text-blue-600">
                                        <span class="text-sm text-gray-600">Principal</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700">Telefones</label>
                                <button type="button" onclick="addTelefone()"
                                    class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Adicionar
                                </button>
                            </div>
                            <div id="telefones" class="space-y-3">
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <input type="text" name="telefones[]"
                                        class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                        placeholder="(00) 0000-0000">
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <input type="radio" name="telefone_principal" checked
                                            class="rounded text-blue-600">
                                        <span class="text-sm text-gray-600">Principal</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: ENDEREÇO --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Endereço
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                            <input type="text" name="cep" placeholder="00000-000"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1 sm:col-span-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                            <input type="text" name="logradouro" placeholder="Rua, Avenida, etc."
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                            <input type="text" name="numero" placeholder="Nº"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mt-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                            <input type="text" name="bairro" placeholder="Bairro"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                            <input type="text" name="cidade" placeholder="Cidade"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                            <input type="text" name="estado" placeholder="UF" maxlength="2"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 uppercase">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                        <input type="text" name="complemento" placeholder="Apto, sala, etc."
                            class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                    </div>
                </div>

                {{-- SEÇÃO 4: CONTRATO --}}
                <div id="bloco-contrato" class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Informações de Contrato
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cliente</label>
                            <select name="tipo_cliente"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-gray-900">
                                <option value="CONTRATO">Contrato</option>
                                <option value="AVULSO">Avulso</option>
                            </select>
                        </div>


                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Mensal</label>
                                <input type="number" step="0.01" name="valor_mensal" placeholder="0,00"
                                    class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            </div>

                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dia de Vencimento</label>
                                <select name="dia_vencimento"
                                    class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 text-gray-900">
                                    <option value="">Selecione</option>
                                    @for($i=1;$i<=28;$i++) <option value="{{ $i }}">Dia {{ $i }}</option>
                                        @endfor
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente Com Nota Fiscal?</label>
                                <select name="nota_fiscal" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                    </div>
                </div>

                {{-- SEÇÃO 5: INSCRIÇÕES --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Inscrições
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Estadual</label>
                            <input type="text" name="inscricao_estadual" placeholder="IE"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Municipal</label>
                            <input type="text" name="inscricao_municipal" placeholder="IM"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 6: OBSERVAÇÕES --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Observações
                    </h3>
                    <textarea name="observacoes" rows="4"
                        class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                        placeholder="Adicione observações importantes sobre o cliente..."></textarea>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 bg-white shadow rounded-lg p-6 sm:p-8">
                    <a href="{{ route('clientes.index') }}"
                        class="btn btn-cancelar inline-flex items-center justify-center px-6 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition duration-200"
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #ef4444; color: white; border: none; border-radius: 9999px; min-width: 130px; justify-content: center;">

                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>

                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary"
                        style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; background: #3b82f6; border-radius: 9999px; min-width: 130px; justify-content: center;">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </button>

            </form>
        </div>
    </div>
</x-app-layout>