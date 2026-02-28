<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">

    @php
    $user = auth()->user();

    // Papéis principais
    $isAdmin = $user && method_exists($user, 'isAdminPanel') ? $user->isAdminPanel() : false;
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
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(34, 197, 94, 0.1); color: #22c55e;' : 'color: #374151;'">
                        Atendimentos
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50 flex flex-col p-1">

                        <x-nav-link :href="route('portal.atendimentos')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#22c55e'; this.style.backgroundColor='rgba(34, 197, 94, 0.08)'; this.style.borderBottom='2px solid #22c55e'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Meus Atendimentos
                        </x-nav-link>

                        <x-nav-link :href="route('portal.chamado.novo')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#22c55e'; this.style.backgroundColor='rgba(34, 197, 94, 0.08)'; this.style.borderBottom='2px solid #22c55e'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Abrir Novo Chamado
                        </x-nav-link>
                    </div>
                </div>
                @endif

                {{-- ============ FINANCEIRO ============ --}}
                @if($isCliente)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(59, 130, 246, 0.1); color: #3b82f6;' : 'color: #374151;'">
                        Financeiro
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50 flex flex-col p-1">

                        <x-nav-link :href="route('portal.financeiro')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3b82f6'; this.style.backgroundColor='rgba(59, 130, 246, 0.08)'; this.style.borderBottom='2px solid #3b82f6'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Dashboard Financeiro
                        </x-nav-link>

                        <x-nav-link :href="route('portal.boletos')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3b82f6'; this.style.backgroundColor='rgba(59, 130, 246, 0.08)'; this.style.borderBottom='2px solid #3b82f6'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Boletos
                        </x-nav-link>

                        <x-nav-link :href="route('portal.notas')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3b82f6'; this.style.backgroundColor='rgba(59, 130, 246, 0.08)'; this.style.borderBottom='2px solid #3b82f6'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Notas Fiscais
                        </x-nav-link>
                    </div>
                </div>
                @endif

                {{-- ============ EQUIPAMENTOS ============ --}}
                @if($isCliente)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(147, 51, 234, 0.1); color: #9333ea;' : 'color: #374151;'">
                        Equipamentos
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-56 bg-white border rounded shadow-md z-50 flex flex-col p-1">

                        <x-nav-link :href="route('portal.equipamentos.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#9333ea'; this.style.backgroundColor='rgba(147, 51, 234, 0.08)'; this.style.borderBottom='2px solid #9333ea'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Dashboard Equipamentos
                        </x-nav-link>

                        <x-nav-link :href="route('portal.equipamentos.lista')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#9333ea'; this.style.backgroundColor='rgba(147, 51, 234, 0.08)'; this.style.borderBottom='2px solid #9333ea'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Lista de Equipamentos
                        </x-nav-link>

                        <x-nav-link :href="route('portal.equipamentos.setores')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#9333ea'; this.style.backgroundColor='rgba(147, 51, 234, 0.08)'; this.style.borderBottom='2px solid #9333ea'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Setores
                        </x-nav-link>

                        <x-nav-link :href="route('portal.equipamentos.responsaveis')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#9333ea'; this.style.backgroundColor='rgba(147, 51, 234, 0.08)'; this.style.borderBottom='2px solid #9333ea'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Responsáveis
                        </x-nav-link>

                        <x-nav-link :href="route('portal.equipamentos.pmoc')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#9333ea'; this.style.backgroundColor='rgba(147, 51, 234, 0.08)'; this.style.borderBottom='2px solid #9333ea'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="portal" data-tab-link="">
                            Plano de Manutenção (PMOC)
                        </x-nav-link>
                    </div>
                </div>
                @endif

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
                        <x-nav-link :href="route('dashboard')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="dashboard">
                            Dashboard Operacional
                        </x-nav-link>
                        @endif

                        @if($isAdmin)
                        <x-nav-link :href="route('dashboard.tecnico')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="dashboard-tecnico">
                            Dashboard Técnico
                        </x-nav-link>
                        @endif

                        @if($isAdmin || $isComercial)
                        <x-nav-link :href="route('dashboard.comercial')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="dashboard-comercial">
                            Dashboard Comercial
                        </x-nav-link>
                        @endif

                        @if($isAdmin || $isFinanceiro)
                        <x-nav-link :href="route('financeiro.dashboard')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="financeiro">
                            Dashboard Financeiro
                        </x-nav-link>
                        @endif

                        @if($isAdmin)
                        <x-nav-link :href="route('atendimentos.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="atendimentos">
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

                        <x-nav-link :href="route('financeiro.dashboard')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="financeiro">
                            Dashboard Financeiro
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.contas-financeiras.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="bancos">
                            Bancos
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.cobrar')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="cobras">
                            Cobrar
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.contasareceber')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="contas-receber">
                            Receber
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.contasapagar')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="contas-pagar">
                            Pagar
                        </x-nav-link>

                        <x-nav-link :href="route('financeiro.movimentacao')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="movimentacao">
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

                        <x-nav-link :href="route('dashboard.comercial')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="dashboard-comercial">
                            Dashboard Comercial
                        </x-nav-link>

                        <x-nav-link :href="route('orcamentos.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="orcamentos">
                            Orçamentos
                        </x-nav-link>

                        <x-nav-link :href="route('itemcomercial.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="produtos">
                            Produtos / Serviços
                        </x-nav-link>

                        <x-nav-link :href="route('pre-clientes.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="pre-clientes">
                            Pré-Clientes
                        </x-nav-link>
                    </div>
                </div>
                @endif

                {{-- ============ RH ============ --}}
                @if($isAdmin)
                <div x-data="{ openMenu: false }" class="relative">
                    <button @click="openMenu = !openMenu" class="font-semibold flex items-center gap-1 hover:text-gray-900 transition-colors rounded px-2 py-1" :style="openMenu ? 'background-color: rgba(63, 156, 174, 0.1); color: #3f9cae;' : 'color: #374151;'">
                        RH
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openMenu" @click.outside="openMenu = false" x-transition.opacity.duration.200
                        class="absolute mt-2 w-64 bg-white border rounded shadow-md z-50 flex flex-col p-1">
                        <x-nav-link :href="route('rh.dashboard')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="rh">
                            Dashboard RH
                        </x-nav-link>

                        <x-nav-link :href="route('rh.funcionarios.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="funcionarios">
                            Funcionários
                        </x-nav-link>

                        <x-nav-link :href="route('rh.ponto-jornada.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="rh">
                            Ponto & Jornada
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
                        <x-nav-link :href="route('empresas.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="empresas">
                            Empresas
                        </x-nav-link>

                        <x-nav-link :href="route('usuarios.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="usuarios">
                            Usuários
                        </x-nav-link>
                        @endif

                        <x-nav-link :href="route('clientes.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="clientes">
                            Clientes
                        </x-nav-link>

                        @if($isAdmin || $isFinanceiro)
                        <x-nav-link :href="route('fornecedores.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="fornecedores">
                            Fornecedores
                        </x-nav-link>
                        @endif

                        <x-nav-link :href="route('assuntos.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="assuntos">
                            Assuntos
                        </x-nav-link>

                        @if($isAdmin || $isFinanceiro)
                        <x-nav-link :href="route('categorias.index')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="categorias">
                            Categorias
                        </x-nav-link>
                        @endif
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

                        <div class="px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Financeiro
                        </div>

                        <div class="pl-3">
                            <x-nav-link :href="route('relatorios.custos-orcamentos')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="relatorios">
                                Custos x Orçamentos
                            </x-nav-link>

                            <x-nav-link :href="route('relatorios.custos-gerencial')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="relatorios">
                                Gerencial de Custos
                            </x-nav-link>

                            <x-nav-link :href="route('relatorios.contas-receber')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="relatorios">
                                Contas a Receber
                            </x-nav-link>

                            <x-nav-link :href="route('relatorios.contas-pagar')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="relatorios">
                                Contas a Pagar
                            </x-nav-link>
                        </div>

                        <div class="mt-1 border-t border-gray-100 px-4 pt-2 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Comercial
                        </div>

                        <div class="pl-3">
                            <x-nav-link :href="route('relatorios.comercial')" class="block transition-colors rounded" style="color: #4b5563; padding: 8px 16px;" onmouseover="this.style.color='#3f9cae'; this.style.backgroundColor='rgba(156, 163, 175, 0.08)'; this.style.borderBottom='2px solid #3f9cae'" onmouseout="this.style.color='#4b5563'; this.style.backgroundColor='transparent'; this.style.borderBottom='none'" data-tab-icon="relatorios">
                                Relatório Comercial
                            </x-nav-link>
                        </div>
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
                @if($isAdmin || $isFinanceiro)
                <x-responsive-nav-link :href="route('categorias.index')" data-tab-icon="categorias">Categorias</x-responsive-nav-link>
                @endif
            </div>
        </details>
        @endif

        {{-- RH --}}
        @if($isAdmin)
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
