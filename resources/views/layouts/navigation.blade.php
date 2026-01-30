<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">

    @php
    $user = auth()->user();

    $isAdmin = $user->isAdminPanel();
    $isComercial = $user->tipo === 'comercial';
    $isCliente = $user->tipo === 'cliente';
    $isFuncionario = $user->tipo === 'funcionario';
    $isFinanceiro = $user->perfis()->where('slug', 'financeiro')->exists();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- LOGO --}}
            <div class="flex items-center">
                <a href="
                    @if($isAdmin) {{ route('dashboard') }}
                    @elseif($isFinanceiro) {{ route('financeiro.dashboard') }}
                    @elseif($isComercial) {{ route('dashboard.comercial') }}
                    @elseif($isCliente) {{ route('portal.index') }}
                    @else {{ route('portal-funcionario.index') }}
                    @endif
                ">
                    <x-application-logo class="h-9 w-auto text-gray-800" />
                </a>
            </div>

            {{-- ================= DESKTOP MENU ================= --}}
            <div class="hidden sm:flex sm:space-x-8 sm:items-center">

                {{-- ============ GESTÃO ============ --}}
                @if($isAdmin || $isComercial || $isFinanceiro)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-medium text-gray-700">
                        Gestão
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false"
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50">

                        @if($isAdmin)
                        <x-nav-link :href="route('dashboard')" class="block px-4 py-2">
                            Dashboard Operacional
                        </x-nav-link>
                        @endif

                        @if($isAdmin)
                        <x-nav-link :href="route('dashboard.tecnico')" class="block px-4 py-2">
                            Dashboard Técnico
                        </x-nav-link>
                        @endif

                        @if($isAdmin || $isComercial)
                        <x-nav-link :href="route('dashboard.comercial')" class="block px-4 py-2">
                            Dashboard Comercial
                        </x-nav-link>
                        @endif

                        @if($isAdmin || $isFinanceiro)
                        <x-nav-link :href="route('financeiro.dashboard')" class="block px-4 py-2">
                            Financeiro
                        </x-nav-link><br>
                        @endif

                        @if($isAdmin)
                        <x-nav-link :href="route('atendimentos.index')" class="block px-4 py-2">
                            Atendimentos
                        </x-nav-link>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ============ FINANCEIRO ============ --}}
                @if($isAdmin || $isFinanceiro)
                <div x-data="{ openMenu: false }" class="relative">
                    <a href="{{ route('financeiro.dashboard') }}" class="font-medium text-gray-700 hover:text-gray-900">
                        Financeiro
                    </a>
                </div>
                @endif

                {{-- ============ COMERCIAL ============ --}}
                @if($isAdmin || $isComercial)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-medium text-gray-700">
                        Comercial
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false"
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50">

                        <x-nav-link :href="route('dashboard.comercial')" class="block px-4 py-2">
                            Dashboard Comercial
                        </x-nav-link>

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
                @endif

                {{-- ============ CADASTROS ============ --}}
                @if($isAdmin || $isFinanceiro || $isComercial)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-medium text-gray-700">
                        Cadastros
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false"
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50">

                        @if($isAdmin)
                        <x-nav-link :href="route('empresas.index')" class="block px-4 py-2">
                            Empresas
                        </x-nav-link><br>

                        <x-nav-link :href="route('funcionarios.index')" class="block px-4 py-2">
                            Funcionários
                        </x-nav-link><br>

                        <x-nav-link :href="route('usuarios.index')" class="block px-4 py-2">
                            Usuários
                        </x-nav-link><br>
                        @endif

                        <x-nav-link :href="route('clientes.index')" class="block px-4 py-2">
                            Clientes
                        </x-nav-link><br>

                        @if($isAdmin || $isFinanceiro)
                        <x-nav-link :href="route('fornecedores.index')" class="block px-4 py-2">
                            Fornecedores
                        </x-nav-link><br>
                        @endif

                        <x-nav-link :href="route('assuntos.index')" class="block px-4 py-2">
                            Assuntos
                        </x-nav-link>
                    </div>
                </div>
                @endif

                {{-- ============ RELATÓRIOS ============ --}}
                @if($isAdmin)
                <span class="text-gray-400 cursor-not-allowed">
                    Relatórios
                </span>
                @endif


            </div>

            {{-- USER --}}
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500">
                            {{ $user->name }}
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

            {{-- HAMBURGER --}}
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="p-2 text-gray-500">
                    ☰
                </button>
            </div>
        </div>
    </div>

    {{-- ================= MOBILE MENU (ANINHADO IGUAL AO DESKTOP) ================= --}}
    <div x-show="open" class="sm:hidden border-t border-gray-200 px-4 py-4 space-y-2">

        {{-- Gestão --}}
        @if($isAdmin || $isComercial || $isFinanceiro)
        <details>
            <summary class="font-medium text-gray-700">Gestão</summary>
            <div class="pl-4 space-y-1">
                @if($isAdmin)
                <x-responsive-nav-link :href="route('dashboard')">Dashboard Operacional</x-responsive-nav-link>
                @endif
                @if($isAdmin || $isComercial)
                <x-responsive-nav-link :href="route('dashboard.comercial')">Dashboard Comercial</x-responsive-nav-link>
                @endif
                @if($isFuncionario)
                <x-responsive-nav-link :href="route('portal-funcionario.index')">Portal do Funcionário</x-responsive-nav-link>
                @endif
                @if($isAdmin || $isFinanceiro)
                <x-responsive-nav-link :href="route('financeiro.dashboard')">Financeiro</x-responsive-nav-link>
                @endif
                @if($isAdmin)
                <x-responsive-nav-link :href="route('atendimentos.index')">Atendimentos</x-responsive-nav-link>
                @endif
            </div>
        </details>
        @endif

        {{-- Financeiro --}}
        @if($isAdmin || $isFinanceiro)
        <details>
            <summary class="font-medium text-gray-700">Financeiro</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('financeiro.dashboard')">Financeiro</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cobrancas.index')">Cobranças</x-responsive-nav-link>
            </div>
        </details>
        @endif

        {{-- Comercial --}}
        @if($isAdmin || $isComercial)
        <details>
            <summary class="font-medium text-gray-700">Comercial</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('dashboard.comercial')">Dashboard Comercial</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orcamentos.index')">Orçamentos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('itemcomercial.index')">Produtos / Serviços</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pre-clientes.index')">Pré-Clientes</x-responsive-nav-link>
            </div>
        </details>
        @endif

        {{-- Cadastros --}}
        @if($isAdmin || $isFinanceiro || $isComercial)
        <details>
            <summary class="font-medium text-gray-700">Cadastros</summary>
            <div class="pl-4 space-y-1">
                @if($isAdmin)
                <x-responsive-nav-link :href="route('empresas.index')">Empresas</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('funcionarios.index')">Funcionários</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('usuarios.index')">Usuários</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('clientes.index')">Clientes</x-responsive-nav-link>
                @if($isAdmin || $isFinanceiro)
                <x-responsive-nav-link :href="route('fornecedores.index')">Fornecedores</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('assuntos.index')">Assuntos</x-responsive-nav-link>
            </div>
        </details>
        @endif

    </div>
</nav>