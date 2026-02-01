<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Relatórios
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Relatório: Custos x Orçamentos -->
                <a href="{{ route('relatorios.custos-orcamentos') }}" class="group block bg-white shadow rounded-xl p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 bg-indigo-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-indigo-700 transition">Custos x Orçamentos</div>
                        <div class="text-xs text-gray-500">Acompanhe custos, receitas e margens por orçamento</div>
                    </div>
                </a>
                <!-- Relatório Gerencial de Custos -->
                <a href="{{ route('relatorios.custos-gerencial') }}" class="group block bg-white shadow rounded-xl p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 bg-red-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-red-700 transition">Gerencial de Custos</div>
                        <div class="text-xs text-gray-500">Cenário, risco e decisão executiva em tempo real</div>
                    </div>
                </a>
                <!-- Adicione outros ícones de relatórios aqui -->
            </div>
        </div>
    </div>
</x-app-layout>
