<x-app-layout>

    @push('styles')
    <style>
        .card-comercial {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            transition: box-shadow 0.2s ease;
        }
        .card-comercial:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .card-comercial .icone-bg {
            background-color: rgba(63, 156, 174, 0.1);
        }
        .card-comercial .icone-cor {
            color: #3f9cae;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Comercial']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                <!-- Dashboard Comercial -->
                <a href="{{ route('dashboard.comercial') }}" class="card-comercial group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Dashboard Comercial</div>
                        <div class="text-xs text-gray-500">Acompanhe os orçamentos e métricas</div>
                    </div>
                </a>

                <!-- Orçamentos -->
                <a href="{{ route('orcamentos.index') }}" class="card-comercial group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Orçamentos</div>
                        <div class="text-xs text-gray-500">Gerencie os orçamentos da empresa</div>
                    </div>
                </a>

                <!-- Serviços e Produtos -->
                <a href="{{ route('itemcomercial.index') }}" class="card-comercial group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Serviços e Produtos</div>
                        <div class="text-xs text-gray-500">Cadastre itens comerciais</div>
                    </div>
                </a>

                <!-- Pré-Clientes -->
                <a href="{{ route('pre-clientes.index') }}" class="card-comercial group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Pré-Clientes</div>
                        <div class="text-xs text-gray-500">Cadastre potenciais clientes</div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
