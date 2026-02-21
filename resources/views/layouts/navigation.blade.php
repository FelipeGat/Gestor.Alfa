<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">

    @php
    $user = auth()->user();

    // Papéis principais
    $isAdmin = $user && method_exists($user, 'isAdminPanel') ? $user->isAdminPanel() : false;
    $isFinanceiro = $user && method_exists($user, 'perfis') ? $user->perfis()->where('slug', 'financeiro')->exists() : false;
    $isComercial = $user && method_exists($user, 'perfis') ? ($user->perfis()->where('slug', 'comercial')->exists() || $user->tipo === 'comercial') : false;
    $isCliente = $user && isset($user->tipo) ? $user->tipo === 'cliente' : false;
    $isFuncionario = $user && isset($user->tipo) ? $user->tipo === 'funcionario' : false;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-12">

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
                    <x-application-logo class="h-6 w-auto text-gray-800" />
                </a>
            </div>

            {{-- ================= DESKTOP MENU ================= --}}
            <div class="hidden sm:flex sm:space-x-8 sm:items-center">

                {{-- ============ GESTÃO ============ --}}
                @if($isAdmin || $isComercial || $isFinanceiro)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(63, 156, 174, 0.1); color: #3f9cae;' : 'color: #374151;'">
                        Gestão
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50 flex flex-col p-1">

                        @if($isAdmin)
                        <x-nav-link :href="route('dashboard')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Dashboard Operacional
                        </x-nav-link>
                        @endif

                        @if($isAdmin)
                        <x-nav-link :href="route('dashboard.tecnico')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Dashboard Técnico
                        </x-nav-link>
                        @endif

                        @if($isAdmin || $isComercial)
                        <x-nav-link :href="route('dashboard.comercial')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Dashboard Comercial
                        </x-nav-link>
                        @endif

                        @if($isAdmin || $isFinanceiro)
                        <x-nav-link :href="route('financeiro.dashboard')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Dashboard Financeiro
                        </x-nav-link>
                        @endif

                        @if($isAdmin)
                        <x-nav-link :href="route('atendimentos.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Atendimentos
                        </x-nav-link>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ============ FINANCEIRO ============ --}}
                @if($isAdmin || $isFinanceiro)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(63, 156, 174, 0.1); color: #3f9cae;' : 'color: #374151;'">
                        Financeiro
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50 flex flex-col p-1">

                        <x-nav-link :href="route('financeiro.dashboard')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Dashboard Financeiro
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.contas-financeiras.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Bancos
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.cobrar')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Cobrar
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.contasareceber')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Receber
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.contasapagar')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Pagar
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.movimentacao')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Extrato
                        </x-nav-link>
                    </div>
                </div>
                @endif

                {{-- ============ COMERCIAL ============ --}}
                @if($isAdmin || $isComercial)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(63, 156, 174, 0.1); color: #3f9cae;' : 'color: #374151;'">
                        Comercial
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50 flex flex-col p-1">

                        <x-nav-link :href="route('dashboard.comercial')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Dashboard Comercial
                        </x-nav-link>

                        <x-nav-link :href="route('orcamentos.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Orçamentos
                        </x-nav-link>

                        <x-nav-link :href="route('itemcomercial.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Produtos / Serviços
                        </x-nav-link>

                        <x-nav-link :href="route('pre-clientes.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Pré-Clientes
                        </x-nav-link>
                    </div>
                </div>
                @endif

                {{-- ============ CADASTROS ============ --}}
                @if($isAdmin || $isFinanceiro || $isComercial)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(63, 156, 174, 0.1); color: #3f9cae;' : 'color: #374151;'">
                        Cadastros
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50 flex flex-col p-1">

                        @if($isAdmin)
                        <x-nav-link :href="route('empresas.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Empresas
                        </x-nav-link>

                        <x-nav-link :href="route('funcionarios.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Funcionários
                        </x-nav-link>

                        <x-nav-link :href="route('usuarios.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Usuários
                        </x-nav-link>
                        @endif

                        <x-nav-link :href="route('clientes.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Clientes
                        </x-nav-link>

                        @if($isAdmin || $isFinanceiro)
                        <x-nav-link :href="route('fornecedores.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Fornecedores
                        </x-nav-link>
                        @endif

                        <x-nav-link :href="route('assuntos.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Assuntos
                        </x-nav-link>
                    </div>
                </div>
                @endif

                {{-- ============ RELATÓRIOS ============ --}}
                @if($isAdmin)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(63, 156, 174, 0.1); color: #3f9cae;' : 'color: #374151;'">
                        Relatórios
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50 flex flex-col p-1">

                        <x-nav-link :href="route('relatorios.custos-orcamentos')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Custos x Orçamentos
                        </x-nav-link>

                        <x-nav-link :href="route('relatorios.custos-gerencial')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Gerencial de Custos
                        </x-nav-link>

                        <x-nav-link :href="route('relatorios.contas-receber')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Contas a Receber
                        </x-nav-link>

                        <x-nav-link :href="route('relatorios.contas-pagar')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'">
                            Contas a Pagar
                        </x-nav-link>
                    </div>
                </div>
                @endif


            </div>

            {{-- USER --}}
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-500">
                            {{ $user ? $user->name : 'Visitante' }}
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

    {{-- ================= MOBILE USER MENU ================= --}}
    <div x-show="open" class="sm:hidden border-t border-gray-200 px-4 py-4 space-y-2">
        <div class="border-t border-gray-100 pt-4">
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

    {{-- ================= MOBILE MENU (ANINHADO IGUAL AO DESKTOP) ================= --}}
    <div x-show="open" class="sm:hidden border-t border-gray-200 px-4 py-4 space-y-2">

        {{-- Gestão --}}
        @if($isAdmin || $isComercial || $isFinanceiro)
        <details>
            <summary class="font-semibold text-gray-700">Gestão</summary>
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
            <summary class="font-semibold text-gray-700">Financeiro</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('financeiro.dashboard')">Financeiro</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cobrancas.index')">Cobranças</x-responsive-nav-link>
            </div>
        </details>
        @endif

        {{-- Comercial --}}
        @if($isAdmin || $isComercial)
        <details>
            <summary class="font-semibold text-gray-700">Comercial</summary>
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
            <summary class="font-semibold text-gray-700">Cadastros</summary>
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

        {{-- Relatórios --}}
        @if($isAdmin)
        <details>
            <summary class="font-semibold text-gray-700">Relatórios</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('relatorios.custos-orcamentos')">Custos x Orçamentos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('relatorios.custos-gerencial')">Gerencial de Custos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('relatorios.contas-receber')">Contas a Receber</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('relatorios.contas-pagar')">Contas a Pagar</x-responsive-nav-link>
            </div>
        </details>
        @endif

    </div>
</nav>