<x-app-layout>

    @push('styles')
    <style>
        .card-gestao {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            transition: box-shadow 0.2s ease;
        }
        .card-gestao:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .card-gestao .icone-bg {
            background-color: rgba(63, 156, 174, 0.1);
        }
        .card-gestao .icone-cor {
            color: #3f9cae;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                <!-- Dashboard Operacional -->
                <a href="{{ route('dashboard') }}" class="card-gestao group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Dashboard Operacional</div>
                        <div class="text-xs text-gray-500">Visão geral das operações</div>
                    </div>
                </a>

                <!-- Dashboard Técnico -->
                <a href="{{ route('dashboard.tecnico') }}" class="card-gestao group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Dashboard Técnico</div>
                        <div class="text-xs text-gray-500">Acompanhamento técnico de atendimentos</div>
                    </div>
                </a>

                <!-- Dashboard Comercial -->
                <a href="{{ route('dashboard.comercial') }}" class="card-gestao group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Dashboard Comercial</div>
                        <div class="text-xs text-gray-500">Métricas e orçamentos</div>
                    </div>
                </a>

                <!-- Dashboard Financeiro -->
                <a href="{{ route('financeiro.dashboard') }}" class="card-gestao group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Dashboard Financeiro</div>
                        <div class="text-xs text-gray-500">Visão geral do financeiro</div>
                    </div>
                </a>

                <!-- Atendimentos -->
                <a href="{{ route('atendimentos.index') }}" class="card-gestao group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Atendimentos</div>
                        <div class="text-xs text-gray-500">Gerencie os atendimentos</div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
