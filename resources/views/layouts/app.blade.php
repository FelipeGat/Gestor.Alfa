<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

        /* Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }

        *:hover {
            scrollbar-color: #cbd5e1 transparent;
        }
    </style>

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Sticky wrapper for header + breadcrumb -->
        <div id="sticky-wrapper" class="relative pt-12">
            <!-- Page Heading -->
            @isset($header)
            <header id="page-header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endisset

            <!-- Breadcrumb -->
            @isset($breadcrumb)
            <div id="breadcrumb-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $breadcrumb }}
            </div>
            @endisset
        </div>

        <!-- Page Content -->
        <main>
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        @include('components.dashboard-fab')
    </div>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('sticky-wrapper');
            const navHeight = 48;
            
            function handleScroll() {
                const scrollTop = window.scrollY;
                
                if (scrollTop > navHeight) {
                    if (wrapper) {
                        wrapper.style.position = 'fixed';
                        wrapper.style.top = '0';
                        wrapper.style.left = '0';
                        wrapper.style.right = '0';
                        wrapper.style.zIndex = '50';
                        wrapper.style.backgroundColor = '#f3f4f6';
                        wrapper.style.paddingTop = '1rem';
                        wrapper.style.paddingBottom = '';
                    }
                } else {
                    if (wrapper) {
                        wrapper.style.position = '';
                        wrapper.style.top = '';
                        wrapper.style.left = '';
                        wrapper.style.right = '';
                        wrapper.style.zIndex = '';
                        wrapper.style.backgroundColor = '';
                        wrapper.style.boxShadow = '';
                        wrapper.style.paddingTop = '1rem';
                        wrapper.style.paddingBottom = '0.20rem';
                    }
                }
            }

            window.addEventListener('scroll', handleScroll);
            handleScroll();

            // Limpar sessionStorage ao carregar página (evita restore de sessão anterior)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    sessionStorage.removeItem('gestor_alfa_active_tab');
                }
            });

            // ============================================
            // Gerenciamento de Abas (Browser Tabs)
            // ============================================
            const ACTIVE_TAB_KEY = 'gestor_alfa_active_tab';

            function getActiveTabId() {
                return sessionStorage.getItem(ACTIVE_TAB_KEY);
            }

            function setActiveTabId(id) {
                sessionStorage.setItem(ACTIVE_TAB_KEY, id);
            }

            window.abrirTab = function(url, label) {
                // Navegar para a URL (nova página vai gerenciar suas próprias abas via Blade)
                window.location.href = url;
            };

            window.fecharTab = function(tabId) {
                // Fechar aba = voltar para a página anterior ou ir para dashboard
                const tabsNav = document.getElementById('tabs-nav');
                if (!tabsNav) return;

                const tabItems = tabsNav.querySelectorAll('.tab-item');
                if (tabItems.length <= 1) {
                    // Se é a única aba, vai para dashboard
                    window.location.href = '{{ route("dashboard") }}';
                    return;
                }

                // Encontrar aba anterior e navegar para ela
                const currentIndex = Array.from(tabItems).findIndex(item => item.dataset.tabId === tabId);
                if (currentIndex > 0) {
                    const previousTab = tabItems[currentIndex - 1];
                    const previousUrl = previousTab.dataset.tabUrl;
                    window.location.href = previousUrl;
                } else {
                    window.location.href = '{{ route("dashboard") }}';
                }
            };

            window.ativarTab = function(tabId) {
                setActiveTabId(tabId);
                const tabsNav = document.getElementById('tabs-nav');
                if (!tabsNav) return;

                const tabItem = tabsNav.querySelector(`[data-tab-id="${tabId}"]`);
                if (tabItem) {
                    const tabUrl = tabItem.dataset.tabUrl;
                    window.location.href = tabUrl;
                }
            };

            // Função global para limpar abas no logout
            window.limparAbasSessao = function() {
                sessionStorage.removeItem(ACTIVE_TAB_KEY);
            };

            // Scroll horizontal das abas com mouse
            function initTabsScroll() {
                const tabsNav = document.getElementById('tabs-nav');
                if (tabsNav) {
                    tabsNav.addEventListener('wheel', function(e) {
                        if (Math.abs(e.deltaY) > 0) {
                            e.preventDefault();
                            tabsNav.scrollLeft += e.deltaY;
                        }
                    }, { passive: false });
                }
            }

            setTimeout(initTabsScroll, 0);
        });
    </script>
</body>

</html>
