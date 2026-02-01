@php
    $user = auth()->user();
    $tipo = $user ? ucfirst($user->tipo) : 'Desconhecido';
@endphp

<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-4">
        <!-- Container Principal -->
        <div class="w-full max-w-2xl">
            <!-- Card de Erro -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Cabeçalho com gradiente -->
<br>

                <!-- Conteúdo Principal -->
                <div class="px-8 py-12">
                    <!-- Animação do Gestor -->
                    <div class="flex justify-center mb-8">
                        <div class="relative w-40 h-40">
                            <!-- Cabeça do Gestor -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-32 h-32 bg-yellow-100 rounded-full shadow-lg flex items-center justify-center animate-pulse">
                                    <!-- Rosto -->
                                    <div class="text-center">
                                        <!-- Olhos -->
                                        <div class="flex justify-center gap-6 mb-4">
                                            <div class="w-4 h-4 bg-gray-800 rounded-full"></div>
                                            <div class="w-4 h-4 bg-gray-800 rounded-full"></div>
                                        </div>
                                        <!-- Boca (expressão de negação) -->
                                        <div class="text-4xl">😤</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mão negando (animação) -->
                            <div class="absolute -right-8 top-1/2 transform -translate-y-1/2 animate-bounce" style="animation: wave 1s ease-in-out infinite;">
                                <div class="text-6xl">✋</div>
                            </div>
                        </div>
                    </div><br><br>

                    <!-- Mensagem de Erro -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">
                            Oops! Acesso Restrito
                        </h2>
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mb-6">
                            <p class="text-gray-700 mb-3">
                                Olá <span class="font-bold text-blue-600">{{ $user->name ?? 'Usuário' }}</span>,
                            </p>
                            <p class="text-gray-700 mb-2">
                                Seu tipo de acesso é: <span class="inline-block bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold">{{ $tipo }}</span>
                            </p>
                            <p class="text-gray-600 text-sm mt-3">
                                Você não possui permissão para acessar esta página. Se acredita que isso é um erro, entre em contato com o administrador do sistema.
                            </p>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-gray-600 text-sm">Tipo de Usuário</p>
                            <p class="text-lg font-bold text-gray-800">{{ $tipo }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-gray-600 text-sm">Status</p>
                            <p class="text-lg font-bold text-red-600">Bloqueado</p>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button 
                            onclick="window.history.back()" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Voltar
                        </button>
                    </div>
                </div>

                <!-- Rodapé -->
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 text-center">
                    <p class="text-gray-600 text-sm">
                        Precisa de ajuda? 
                        <a href="mailto:suporte@empresa.com" class="text-blue-600 hover:text-blue-700 font-semibold">
                            Entre em contato com o suporte
                        </a>
                    </p>
                </div>
            </div>

            <!-- Mensagem de Suporte -->
            <div class="mt-8 text-center">
                <p class="text-gray-400 text-sm">
                    Código de Erro: <span class="font-mono text-gray-300">403 - FORBIDDEN</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Estilos Personalizados -->
    <style>
        @keyframes wave {
            0%, 100% {
                transform: translateY(0px) translateX(0px) rotate(0deg);
            }
            25% {
                transform: translateY(-10px) translateX(5px) rotate(-10deg);
            }
            50% {
                transform: translateY(-20px) translateX(10px) rotate(-20deg);
            }
            75% {
                transform: translateY(-10px) translateX(5px) rotate(-10deg);
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
