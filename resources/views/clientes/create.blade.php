<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Clientes', 'url' => route('clientes.index')],
            ['label' => 'Novo Cliente']
        ]" />
    </x-slot>

    <x-page-title title="Novo Cliente" :route="route('clientes.index')" />

    <script>
        function addEmail() {
            document.getElementById('emails').insertAdjacentHTML(
                'beforeend',
                `<div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                <input type="email" name="emails[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" required>
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <input type="radio" name="email_principal" value="1" class="rounded-lg text-blue-600">
                    <span class="text-sm text-gray-600">Principal</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">
                    Remover
                </button>
            </div>`
            );
        }

        function addTelefone() {
            document.getElementById('telefones').insertAdjacentHTML(
                'beforeend',
                `<div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                <input type="text" name="telefones[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <input type="radio" name="telefone_principal" value="1" class="rounded-lg text-blue-600">
                    <span class="text-sm text-gray-600">Principal</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">
                    Remover
                </button>
            </div>`
            );
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('telefone')) {
                let v = e.target.value.replace(/\D/g, '');
                e.target.value = v.length <= 10 ?
                    v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3') :
                    v.replace(/(\d{2})(\d{1})(\d{4})(\d{0,4})/, '($1) $2.$3-$4');
            }
            if (e.target.name === 'cpf_cnpj') {
                let v = e.target.value.replace(/\D/g, '');
                e.target.value = v.length <= 11 ?
                    v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2') :
                    v.replace(/(\d{2})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1/$2').replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            }
            if (e.target.name === 'cep') {
                let v = e.target.value.replace(/\D/g, '');
                e.target.value = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
            }
        });

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
            el.innerHTML = `<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
        }

        function setCnpjError() {
            const el = cnpjStatusEl();
            if (!el) return;
            el.classList.remove('hidden');
            el.innerHTML = `<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
        }

        function clearCnpjStatus() {
            const el = cnpjStatusEl();
            if (!el) return;
            el.classList.add('hidden');
            el.innerHTML = '';
        }

        async function buscarCNPJ(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');
            if (cnpj.length !== 14) { clearCnpjStatus(); return; }
            setCnpjLoading();
            try {
                const response = await fetch(`/api/cnpj/${cnpj}`);
                const data = await response.json();
                if (data.status === 'ERROR') { setCnpjError(); return; }
                document.querySelector('[name="razao_social"]').value = data.nome || '';
                document.querySelector('[name="nome_fantasia"]').value = data.fantasia || '';
                document.querySelector('[name="cep"]').value = data.cep || '';
                document.querySelector('[name="logradouro"]').value = data.logradouro || '';
                document.querySelector('[name="numero"]').value = data.numero || '';
                document.querySelector('[name="bairro"]').value = data.bairro || '';
                document.querySelector('[name="cidade"]').value = data.municipio || '';
                document.querySelector('[name="estado"]').value = data.uf || '';
                if (data.cep) buscarCEP(data.cep.replace(/\D/g, ''));
                setCnpjSuccess();
            } catch (error) { console.error(error); setCnpjError(); }
        }

        async function buscarCEP(cep) {
            cep = cep.replace(/\D/g, '');
            if (cep.length !== 8) return;
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                if (data.erro) { console.warn('CEP não encontrado'); return; }
                document.querySelector('[name="logradouro"]').value = data.logradouro || '';
                document.querySelector('[name="bairro"]').value = data.bairro || '';
                document.querySelector('[name="cidade"]').value = data.localidade || '';
                document.querySelector('[name="estado"]').value = data.uf || '';
            } catch (error) { console.error('Erro ao consultar CEP', error); }
        }

        document.addEventListener('blur', function(e) {
            if (e.target.name === 'cpf_cnpj') buscarCNPJ(e.target.value);
            if (e.target.name === 'cep') buscarCEP(e.target.value);
        }, true);

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector("form[action='{{ route('clientes.store') }}']");
            if (!form) return;
            form.addEventListener('submit', function() {
                const tipo = document.querySelector('[name="tipo_pessoa"]')?.value;
                const razao = document.querySelector('[name="razao_social"]');
                const fantasia = document.querySelector('[name="nome_fantasia"]');
                const nome = document.querySelector('[name="nome"]');
                if (!nome || !razao) return;
                if (tipo === 'PF') { nome.value = razao.value.trim(); }
                else { nome.value = fantasia && fantasia.value.trim() !== '' ? fantasia.value.trim() : razao.value.trim(); }
            });
        });
    </script>

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">

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

            <form action="{{ route('clientes.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="nome" value="">

                {{-- SEÇÃO 1: DADOS BÁSICOS --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Dados Básicos</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pessoa <span class="text-red-500">*</span></label>
                            <select name="tipo_pessoa" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" required>
                                <option value="">Selecione</option>
                                <option value="PF">Pessoa Física</option>
                                <option value="PJ">Pessoa Jurídica</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CPF / CNPJ <span class="text-red-500">*</span></label>
                            <input type="text" name="cpf_cnpj" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="000.000.000-00" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Cadastro <span class="text-red-500">*</span></label>
                            <input type="date" name="data_cadastro" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome / Razão Social <span class="text-red-500">*</span></label>
                            <input type="text" name="razao_social" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="Digite o nome completo" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="Digite o nome fantasia">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 2: CONTATOS --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Contatos</h3>

                    <div class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700">Emails <span class="text-red-500">*</span></label>
                                <x-button type="button" variant="success" size="sm" onclick="addEmail()">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                                    Adicionar
                                </x-button>
                            </div>
                            <div id="emails" class="space-y-3">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <input type="email" name="emails[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="seu.email@exemplo.com" required>
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <input type="radio" name="email_principal" checked class="rounded-lg text-blue-600">
                                        <span class="text-sm text-gray-600">Principal</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700">Telefones</label>
                                <x-button type="button" variant="success" size="sm" onclick="addTelefone()">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                                    Adicionar
                                </x-button>
                            </div>
                            <div id="telefones" class="space-y-3">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <input type="text" name="telefones[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="(00) 0000-0000">
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <input type="radio" name="telefone_principal" checked class="rounded-lg text-blue-600">
                                        <span class="text-sm text-gray-600">Principal</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: ENDEREÇO --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Endereço</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                            <input type="text" name="cep" placeholder="00000-000" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                            <input type="text" name="logradouro" placeholder="Rua, Avenida, etc." class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                            <input type="text" name="numero" placeholder="Nº" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                            <input type="text" name="bairro" placeholder="Bairro" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                            <input type="text" name="cidade" placeholder="Cidade" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                            <input type="text" name="estado" placeholder="UF" maxlength="2" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2 uppercase">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                        <input type="text" name="complemento" placeholder="Apto, sala, etc." class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                    </div>
                </div>

                {{-- SEÇÃO 4: CONTRATO --}}
                <div id="bloco-contrato" class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Informações de Contrato</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cliente</label>
                            <select name="tipo_cliente" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                                <option value="CONTRATO">Contrato</option>
                                <option value="AVULSO">Avulso</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Mensal</label>
                            <input type="number" step="0.01" name="valor_mensal" placeholder="0,00" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dia de Vencimento</label>
                            <select name="dia_vencimento" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                                <option value="">Selecione</option>
                                @for($i=1;$i<=28;$i++) <option value="{{ $i }}">Dia {{ $i }}</option> @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente Com Nota Fiscal?</label>
                            <select name="nota_fiscal" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                                <option value="0">Não</option>
                                <option value="1">Sim</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 5: INSCRIÇÕES --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Inscrições</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Estadual</label>
                            <input type="text" name="inscricao_estadual" placeholder="IE" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Municipal</label>
                            <input type="text" name="inscricao_municipal" placeholder="IM" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 6: OBSERVAÇÕES --}}
                <div class="bg-white rounded-lg p-6 sm:p-8" style="border: 1px solid #3f9cae; border-top-width: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">Observações</h3>
                    <textarea name="observacoes" rows="4" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="Adicione observações importantes sobre o cliente..."></textarea>
                </div>

                {{-- AÇÕES --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <x-button href="{{ route('clientes.index') }}" variant="danger" size="md" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Cancelar
                    </x-button>

                    <x-button type="submit" variant="primary" size="md" class="min-w-[130px]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Salvar
                    </x-button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
