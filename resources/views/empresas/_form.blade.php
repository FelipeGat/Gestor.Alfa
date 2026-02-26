@php
$isEdit = isset($empresa);
$route = $isEdit ? route('empresas.update', $empresa) : route('empresas.store');
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

<form method="POST" action="{{ $route }}" class="space-y-4">
    @csrf
    @method($method)
    <input type="hidden" name="is_edit" value="{{ $isEdit ? '1' : '0' }}">

    <div class="bg-white rounded-lg p-4" style="border: 1px solid #3f9cae; border-top-width: 4px;">
        <h3 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
            Dados da Empresa
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Razão Social <span class="text-red-500">*</span></label>
                <input type="text" name="razao_social" value="{{ old('razao_social', $empresa->razao_social ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="Razão Social da empresa" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia', $empresa->nome_fantasia ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="Nome fantasia (opcional)">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ <span class="text-red-500">*</span></label>
                <input type="text" name="cnpj" value="{{ old('cnpj', $empresa->cnpj ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="00.000.000/0000-00" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                <input type="text" name="endereco" value="{{ old('endereco', $empresa->endereco ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="Endereço completo">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg p-4" style="border: 1px solid #3f9cae; border-top-width: 4px;">
        <h3 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
            Contatos
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Comercial</label>
                <input type="email" name="email_comercial" value="{{ old('email_comercial', $empresa->email_comercial ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="comercial@empresa.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Administrativo</label>
                <input type="email" name="email_administrativo" value="{{ old('email_administrativo', $empresa->email_administrativo ?? '') }}" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2" placeholder="adm@empresa.com">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg p-4" style="border: 1px solid #3f9cae; border-top-width: 4px;">
        <h3 class="text-base font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
            Status
        </h3>

        <div class="max-w-xs">
            <label class="block text-sm font-medium text-gray-700 mb-1">Situação da Empresa</label>
            <select name="ativo" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-[#3f9cae] focus:ring-[#3f9cae] px-3 py-2">
                <option value="1" @selected(old('ativo', $empresa->ativo ?? true))>Ativa</option>
                <option value="0" @selected(old('ativo', $empresa->ativo ?? true) == false)>Inativa</option>
            </select>
        </div>
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t">
        <x-button 
            variant="danger" 
            size="sm" 
            class="min-w-[130px]"
            x-on:click="$dispatch('close-modal', 'empresa-modal')"
            iconLeft="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' /></svg>"
        >
            Cancelar
        </x-button>
        
        <x-button 
            variant="primary" 
            size="sm" 
            class="min-w-[130px]"
            type="submit"
            iconLeft="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7' /></svg>"
        >
            {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </x-button>
    </div>
</form>

<script>
var isEdit = {{ $isEdit ? 'true' : 'false' }};
document.querySelector('form').addEventListener('submit', function(e) {
    if (isEdit) return;
    
    const form = this;
    let valid = true;
    let firstInvalid = null;
    
    const campos = ['razao_social', 'cnpj'];
    campos.forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input) {
            input.classList.remove('border-red-500');
            if (!input.value || !input.value.trim()) {
                valid = false;
                input.classList.add('border-red-500');
                if (!firstInvalid) firstInvalid = input;
            }
        }
    });
    
    if (!valid) {
        e.preventDefault();
        alert('Preencha os campos obrigatórios.');
        if (firstInvalid) firstInvalid.focus();
    }
});
</script>
