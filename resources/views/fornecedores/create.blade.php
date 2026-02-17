<x-app-layout>
    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Novo Fornecedor
        </h2>
    </x-slot>

    <div class="py-8" x-data="fornecedorForm()">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER DO FORMULÁRIO --}}
            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 sm:px-8 sm:py-6 mb-6">
                <h1 class="text-2xl font-bold text-black">Cadastro de Fornecedor</h1>
                <p class="text-sm text-gray-600 mt-1">Preencha os dados abaixo para criar um novo fornecedor</p>
            </div>

            {{-- ERROS --}}
            @if($errors->any())
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
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('fornecedores.store') }}" class="space-y-6">
                @csrf

                {{-- SEÇÃO 1: DADOS BÁSICOS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados Básicos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pessoa</label>
                            <select name="tipo_pessoa" x-model="tipoPessoa"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                <option value="PF">Pessoa Física</option>
                                <option value="PJ">Pessoa Jurídica</option>
                            </select>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="tipoPessoa === 'PJ' ? 'CNPJ' : 'CPF'"></label>
                            <input type="text"
                                name="cpf_cnpj"
                                x-model="cpfCnpj"
                                @blur="buscarPorCnpj()"
                                :maxlength="tipoPessoa === 'PJ' ? 18 : 14"
                                :placeholder="tipoPessoa === 'PJ' ? '00.000.000/0000-00' : '000.000.000-00'"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            <small class="text-red-500 font-semibold mt-1 block" x-show="duplicado" x-text="msgDuplicado"></small>
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="tipoPessoa === 'PJ' ? 'Razão Social' : 'Nome Completo'"></label>
                            <input type="text" name="razao_social" value="{{ old('razao_social') }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                required>
                        </div>

                        <div class="col-span-1 sm:col-span-4" x-show="tipoPessoa === 'PJ'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 2: ENDEREÇO --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Endereço
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                            <input type="text"
                                name="cep"
                                x-model="cep"
                                @blur="buscarCep()"
                                maxlength="9"
                                placeholder="00000-000"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                            <input type="text" name="logradouro" x-model="logradouro"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                            <input type="text" name="numero" value="{{ old('numero') }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                            <input type="text" name="complemento" value="{{ old('complemento') }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                            <input type="text" name="bairro" x-model="bairro"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                            <input type="text" name="cidade" x-model="cidade"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" x-model="estado"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                <option value="">Selecione</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: CONTATOS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Contatos
                    </h3>

                    <div class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700">Emails</label>
                                <button type="button" onclick="addEmail()"
                                    class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.25rem; min-width: 130px; justify-content: center; background: #22c55e; border-radius: 9999px;">
                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                    </svg>
                                    Adicionar
                                </button>
                            </div>
                            <div id="emails" class="space-y-3">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <input type="email" name="emails[]"
                                        class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                        placeholder="seu.email@exemplo.com">
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <input type="radio" name="email_principal" value="1" class="rounded-full text-blue-600">
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
                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 18px; height: 18px;">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                    </svg>
                                    Adicionar
                                </button>
                            </div>
                            <div id="telefones" class="space-y-3">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <input type="text" name="telefones[]"
                                        class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                        placeholder="(00) 0000-0000">
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <input type="radio" name="telefone_principal" value="1" class="rounded-full text-blue-600">
                                        <span class="text-sm text-gray-600">Principal</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Contato</label>
                                <input type="text" name="nome_contato" value="{{ old('nome_contato') }}"
                                    class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                                <input type="text" name="cargo_contato" value="{{ old('cargo_contato') }}"
                                    class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 4: STATUS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="ativo" value="1" checked
                                class="rounded text-blue-600 border-gray-300 shadow-sm focus:border-blue-300 focus:ring-blue-500 w-5 h-5" id="fornecedor-ativo">
                            <span class="ml-3 text-sm font-medium text-gray-700">Fornecedor Ativo</span>
                        </label>
                    </div>
                </div>

                {{-- BOTÕES DE AÇÃO --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3
                            bg-white shadow rounded-lg p-6 sm:p-8">

                    <a href="{{ route('fornecedores.index') }}"
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
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function addEmail() {
            document.getElementById('emails').insertAdjacentHTML(
                'beforeend',
                `<div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                <input type="email" name="emails[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <input type="radio" name="email_principal" value="1" class="rounded-full text-blue-600">
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
                <input type="text" name="telefones[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-blue-500 focus:ring-blue-500 px-3 py-2" placeholder="(00) 0000-0000">
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <input type="radio" name="telefone_principal" value="1" class="rounded-full text-blue-600">
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
        });

        function fornecedorForm() {
            return {
                tipoPessoa: 'PJ',
                cpfCnpj: '',
                duplicado: false,
                msgDuplicado: '',
                cep: '',
                logradouro: '',
                bairro: '',
                cidade: '',
                estado: '',

                buscarPorCnpj() {
                    if (!this.cpfCnpj) return;

                    fetch(`/fornecedores/api/buscar-cnpj?cnpj=${this.cpfCnpj}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.exists) {
                                this.duplicado = true;
                                this.msgDuplicado = `Fornecedor já cadastrado: ${data.fornecedor.razao_social}`;
                            } else {
                                this.duplicado = false;
                                this.msgDuplicado = '';
                            }
                        });
                },

                buscarCep() {
                    const cepLimpo = this.cep.replace(/\D/g, '');
                    if (cepLimpo.length !== 8) return;

                    fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
                        .then(res => res.json())
                        .then(data => {
                            if (!data.erro) {
                                this.logradouro = data.logradouro;
                                this.bairro = data.bairro;
                                this.cidade = data.localidade;
                                this.estado = data.uf;
                            }
                        })
                        .catch(err => console.error('Erro ao buscar CEP:', err));
                }
            }
        }
    </script>
    @endpush
</x-app-layout>