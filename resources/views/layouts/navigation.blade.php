<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">

    @php
    $user = auth()->user();

    $isAdmin = $user->isAdminPanel();
    $isComercial = $user->tipo === 'comercial';
    $isCliente = $user->tipo === 'cliente';
    $isFuncionario = $user->tipo === 'funcionario';
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Left side -->
            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @if($isComercial)
                    <a href="{{ route('dashboard.comercial') }}">
                        @elseif($isAdmin)
                        <a href="{{ route('dashboard') }}">
                            @elseif($isCliente)
                            <a href="{{ route('portal.index') }}">
                                @else
                                <a href="{{ route('portal-funcionario.dashboard') }}">
                                    @endif
                                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                                </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:ms-10 space-x-8 py-6">

                    {{-- ================= ADMIN / COMERCIAL ================= --}}
                    @if($isAdmin || $isComercial)

                    <!-- Gestão -->
                    <div x-data="{ openMenu: false }" class="relative">
                        <button @click="openMenu = !openMenu"
                            class="text-gray-600 hover:text-gray-800 font-medium">
                            Gestão
                        </button>

                        <div x-show="openMenu" @click.outside="openMenu = false"
                            class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50">

                            <x-nav-link :href="route('dashboard')" class="block px-4 py-2">
                                Dashboard Administrativo
                            </x-nav-link>

                            <x-nav-link :href="route('dashboard.comercial')" class="block px-4 py-2">
                                Dashboard Comercial
                            </x-nav-link>

                            <x-nav-link :href="route('atendimentos.index')" class="block px-4 py-2">
                                Atendimentos
                            </x-nav-link>

                            <x-nav-link :href="route('cobrancas.index')" class="block px-4 py-2">
                                Cobranças
                            </x-nav-link>
                        </div>
                    </div>

                    <!-- Cadastros -->
                    <div x-data="{ openMenu: false }" class="relative">
                        <button @click="openMenu = !openMenu"
                            class="text-gray-600 hover:text-gray-800 font-medium">
                            Cadastros
                        </button>

                        <div x-show="openMenu" @click.outside="openMenu = false"
                            class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50 flex flex-col">
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
                                Usuários
                            </x-nav-link>
                        </div>
                    </div>

                    <!-- Comercial -->
                    <div x-data="{ openMenu: false }" class="relative">
                        <button @click="openMenu = !openMenu"
                            class="text-gray-600 hover:text-gray-800 font-medium">
                            Comercial
                        </button>

                        <div x-show="openMenu" @click.outside="openMenu = false"
                            class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50 flex flex-col">
                            <x-nav-link :href="route('orcamentos.index')" class="block px-4 py-2">
                                Orçamentos
                            </x-nav-link>

                            <x-nav-link :href="route('itemcomercial.index')" class="block px-4 py-2">
                                Produtos / Serviços
                            </x-nav-link>

                            <x-nav-link :href="route('pre-clientes.index')" class="block px-4 py-2">
                                Pré-Clientes
                            </x-nav-link>
                        </div>
                    </div>

                    <!-- Relatórios -->
                    <span class="text-gray-400 cursor-not-allowed">
                        Relatórios
                    </span>

                    @endif

                    {{-- ================= CLIENTE ================= --}}
                    @if($isCliente)
                    <x-nav-link
                        :href="route('portal.index')"
                        :active="request()->routeIs('portal.*')">
                        Financeiro
                    </x-nav-link>
                    @endif

                    {{-- ================= FUNCIONÁRIO ================= --}}
                    @if($isFuncionario)
                    <x-nav-link
                        :href="route('portal-funcionario.atendimentos.index')"
                        :active="request()->routeIs('portal-funcionario.*')">
                        Meus Atendimentos
                    </x-nav-link>
                    @endif

                </div>
            </div>

            <!-- User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium
                            text-gray-500 bg-white hover:text-gray-700 transition">
                            <div>{{ $user->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586
                                        l3.293-3.293a1 1 0 111.414 1.414
                                        l-4 4a1 1 0 01-1.414 0l-4-4
                                        a1 1 0 010-1.414z" />
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
                <button @click="open = !open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400
                    hover:text-gray-500 hover:bg-gray-100 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ================= MENU MOBILE ================= --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">

        <div class="pt-2 pb-3 space-y-1">

            @if($isAdmin || $isComercial)
            <x-responsive-nav-link :href="route('dashboard.comercial')">
                Dashboard Comercial
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('clientes.index')">
                Clientes
            </x-responsive-nav-link>
            @endif

            @if($isCliente)
            <x-responsive-nav-link :href="route('portal.index')">
                Financeiro
            </x-responsive-nav-link>
            @endif

            @if($isFuncionario)
            <x-responsive-nav-link :href="route('portal-funcionario.atendimentos.index')">
                Meus Atendimentos
            </x-responsive-nav-link>
            @endif

        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ $user->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ $user->email }}</div>
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