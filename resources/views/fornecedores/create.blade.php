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

            @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Foram encontrados erros no formulário:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
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
                <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <span class="p-1.5 bg-blue-50 text-blue-600 rounded-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            Dados Básicos
                        </h3>
                    </div>

                    <div class="p-6 sm:p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Tipo de Pessoa</label>
                                <select name="tipo_pessoa" x-model="tipoPessoa"
                                    <option value="PF">Pessoa Física</option>
                                    <option value="PJ">Pessoa Jurídica</option>
                                </select>
                            </div>

                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2" x-text="tipoPessoa === 'PJ' ? 'CNPJ' : 'CPF'"></label>
                                <input type="text"
                                    name="cpf_cnpj"
                                    x-model="cpfCnpj"
                                    @blur="buscarPorCnpj()"
                                    :maxlength="tipoPessoa === 'PJ' ? 18 : 14"
                                    :placeholder="tipoPessoa === 'PJ' ? '00.000.000/0000-00' : '000.000.000-00'"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
                                <small class="text-red-500 font-semibold mt-1 block" x-show="duplicado" x-text="msgDuplicado"></small>
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2" x-text="tipoPessoa === 'PJ' ? 'Razão Social' : 'Nome Completo'"></label>
                                <input type="text" name="razao_social" value="{{ old('razao_social') }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all"
                                    required>
                            </div>

                            <div class="col-span-1 sm:col-span-4" x-show="tipoPessoa === 'PJ'">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 2: ENDEREÇO --}}
                <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <span class="p-1.5 bg-green-50 text-green-600 rounded-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            Endereço
                        </h3>
                    </div>

                    <div class="p-6 sm:p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">CEP</label>
                                <input type="text"
                                    name="cep"
                                    x-model="cep"
                                    @blur="buscarCep()"
                                    maxlength="9"
                                    placeholder="00000-000"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Logradouro</label>
                                <input type="text" name="logradouro" x-model="logradouro"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
                            </div>

                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Número</label>
                                <input type="text" name="numero" value="{{ old('numero') }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Complemento</label>
                                <input type="text" name="complemento" value="{{ old('complemento') }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Bairro</label>
                                <input type="text" name="bairro" x-model="bairro"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Cidade</label>
                                <input type="text" name="cidade" x-model="cidade"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Estado</label>
                                <select name="estado" x-model="estado"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2.5 text-sm transition-all">
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
                </div>

                {{-- SEÇÃO 3: CONTATOS --}}
                <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <span class="p-1.5 bg-orange-50 text-orange-600 rounded-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            Contatos
                        </h3>
                        <button type="button" @click="adicionarContato()" class="btn btn-sm btn-success">
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Adicionar Contato
                        </button>
                    </div>

                    <div class="p-6 sm:p-8">
                        <template x-for="(contato, index) in contatos" :key="index">
                            <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100 relative group transition-all hover:shadow-sm">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                                    <div class="col-span-1 sm:col-span-2">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nome</label>
                                        <input type="text" :name="'contatos['+index+'][nome]'" x-model="contato.nome"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2 text-sm transition-all">
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Cargo</label>
                                        <input type="text" :name="'contatos['+index+'][cargo]'" x-model="contato.cargo"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2 text-sm transition-all">
                                    </div>
                                    <div class="col-span-1 sm:col-span-2">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email</label>
                                        <input type="email" :name="'contatos['+index+'][email]'" x-model="contato.email"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2 text-sm transition-all">
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Telefone</label>
                                        <input type="text" :name="'contatos['+index+'][telefone]'" x-model="contato.telefone"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-3 py-2 text-sm transition-all">
                                    </div>

                                    <div class="col-span-1 sm:col-span-3 flex items-center mt-2">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" :name="'contatos['+index+'][principal]'" value="1" x-model="contato.principal"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-5 h-5">
                                            <span class="ml-2 text-sm font-semibold text-gray-700">Contato Principal</span>
                                        </label>
                                    </div>

                                    <div class="col-span-1 sm:col-span-3 flex items-center justify-end mt-2">
                                        <button type="button" @click="removerContato(index)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
                                            x-show="contatos.length > 1">
                                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-4 h-4">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            Remover
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- SEÇÃO 4: STATUS --}}
                <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="ativo" value="1" checked
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-6 h-6" id="fornecedor-ativo">
                            <span class="ml-3 text-sm font-bold text-gray-700 uppercase tracking-wider">Fornecedor Ativo</span>
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
                contatos: [{
                    nome: '',
                    cargo: '',
                    email: '',
                    telefone: '',
                    principal: true
                }],

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
                },

                adicionarContato() {
                    this.contatos.push({
                        nome: '',
                        cargo: '',
                        email: '',
                        telefone: '',
                        principal: false
                    });
                },

                removerContato(index) {
                    if (this.contatos.length > 1) {
                        this.contatos.splice(index, 1);
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>