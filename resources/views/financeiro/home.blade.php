<x-app-layout>

    @push('styles')
    <style>
        .card-financeiro {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            transition: box-shadow 0.2s ease;
        }
        .card-financeiro:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .card-financeiro .icone-bg {
            background-color: rgba(63, 156, 174, 0.1);
        }
        .card-financeiro .icone-cor {
            color: #3f9cae;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Financeiro']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                <!-- Dashboard Financeiro -->
                <a href="{{ route('financeiro.dashboard') }}" class="card-financeiro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Dashboard Financeiro</div>
                        <div class="text-xs text-gray-500">Visão geral do financeiro</div>
                    </div>
                </a>

                <!-- Bancos -->
                <a href="{{ route('financeiro.contas-financeiras.index') }}" class="card-financeiro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Bancos</div>
                        <div class="text-xs text-gray-500">Gerencie as contas bancárias</div>
                    </div>
                </a>

                <!-- Cobrar -->
                <a href="{{ route('financeiro.cobrar') }}" class="card-financeiro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Cobrar</div>
                        <div class="text-xs text-gray-500">Painel de cobrança</div>
                    </div>
                </a>

                <!-- Receber -->
                <a href="{{ route('financeiro.contasareceber') }}" class="card-financeiro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Receber</div>
                        <div class="text-xs text-gray-500">Contas a receber</div>
                    </div>
                </a>

                <!-- Pagar -->
                <a href="{{ route('financeiro.contasapagar') }}" class="card-financeiro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Pagar</div>
                        <div class="text-xs text-gray-500">Contas a pagar</div>
                    </div>
                </a>

                <!-- Extrato -->
                <a href="{{ route('financeiro.movimentacao') }}" class="card-financeiro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M9 16h6m-6-4h6" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Extrato</div>
                        <div class="text-xs text-gray-500">Movimentação financeira</div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
