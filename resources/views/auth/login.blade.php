<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Grupo Soluções</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class=" min-h-screen flex items-center justify-center px-4 py-12 relative overflow-hidden">

    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-green-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-20 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>

    <!-- Main Container -->
    <div class="w-full max-w-4xl relative z-10">

        <!-- Card Container -->
        <div class="bg-blue-50 bg-opacity-90 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden border border-white border-opacity-20">

            <!-- Grid Layout - Two Columns -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-0 min-h-96">

                <!-- Left Section - Branding (2 columns) -->
                <div class="lg:col-span-2 bg-gradient-to-br from-blue-600 to-green-600 p-8 lg:p-12 text-white flex flex-col justify-between">

                    <!-- Logo & Title -->
                    <div>
                        <h1 class="text-4xl font-bold mb-3">
                            <div class="bg-white bg-opacity-50 rounded-lg p-1 flex items-center justify-center">
                                <img src="https://alfa.solucoesgrupo.com/alfa-logo-2.svg" alt="Alfa Soluções" class="h-14 w-auto">
                            </div>
                            <br>
                            Bem-vindo
                        </h1>
                        <p class="text-blue-100 text-base leading-relaxed">
                            Acesse seu portal e gerencie seus financeiros, atendimentos e documentos com segurança e facilidade.
                        </p>
                    </div>

                    <!-- Features List -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 text-blue-200 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-semibold">Acesso Seguro 24/7</p>
                                <p class="text-sm text-blue-100">Seus dados sempre protegidos</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 text-blue-200 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-semibold">Criptografia Total</p>
                                <p class="text-sm text-blue-100">Proteção de ponta a ponta</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 text-blue-200 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-semibold">Suporte Técnico</p>
                                <p class="text-sm text-blue-100">Equipe pronta para ajudar</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="pt-8 border-t border-blue-400 border-opacity-30">
                        <p class="text-xs text-blue-100">
                            © 2026 Grupo Soluções. Todos os direitos reservados.
                        </p>
                    </div>
                </div>

                <!-- Right Section - Login Form (3 columns) -->
                <div class="lg:col-span-3 p-8 lg:p-12 flex flex-col justify-center">

                    <!-- Mobile Logo -->
                    <div class="lg:hidden mb-8 text-center">
                        <img src="https://alfa.solucoesgrupo.com/alfa-logo-2.svg" alt="Alfa Soluções" class="h-12 w-auto mx-auto mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">Grupo Soluções</h2>
                        <p class="text-sm text-gray-600 mt-1">Portal de Clientes</p>
                    </div>

                    <!-- Form Header -->
                    <div class="mb-8">

                        <h2 class="text-3xl font-bold text-gray-900 mb-2">
                            Faça seu Login
                        </h2>
                        <p class="text-gray-600 text-base">
                            Digite suas credenciais para acessar o portal
                        </p>
                    </div>

                    <!-- Status Message -->
                    @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm font-medium">
                        {{ session('status') }}
                    </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-6" id="loginForm">
                        @csrf

                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">Email</label>

                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>

                                <input
                                    id="email"
                                    class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-gray-900 placeholder-gray-400 transition-all duration-200"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="seu@email.com" />
                            </div>

                            @if ($errors->has('email'))
                            <p class="mt-2 text-red-600 text-sm">{{ $errors->first('email') }}</p>
                            @endif
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">Senha</label>

                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>

                                <input
                                    id="password"
                                    class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-gray-900 placeholder-gray-400 transition-all duration-200"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••" />
                            </div>

                            @if ($errors->has('password'))
                            <p class="mt-2 text-red-600 text-sm">{{ $errors->first('password') }}</p>
                            @endif
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    class="w-5 h-5 rounded border-2 border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-0 cursor-pointer transition-all duration-200"
                                    name="remember">
                                <span class="ms-3 text-sm text-gray-700">
                                    Lembrar-me neste dispositivo
                                </span>
                            </label>

                            @if (Route::has('password.request'))
                            <a class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors duration-200"
                                href="{{ route('password.request') }}">
                                Esqueceu a senha?
                            </a>
                            @endif
                        </div>

                        <!-- Submit Button with Loading Animation -->
                        <button type="submit" id="submitBtn" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center justify-center gap-2 mt-8 relative overflow-hidden" onclick="handleLogin(event)">
                            <svg id="enterIcon" class="w-5 h-5 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <span id="buttonText" class="transition-opacity duration-300">Entrar</span>

                            <!-- Loading Spinner -->
                            <svg id="loadingSpinner" class="w-5 h-5 animate-spin opacity-0 absolute transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-3 bg-white text-gray-600">Primeira vez?</span>
                        </div>
                    </div>

                    <!-- Info Section -->
                    <div class="bg-gradient-to-br from-blue-50 to-green-50 rounded-lg p-4 border border-blue-100 text-center">
                        <p class="text-sm text-gray-700">
                            <span class="font-semibold text-gray-900">Não tem acesso?</span><br>
                            <span class="text-gray-600 text-sm">Entre em contato conosco para solicitar seu acesso ao portal.</span>
                        </p>
                    </div>

                    <!-- Footer Links -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex flex-wrap gap-4 justify-center text-center">
                            <a href="https://alfa.solucoesgrupo.com/" target="_blank" rel="noopener noreferrer" class="text-sm text-gray-600 hover:text-blue-600 transition-colors font-medium">
                                Alfa
                            </a>
                            <span class="text-gray-300 text-sm">•</span>

                            <a href="https://invest.solucoesgrupo.com/" target="_blank" rel="noopener noreferrer" class="text-sm text-gray-600 hover:text-blue-600 transition-colors font-medium">
                                Invest
                            </a>
                            <span class="text-gray-300 text-sm">•</span>

                            <a href="https://gw.solucoesgrupo.com/" target="_blank" rel="noopener noreferrer" class="text-sm text-gray-600 hover:text-blue-600 transition-colors font-medium">
                                GW
                            </a>
                            <span class="text-gray-300 text-sm">•</span>

                            <a href="https://delta.solucoesgrupo.com/" target="_blank" rel="noopener noreferrer" class="text-sm text-gray-600 hover:text-blue-600 transition-colors font-medium">
                                Delta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Section -->
        <div class="mt-8 text-center">
            <p class="text-gray-300 text-sm">
                Precisa de ajuda?
                <a href="mailto:suporte@gruposoluções.com" class="text-blue-400 hover:text-blue-300 font-semibold transition-colors">
                    Contate o suporte
                </a>
            </p>
        </div>
    </div>

    <!-- Tailwind CSS Animations -->
    <style>
        @keyframes blob {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            }

            50% {
                box-shadow: 0 0 30px rgba(59, 130, 246, 0.6);
            }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- JavaScript for Login Animation -->
    <script>
        function handleLogin(event) {
            const submitBtn = document.getElementById('submitBtn');
            const enterIcon = document.getElementById('enterIcon');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const buttonText = document.getElementById('buttonText');

            // Add loading state
            submitBtn.disabled = true;
            submitBtn.classList.add('animate-pulse-glow');

            // Hide icon and text, show spinner
            enterIcon.classList.add('opacity-0');
            buttonText.classList.add('opacity-0');
            loadingSpinner.classList.remove('opacity-0');

            // Submit form after animation starts
            setTimeout(() => {
                document.getElementById('loginForm').submit();
            }, 300);
        }
    </script>
</body>

</html>