<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Left side -->
            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @php
                    $user = auth()->user();
                    @endphp

                    @if($user->tipo === 'comercial')
                    <a href="{{ route('dashboard.comercial') }}">
                        @elseif($user->isAdminPanel())
                        <a href="{{ route('dashboard') }}">
                            @elseif($user->tipo === 'cliente')
                            <a href="{{ route('portal.index') }}">
                                @else
                                <a href="{{ route('portal-funcionario.dashboard') }}">
                                    @endif

                                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                                </a>
                </div>


                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:ms-10 space-x-8 py-6">

                    {{-- MENU ADMIN / COMERCIAL --}}
                    @php
                    $isAdmin = auth()->user()->isAdminPanel() && auth()->user()->tipo !== 'comercial';
                    $isComercial = auth()->user()->tipo === 'comercial';
                    @endphp

                    @if($isAdmin || $isComercial)
                    <div x-data="{ openMenu: false }" class="relative">
                        <button @click="openMenu = !openMenu" class="text-gray-600 hover:text-gray-800 font-medium">
                            Gestão
                        </button>

                        <div x-show="openMenu" @click.outside="openMenu = false"
                            class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50">

                            {{-- Dashboard Administrativo (SÓ ADMIN) --}}
                            @if($isAdmin)
                            <x-nav-link :href="route('dashboard')" class="block px-4 py-2">
                                🧭 Dashboard Administrativo
                            </x-nav-link>
                            @endif

                            {{-- Dashboard Comercial (ADMIN + COMERCIAL) --}}
                            @if($isAdmin || $isComercial)
                            <x-nav-link :href="route('dashboard.comercial')" class="block px-4 py-2">
                                📈 Dashboard Comercial
                            </x-nav-link>
                            @endif

                            {{-- Atendimentos --}}
                            @if(auth()->user()->canPermissao('atendimentos', 'ler'))
                            <x-nav-link :href="route('atendimentos.index')" class="block px-4 py-2">
                                📋 Atendimentos
                            </x-nav-link>
                            @endif

                            {{-- Cobranças --}}
                            @if(auth()->user()->canPermissao('cobrancas', 'ler'))
                            <x-nav-link :href="route('cobrancas.index')" class="block px-4 py-2">
                                💰 Cobranças
                            </x-nav-link>
                            @endif

                        </div>
                    </div>
                    @endif


                    <!-- Cadastros -->
                    <div x-data="{ openMenu: false }" class="relative">
                        <button @click="openMenu = !openMenu" class="text-gray-600 hover:text-gray-800 font-medium">
                            Cadastros
                        </button>

                        <div x-show="openMenu" @click.outside="openMenu = false" class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50
                            flex flex-col">
                            <x-nav-link :href="route('empresas.index')" class="block px-4 py-2">
                                Empresas
                            </x-nav-link>

                            <x-nav-link :href="route('funcionarios.index')" class="block px-4 py-2">
                                Funcionários
                            </x-nav-link>

                            <x-nav-link :href="route('clientes.index')" class="block px-4 py-2">
                                Clientes
                            </x-nav-link>

                            <x-nav-link :href="route('assuntos.index')" class="block px-4 py-2">
                                Assuntos
                            </x-nav-link>

                            <x-nav-link :href="route('usuarios.index')" class="block px-4 py-2">
                                Usuarios
                            </x-nav-link>
                        </div>
                    </div>

                    <!-- Comercial -->
                    <div x-data="{ openMenu: false }" class="relative">
                        <button @click="openMenu = !openMenu" class="text-gray-600 hover:text-gray-800 font-medium">
                            Comercial
                        </button>

                        <div x-show="openMenu" @click.outside="openMenu = false" class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50
                            flex flex-col">
                            <x-nav-link :href="route('orcamentos.index')" class="block px-4 py-2">
                                Orcamentos
                            </x-nav-link>

                            <x-nav-link :href="route('itemcomercial.index')" class="block px-4 py-2">
                                Produtos/Serviços
                            </x-nav-link>

                            <x-nav-link :href="route('pre-clientes.index')" class="block px-4 py-2">
                                Pré Clientes
                            </x-nav-link>

                        </div>
                    </div>


                    <!-- Relatórios (placeholder) -->
                    <span class="text-gray-400 cursor-not-allowed">
                        Relatórios
                    </span>

                    {{-- MENU CLIENTE --}}
                    @if(Auth::user()->tipo === 'cliente')
                    <x-nav-link :href="route('portal.index')" :active="request()->routeIs('portal.*')">
                        Meus Boletos
                    </x-nav-link>
                    @endif

                    {{-- MENU FUNCIONÁRIO --}}
                    @if(Auth::user()->tipo === 'funcionario')
                    <x-nav-link :href="route('portal-funcionario.atendimentos.index')"
                        :active="request()->routeIs('portal-funcionario.atendimentos.*')">
                        Meus Atendimentos
                    </x-nav-link>
                    @endif

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent
                                   text-sm leading-4 font-medium rounded-md
                                   text-gray-500 bg-white hover:text-gray-700
                                   focus:outline-none transition">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586
                                           l3.293-3.293a1 1 0 111.414 1.414
                                           l-4 4a1 1 0 01-1.414 0l-4-4
                                           a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Editar Usuário
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Sair
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md
                           text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">

            @php
            $isAdmin = auth()->user()->isAdminPanel() && auth()->user()->tipo !== 'comercial';
            $isComercial = auth()->user()->tipo === 'comercial';
            @endphp

            @if($isAdmin)
            <x-responsive-nav-link :href="route('dashboard')">
                🧭 Dashboard Administrativo
            </x-responsive-nav-link>
            @endif

            @if($isAdmin || $isComercial)
            <x-responsive-nav-link :href="route('dashboard.comercial')">
                📈 Dashboard Comercial
            </x-responsive-nav-link>
            @endif

            @if(auth()->user()->canPermissao('atendimentos', 'ler'))
            <x-responsive-nav-link :href="route('atendimentos.index')">
                📋 Atendimentos
            </x-responsive-nav-link>
            @endif

            @if(auth()->user()->canPermissao('cobrancas', 'ler'))
            <x-responsive-nav-link :href="route('cobrancas.index')">
                💰 Cobranças
            </x-responsive-nav-link>
            @endif


            @if(Auth::user()->tipo === 'cliente')
            <x-responsive-nav-link :href="route('portal.index')">
                Meus Boletos
            </x-responsive-nav-link>
            @endif

            @if(Auth::user()->tipo === 'funcionario')
            <x-responsive-nav-link :href="route('portal-funcionario.atendimentos.index')">
                Meus Atendimentos
            </x-responsive-nav-link>
            @endif

        </div>

        <!-- Responsive Settings -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Editar Usuário
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Sair
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>