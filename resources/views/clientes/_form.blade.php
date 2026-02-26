@php
$isEdit = isset($cliente);
$route = $isEdit ? route('clientes.update', $cliente) : route('clientes.store');
$method = $isEdit ? 'PUT' : 'POST';
@endphp

@if ($errors->any())
<div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
    <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $erro)
        <li>{{ $erro }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $route }}" class="space-y-4" x-data="clienteForm()">
    @csrf
    @method($method)
    <input type="hidden" name="nome" value="">
    <input type="hidden" name="is_edit" value="{{ $isEdit ? '1' : '0' }}">

    <div class="mb-1 mt-0">
        <h3 class="text-base font-semibold text-gray-800" x-text="'Etapa ' + step + ': ' + steps[step - 1].title"></h3>
        <p class="text-sm text-gray-500" x-text="steps[step - 1].description"></p>
        <div class="border-b border-gray-200 pb-2"></div>
    </div>
    <div class="pb-4"></div>

    <div class="w-full mb-4 pb-8">
        <div class="flex items-center justify-between">
            <template x-for="(s, index) in steps" :key="index">
                <div class="flex items-center" :class="index < steps.length - 1 ? 'flex-1' : ''">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-300"
                            :class="step > index + 1 ? 'bg-green-500 border-green-500 text-white' : step === index + 1 ? 'bg-[#3f9cae] border-[#3f9cae] text-white ring-4 ring-[#3f9cae]/20' : 'bg-white border-gray-300 text-gray-400'">
                            <span x-show="step > index + 1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span x-show="step <= index + 1" x-text="index + 1"></span>
                        </div>
                        <span class="text-xs mt-1 font-medium" :class="step >= index + 1 ? 'text-[#3f9cae]' : 'text-gray-400'" x-text="s.label"></span>
                    </div>
                    <div x-show="index < steps.length - 1" class="flex-1 h-1 mx-2 transition-colors duration-300" 
                        :class="step > index + 1 ? 'bg-green-500' : 'bg-gray-200'">
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- STEP 1: DADOS BÁSICOS --}}
    <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pessoa <span class="text-red-500">*</span></label>
                <select name="tipo_pessoa" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2 text-gray-900" required>
                    <option value="PF" @selected(old('tipo_pessoa', $cliente->tipo_pessoa ?? 'PF')=='PF')>Pessoa Física</option>
                    <option value="PJ" @selected(old('tipo_pessoa', $cliente->tipo_pessoa ?? 'PJ')=='PJ')>Pessoa Jurídica</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CPF / CNPJ <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="000.000.000-00" required>
                    <x-button variant="info" size="sm" onclick="buscarCNPJ(this.previousElementSibling.value)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </x-button>
                </div>
                <div id="cnpj-status" class="hidden mt-1"></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Cadastro <span class="text-red-500">*</span></label>
                <input type="date" name="data_cadastro" value="{{ old('data_cadastro', $cliente->data_cadastro ?? date('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" required>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome / Razão Social <span class="text-red-500">*</span></label>
                <input type="text" name="razao_social" value="{{ old('razao_social', $cliente->razao_social ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia', $cliente->nome ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
            </div>
        </div>
    </div>

    {{-- STEP 2: ENDEREÇO --}}
    <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                <input type="text" name="cep" value="{{ old('cep', $cliente->cep ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="00000-000">
            </div>
            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                <input type="text" name="logradouro" value="{{ old('logradouro', $cliente->logradouro ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                <input type="text" name="numero" value="{{ old('numero', $cliente->numero ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                <input type="text" name="bairro" value="{{ old('bairro', $cliente->bairro ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input type="text" name="cidade" value="{{ old('cidade', $cliente->cidade ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                <input type="text" name="estado" value="{{ old('estado', $cliente->estado ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 uppercase" maxlength="2">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
            <input type="text" name="complemento" value="{{ old('complemento', $cliente->complemento ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
        </div>
    </div>

    {{-- STEP 3: CONTATOS --}}
    <div x-show="step === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">Emails <span class="text-red-500">*</span></label>
                <button type="button" onclick="addEmail()" class="text-sm px-2 py-1 bg-green-500 text-white rounded-full hover:bg-green-600">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Adicionar
                </button>
            </div>
            <div id="emails-container" class="space-y-2">
                @if($isEdit && $cliente->emails->count())
                    @foreach($cliente->emails as $i => $email)
                    <div class="flex gap-2 items-center">
                        <input type="email" name="emails[]" value="{{ $email->valor }}" class="flex-1 rounded-md border border-gray-300 px-3 py-2" required>
                        <label class="flex items-center gap-1 text-sm">
                            <input type="radio" name="email_principal" value="{{ $i }}" {{ $email->principal ? 'checked' : '' }}> Principal
                        </label>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 text-sm">✕</button>
                    </div>
                    @endforeach
                @else
                <div class="flex gap-2 items-center">
                    <input type="email" name="emails[]" class="flex-1 rounded-md border border-gray-300 px-3 py-2" placeholder="email@exemplo.com" required>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="radio" name="email_principal" value="0" checked> Principal
                    </label>
                </div>
                @endif
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">Telefones</label>
                <button type="button" onclick="addTelefone()" class="text-sm px-2 py-1 bg-green-500 text-white rounded-full hover:bg-green-600">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Adicionar
                </button>
            </div>
            <div id="telefones-container" class="space-y-2">
                @if($isEdit && $cliente->telefones->count())
                    @foreach($cliente->telefones as $i => $telefone)
                    <div class="flex gap-2 items-center">
                        <input type="text" name="telefones[]" value="{{ $telefone->valor }}" class="telefone flex-1 rounded-md border border-gray-300 px-3 py-2">
                        <label class="flex items-center gap-1 text-sm">
                            <input type="radio" name="telefone_principal" value="{{ $i }}" {{ $telefone->principal ? 'checked' : '' }}> Principal
                        </label>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 text-sm">✕</button>
                    </div>
                    @endforeach
                @else
                <div class="flex gap-2 items-center">
                    <input type="text" name="telefones[]" class="telefone flex-1 rounded-md border border-gray-300 px-3 py-2" placeholder="(00) 00000-0000">
                    <label class="flex items-center gap-1 text-sm">
                        <input type="radio" name="telefone_principal" value="0"> Principal
                    </label>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- STEP 4: INFORMAÇÕES ADICIONAIS --}}
    <div x-show="step === 4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @if($isEdit)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cliente</label>
                <select name="tipo_cliente" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="CONTRATO" @selected(old('tipo_cliente', $cliente->tipo_cliente ?? '')=='CONTRATO')>Contrato</option>
                    <option value="AVULSO" @selected(old('tipo_cliente', $cliente->tipo_cliente ?? '')=='AVULSO')>Avulso</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Mensal</label>
                <input type="number" step="0.01" name="valor_mensal" value="{{ old('valor_mensal', $cliente->valor_mensal ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dia de Vencimento</label>
                <select name="dia_vencimento" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">Selecione</option>
                    @for($i=1;$i<=28;$i++)
                    <option value="{{ $i }}" @selected(old('dia_vencimento', $cliente->dia_vencimento ?? '')==$i)>Dia {{ $i }}</option>
                    @endfor
                </select>
            </div>
            @else
            <input type="hidden" name="tipo_cliente" value="CONTRATO">
            <input type="hidden" name="ativo" value="1">
            @endif
            @if($isEdit)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nota Fiscal?</label>
                <select name="nota_fiscal" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="0" @selected(old('nota_fiscal', $cliente->nota_fiscal ?? 0)==0)>Não</option>
                    <option value="1" @selected(old('nota_fiscal', $cliente->nota_fiscal ?? 0)==1)>Sim</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Estadual</label>
                <input type="text" name="inscricao_estadual" value="{{ old('inscricao_estadual', $cliente->inscricao_estadual ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Municipal</label>
                <input type="text" name="inscricao_municipal" value="{{ old('inscricao_municipal', $cliente->inscricao_municipal ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Situação</label>
                <select name="ativo" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="1" @selected(old('ativo', $cliente->ativo ?? true))>Ativo</option>
                    <option value="0" @selected(old('ativo', $cliente->ativo ?? true) == false)>Inativo</option>
                </select>
            </div>
        </div>

        @if($isEdit)
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Responsáveis do Portal</label>
            <div id="lista-usuarios-portal" class="max-h-24 overflow-y-auto border rounded-md p-3 space-y-2">
                @foreach($usuarios as $usuario)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="usuarios_portal[]" value="{{ $usuario->id }}" @checked(in_array($usuario->id, $usuariosVinculados ?? [])) class="rounded-lg border-gray-300">
                    <span class="text-sm"><strong>{{ $usuario->name }}</strong> ({{ $usuario->email }})</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="observacoes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('observacoes', $cliente->observacoes ?? '') }}</textarea>
        </div>
        @endif
    </div>

    <div class="flex justify-between gap-3 pt-4 border-t">
        <div>
            <x-button 
                variant="secondary" 
                size="sm" 
                class="min-w-[130px]"
                x-show="step > 1" 
                @click="prevStep()"
                iconLeft="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7' /></svg>"
            >
                Anterior
            </x-button>
        </div>
        <div class="flex gap-3">
            <x-button 
                variant="danger" 
                size="sm" 
                class="min-w-[130px]"
                x-on:click="$dispatch('close-modal', 'cliente-modal')"
                iconLeft="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' /></svg>"
            >
                Cancelar
            </x-button>
            
            <x-button 
                variant="primary" 
                size="sm" 
                class="min-w-[130px]"
                x-show="step < 4" 
                @click="nextStep()"
                iconRight="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7' /></svg>"
            >
                Próximo
            </x-button>
            
            <x-button 
                variant="success" 
                size="sm" 
                class="min-w-[130px]"
                type="submit" 
                x-show="step === 4"
                iconLeft="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7' /></svg>"
            >
                {{ $isEdit ? 'Atualizar' : 'Cadastrar' }}
            </x-button>
        </div>
    </div>
</form>

<script>
window.addEmail = function() {
    const container = document.getElementById('emails-container');
    const count = container.children.length;
    container.insertAdjacentHTML('beforeend', `
        <div class="flex gap-2 items-center">
            <input type="email" name="emails[]" class="flex-1 rounded-md border border-gray-300 px-3 py-2" required>
            <label class="flex items-center gap-1 text-sm">
                <input type="radio" name="email_principal" value="${count}"> Principal
            </label>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 text-sm">✕</button>
        </div>
    `);
};

window.addTelefone = function() {
    const container = document.getElementById('telefones-container');
    const count = container.children.length;
    container.insertAdjacentHTML('beforeend', `
        <div class="flex gap-2 items-center">
            <input type="text" name="telefones[]" class="telefone flex-1 rounded-md border border-gray-300 px-3 py-2">
            <label class="flex items-center gap-1 text-sm">
                <input type="radio" name="telefone_principal" value="${count}"> Principal
            </label>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 text-sm">✕</button>
        </div>
    `);
    window.applyTelefoneMask(container.lastElementChild.querySelector('.telefone'));
};

window.applyTelefoneMask = function(input) {
    input.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        e.target.value = v.length <= 10 ?
            v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3') :
            v.replace(/(\d{2})(\d{1})(\d{4})(\d{0,4})/, '($1) $2.$3-$4');
    });
};

document.querySelectorAll('.telefone').forEach(window.applyTelefoneMask);

window.buscarCNPJ = async function(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    if (cnpj.length !== 14) return;
    
    const statusEl = document.getElementById('cnpj-status');
    statusEl.classList.remove('hidden');
    statusEl.innerHTML = '<div class="cnpj-spinner" style="width:20px;height:20px;border-width:2px;"></div>';
    
    try {
        const response = await fetch(`/api/cnpj/${cnpj}`);
        const data = await response.json();
        
        if (data.status === 'ERROR') {
            statusEl.innerHTML = '<span class="text-red-600 text-sm">CNPJ não encontrado</span>';
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
        
        statusEl.innerHTML = '<span class="text-green-600 text-sm">✓ Dados carregados</span>';
    } catch(e) {
        statusEl.innerHTML = '<span class="text-red-600 text-sm">Erro ao buscar</span>';
    }
}

// Preencher campo nome antes do submit
document.querySelector('form').addEventListener('submit', function() {
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
</script>
