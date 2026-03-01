<x-portal-funcionario-layout>
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('portal-funcionario.index') }}" class="hover:text-[#3f9cae]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </a>
            <span>/</span>
            <span class="font-medium text-gray-900">Documentos</span>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Card Principal -->
        <x-card class="text-center">
            <!-- Ícone -->
            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-teal-50 flex items-center justify-center">
                <svg class="w-12 h-12 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>

            <!-- Título -->
            <h2 class="text-2xl font-bold text-gray-900 mb-2">🚧 Em Desenvolvimento</h2>
            <p class="text-gray-600 mb-6">
                A área de documentos está sendo preparada e estará disponível em breve.
            </p>

            <!-- Botão Voltar -->
            <x-button href="{{ route('portal-funcionario.index') }}" variant="primary" size="md" iconLeft="
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            ">
                Voltar ao Início
            </x-button>
        </x-card>

        <!-- Card de Recursos Futuros -->
        <x-card class="mt-8" title="📋 Recursos Futuros">
            <div class="space-y-3">
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                    <span class="text-2xl">📄</span>
                    <span class="text-sm text-gray-700">Manuais técnicos e procedimentos</span>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                    <span class="text-2xl">📋</span>
                    <span class="text-sm text-gray-700">Formulários e checklists</span>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                    <span class="text-2xl">🔧</span>
                    <span class="text-sm text-gray-700">Guias de instalação</span>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                    <span class="text-2xl">📊</span>
                    <span class="text-sm text-gray-700">Relatórios e documentação técnica</span>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                    <span class="text-2xl">🎓</span>
                    <span class="text-sm text-gray-700">Material de treinamento</span>
                </div>
            </div>
        </x-card>
    </div>
</x-portal-funcionario-layout>
