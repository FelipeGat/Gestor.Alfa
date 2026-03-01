<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">

    @php
    $user = auth()->user();

    // Papéis principais
    $isAdmin = $user && method_exists($user, 'isAdminPanel') ? $user->isAdminPanel() : false;
    $isRhAdmin = $user && method_exists($user, 'isAdmin') ? $user->isAdmin() : false;
    $isAdministrativo = $user && method_exists($user, 'isAdministrativo') ? $user->isAdministrativo() : false;
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
                    @if($isAdministrativo) {{ route('dashboard.tecnico') }}
                    @elseif($isAdmin) {{ route('financeiro.dashboard') }}
                    @elseif($isFinanceiro) {{ route('financeiro.dashboard') }}
                    @elseif($isComercial) {{ route('dashboard.comercial') }}
                    @elseif($isCliente) {{ route('portal.index') }}
                    @else {{ route('portal-funcionario.index') }}
                    @endif
                " data-tab-link data-tab-label="Dashboard"
                    @if($isCliente) data-tab-icon="portal"
                    @elseif(!$isAdmin && !$isFinanceiro && !$isComercial) data-tab-icon="portal-funcionario"
                    @else data-tab-icon="dashboard"
                    @endif>
                    <x-application-logo class="h-6 w-auto text-gray-800" />
                </a>
            </div>

            {{-- ================= DESKTOP MENU ================= --}}
            <div class="hidden sm:flex sm:space-x-8 sm:items-center">

                {{-- ============ ATENDIMENTOS ============ --}}
                @if($isCliente)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Atendimentos
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">

                        <a href="{{ route('portal.atendimentos') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Meus Atendimentos" 
                            @click.stop>
                            Meus Atendimentos
                        </a>

                        <a href="{{ route('portal.chamado.novo') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Abrir Novo Chamado" 
                            @click.stop>
                            Abrir Novo Chamado
                        </a>
                    </div>
                </div>
                @endif

                {{-- ============ FINANCEIRO ============ --}}
                @if($isCliente)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Financeiro
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">

                        <a href="{{ route('portal.financeiro') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Dashboard Financeiro" 
                            @click.stop>
                            Dashboard Financeiro
                        </a>

                        <a href="{{ route('portal.boletos') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Boletos" 
                            @click.stop>
                            Boletos
                        </a>

                        <a href="{{ route('portal.notas') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Notas Fiscais" 
                            @click.stop>
                            Notas Fiscais
                        </a>
                    </div>
                </div>
                @endif

                {{-- ============ EQUIPAMENTOS ============ --}}
                @if($isCliente)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Equipamentos
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">

                        <a href="{{ route('portal.equipamentos.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Dashboard Equipamentos" 
                            @click.stop>
                            Dashboard Equipamentos
                        </a>

                        <a href="{{ route('portal.equipamentos.lista') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Lista de Equipamentos" 
                            @click.stop>
                            Lista de Equipamentos
                        </a>

                        <a href="{{ route('portal.equipamentos.setores') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Setores" 
                            @click.stop>
                            Setores
                        </a>

                        <a href="{{ route('portal.equipamentos.responsaveis') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Responsáveis" 
                            @click.stop>
                            Responsáveis
                        </a>

                        <a href="{{ route('portal.equipamentos.pmoc') }}" 
                            class="portal-nav-item"
                            data-tab-icon="portal" 
                            data-tab-link 
                            data-tab-label="Plano de Manutenção (PMOC)" 
                            @click.stop>
                            Plano de Manutenção (PMOC)
                        </a>
                    </div>
                </div>
                @endif

                {{-- ============ GESTÃO ============ --}}
                @if($isAdmin || $isComercial || $isFinanceiro)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Gestão
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">

                        @if($isAdmin)
                        <a href="{{ route('dashboard') }}" 
                            class="portal-nav-item"
                            data-tab-icon="dashboard">
                            Dashboard Operacional
                        </a>
                        @endif

                        @if($isAdmin)
                        <a href="{{ route('dashboard.tecnico') }}" 
                            class="portal-nav-item"
                            data-tab-icon="dashboard-tecnico">
                            Dashboard Técnico
                        </a>
                        @endif

                        @if($isAdmin || $isComercial)
                        <a href="{{ route('dashboard.comercial') }}" 
                            class="portal-nav-item"
                            data-tab-icon="dashboard-comercial">
                            Dashboard Comercial
                        </a>
                        @endif

                        @if($isAdmin || $isFinanceiro)
                        <a href="{{ route('financeiro.dashboard') }}" 
                            class="portal-nav-item"
                            data-tab-icon="financeiro">
                            Dashboard Financeiro
                        </a>
                        @endif

                        @if($isAdmin)
                        <a href="{{ route('rh.dashboard') }}" 
                            class="portal-nav-item"
                            data-tab-icon="rh">
                            Dashboard RH
                        </a>
                        @endif

                        @if($isAdmin)
                        <a href="{{ route('atendimentos.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="atendimentos">
                            Atendimentos
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ============ FINANCEIRO ============ --}}
                @if($isAdmin || $isFinanceiro)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Financeiro
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">

                        <a href="{{ route('financeiro.dashboard') }}" 
                            class="portal-nav-item"
                            data-tab-icon="financeiro">
                            Dashboard Financeiro
                        </a>

                        <a href="{{ route('financeiro.contas-financeiras.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="bancos">
                            Bancos
                        </a>

                        <a href="{{ route('financeiro.cobrar') }}" 
                            class="portal-nav-item"
                            data-tab-icon="cobras">
                            Cobrar
                        </a>

                        <a href="{{ route('financeiro.contasareceber') }}" 
                            class="portal-nav-item"
                            data-tab-icon="contas-receber">
                            Receber
                        </a>

                        <a href="{{ route('financeiro.contasapagar') }}" 
                            class="portal-nav-item"
                            data-tab-icon="contas-pagar">
                            Pagar
                        </a>

                        <a href="{{ route('financeiro.movimentacao') }}" 
                            class="portal-nav-item"
                            data-tab-icon="movimentacao">
                            Extrato
                        </a>
                    </div>
                </div>
                @endif

                {{-- ============ COMERCIAL ============ --}}
                @if($isAdmin || $isComercial)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Comercial
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">

                        <a href="{{ route('dashboard.comercial') }}" 
                            class="portal-nav-item"
                            data-tab-icon="dashboard-comercial">
                            Dashboard Comercial
                        </a>

                        <a href="{{ route('orcamentos.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="orcamentos">
                            Orçamentos
                        </a>

                        <a href="{{ route('itemcomercial.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="produtos">
                            Produtos / Serviços
                        </a>

                        <a href="{{ route('pre-clientes.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="pre-clientes">
                            Pré-Clientes
                        </a>
                    </div>
                </div>
                @endif

                {{-- ============ RH ============ --}}
                @if($isRhAdmin)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        RH
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">
                        <a href="{{ route('rh.dashboard') }}" 
                            class="portal-nav-item"
                            data-tab-icon="rh">
                            Dashboard RH
                        </a>

                        <a href="{{ route('rh.funcionarios.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="funcionarios">
                            Funcionários
                        </a>

                        <a href="{{ route('rh.ponto-jornada.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="rh">
                            Ponto & Jornada
                        </a>

                        <a href="{{ route('rh.jornadas.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="rh">
                            Cadastro de Jornada
                        </a>

                        <a href="{{ route('rh.feriados.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="rh">
                            Cadastro de Feriados
                        </a>
                    </div>
                </div>
                @endif

                {{-- ============ CADASTROS ============ --}}
                @if($isAdmin || $isFinanceiro || $isComercial)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Cadastros
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">

                        @if($isAdmin)
                        <a href="{{ route('empresas.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="empresas">
                            Empresas
                        </a>

                        <a href="{{ route('usuarios.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="usuarios">
                            Usuários
                        </a>
                        @endif

                        <a href="{{ route('clientes.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="clientes">
                            Clientes
                        </a>

                        @if($isAdmin || $isFinanceiro)
                        <a href="{{ route('fornecedores.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="fornecedores">
                            Fornecedores
                        </a>
                        @endif

                        <a href="{{ route('assuntos.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="assuntos">
                            Assuntos
                        </a>

                        @if($isAdmin)
                        <a href="{{ route('admin.equipamentos.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="equipamentos">
                            Equipamentos
                        </a>
                        @endif

                        @if($isAdmin || $isFinanceiro)
                        <a href="{{ route('categorias.index') }}" 
                            class="portal-nav-item"
                            data-tab-icon="categorias">
                            Categorias
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ============ RELATÓRIOS ============ --}}
                @if($isAdmin)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" 
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Relatórios
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">

                        <div class="portal-nav-label">
                            Financeiro
                        </div>

                        <a href="{{ route('relatorios.custos-orcamentos') }}" 
                            class="portal-nav-item"
                            data-tab-icon="relatorios">
                            Custos x Orçamentos
                        </a>

                        <a href="{{ route('relatorios.custos-gerencial') }}" 
                            class="portal-nav-item"
                            data-tab-icon="relatorios">
                            Gerencial de Custos
                        </a>

                        <a href="{{ route('relatorios.contas-receber') }}" 
                            class="portal-nav-item"
                            data-tab-icon="relatorios">
                            Contas a Receber
                        </a>

                        <a href="{{ route('relatorios.contas-pagar') }}" 
                            class="portal-nav-item"
                            data-tab-icon="relatorios">
                            Contas a Pagar
                        </a>

                        <div class="portal-nav-divider"></div>

                        <div class="portal-nav-label">
                            Comercial
                        </div>

                        <a href="{{ route('relatorios.comercial') }}" 
                            class="portal-nav-item"
                            data-tab-icon="relatorios">
                            Relatório Comercial
                        </a>
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
                        <x-dropdown-link :href="route('profile.edit')" data-tab-icon="perfil">
                            Editar Usuário
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}" onsubmit="limparAbasSessao()">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 w-full text-left">
                                Sair
                            </button>
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
            <form method="POST" action="{{ route('logout') }}" onsubmit="limparAbasSessao()">
                @csrf
                <button type="submit" class="inline-flex items-center w-full px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 text-left">
                    Sair
                </button>
            </form>
        </div>
    </div>

    {{-- ================= MOBILE MENU (ANINHADO IGUAL AO DESKTOP) ================= --}}
    <div x-show="open" class="sm:hidden border-t border-gray-200 px-4 py-4 space-y-2">

        {{-- Atendimentos --}}
        @if($isCliente)
        <details>
            <summary class="font-semibold text-green-700">Atendimentos</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('portal.atendimentos')" data-tab-icon="portal">Meus Atendimentos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('portal.chamado.novo')" data-tab-icon="portal">Abrir Novo Chamado</x-responsive-nav-link>
            </div>
        </details>

        {{-- Financeiro --}}
        <details>
            <summary class="font-semibold text-blue-700">Financeiro</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('portal.financeiro')" data-tab-icon="portal">Dashboard Financeiro</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('portal.boletos')" data-tab-icon="portal">Boletos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('portal.notas')" data-tab-icon="portal">Notas Fiscais</x-responsive-nav-link>
            </div>
        </details>

        {{-- Equipamentos --}}
        <details>
            <summary class="font-semibold text-purple-700">Equipamentos</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('portal.equipamentos.index')" data-tab-icon="portal">Dashboard Equipamentos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('portal.equipamentos.lista')" data-tab-icon="portal">Lista de Equipamentos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('portal.equipamentos.setores')" data-tab-icon="portal">Setores</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('portal.equipamentos.responsaveis')" data-tab-icon="portal">Responsáveis</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('portal.equipamentos.pmoc')" data-tab-icon="portal">Plano de Manutenção (PMOC)</x-responsive-nav-link>
            </div>
        </details>
        @endif

        {{-- Gestão --}}
        @if($isAdmin || $isComercial || $isFinanceiro)
        <details>
            <summary class="font-semibold text-gray-700">Gestão</summary>
            <div class="pl-4 space-y-1">
                @if($isAdmin)
                <x-responsive-nav-link :href="route('dashboard')" data-tab-icon="dashboard">Dashboard Operacional</x-responsive-nav-link>
                @endif
                @if($isAdmin || $isComercial)
                <x-responsive-nav-link :href="route('dashboard.comercial')" data-tab-icon="dashboard-comercial">Dashboard Comercial</x-responsive-nav-link>
                @endif
                @if($isFuncionario)
                <x-responsive-nav-link :href="route('portal-funcionario.index')" data-tab-icon="portal-funcionario">Portal do Funcionário</x-responsive-nav-link>
                @endif
                @if($isAdmin || $isFinanceiro)
                <x-responsive-nav-link :href="route('financeiro.dashboard')" data-tab-icon="financeiro">Financeiro</x-responsive-nav-link>
                @endif
                @if($isAdmin)
                <x-responsive-nav-link :href="route('rh.dashboard')" data-tab-icon="rh">Dashboard RH</x-responsive-nav-link>
                @endif
                @if($isAdmin)
                <x-responsive-nav-link :href="route('atendimentos.index')" data-tab-icon="atendimentos">Atendimentos</x-responsive-nav-link>
                @endif
            </div>
        </details>
        @endif

        {{-- Financeiro --}}
        @if($isAdmin || $isFinanceiro)
        <details>
            <summary class="font-semibold text-gray-700">Financeiro</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('financeiro.dashboard')" data-tab-icon="financeiro">Financeiro</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cobrancas.index')" data-tab-icon="cobras">Cobranças</x-responsive-nav-link>
            </div>
        </details>
        @endif

        {{-- Comercial --}}
        @if($isAdmin || $isComercial)
        <details>
            <summary class="font-semibold text-gray-700">Comercial</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('dashboard.comercial')" data-tab-icon="dashboard-comercial">Dashboard Comercial</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orcamentos.index')" data-tab-icon="orcamentos">Orçamentos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('itemcomercial.index')" data-tab-icon="produtos">Produtos / Serviços</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pre-clientes.index')" data-tab-icon="pre-clientes">Pré-Clientes</x-responsive-nav-link>
            </div>
        </details>
        @endif

        {{-- Cadastros --}}
        @if($isAdmin || $isFinanceiro || $isComercial)
        <details>
            <summary class="font-semibold text-gray-700">Cadastros</summary>
            <div class="pl-4 space-y-1">
                @if($isAdmin)
                <x-responsive-nav-link :href="route('empresas.index')" data-tab-icon="empresas">Empresas</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('usuarios.index')" data-tab-icon="usuarios">Usuários</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('clientes.index')" data-tab-icon="clientes">Clientes</x-responsive-nav-link>
                @if($isAdmin || $isFinanceiro)
                <x-responsive-nav-link :href="route('fornecedores.index')" data-tab-icon="fornecedores">Fornecedores</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('assuntos.index')" data-tab-icon="assuntos">Assuntos</x-responsive-nav-link>
                @if($isAdmin)
                <x-responsive-nav-link :href="route('admin.equipamentos.index')" data-tab-icon="equipamentos">Equipamentos</x-responsive-nav-link>
                @endif
                @if($isAdmin || $isFinanceiro)
                <x-responsive-nav-link :href="route('categorias.index')" data-tab-icon="categorias">Categorias</x-responsive-nav-link>
                @endif
            </div>
        </details>
        @endif

        {{-- RH --}}
        @if($isRhAdmin)
        <details>
            <summary class="font-semibold text-gray-700">RH</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('rh.dashboard')" data-tab-icon="rh">Dashboard RH</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('rh.funcionarios.index')" data-tab-icon="funcionarios">Funcionários</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('rh.ponto-jornada.index')" data-tab-icon="rh">Ponto & Jornada</x-responsive-nav-link>
            </div>
        </details>
        @endif

        {{-- Relatórios --}}
        @if($isAdmin)
        <details>
            <summary class="font-semibold text-gray-700">Relatórios</summary>
            <div class="pl-4 space-y-1">
                <details>
                    <summary class="font-semibold text-gray-600">Financeiro</summary>
                    <div class="pl-4 space-y-1">
                        <x-responsive-nav-link :href="route('relatorios.custos-orcamentos')" data-tab-icon="relatorios">Custos x Orçamentos</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('relatorios.custos-gerencial')" data-tab-icon="relatorios">Gerencial de Custos</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('relatorios.contas-receber')" data-tab-icon="relatorios">Contas a Receber</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('relatorios.contas-pagar')" data-tab-icon="relatorios">Contas a Pagar</x-responsive-nav-link>
                    </div>
                </details>

                <details>
                    <summary class="font-semibold text-gray-600">Comercial</summary>
                    <div class="pl-4 space-y-1">
                        <x-responsive-nav-link :href="route('relatorios.comercial')" data-tab-icon="relatorios">Relatório Comercial</x-responsive-nav-link>
                    </div>
                </details>
            </div>
        </details>
        @endif

    </div>
</nav>
