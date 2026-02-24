<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Usuários', 'url' => route('usuarios.index')],
            ['label' => 'Novo Usuário']
        ]" />
    </x-slot>

    <x-back-button :route="route('usuarios.index')" />

    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">

            <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6">
                @csrf

                <x-form-section title="Dados do Usuário">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <x-form-input name="name" label="Nome" required placeholder="Digite o nome completo" />
                        <x-form-input name="email" label="E-mail" type="email" required placeholder="Digite o e-mail" />
                        <x-form-input name="password" label="Senha" type="password" required placeholder="Digite a senha" />
                        <x-form-select name="tipo" label="Tipo de Usuário" :options="[
                            'admin' => 'Admin',
                            'administrativo' => 'Administrativo',
                            'financeiro' => 'Financeiro',
                            'comercial' => 'Comercial',
                        ]" />
                    </div>
                </x-form-section>

                <x-form-section title="Perfis">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($perfis as $perfil)
                            <x-form-checkbox name="perfis[]" :value="$perfil->id" :label="$perfil->nome" />
                        @endforeach
                    </div>
                </x-form-section>

                <x-form-section title="Empresas com Acesso">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($empresas as $empresa)
                            <x-form-checkbox name="empresas[]" :value="$empresa->id" :label="$empresa->nome_fantasia ?? $empresa->razao_social" />
                        @endforeach
                    </div>
                </x-form-section>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <x-button href="{{ route('usuarios.index') }}" variant="danger" size="md" class="min-w-[130px]">
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
