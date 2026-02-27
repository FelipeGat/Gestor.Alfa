<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    <style>
        .card-relatorio {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            transition: box-shadow 0.2s ease;
        }
        .card-relatorio:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .card-relatorio .icone-bg {
            background-color: rgba(63, 156, 174, 0.1);
        }
        .card-relatorio .icone-cor {
            color: #3f9cae;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Relatórios']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Financeiro</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                    <!-- Relatório: Custos x Orçamentos -->
                    <a href="{{ route('relatorios.custos-orcamentos') }}" class="card-relatorio group block p-6">
                        <div class="flex items-center justify-center mb-4">
                            <div class="p-3 icone-bg rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Custos x Orçamentos</div>
                            <div class="text-xs text-gray-500">Acompanhe custos, receitas e margens por orçamento</div>
                        </div>
                    </a>

                    <!-- Relatório Gerencial de Custos -->
                    <a href="{{ route('relatorios.custos-gerencial') }}" class="card-relatorio group block p-6">
                        <div class="flex items-center justify-center mb-4">
                            <div class="p-3 icone-bg rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Gerencial de Custos</div>
                            <div class="text-xs text-gray-500">Cenário, risco e decisão executiva em tempo real</div>
                        </div>
                    </a>

                    <!-- Relatório: Contas a Receber -->
                    <a href="{{ route('relatorios.contas-receber') }}" class="card-relatorio group block p-6">
                        <div class="flex items-center justify-center mb-4">
                            <div class="p-3 icone-bg rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v2a3 3 0 006 0v-2c0-1.657-1.343-3-3-3zm0 0V6a2 2 0 10-4 0" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Contas a Receber</div>
                            <div class="text-xs text-gray-500">Relatório de contas a receber com filtros por periodo e cliente</div>
                        </div>
                    </a>

                    <!-- Relatório: Contas a Pagar -->
                    <a href="{{ route('relatorios.contas-pagar') }}" class="card-relatorio group block p-6">
                        <div class="flex items-center justify-center mb-4">
                            <div class="p-3 icone-bg rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Contas a Pagar</div>
                            <div class="text-xs text-gray-500">Relatório de contas a pagar com filtros por periodo e fornecedor</div>
                        </div>
                    </a>

                </div>
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4">Comercial</h2>
                <div class="bg-white border border-gray-200 rounded-lg p-6 text-sm text-gray-500">
                    Em breve
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
