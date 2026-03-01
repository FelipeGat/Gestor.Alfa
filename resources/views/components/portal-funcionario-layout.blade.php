@props(['slot'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#3f9cae">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Portal Funcionário">
    
    <!-- iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Portal Funcionário">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    
    <!-- Android/Chrome -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <title>{{ config('app.name', 'Laravel') }} - Portal do Funcionário</title>

    <!-- Fonts -->
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        /* Scrollbar visível apenas no hover */
        ::-webkit-scrollbar {
            height: 6px;
            width: 6px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        :hover::-webkit-scrollbar {
            opacity: 1;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }

        *:hover {
            scrollbar-color: #cbd5e1 transparent;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Estilos específicos do Portal do Funcionário */
        .portal-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Centralizar conteúdo no desktop */
        .portal-content {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (min-width: 640px) {
            .portal-content {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .portal-content {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }

        /* Safe area para dispositivos iOS */
        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50">
    <!-- Header Mobile-First -->
    <header class="portal-header sticky top-0 z-50">
        <div class="relative px-4 py-3 portal-content">
            <div class="flex items-center justify-between lg:gap-4 flex-wrap lg:flex-nowrap">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0">
                    <x-application-logo class="h-7 w-auto" />
                </div>

                <!-- Navegação Rápida (Desktop) -->
                <nav class="hidden lg:flex gap-2 overflow-visible pb-1 justify-center flex-nowrap" x-data="{ activeTab: window.location.pathname }">
                    <a href="{{ route('portal-funcionario.index') }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full whitespace-nowrap transition-colors {{ request()->routeIs('portal-funcionario.index') ? 'bg-[#3f9cae] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Início
                    </a>
                    <a href="{{ route('portal-funcionario.chamados') }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full whitespace-nowrap transition-colors {{ request()->routeIs('portal-funcionario.chamados') ? 'bg-[#3f9cae] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Chamados
                    </a>
                    <a href="{{ route('portal-funcionario.agenda') }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full whitespace-nowrap transition-colors {{ request()->routeIs('portal-funcionario.agenda') ? 'bg-[#3f9cae] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Agenda
                    </a>
                    <a href="{{ route('portal-funcionario.ponto') }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full whitespace-nowrap transition-colors {{ request()->routeIs('portal-funcionario.ponto') ? 'bg-[#3f9cae] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <svg class="w-3.5 h-3.5 inline-block mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Ponto
                    </a>
                </nav>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" onsubmit="if(window.limparAbasSessao){window.limparAbasSessao();}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="pb-16 lg:pb-0 portal-content">
        <!-- Toast Notifications -->
        <x-toast />

        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>

    @stack('scripts')

    <script>
        // Prevenir restauração do bfcache após logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        // Sistema de abas (herdado do layout principal)
        const STORAGE_KEY = 'gestor_alfa_tabs';
        const ACTIVE_TAB_KEY = 'gestor_alfa_active_tab';

        function getTabs() {
            const data = sessionStorage.getItem(STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        }

        function saveTabs(tabs) {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(tabs));
        }

        function getActiveTabId() {
            return sessionStorage.getItem(ACTIVE_TAB_KEY);
        }

        function setActiveTabId(id) {
            sessionStorage.setItem(ACTIVE_TAB_KEY, id);
        }

        // Limpar abas ao sair
        window.limparAbasSessao = function() {
            sessionStorage.removeItem(STORAGE_KEY);
            sessionStorage.removeItem(ACTIVE_TAB_KEY);
        };
    </script>

    <!-- Barra de Navegação Mobile (Inferior) -->
    <nav class="fixed bottom-0 left-0 right-0 lg:hidden bg-white border-t border-gray-200 z-50 safe-area-bottom">
        <div class="flex justify-around items-center">
            <a href="{{ route('portal-funcionario.index') }}"
               class="flex flex-col items-center justify-center w-full py-2 px-1 {{ request()->routeIs('portal-funcionario.index') ? 'text-[#3f9cae]' : 'text-gray-600' }}">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-[10px] font-medium">Início</span>
            </a>
            <a href="{{ route('portal-funcionario.chamados') }}"
               class="flex flex-col items-center justify-center w-full py-2 px-1 {{ request()->routeIs('portal-funcionario.chamados') ? 'text-[#3f9cae]' : 'text-gray-600' }}">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="text-[10px] font-medium">Chamados</span>
            </a>
            <a href="{{ route('portal-funcionario.agenda') }}"
               class="flex flex-col items-center justify-center w-full py-2 px-1 {{ request()->routeIs('portal-funcionario.agenda') ? 'text-[#3f9cae]' : 'text-gray-600' }}">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-[10px] font-medium">Agenda</span>
            </a>
            <a href="{{ route('portal-funcionario.ponto') }}"
               class="flex flex-col items-center justify-center w-full py-2 px-1 {{ request()->routeIs('portal-funcionario.ponto') ? 'text-[#3f9cae]' : 'text-gray-600' }}">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-[10px] font-medium">Ponto</span>
            </a>
        </div>
    </nav>

    <!-- PWA: Registro do Service Worker -->
    <script>
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('[PWA] Service Worker registrado:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('[PWA] Erro ao registrar Service Worker:', error);
                    });
            });
        }

        // Prevenir zoom duplo tap no iOS
        document.addEventListener('dblclick', function(event) {
            event.preventDefault();
        });

        // Adicionar à tela inicial - prompt customizado
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            console.log('[PWA] Banner de instalação disponível');
        });
    </script>
</body>

</html>
