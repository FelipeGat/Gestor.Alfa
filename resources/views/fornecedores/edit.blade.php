<x-app-layout>
    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Fornecedor
        </h2>
    </x-slot>

    <div class="py-8" x-data="fornecedorForm({{ json_encode($fornecedor) }})">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- HEADER DO FORMULÁRIO --}}
            <div class="mb-4">
                <x-back-button />
            </div>

            <div class="bg-slate-100 shadow-lg rounded-lg px-6 py-4 sm:px-8 sm:py-6 mb-6">
                <h1 class="text-2xl font-bold text-black">Editar Fornecedor</h1>
                <p class="text-sm text-gray-600 mt-1">Altere os dados do fornecedor conforme necessário</p>
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

            <form method="POST" action="{{ route('fornecedores.update', $fornecedor) }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- SEÇÃO 1: DADOS BÁSICOS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Dados Básicos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <input type="hidden" x-model="tipoPessoa" value="{{ $fornecedor->tipo_pessoa }}">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pessoa</label>
                            <select name="tipo_pessoa" @change="tipoPessoa = $event.target.value"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                <option value="PF" {{ old('tipo_pessoa', $fornecedor->tipo_pessoa) == 'PF' ? 'selected' : '' }}>Pessoa Física</option>
                                <option value="PJ" {{ old('tipo_pessoa', $fornecedor->tipo_pessoa) == 'PJ' ? 'selected' : '' }}>Pessoa Jurídica</option>
                            </select>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="tipoPessoa === 'PJ' ? 'CNPJ' : 'CPF'"></label>
                            <input type="text"
                                name="cpf_cnpj"
                                x-model="cpfCnpj"
                                :maxlength="tipoPessoa === 'PJ' ? 18 : 14"
                                :placeholder="tipoPessoa === 'PJ' ? '00.000.000/0000-00' : '000.000.000-00'"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="tipoPessoa === 'PJ' ? 'Razão Social' : 'Nome Completo'"></label>
                            <input type="text" name="razao_social" value="{{ old('razao_social', $fornecedor->razao_social) }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                required>
                        </div>

                        <div class="col-span-1 sm:col-span-4" x-show="tipoPessoa === 'PJ'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia', $fornecedor->nome_fantasia) }}"
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
                                value="{{ old('cep', $fornecedor->cep) }}"
                                x-model="cep"
                                @blur="buscarCep()"
                                maxlength="9"
                                placeholder="00000-000"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                            <input type="text" name="logradouro" value="{{ old('logradouro', $fornecedor->logradouro) }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                            <input type="text" name="numero" value="{{ old('numero', $fornecedor->numero) }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                            <input type="text" name="complemento" value="{{ old('complemento', $fornecedor->complemento) }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                            <input type="text" name="bairro" value="{{ old('bairro', $fornecedor->bairro) }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                            <input type="text" name="cidade" value="{{ old('cidade', $fornecedor->cidade) }}"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                        </div>

                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" x-model="estado"
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                                <option value="">Selecione</option>
                                <option value="AC" {{ old('estado', $fornecedor->estado) == 'AC' ? 'selected' : '' }}>Acre</option>
                                <option value="AL" {{ old('estado', $fornecedor->estado) == 'AL' ? 'selected' : '' }}>Alagoas</option>
                                <option value="AP" {{ old('estado', $fornecedor->estado) == 'AP' ? 'selected' : '' }}>Amapá</option>
                                <option value="AM" {{ old('estado', $fornecedor->estado) == 'AM' ? 'selected' : '' }}>Amazonas</option>
                                <option value="BA" {{ old('estado', $fornecedor->estado) == 'BA' ? 'selected' : '' }}>Bahia</option>
                                <option value="CE" {{ old('estado', $fornecedor->estado) == 'CE' ? 'selected' : '' }}>Ceará</option>
                                <option value="DF" {{ old('estado', $fornecedor->estado) == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                                <option value="ES" {{ old('estado', $fornecedor->estado) == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                                <option value="GO" {{ old('estado', $fornecedor->estado) == 'GO' ? 'selected' : '' }}>Goiás</option>
                                <option value="MA" {{ old('estado', $fornecedor->estado) == 'MA' ? 'selected' : '' }}>Maranhão</option>
                                <option value="MT" {{ old('estado', $fornecedor->estado) == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                                <option value="MS" {{ old('estado', $fornecedor->estado) == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                                <option value="MG" {{ old('estado', $fornecedor->estado) == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                                <option value="PA" {{ old('estado', $fornecedor->estado) == 'PA' ? 'selected' : '' }}>Pará</option>
                                <option value="PB" {{ old('estado', $fornecedor->estado) == 'PB' ? 'selected' : '' }}>Paraíba</option>
                                <option value="PR" {{ old('estado', $fornecedor->estado) == 'PR' ? 'selected' : '' }}>Paraná</option>
                                <option value="PE" {{ old('estado', $fornecedor->estado) == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                                <option value="PI" {{ old('estado', $fornecedor->estado) == 'PI' ? 'selected' : '' }}>Piauí</option>
                                <option value="RJ" {{ old('estado', $fornecedor->estado) == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                                <option value="RN" {{ old('estado', $fornecedor->estado) == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                                <option value="RS" {{ old('estado', $fornecedor->estado) == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                                <option value="RO" {{ old('estado', $fornecedor->estado) == 'RO' ? 'selected' : '' }}>Rondônia</option>
                                <option value="RR" {{ old('estado', $fornecedor->estado) == 'RR' ? 'selected' : '' }}>Roraima</option>
                                <option value="SC" {{ old('estado', $fornecedor->estado) == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                                <option value="SP" {{ old('estado', $fornecedor->estado) == 'SP' ? 'selected' : '' }}>São Paulo</option>
                                <option value="SE" {{ old('estado', $fornecedor->estado) == 'SE' ? 'selected' : '' }}>Sergipe</option>
                                <option value="TO" {{ old('estado', $fornecedor->estado) == 'TO' ? 'selected' : '' }}>Tocantins</option>
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Contato</label>
                                <input type="text" name="nome_contato" value="{{ old('nome_contato', $fornecedor->contatos->first()->nome ?? '') }}"
                                    class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                                <input type="text" name="cargo_contato" value="{{ old('cargo_contato', $fornecedor->contatos->first()->cargo ?? '') }}"
                                    class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            </div>
                        </div>

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
                                @if(old('emails'))
                                    @foreach(old('emails') as $index => $email)
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <input type="email" name="emails[]" value="{{ $email }}"
                                            class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                            placeholder="seu.email@exemplo.com">
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <input type="radio" name="email_principal" value="{{ $index }}" {{ old('email_principal') == $index ? 'checked' : '' }} class="rounded-full text-blue-600">
                                            <span class="text-sm text-gray-600">Principal</span>
                                        </div>
                                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">Remover</button>
                                    </div>
                                    @endforeach
                                @else
                                    @php $hasEmails = false; @endphp
                                    @foreach($fornecedor->contatos as $contato)
                                        @if($contato->email)
                                            @php $hasEmails = true; @endphp
                                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <input type="email" name="emails[]" value="{{ $contato->email }}"
                                                    class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                                    placeholder="seu.email@exemplo.com">
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <input type="radio" name="email_principal" value="{{ $loop->index }}" {{ $contato->principal ? 'checked' : '' }} class="rounded-full text-blue-600">
                                                    <span class="text-sm text-gray-600">Principal</span>
                                                </div>
                                                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">Remover</button>
                                            </div>
                                        @endif
                                    @endforeach
                                    @if(!$hasEmails)
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <input type="email" name="emails[]"
                                            class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                            placeholder="seu.email@exemplo.com">
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <input type="radio" name="email_principal" value="0" class="rounded-full text-blue-600">
                                            <span class="text-sm text-gray-600">Principal</span>
                                        </div>
                                    </div>
                                    @endif
                                @endif
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
                                @if(old('telefones'))
                                    @foreach(old('telefones') as $index => $telefone)
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <input type="text" name="telefones[]" value="{{ $telefone }}"
                                            class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                            placeholder="(00) 0000-0000">
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <input type="radio" name="telefone_principal" value="{{ $index }}" {{ old('telefone_principal') == $index ? 'checked' : '' }} class="rounded-full text-blue-600">
                                            <span class="text-sm text-gray-600">Principal</span>
                                        </div>
                                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">Remover</button>
                                    </div>
                                    @endforeach
                                @else
                                    @php $hasTelefones = false; @endphp
                                    @foreach($fornecedor->contatos as $contato)
                                        @if($contato->telefone)
                                            @php $hasTelefones = true; @endphp
                                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <input type="text" name="telefones[]" value="{{ $contato->telefone }}"
                                                    class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                                    placeholder="(00) 0000-0000">
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <input type="radio" name="telefone_principal" value="{{ $loop->index }}" {{ $contato->principal ? 'checked' : '' }} class="rounded-full text-blue-600">
                                                    <span class="text-sm text-gray-600">Principal</span>
                                                </div>
                                                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">Remover</button>
                                            </div>
                                        @endif
                                    @endforeach
                                    @if(!$hasTelefones)
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <input type="text" name="telefones[]"
                                            class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                            placeholder="(00) 0000-0000">
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <input type="radio" name="telefone_principal" value="0" class="rounded-full text-blue-600">
                                            <span class="text-sm text-gray-600">Principal</span>
                                        </div>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 4: STATUS --}}
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-200">
                        Status
                    </h3>

                    <div class="max-w-xs">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Situação do Fornecedor
                        </label>
                        <select name="ativo" 
                            class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                            <option value="1" {{ old('ativo', $fornecedor->ativo) == 1 ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ old('ativo', $fornecedor->ativo) == 0 ? 'selected' : '' }}>Inativo</option>
                        </select>
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
            const container = document.getElementById('emails');
            const count = container.querySelectorAll('input[name="emails[]"]').length;
            container.insertAdjacentHTML(
                'beforeend',
                `<div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                    <input type="email" name="emails[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <input type="radio" name="email_principal" value="${count}" class="rounded-full text-blue-600">
                        <span class="text-sm text-gray-600">Principal</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm font-medium">
                        Remover
                    </button>
                </div>`
            );
        }

        function addTelefone() {
            const container = document.getElementById('telefones');
            const count = container.querySelectorAll('input[name="telefones[]"]').length;
            container.insertAdjacentHTML(
                'beforeend',
                `<div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                    <input type="text" name="telefones[]" class="block w-full sm:flex-1 rounded-md border border-gray-300 shadow-sm telefone focus:border-blue-500 focus:ring-blue-500 px-3 py-2" placeholder="(00) 0000-0000">
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <input type="radio" name="telefone_principal" value="${count}" class="rounded-full text-blue-600">
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

        function fornecedorForm(fornecedor) {
            return {
                tipoPessoa: fornecedor.tipo_pessoa || 'PJ',
                cpfCnpj: fornecedor.cpf_cnpj || '',
                razaoSocial: fornecedor.razao_social || '',
                nomeFantasia: fornecedor.nome_fantasia || '',
                cep: fornecedor.cep || '',
                logradouro: fornecedor.logradouro || '',
                numero: fornecedor.numero || '',
                complemento: fornecedor.complemento || '',
                bairro: fornecedor.bairro || '',
                cidade: fornecedor.cidade || '',
                estado: fornecedor.estado || '',
                ativo: fornecedor.ativo == 1 ? 1 : 0,
                contatoNome: (fornecedor.contatos && fornecedor.contatos.length > 0) ? (fornecedor.contatos[0].nome || '') : '',
                contatoCargo: (fornecedor.contatos && fornecedor.contatos.length > 0) ? (fornecedor.contatos[0].cargo || '') : '',

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
