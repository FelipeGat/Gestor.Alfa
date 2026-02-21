<x-app-layout>

    @push('styles')
    <style>
        .card-cadastro {
            background: white;
            border: 1px solid #3f9cae;
            border-top-width: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            transition: box-shadow 0.2s ease;
        }
        .card-cadastro:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .card-cadastro .icone-bg {
            background-color: rgba(63, 156, 174, 0.1);
        }
        .card-cadastro .icone-cor {
            color: #3f9cae;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Cadastros']
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                <!-- Clientes -->
                <a href="{{ route('clientes.index') }}" class="card-cadastro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Clientes</div>
                        <div class="text-xs text-gray-500">Gerencie os clientes da empresa</div>
                    </div>
                </a>

                <!-- Empresas -->
                <a href="{{ route('empresas.index') }}" class="card-cadastro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Empresas</div>
                        <div class="text-xs text-gray-500">Gerencie as empresas/unidades</div>
                    </div>
                </a>

                <!-- Funcionários -->
                <a href="{{ route('funcionarios.index') }}" class="card-cadastro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Funcionários</div>
                        <div class="text-xs text-gray-500">Gerencie a equipe de funcionários</div>
                    </div>
                </a>

                <!-- Usuários -->
                <a href="{{ route('usuarios.index') }}" class="card-cadastro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Usuários</div>
                        <div class="text-xs text-gray-500">Gerencie os usuários do sistema</div>
                    </div>
                </a>

                <!-- Fornecedores -->
                <a href="{{ route('fornecedores.index') }}" class="card-cadastro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Fornecedores</div>
                        <div class="text-xs text-gray-500">Cadastre e gerencie fornecedores</div>
                    </div>
                </a>

                <!-- Assuntos -->
                <a href="{{ route('assuntos.index') }}" class="card-cadastro group block p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="p-3 icone-bg rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 icone-cor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="font-bold text-gray-800 text-lg mb-1 group-hover:text-[#3f9cae] transition">Assuntos</div>
                        <div class="text-xs text-gray-500">Gerencie os assuntos de atendimentos</div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
