<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📈 Dashboard Comercial
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- RESUMO --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Leads</h3>
                    <p class="text-3xl font-bold text-gray-900">0</p>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Atendimentos</h3>
                    <p class="text-3xl font-bold text-gray-900">0</p>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Propostas</h3>
                    <p class="text-3xl font-bold text-gray-900">0</p>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500">Conversão</h3>
                    <p class="text-3xl font-bold text-gray-900">0%</p>
                </div>

            </div>

            {{-- EM BREVE --}}
            <div class="bg-white shadow rounded-lg p-8 text-center">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">
                    🚧 Dashboard Comercial em construção
                </h3>
                <p class="text-sm text-gray-500">
                    Aqui entrarão metas, avanços, funil de vendas e relatórios comerciais.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>