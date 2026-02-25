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
    <meta name="cache-control" content="no-cache">
    <meta name="expires" content="0">
    <meta name="pragma" content="no-cache">

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
            <!-- Toast Notifications -->
            <x-toast />

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

            // ============================================
            // Gerenciamento de Abas (Sistema de Abas Internas)
            // ============================================
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

            function generateTabId(url, label) {
                return 'tab_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);
            }

            // Abrir aba interna - cria nova ou focaliza existente
            window.abrirTab = function(url, label) {
                let tabs = getTabs();
                
                // Verificar se já existe aba com mesma URL
                const existingTab = tabs.find(t => t.url === url);
                
                if (existingTab) {
                    // Se já existe, apenas ativar e navegar
                    setActiveTabId(existingTab.id);
                    window.location.href = url;
                } else {
                    // Se não existe, criar nova aba
                    const tabId = generateTabId(url, label);
                    tabs.push({ id: tabId, url: url, label: label });
                    
                    saveTabs(tabs);
                    setActiveTabId(tabId);
                    
                    window.location.href = url;
                }
            };

            // Fechar aba interna
            window.fecharTab = function(tabId) {
                let tabs = getTabs();
                const activeId = getActiveTabId();
                
                const tabIndex = tabs.findIndex(t => t.id === tabId);
                if (tabIndex === -1) return;
                
                tabs.splice(tabIndex, 1);
                saveTabs(tabs);
                
                if (tabId === activeId) {
                    if (tabs.length > 0) {
                        const newActiveIndex = Math.min(tabIndex, tabs.length - 1);
                        const newActive = tabs[newActiveIndex];
                        setActiveTabId(newActive.id);
                        window.location.href = newActive.url;
                    } else {
                        sessionStorage.removeItem(ACTIVE_TAB_KEY);
                        window.location.href = '{{ route("dashboard") }}';
                    }
                } else {
                    window.location.reload();
                }
            };

            // Ativar aba existente
            window.ativarTab = function(tabId) {
                let tabs = getTabs();
                const tab = tabs.find(t => t.id === tabId);
                
                if (tab) {
                    setActiveTabId(tabId);
                    window.location.href = tab.url;
                }
            };

            // Renderizar abas visuais no topo da página
            function renderAbas() {
                let tabsContainer = document.getElementById('tabs-container');
                
                const tabs = getTabs();
                const activeId = getActiveTabId();
                const currentUrl = window.location.href;

                if (tabs.length === 0) {
                    if (tabsContainer) tabsContainer.innerHTML = '';
                    return;
                }

                const tabsHtml = tabs.map(tab => {
                    const isActive = tab.id === activeId;

                    if (isActive) {
                        return `<div class="tab-item group relative flex-shrink-0" data-tab-id="${tab.id}" data-tab-url="${tab.url}">
                            <span class="relative bg-white px-4 pr-8 py-2 text-sm font-semibold text-[#3f9cae] rounded-t-lg border-2 border-[#3f9cae] flex items-center whitespace-nowrap gap-2">
                                ${tab.label}
                                <button type="button" onclick="event.stopPropagation(); window.fecharTab('${tab.id}')" class="ml-auto pr-px h-5 flex items-center justify-center rounded-full text-gray-400 hover:text-red-500 hover:bg-red-100 transition-colors text-sm leading-none">X</button>
                            </span>
                        </div>`;
                    } else {
                        return `<div class="tab-item group relative flex-shrink-0" data-tab-id="${tab.id}" data-tab-url="${tab.url}">
                            <a href="${tab.url}" onclick="event.preventDefault(); window.ativarTab('${tab.id}')" class="relative bg-gray-200 px-4 pr-8 py-2 text-sm font-semibold text-gray-600 rounded-t-lg border border-gray-300 flex items-center whitespace-nowrap gap-2 hover:bg-gray-300 hover:text-gray-800 transition-all">
                                ${tab.label}
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); window.fecharTab('${tab.id}')" class="ml-auto pr-px h-5 flex items-center justify-center rounded-full text-gray-600 hover:text-red-500 hover:bg-red-100 transition-colors text-sm leading-none">X</button>
                            </a>
                        </div>`;
                    }
                }).join('');

                const html = `<nav class="flex items-end gap-1 overflow-x-auto">${tabsHtml}</nav>`;

                // Se o container não existir, criar
                if (!tabsContainer) {
                    const wrapper = document.createElement('div');
                    wrapper.id = 'tabs-container';
                    wrapper.className = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2';
                    wrapper.innerHTML = html;
                    
                    const header = document.getElementById('page-header');
                    const breadcrumbContainer = document.getElementById('breadcrumb-container');
                    
                    if (header && header.parentNode) {
                        header.parentNode.insertBefore(wrapper, header.nextSibling);
                    } else if (breadcrumbContainer && breadcrumbContainer.parentNode) {
                        breadcrumbContainer.parentNode.insertBefore(wrapper, breadcrumbContainer);
                    }
                } else {
                    tabsContainer.innerHTML = html;
                }
            }

            // Inicializar aba atual se não existir
            function inicializarAbas() {
                let tabs = getTabs();
                const activeId = getActiveTabId();

                if (tabs.length === 0) {
                    const currentLabel = document.querySelector('h2')?.textContent?.trim() || 'Página';
                    const currentUrl = window.location.href;
                    const tabId = generateTabId(currentUrl, currentLabel);
                    tabs = [{ id: tabId, url: currentUrl, label: currentLabel }];
                    saveTabs(tabs);
                    setActiveTabId(tabId);
                } else if (!activeId && tabs.length > 0) {
                    setActiveTabId(tabs[0].id);
                }
            }

            // Interceptor de cliques nos links do menu
            document.addEventListener('click', function(e) {
                const link = e.target.closest('[data-tab-link]');
                if (link) {
                    e.preventDefault();
                    const url = link.getAttribute('href');
                    const label = link.getAttribute('data-tab-label') || link.textContent.trim();
                    if (url && url !== '#') {
                        window.abrirTab(url, label);
                        return false;
                    }
                }
            });

            // Função global para limpar abas no logout
            window.limparAbasSessao = function() {
                sessionStorage.removeItem(STORAGE_KEY);
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

            inicializarAbas();
            renderAbas();
            setTimeout(initTabsScroll, 0);
        });
    </script>
</body>

</html>
