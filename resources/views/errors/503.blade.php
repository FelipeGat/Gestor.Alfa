@php
    $user = auth()->user();
    $isAdmin = $user && method_exists($user, 'isAdminPanel') ? $user->isAdminPanel() : false;
@endphp

<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-orange-900 via-red-900 to-orange-900 flex items-center justify-center p-4">
        <!-- Container Principal -->
        <div class="w-full max-w-2xl">
            <!-- Card de Manutenção -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Conteúdo Principal -->
                <div class="px-8 py-12">
                    <!-- Animação de Manutenção -->
                    <div class="flex justify-center mb-8">
                        <div class="relative w-40 h-40">
                            <!-- Ícone de manutenção -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-32 h-32 bg-orange-100 rounded-full shadow-lg flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-6xl mb-2">🔧</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Animação de engrenagem -->
                            <div class="absolute -right-4 top-0 animate-spin" style="animation-duration: 3s;">
                                <div class="text-4xl">⚙️</div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagem de Manutenção -->
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">
                            Sistema em Manutenção
                        </h2>
                        <div class="bg-orange-50 border-l-4 border-orange-500 p-6 rounded-lg mb-6">
                            <p class="text-gray-700 mb-3">
                                Olá <span class="font-bold text-orange-600">{{ $user->name ?? 'Visitante' }}</span>,
                            </p>
                            <p class="text-gray-700 mb-2">
                                O sistema está passando por uma atualização programada.
                            </p>
                            <p class="text-gray-600 text-sm mt-3">
                                Em breve, todos os serviços estarão disponíveis novamente. Agradecemos sua compreensão.
                            </p>
                        </div>
                    </div>

                    <!-- Informações de Status -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-gray-600 text-sm">Status</p>
                            <p class="text-lg font-bold text-orange-600">Manutenção</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-gray-600 text-sm">Previsão</p>
                            <p class="text-lg font-bold text-gray-800">Em breve</p>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @if($isAdmin)
                            <a href="{{ route('dashboard') }}"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl hover:from-orange-600 hover:to-orange-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Voltar ao Dashboard
                            </a>
                        @else
                            <div class="text-center text-gray-600 text-sm">
                                <p>Aguarde a conclusão da manutenção ou entre em contato com o administrador.</p>
                            </div>
                        @endif
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
                <p class="text-orange-200 text-sm">
                    Código: <span class="font-mono">503 - SERVICE UNAVAILABLE</span>
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
