<x-app-layout>
    @push('styles')
    @vite('resources/css/financeiro/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('fornecedores.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">Editar Fornecedor</h2>
        </div>
    </x-slot>

    <div class="py-8" x-data="fornecedorForm({{ json_encode($fornecedor) }})">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('fornecedores.update', $fornecedor) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="filters-card">
                    <h3 class="text-lg font-semibold mb-4">Dados Básicos</h3>
                    
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="required">Tipo de Pessoa</label>
                            <select name="tipo_pessoa" x-model="tipoPessoa" required>
                                <option value="PF">Pessoa Física</option>
                                <option value="PJ">Pessoa Jurídica</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="required" x-text="tipoPessoa === 'PJ' ? 'CNPJ' : 'CPF'"></label>
                            <input type="text" 
                                name="cpf_cnpj" 
                                x-model="cpfCnpj"
                                :maxlength="tipoPessoa === 'PJ' ? 18 : 14"
                                :placeholder="tipoPessoa === 'PJ' ? '00.000.000/0000-00' : '000.000.000-00'"
                                required>
                        </div>

                        <div class="filter-group" :class="tipoPessoa === 'PF' ? 'col-span-2' : ''">
                            <label class="required" x-text="tipoPessoa === 'PJ' ? 'Razão Social' : 'Nome Completo'"></label>
                            <input type="text" name="razao_social" x-model="razaoSocial" required>
                        </div>

                        <div class="filter-group" x-show="tipoPessoa === 'PJ'">
                            <label>Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" x-model="nomeFantasia">
                        </div>
                    </div>
                </div>

                <div class="filters-card">
                    <h3 class="text-lg font-semibold mb-4">Endereço</h3>
                    
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="required">CEP</label>
                            <input type="text" 
                                name="cep" 
                                x-model="cep"
                                @blur="buscarCep()"
                                maxlength="9"
                                placeholder="00000-000"
                                required>
                        </div>

                        <div class="filter-group col-span-2">
                            <label class="required">Logradouro</label>
                            <input type="text" name="logradouro" x-model="logradouro" required>
                        </div>

                        <div class="filter-group">
                            <label class="required">Número</label>
                            <input type="text" name="numero" x-model="numero" required>
                        </div>

                        <div class="filter-group col-span-2">
                            <label>Complemento</label>
                            <input type="text" name="complemento" x-model="complemento">
                        </div>

                        <div class="filter-group">
                            <label class="required">Bairro</label>
                            <input type="text" name="bairro" x-model="bairro" required>
                        </div>

                        <div class="filter-group">
                            <label class="required">Cidade</label>
                            <input type="text" name="cidade" x-model="cidade" required>
                        </div>

                        <div class="filter-group">
                            <label class="required">Estado</label>
                            <select name="estado" x-model="estado" required>
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

                <div class="filters-card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Contatos</h3>
                        <button type="button" @click="adicionarContato()" class="btn btn-secondary btn-sm">
                            + Adicionar Contato
                        </button>
                    </div>

                    <template x-for="(contato, index) in contatos" :key="index">
                        <div class="filters-grid mb-3 pb-3 border-b border-gray-200 last:border-0">
                            <div class="filter-group">
                                <label>Nome</label>
                                <input type="text" :name="'contatos['+index+'][nome]'" x-model="contato.nome">
                            </div>
                            <div class="filter-group">
                                <label>Cargo</label>
                                <input type="text" :name="'contatos['+index+'][cargo]'" x-model="contato.cargo">
                            </div>
                            <div class="filter-group">
                                <label>Email</label>
                                <input type="email" :name="'contatos['+index+'][email]'" x-model="contato.email">
                            </div>
                            <div class="filter-group">
                                <label>Telefone</label>
                                <input type="text" :name="'contatos['+index+'][telefone]'" x-model="contato.telefone">
                            </div>
                            <div class="filter-group flex items-end">
                                <label class="flex items-center">
                                    <input type="checkbox" :name="'contatos['+index+'][principal]'" value="1" x-model="contato.principal" class="mr-2">
                                    Contato Principal
                                </label>
                            </div>
                            <div class="filter-group flex items-end">
                                <button type="button" @click="removerContato(index)" class="btn btn-sm bg-red-400 hover:bg-red-500 text-white">
                                    Remover
                                </button>
                            </div>
                        </div>
                    </template>

                    <p x-show="contatos.length === 0" class="text-gray-500 text-sm">Nenhum contato adicionado</p>
                </div>

                <div class="filters-card">
                    <div class="filter-group" style="max-width: 200px;">
                        <label class="flex items-center">
                            <input type="checkbox" name="ativo" value="1" x-model="ativo" class="mr-2">
                            Fornecedor Ativo
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar Fornecedor</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function fornecedorForm(fornecedor) {
            return {
                tipoPessoa: fornecedor.tipo_pessoa,
                cpfCnpj: fornecedor.cpf_cnpj,
                razaoSocial: fornecedor.razao_social,
                nomeFantasia: fornecedor.nome_fantasia || '',
                cep: fornecedor.cep,
                logradouro: fornecedor.logradouro,
                numero: fornecedor.numero,
                complemento: fornecedor.complemento || '',
                bairro: fornecedor.bairro,
                cidade: fornecedor.cidade,
                estado: fornecedor.estado,
                ativo: fornecedor.ativo == 1,
                contatos: fornecedor.contatos.map(c => ({
                    nome: c.nome || '',
                    cargo: c.cargo || '',
                    email: c.email || '',
                    telefone: c.telefone || '',
                    principal: c.principal == 1
                })),

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
                    this.contatos.push({ nome: '', cargo: '', email: '', telefone: '', principal: false });
                },

                removerContato(index) {
                    this.contatos.splice(index, 1);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
