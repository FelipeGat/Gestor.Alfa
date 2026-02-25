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

            // Dicionário de ícones (Heroicons outline)
            const TAB_ICONS = {
                dashboard: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
                'dashboard-tecnico': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'dashboard-comercial': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                financeiro: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                atendimentos: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                orcamentos: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                clientes: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                empresas: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                funcionarios: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                usuarios: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                fornecedores: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>',
                categorias: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>',
                assuntos: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>',
                'contas-receber': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                'contas-pagar': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                movimentacao: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
                cobras: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
                'bancos': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
                relatorios: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'pre-clientes': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>',
                'produtos': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                perfil: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                portal: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'portal-funcionario': '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
            };

            // Ícone padrão quando não encontrado
            const DEFAULT_ICON = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';

            // Ícone X para fechar
            const CLOSE_ICON = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

            function getIcon(iconName, url) {
                // Se tem iconName explícito, usar ele
                if (iconName && TAB_ICONS[iconName]) {
                    return TAB_ICONS[iconName];
                }
                
                // Gerar ícone baseado na URL
                if (url) {
                    const urlLower = url.toLowerCase();
                    
                    // Mapeamento de palavras-chave da URL para ícones
                    const urlMappings = {
                        'dashboard': 'dashboard',
                        'dashboard-tecnico': 'dashboard-tecnico',
                        'dashboard-comercial': 'dashboard-comercial',
                        'financeiro': 'financeiro',
                        'atendimentos': 'atendimentos',
                        'atendimento': 'atendimentos',
                        'orcamentos': 'orcamentos',
                        'orcamento': 'orcamentos',
                        'clientes': 'clientes',
                        'cliente': 'clientes',
                        'empresas': 'empresas',
                        'empresa': 'empresas',
                        'funcionarios': 'funcionarios',
                        'funcionario': 'funcionarios',
                        'usuarios': 'usuarios',
                        'usuario': 'usuarios',
                        'fornecedores': 'fornecedores',
                        'fornecedor': 'fornecedores',
                        'categorias': 'categorias',
                        'categoria': 'categorias',
                        'assuntos': 'assuntos',
                        'assunto': 'assuntos',
                        'contasreceber': 'contas-receber',
                        'contas-receber': 'contas-receber',
                        'contaspagar': 'contas-pagar',
                        'contas-pagar': 'contas-pagar',
                        'movimentacao': 'movimentacao',
                        'cobrar': 'cobras',
                        'contas-financeiras': 'bancos',
                        'bancos': 'bancos',
                        'relatorios': 'relatorios',
                        'relatorio': 'relatorios',
                        'pre-clientes': 'pre-clientes',
                        'preclientes': 'pre-clientes',
                        'itemcomercial': 'produtos',
                        'produtos': 'produtos',
                        'servicos': 'produtos',
                        'portal': 'portal',
                        'portal-funcionario': 'portal-funcionario',
                        'profile': 'perfil',
                        'perfil': 'perfil',
                    };

                    // Procurar correspondência na URL
                    for (const [key, icon] of Object.entries(urlMappings)) {
                        if (urlLower.includes(key)) {
                            return TAB_ICONS[icon];
                        }
                    }
                }
                
                return DEFAULT_ICON;
            }

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
            window.abrirTab = function(url, label, icon = null) {
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
                    tabs.push({ id: tabId, url: url, label: label, icon: icon });
                    
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
                    const iconHtml = getIcon(tab.icon, tab.url);

                    if (isActive) {
                        return `<div class="tab-item group relative flex-shrink-0" data-tab-id="${tab.id}" data-tab-url="${tab.url}">
                            <span class="relative bg-white px-4 pr-8 py-2 text-sm font-semibold text-[#3f9cae] rounded-t-lg border-2 border-[#3f9cae] flex items-center whitespace-nowrap gap-2">
                                ${iconHtml}
                                ${tab.label}
                                <button type="button" onclick="event.stopPropagation(); window.fecharTab('${tab.id}')" class="ml-auto pr-px h-5 flex items-center justify-center rounded-full text-gray-400 hover:text-red-500 hover:bg-red-100 transition-colors">${CLOSE_ICON}</button>
                            </span>
                        </div>`;
                    } else {
                        return `<div class="tab-item group relative flex-shrink-0" data-tab-id="${tab.id}" data-tab-url="${tab.url}">
                            <a href="${tab.url}" onclick="event.preventDefault(); window.ativarTab('${tab.id}')" class="relative bg-gray-200 px-4 pr-8 py-2 text-sm font-semibold text-gray-600 rounded-t-lg border border-gray-300 flex items-center whitespace-nowrap gap-2 hover:bg-gray-300 hover:text-gray-800 transition-all">
                                ${iconHtml}
                                ${tab.label}
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); window.fecharTab('${tab.id}')" class="ml-auto pr-px h-5 flex items-center justify-center rounded-full text-gray-600 hover:text-red-500 hover:bg-red-100 transition-colors">${CLOSE_ICON}</button>
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
                    const currentIcon = document.querySelector('[data-tab-icon]')?.getAttribute('data-tab-icon');
                    const tabId = generateTabId(currentUrl, currentLabel);
                    tabs = [{ id: tabId, url: currentUrl, label: currentLabel, icon: currentIcon }];
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
                    const icon = link.getAttribute('data-tab-icon');
                    if (url && url !== '#') {
                        window.abrirTab(url, label, icon);
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
