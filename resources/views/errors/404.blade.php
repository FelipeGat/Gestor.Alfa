<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-orange-900 via-orange-800 to-orange-900 flex items-center justify-center p-4">
        <!-- Container Principal -->
        <div class="w-full max-w-2xl">
            <!-- Card de Erro -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-8 py-6">
                    <h1 class="text-5xl font-bold text-white">404</h1>
                    <p class="text-orange-100 text-lg mt-2">Página Não Encontrada</p>
                </div>

                <!-- Conteúdo Principal -->
                <div class="px-8 py-12">
                    <!-- Animação de Busca Frustrada -->
                    <div class="flex justify-center mb-8">
                        <div class="relative w-40 h-40">
                            <!-- Personagem procurando -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-32 h-32 bg-yellow-100 rounded-full shadow-lg flex items-center justify-center animate-pulse">
                                    <!-- Rosto -->
                                    <div class="text-center">
                                        <!-- Olhos (procurando) -->
                                        <div class="flex justify-center gap-6 mb-4">
                                            <div class="w-4 h-4 bg-gray-800 rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                                            <div class="w-4 h-4 bg-gray-800 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                                        </div>
                                        <!-- Expressão de confusão -->
                                        <div class="text-4xl">🤔</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Lupa animada (procurando) -->
                            <div class="absolute -right-8 top-1/2 transform -translate-y-1/2" style="animation: search 2s ease-in-out infinite;">
                                <div class="text-6xl">🔍</div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagem de Erro -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">
                            Oops! Página Não Encontrada
                        </h2>
                        <div class="bg-orange-50 border-l-4 border-orange-500 p-6 rounded-lg mb-6">
                            <p class="text-gray-700 mb-2">
                                A página que você tentou acessar não existe ou foi removida do sistema.
                            </p>
                            <p class="text-gray-600 text-sm mt-3">
                                Verifique o endereço digitado ou utilize o menu de navegação para encontrar o que procura.
                            </p>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-gray-600 text-sm">Motivo</p>
                            <p class="text-lg font-bold text-gray-800">Recurso Indisponível</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-gray-600 text-sm">Status</p>
                            <p class="text-lg font-bold text-orange-600">Não Encontrado</p>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button 
                            onclick="window.history.back()" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl hover:from-orange-600 hover:to-orange-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Voltar
                        </button>
                        <a 
                            href="{{ route('dashboard') }}" 
                            class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg shadow-lg hover:shadow-xl hover:bg-gray-300 transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4 4m-4-4v4"></path>
                            </svg>
                            Ir para Dashboard
                        </a>
                    </div>
                </div>

                <!-- Rodapé -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600 text-sm">
                        Precisa de ajuda? 
                        <a href="mailto:suporte@empresa.com" class="text-orange-600 hover:text-orange-700 font-semibold">
                            Entre em contato com o suporte
                        </a>
                    </p>
                </div>
            </div>

            <!-- Mensagem de Suporte -->
            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm">
                    Código de Erro: <span class="font-mono text-gray-300">404 - NOT FOUND</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Estilos Personalizados -->
    <style>
        @keyframes search {
            0%, 100% {
                transform: translateX(0px) translateY(0px) rotate(0deg);
            }
            25% {
                transform: translateX(15px) translateY(-15px) rotate(15deg);
            }
            50% {
                transform: translateX(20px) translateY(-20px) rotate(30deg);
            }
            75% {
                transform: translateX(15px) translateY(-15px) rotate(15deg);
            }
        }

        @keyframes shake-head {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(-5deg);
            }
            75% {
                transform: rotate(5deg);
            }
        }

        .animate-shake-head {
            animation: shake-head 0.5s ease-in-out infinite;
        }

        /* Responsividade */
        @media (max-width: 640px) {
            .text-5xl {
                font-size: 3rem;
            }
            
            .text-3xl {
                font-size: 1.875rem;
            }
        }
    </style>
</x-app-layout>
