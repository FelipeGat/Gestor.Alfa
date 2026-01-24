<x-app-layout>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-green-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Bem-vindo ao Portal do Cliente
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Você possui acesso a múltiplas unidades. <br><strong>Selecione qual deseja acessar para continuar.</strong>
                </p>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                <!-- Selection Card -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Minhas Unidades
                        </h2>

                        <form method="POST" action="{{ route('portal.unidade.definir') }}" class="space-y-6">
                            @csrf

                            <div>
                                <label for="cliente_id" class="block text-sm font-semibold text-gray-700 mb-3">
                                    Selecione a Unidade
                                </label>

                                <div class="relative">
                                    <select name="cliente_id" id="cliente_id" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 appearance-none bg-white text-gray-900 font-medium cursor-pointer hover:border-gray-300"
                                        onchange="updateUnitadeInfo(this)">
                                        <option value="">-- Escolha uma unidade --</option>
                                        @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" data-descricao="{{ $cliente->descricao ?? 'Acesso completo ao portal' }}">
                                            {{ $cliente->nome_exibicao }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <!-- Custom dropdown arrow -->
                                    <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                </div>

                                @error('cliente_id')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414L10 14.586l-6.687-6.687a1 1 0 00-1.414 1.414l8 8a1 1 0 001.414 0l8-8z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Unit Info Display -->
                            <div id="unitInfo" class="hidden p-4 bg-gradient-to-r from-blue-50 to-green-50 rounded-lg border-l-4 border-blue-500">
                                <p class="text-sm text-gray-600">
                                    <span class="font-semibold text-gray-900" id="unitDescricao"></span>
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex gap-3 pt-4">
                                <button type="submit"
                                    class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Acessar Unidade
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="lg:col-span-1">
                    <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-2xl p-6 border border-green-100 h-full">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Informações
                        </h3>
                        <div class="space-y-3 text-sm text-gray-600">
                            <p>✓ Acesso seguro ao seu Portal</p>
                            <p>✓ Gerenciamento de Múltiplas Unidades</p>
                            <p>✓ Ordens de Serviços</p>
                            <p>✓ Financeiro para baixar <strong>NF e Boletos</strong></p>
                            <p><strong>✓ Dados Protegidos e Criptografados<strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grupo Soluções Showcase -->
            <div class="mt-16 pt-12 border-t-2 border-gray-200">
                <div class="text-center mb-10">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                        Conheça o Grupo Soluções
                    </h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">
                        Somos Especializados em diferentes segmentos, oferecendo <br>Soluções Completas para seu negócio.
                    </p>
                </div>

                <!-- Solutions Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Alfa Soluções -->
                    <a href="https://alfa.solucoesgrupo.com/" target="_blank" rel="noopener noreferrer"
                        class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-green-300 transform hover:scale-105">
                        <div class="bg-gradient-to-br from-green-500 to-green-600 h-24 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5.36 4.24l-.707-.707M5.05 6.05l.707.707"></path>
                            </svg>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 mb-2 group-hover:text-green-600 transition-colors">
                                Alfa Tecnologia
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Desenvolvimento Web, Apps e Sistemas de Gestão
                            </p>
                            <span class="inline-flex items-center text-green-600 font-semibold text-sm group-hover:gap-2 transition-all">
                                Saiba mais
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </a>

                    <!-- Invest Digital -->
                    <a href="https://invest.solucoesgrupo.com/" target="_blank" rel="noopener noreferrer"
                        class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-blue-300 transform hover:scale-105">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 h-24 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                Invest Digital
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Segurança Eletrônica e Soluções em TI
                            </p>
                            <span class="inline-flex items-center text-blue-600 font-semibold text-sm group-hover:gap-2 transition-all">
                                Saiba mais
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </a>

                    <!-- GW Soluções -->
                    <a href="https://gw.solucoesgrupo.com/" target="_blank" rel="noopener noreferrer"
                        class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-cyan-300 transform hover:scale-105">
                        <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 h-24 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 mb-2 group-hover:text-cyan-600 transition-colors">
                                GW Soluções
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Serviços Elétricos e Refrigeração
                            </p>
                            <span class="inline-flex items-center text-cyan-600 font-semibold text-sm group-hover:gap-2 transition-all">
                                Saiba mais
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </a>

                    <!-- Delta Soluções -->
                    <a href="https://delta.solucoesgrupo.com/" target="_blank" rel="noopener noreferrer"
                        class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-orange-300 transform hover:scale-105">
                        <div class="bg-gradient-to-br from-orange-500 to-orange-600 h-24 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors">
                                Delta Soluções
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Construção Civil e Serralheria
                            </p>
                            <span class="inline-flex items-center text-orange-600 font-semibold text-sm group-hover:gap-2 transition-all">
                                Saiba mais
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </a>
                </div>

                <!-- CTA Section -->
                <div class="mt-12 bg-gradient-to-r from-blue-600 to-green-600 rounded-2xl p-8 text-center text-white shadow-lg">
                    <h3 class="text-2xl font-bold mb-3">
                        Explore Todas as Nossas Soluções
                    </h3>
                    <p class="mb-6 text-blue-50 max-w-2xl mx-auto">
                        O Grupo Soluções oferece um portfólio completo de serviços para transformar seu negócio. Conheça cada uma de nossas empresas especializadas.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="https://solucoesgrupo.com/" target="_blank" rel="noopener noreferrer"
                            class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors">
                            Visitar Sites
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-12 pt-8 border-t border-gray-200 text-center text-gray-600 text-sm">
                <p class="mb-2">
                    © 2026 Grupo Soluções. Todos os direitos reservados.
                </p>
                <p>
                    Soluções Tecnológicas, Segurança, Elétrica, Refrigeração e Construção Civil
                </p>
            </div>
        </div>
    </div>

    <!-- JavaScript for interactive features -->
    <script>
        function updateUnitadeInfo(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const descricao = selectedOption.getAttribute('data-descricao');
            const unitInfo = document.getElementById('unitInfo');
            const unitDescricao = document.getElementById('unitDescricao');

            if (selectElement.value) {
                unitDescricao.textContent = descricao;
                unitInfo.classList.remove('hidden');
                unitInfo.classList.add('animate-fadeIn');
            } else {
                unitInfo.classList.add('hidden');
            }
        }

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>

    <!-- Tailwind CSS Animation Styles -->
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }

        /* Smooth transitions */
        * {
            @apply transition-all duration-200;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</x-app-layout>