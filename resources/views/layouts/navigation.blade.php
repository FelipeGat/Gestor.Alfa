<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shadow-sm">

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
    $canPortalFuncionario = $user && !$isCliente && !empty($user->funcionario_id)
        && ($isFuncionario || $isAdmin || $isAdministrativo || $isFinanceiro || $isComercial);

    // Abreviação do nome
    $userName = $user->name ?? 'Visitante';
    $nameParts = explode(' ', trim($userName));
    $displayName = count($nameParts) > 1
        ? $nameParts[0] . ' ' . end($nameParts)
        : $userName;
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

                        <a href="{{ route('portal.index') }}"
                            class="portal-nav-item"
                            data-tab-icon="portal"
                            data-tab-link
                            data-tab-label="Dashboard Atendimentos"
                            @click.stop>
                            Dashboard Atendimentos
                        </a>

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

                {{-- ============ ATIVOS TÉCNICOS ============ --}}
                @if($isCliente)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu"
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Ativos Técnicos
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
                            data-tab-label="Dashboard Ativos Técnicos"
                            @click.stop>
                            Dashboard Ativos Técnicos
                        </a>

                        <a href="{{ route('portal.equipamentos.lista') }}"
                            class="portal-nav-item"
                            data-tab-icon="portal"
                            data-tab-link
                            data-tab-label="Lista de Ativos Técnicos"
                            @click.stop>
                            Lista de Ativos Técnicos
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

                    <div x-show="openMenu"
                        @click.outside="openMenu = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="portal-nav-dropdown p-2 w-64 overflow-y-auto max-h-[85vh] shadow-xl border border-gray-200"
                        x-data="{ activeSection: null }"
                        style="min-width: 16rem; max-width: 20rem; left: 0;">

                        <div class="flex flex-col space-y-1">
                            {{-- SECTION: DASHBOARDS --}}
                            <div>
                                <button @click="activeSection = (activeSection === 'dashboards' ? null : 'dashboards')"
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    <span>Dashboards</span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeSection === 'dashboards' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="activeSection === 'dashboards'" x-collapse class="pl-4 pb-1 space-y-1">
                                    @if($isAdmin)
                                    <a href="{{ route('dashboard') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Dashboard Operacional">Operacional</a>
                                    <a href="{{ route('dashboard.tecnico') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Dashboard Técnico">Técnico</a>
                                    @endif
                                    @if($isAdmin || $isComercial)
                                    <a href="{{ route('dashboard.comercial') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Dashboard Comercial">Comercial</a>
                                    @endif
                                    @if($isAdmin || $isFinanceiro)
                                    <a href="{{ route('financeiro.dashboard') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Dashboard Financeiro">Financeiro</a>
                                    @endif
                                    @if($isAdmin || $isRhAdmin)
                                    <a href="{{ route('rh.dashboard') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Dashboard RH">RH</a>
                                    @endif
                                </div>
                            </div>

                            {{-- SECTION: CADASTROS --}}
                            <div>
                                <button @click="activeSection = (activeSection === 'cadastros' ? null : 'cadastros')"
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    <span>Cadastros</span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeSection === 'cadastros' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="activeSection === 'cadastros'" x-collapse class="pl-4 pb-1 space-y-1">
                                    @if($isAdmin)
                                    <a href="{{ route('empresas.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Empresas">Empresas</a>
                                    <a href="{{ route('usuarios.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Usuários">Usuários</a>
                                    @endif
                                    <a href="{{ route('clientes.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Clientes">Clientes</a>
                                    @if($isAdmin || $isFinanceiro)
                                    <a href="{{ route('fornecedores.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Fornecedores">Fornecedores</a>
                                    @endif
                                    <a href="{{ route('assuntos.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Assuntos">Assuntos</a>
                                    @if($isAdmin)
                                    <a href="{{ route('admin.equipamentos.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Ativos Técnicos">Ativos Técnicos</a>
                                    @endif
                                    @if($isAdmin || $isFinanceiro)
                                    <a href="{{ route('categorias.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Categorias">Categorias</a>
                                    @endif
                                </div>
                            </div>

                            {{-- SECTION: RELATÓRIOS --}}
                            <div>
                                <button @click="activeSection = (activeSection === 'relatorios' ? null : 'relatorios')"
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    <span>Relatórios</span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeSection === 'relatorios' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="activeSection === 'relatorios'" x-collapse class="pl-4 pb-1 space-y-1">
                                    @if($isAdmin)
                                    <a href="{{ route('relatorios.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Todos Relatórios">Todos</a>
                                    <a href="{{ route('relatorios.modulo', ['tipo' => 'financeiro']) }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Relatórios Financeiros">Financeiro</a>
                                    <a href="{{ route('relatorios.modulo', ['tipo' => 'comercial']) }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Relatórios Comerciais">Comercial</a>
                                    <a href="{{ route('relatorios.modulo', ['tipo' => 'tecnico']) }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Relatórios Técnicos">Técnico</a>
                                    <a href="{{ route('relatorios.modulo', ['tipo' => 'rh']) }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Relatórios RH">RH</a>
                                    <a href="{{ route('relatorios.auditoria') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Auditoria do Sistema">Auditoria</a>
                                    @endif
                                </div>
                            </div>

                            {{-- SECTION: RH --}}
                            @if($isRhAdmin)
                            <div>
                                <button @click="activeSection = (activeSection === 'rh' ? null : 'rh')"
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                                    <span>RH</span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="activeSection === 'rh' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="activeSection === 'rh'" x-collapse class="pl-4 pb-1 space-y-1">
                                    <a href="{{ route('rh.funcionarios.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Funcionários">Funcionários</a>
                                    <a href="{{ route('rh.ponto-jornada.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Ponto & Jornada">Ponto & Jornada</a>
                                    <a href="{{ route('rh.jornadas.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Cadastro de Jornada">Jornadas</a>
                                    <a href="{{ route('rh.feriados.index') }}" class="portal-nav-item py-1 text-xs" data-tab-link data-tab-label="Cadastro de Feriados">Feriados</a>
                                </div>
                            </div>
                            @endif
                        </div>
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
                            data-tab-link
                            data-tab-label="Dashboard Comercial"
                            data-tab-icon="dashboard-comercial">
                            Dashboard Comercial
                        </a>

                        <a href="{{ route('orcamentos.index') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Orçamentos"
                            data-tab-icon="orcamentos">
                            Orçamentos
                        </a>

                        <a href="{{ route('itemcomercial.index') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Produtos / Serviços"
                            data-tab-icon="produtos">
                            Produtos / Serviços
                        </a>

                        <a href="{{ route('pre-clientes.index') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Pré-Clientes"
                            data-tab-icon="pre-clientes">
                            Pré-Clientes
                        </a>
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
                            data-tab-link
                            data-tab-label="Dashboard Financeiro"
                            data-tab-icon="financeiro">
                            Dashboard Financeiro
                        </a>

                        <a href="{{ route('financeiro.contas-financeiras.index') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Bancos"
                            data-tab-icon="bancos">
                            Bancos
                        </a>

                        <a href="{{ route('financeiro.cobrar') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Cobrar"
                            data-tab-icon="cobras">
                            Cobrar
                        </a>

                        <a href="{{ route('financeiro.contasareceber') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Receber"
                            data-tab-icon="contas-receber">
                            Receber
                        </a>

                        <a href="{{ route('financeiro.contasapagar') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Pagar"
                            data-tab-icon="contas-pagar">
                            Pagar
                        </a>

                        <a href="{{ route('financeiro.movimentacao') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Extrato"
                            data-tab-icon="movimentacao">
                            Extrato
                        </a>
                    </div>
                </div>
                @endif

                {{-- ============ SUPORTE ============ --}}
                @if($isAdmin)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu"
                        class="portal-nav-btn"
                        :aria-expanded="openMenu">
                        Suporte
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="portal-nav-dropdown flex flex-col">
                        <a href="{{ route('atendimentos.index') }}"
                            class="portal-nav-item"
                            data-tab-link
                            data-tab-label="Atendimentos"
                            data-tab-icon="atendimentos">
                            Atendimentos
                        </a>
                    </div>
                </div>
                @endif


            </div>

            {{-- USER --}}
            <div class="hidden sm:flex sm:items-center sm:ml-6 gap-2">
                <x-theme-toggle />
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-500 dark:text-gray-300">
                            {{ $displayName }}
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" data-tab-icon="perfil" data-tab-link data-tab-label="Editar Usuário">
                            Editar Usuário
                        </x-dropdown-link>

                        @if($canPortalFuncionario)
                        <x-dropdown-link :href="route('portal-funcionario.index')" data-tab-icon="portal-funcionario" data-tab-link data-tab-label="Portal Funcionário">
                            Portal Funcionário
                        </x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" onsubmit="limparAbasSessao()">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left">
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
    <div x-show="open" class="sm:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-4 space-y-2">
        <div class="border-t border-gray-100 pt-4">
            <x-responsive-nav-link :href="route('profile.edit')">
                Editar Usuário
            </x-responsive-nav-link>
            @if($canPortalFuncionario)
            <x-responsive-nav-link :href="route('portal-funcionario.index')" data-tab-icon="portal-funcionario">
                Portal Funcionário
            </x-responsive-nav-link>
            @endif
            <form method="POST" action="{{ route('logout') }}" onsubmit="limparAbasSessao()">
                @csrf
                <button type="submit" class="inline-flex items-center w-full px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-left">
                    Sair
                </button>
            </form>
        </div>
    </div>

    {{-- ================= MOBILE MENU (ANINHADO IGUAL AO DESKTOP) ================= --}}
    <div x-show="open" class="sm:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-4 space-y-2">

        {{-- Atendimentos --}}
        @if($isCliente)
        <details>
            <summary class="font-semibold text-green-700">Atendimentos</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('portal.index')" data-tab-icon="portal">Dashboard Atendimentos</x-responsive-nav-link>
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

        {{-- Ativos Técnicos --}}
        <details>
            <summary class="font-semibold text-purple-700">Ativos Técnicos</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('portal.equipamentos.index')" data-tab-icon="portal">Dashboard Ativos Técnicos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('portal.equipamentos.lista')" data-tab-icon="portal">Lista de Ativos Técnicos</x-responsive-nav-link>
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
                {{-- Dashboards --}}
                <details>
                    <summary class="text-sm font-medium text-gray-600">Dashboards</summary>
                    <div class="pl-4 space-y-1">
                        @if($isAdmin)
                        <x-responsive-nav-link :href="route('dashboard')" data-tab-icon="dashboard">Dashboard Operacional</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('dashboard.tecnico')" data-tab-icon="dashboard-tecnico">Dashboard Técnico</x-responsive-nav-link>
                        @endif
                        @if($isAdmin || $isComercial)
                        <x-responsive-nav-link :href="route('dashboard.comercial')" data-tab-icon="dashboard-comercial">Dashboard Comercial</x-responsive-nav-link>
                        @endif
                        @if($isAdmin || $isFinanceiro)
                        <x-responsive-nav-link :href="route('financeiro.dashboard')" data-tab-icon="financeiro">Dashboard Financeiro</x-responsive-nav-link>
                        @endif
                        @if($isAdmin || $isRhAdmin)
                        <x-responsive-nav-link :href="route('rh.dashboard')" data-tab-icon="rh">Dashboard RH</x-responsive-nav-link>
                        @endif
                    </div>
                </details>

                {{-- Cadastros --}}
                <details>
                    <summary class="text-sm font-medium text-gray-600">Cadastros</summary>
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
                        <x-responsive-nav-link :href="route('admin.equipamentos.index')" data-tab-icon="equipamentos">Ativos Técnicos</x-responsive-nav-link>
                        @endif
                        @if($isAdmin || $isFinanceiro)
                        <x-responsive-nav-link :href="route('categorias.index')" data-tab-icon="categorias">Categorias</x-responsive-nav-link>
                        @endif
                    </div>
                </details>

                {{-- Relatórios --}}
                <details>
                    <summary class="text-sm font-medium text-gray-600">Relatórios</summary>
                    <div class="pl-4 space-y-1">
                        @if($isAdmin)
                        <x-responsive-nav-link :href="route('relatorios.index')" data-tab-icon="relatorios">Todos os Relatórios</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('relatorios.modulo', ['tipo' => 'financeiro'])" data-tab-icon="relatorios">Financeiro</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('relatorios.modulo', ['tipo' => 'comercial'])" data-tab-icon="relatorios">Comercial</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('relatorios.modulo', ['tipo' => 'tecnico'])" data-tab-icon="relatorios">Técnico</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('relatorios.modulo', ['tipo' => 'rh'])" data-tab-icon="relatorios">RH</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('relatorios.auditoria')" data-tab-icon="relatorios">Auditoria</x-responsive-nav-link>
                        @endif
                    </div>
                </details>

                {{-- RH --}}
                @if($isRhAdmin)
                <details>
                    <summary class="text-sm font-medium text-gray-600">Recursos Humanos</summary>
                    <div class="pl-4 space-y-1">
                        <x-responsive-nav-link :href="route('rh.funcionarios.index')" data-tab-icon="funcionarios">Funcionários</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('rh.ponto-jornada.index')" data-tab-icon="rh">Ponto & Jornada</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('rh.jornadas.index')" data-tab-icon="rh">Cadastro de Jornada</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('rh.feriados.index')" data-tab-icon="rh">Cadastro de Feriados</x-responsive-nav-link>
                    </div>
                </details>
                @endif
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

        {{-- Financeiro --}}
        @if($isAdmin || $isFinanceiro)
        <details>
            <summary class="font-semibold text-gray-700">Financeiro</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('financeiro.dashboard')" data-tab-icon="financeiro">Dashboard Financeiro</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('financeiro.contas-financeiras.index')" data-tab-icon="bancos">Bancos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('financeiro.cobrar')" data-tab-icon="cobras">Cobrar</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('financeiro.contasareceber')" data-tab-icon="contas-receber">Receber</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('financeiro.contasapagar')" data-tab-icon="contas-pagar">Pagar</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('financeiro.movimentacao')" data-tab-icon="movimentacao">Extrato</x-responsive-nav-link>
            </div>
        </details>
        @endif

        {{-- Suporte --}}
        @if($isAdmin)
        <details>
            <summary class="font-semibold text-gray-700">Suporte</summary>
            <div class="pl-4 space-y-1">
                <x-responsive-nav-link :href="route('atendimentos.index')" data-tab-icon="atendimentos">Atendimentos</x-responsive-nav-link>
            </div>
        </details>
        @endif

    </div>
</nav>
