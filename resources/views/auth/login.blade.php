<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Login - Grupo Soluções</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Se o CSS não estiver no bundle, descomente a linha abaixo --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/login.css') }}"> --}}
</head>

<body class="min-h-screen flex items-center justify-center px-4 py-6 sm:py-12 relative overflow-x-hidden bg-gray-50">
    <script>
        // Limpar sessionStorage ao carregar página de login
        try {
            sessionStorage.removeItem('gestor_alfa_tabs');
            sessionStorage.removeItem('gestor_alfa_active_tab');
        } catch(e) {}
    </script>

    <!-- Decorative Background Elements -->
    <div class="hidden sm:block absolute top-0 left-0 w-72 h-72 lg:w-96 lg:h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob"></div>
    <div class="hidden sm:block absolute top-0 right-0 w-72 h-72 lg:w-96 lg:h-96 bg-green-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-2000"></div>

    <!-- Main Container -->
    <div class="w-full max-w-4xl relative z-10">

        <!-- Card Container -->
        <div class="bg-white lg:bg-blue-50 lg:bg-opacity-90 backdrop-blur-md rounded-3xl shadow-2xl overflow-hidden border border-white border-opacity-20 login-card-container">

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">

                <!-- Left Section - Branding -->
                <div class="lg:col-span-2 bg-gradient-to-br from-blue-600 to-green-600 p-8 lg:p-12 text-white flex flex-col justify-between branding-section items-center lg:items-start text-center lg:text-left">

                    <!-- Logo & Title -->
                    <div class="w-full flex flex-col items-center lg:items-start">
                        <div class="bg-white bg-opacity-50 rounded-2xl p-4 inline-flex items-center justify-center mb-8 shadow-inner">
                            <img src="https://alfa.solucoesgrupo.com/alfa-logo-2.svg" alt="Alfa Soluções" class="h-12 lg:h-14 w-auto">
                        </div>
                        <h1 class="text-3xl lg:text-4xl font-bold mb-4">Bem-vindo</h1>
                        <p class="text-blue-50 text-sm lg:text-base leading-relaxed opacity-90 max-w-xs lg:max-w-none">
                            Acesse seu portal e gerencie seus financeiros, atendimentos e documentos com segurança e facilidade.
                        </p>
                    </div>

                    <!-- Features List - Hidden on small mobile -->
                    <div class="hidden sm:flex flex-col space-y-5 mt-10 lg:mt-0 w-full">
                        <div class="flex items-center lg:items-start gap-4 justify-center lg:justify-start">
                            <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                                <svg class="w-5 h-5 text-blue-100" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-sm lg:text-base">Acesso Seguro 24/7</p>
                                <p class="text-xs text-blue-100 opacity-80">Dados sempre protegidos</p>
                            </div>
                        </div>
                        <div class="flex items-center lg:items-start gap-4 justify-center lg:justify-start">
                            <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                                <svg class="w-5 h-5 text-blue-100" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-sm lg:text-base">Criptografia Total</p>
                                <p class="text-xs text-blue-100 opacity-80">Proteção de ponta a ponta</p>
                            </div>
                        </div>
                        <div class="flex items-center lg:items-start gap-4 justify-center lg:justify-start">
                            <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                                <svg class="w-5 h-5 text-blue-100" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-sm lg:text-base">Suporte Técnico</p>
                                <p class="text-xs text-blue-100 opacity-80">Equipe pronta para ajudar</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Branding -->
                    <div class="hidden lg:block pt-8 border-t border-blue-400 border-opacity-30 mt-8 w-full">
                        <p class="text-xs text-blue-100 opacity-60">
                            © 2026 Grupo Soluções. Todos os direitos reservados.
                        </p>
                    </div>
                </div>

                <!-- Right Section - Login Form -->
                <div class="lg:col-span-3 p-8 lg:p-12 flex flex-col justify-center bg-white">

                    <!-- Form Header -->
                    <div class="mb-8 text-center lg:text-left">
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Faça seu Login</h2>
                        <p class="text-gray-500 text-sm lg:text-base">
                            Digite suas credenciais para acessar
                        </p>
                    </div>

                    <!-- Status Message -->
                    @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm font-medium text-center">
                        {{ session('status') }}
                    </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5" id="loginForm">
                        @csrf

                        <!-- Mensagem de erro 419 (CSRF expirado) -->
                        @if ($errors->has('email') && str_contains($errors->first('email'), 'CSRF'))
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-sm">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-semibold">Sessão expirada</p>
                                    <p class="text-sm mt-1">Por segurança, seu token de autenticação expirou. Por favor, tente novamente.</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Email</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <input id="email" class="w-full pl-11 pr-4 py-3.5 border-2 border-gray-100 rounded-2xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition-all text-gray-900 placeholder-gray-400" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="seu@email.com" />
                            </div>
                            @error('email')
                            <p class="mt-2 text-red-500 text-xs font-medium ml-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Senha</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input id="password" class="w-full pl-11 pr-4 py-3.5 border-2 border-gray-100 rounded-2xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition-all text-gray-900 placeholder-gray-400" type="password" name="password" required placeholder="••••••••" />
                            </div>
                            @error('password')
                            <p class="mt-2 text-red-500 text-xs font-medium ml-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-2">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer justify-center sm:justify-start">
                                <input id="remember_me" type="checkbox" class="w-5 h-5 rounded-lg border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer transition-all" name="remember">
                                <span class="ms-2 text-sm text-gray-600 font-medium">Lembrar-me</span>
                            </label>
                            @if (Route::has('password.request'))
                            <a class="text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors text-center" href="{{ route('password.request') }}">
                                Esqueceu a senha?
                            </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submitBtn" class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-2xl shadow-lg shadow-blue-100 hover:shadow-xl transition-all transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center justify-center gap-3 mt-4 relative overflow-hidden" onclick="handleLogin(event)">
                            <svg id="enterIcon" class="w-5 h-5 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <span id="buttonText" class="transition-opacity duration-300">Entrar</span>

                            <!-- Loading Spinner -->
                            <svg id="loadingSpinner" class="w-6 h-6 animate-spin opacity-0 absolute transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-10">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-100"></div>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase tracking-widest">
                            <span class="px-4 bg-white text-gray-400 font-bold">Primeira vez?</span>
                        </div>
                    </div>

                    <!-- Info Section -->
                    <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100 text-center">
                        <p class="text-sm text-gray-600 leading-relaxed">
                            <span class="font-bold text-gray-800">Não tem acesso?</span><br>
                            Entre em contato com nossa equipe para solicitar suas credenciais.
                        </p>
                    </div>

                    <!-- Footer Links -->
                    <div class="mt-10 pt-8 border-t border-gray-100">
                        <div class="flex flex-wrap gap-4 justify-center text-center">
                            <a href="https://alfa.solucoesgrupo.com/" target="_blank" class="text-xs font-black text-gray-300 hover:text-blue-600 transition-colors tracking-tighter">ALFA</a>
                            <span class="text-gray-100">•</span>
                            <a href="https://invest.solucoesgrupo.com/" target="_blank" class="text-xs font-black text-gray-300 hover:text-blue-600 transition-colors tracking-tighter">INVEST</a>
                            <span class="text-gray-100">•</span>
                            <a href="https://gw.solucoesgrupo.com/" target="_blank" class="text-xs font-black text-gray-300 hover:text-blue-600 transition-colors tracking-tighter">GW</a>
                            <span class="text-gray-100">•</span>
                            <a href="https://delta.solucoesgrupo.com/" target="_blank" class="text-xs font-black text-gray-300 hover:text-blue-600 transition-colors tracking-tighter">DELTA</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Section -->
        <div class="mt-8 text-center">
            <p class="text-gray-400 text-sm">
                Precisa de ajuda?
                <a href="mailto:suporte@gruposoluções.com" class="text-blue-500 hover:text-blue-400 font-bold transition-colors underline underline-offset-4">
                    Contate o suporte
                </a>
            </p>
        </div>
    </div>

    <!-- JavaScript for Login Animation -->
    <script>
        function handleLogin(event) {
            const submitBtn = document.getElementById('submitBtn');
            const enterIcon = document.getElementById('enterIcon');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const buttonText = document.getElementById('buttonText');
            const form = document.getElementById('loginForm');

            if (form.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.classList.add('animate-pulse-glow', 'opacity-90');
                enterIcon.classList.add('opacity-0');
                buttonText.classList.add('opacity-0');
                loadingSpinner.classList.remove('opacity-0');

                setTimeout(() => { form.submit(); }, 400);

            }
        }

        // Auto-refresh do token CSRF para prevenir erro 419
        // Renova o token a cada 30 minutos (antes da expiração de 120 min)
        let csrfRefreshInterval;

        function startCsrfRefresh() {
            // Primeiro refresh após 30 minutos
            csrfRefreshInterval = setInterval(refreshCsrfToken, 30 * 60 * 1000);
        }

        async function refreshCsrfToken() {
            try {
                const response = await fetch('{{ route("csrf.refresh") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();
                    // Atualiza o token CSRF no formulário
                    const csrfInput = document.querySelector('input[name="_token"]');
                    if (csrfInput) {
                        csrfInput.value = data.token;
                        console.log('Token CSRF renovado com sucesso');
                    }
                } else {
                    console.warn('Falha ao renovar token CSRF:', response.status);
                }
            } catch (error) {
                console.warn('Erro ao renovar token CSRF:', error);
            }
        }

        // Detectar erro 419 e recarregar a página
        function handle419Error() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('expired') === '1') {
                showSessionExpiredMessage();
                // Limpar parâmetro da URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }

        function showSessionExpiredMessage() {
            // Criar notificação de sessão expirada
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 z-50 bg-yellow-50 border border-yellow-200 rounded-xl p-4 shadow-lg max-w-sm';
            notification.innerHTML = `
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800">Sessão expirada</p>
                        <p class="text-sm text-yellow-700 mt-1">Seu token de segurança expirou. A página foi recarregada para sua segurança.</p>
                    </div>
                </div>
            `;
            document.body.appendChild(notification);

            // Remover após 5 segundos
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Iniciar refresh do CSRF ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            startCsrfRefresh();
            handle419Error();

            // Intercepta submit do formulário para detectar erro 419
            const form = document.getElementById('loginForm');
            const originalSubmit = form.submit;

            form.addEventListener('submit', async function(e) {
                // Se já está sendo submetido normalmente, não interfere
                // Apenas monitora se há redirecionamento com erro
            });
        });
    </script>
</body>

</html>