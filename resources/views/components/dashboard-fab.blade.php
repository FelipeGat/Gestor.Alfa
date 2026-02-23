@php
    $user = auth()->user();
    $isAdmin = $user && $user->isAdminPanel();
    $isComercial = $user && $user->tipo === 'comercial';
@endphp
<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
    <!-- Botão Flutuante -->
    <button @click="open = !open" class="w-16 h-16 rounded-full bg-indigo-600 shadow-lg flex items-center justify-center text-white text-3xl hover:bg-indigo-700 transition focus:outline-none">
        <!-- Ícone de dashboard -->
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <rect x="3" y="3" width="7" height="7" rx="2" stroke-width="2" stroke="currentColor" fill="none"/>
            <rect x="14" y="3" width="7" height="7" rx="2" stroke-width="2" stroke="currentColor" fill="none"/>
            <rect x="14" y="14" width="7" height="7" rx="2" stroke-width="2" stroke="currentColor" fill="none"/>
            <rect x="3" y="14" width="7" height="7" rx="2" stroke-width="2" stroke="currentColor" fill="none"/>
        </svg>
        <!-- Ícone de fechar -->
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Menu de Dashboards -->
    <div x-show="open" x-transition class="flex flex-col items-end space-y-3 mb-4">
        @if($isAdmin)
            <a href="{{ route('dashboard') }}" class="fab-menu-item" data-tab-link data-tab-label="Dashboard Operacional">
                <span class="fab-icon bg-blue-500"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L15 12.75 9.75 8.5" /></svg></span>
                Operacional
            </a>
            <a href="{{ route('dashboard.tecnico') }}" class="fab-menu-item" data-tab-link data-tab-label="Dashboard Técnico">
                <span class="fab-icon bg-green-500"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" /></svg></span>
                Técnico
            </a>
            <a href="{{ route('dashboard.comercial') }}" class="fab-menu-item" data-tab-link data-tab-label="Dashboard Comercial">
                <span class="fab-icon bg-yellow-500"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18" /></svg></span>
                Comercial
            </a>
            <a href="{{ route('financeiro.dashboard') }}" class="fab-menu-item" data-tab-link data-tab-label="Dashboard Financeiro">
                <span class="fab-icon bg-purple-500"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" /></svg></span>
                Financeiro
            </a>
        @endif
        @if($isComercial)
            <a href="{{ route('dashboard.comercial') }}" class="fab-menu-item" data-tab-link data-tab-label="Dashboard Comercial">
                <span class="fab-icon bg-yellow-500"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18" /></svg></span>
                Comercial
            </a>
        @endif
    </div>

    <style>
        .fab-menu-item {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 9999px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 0.5rem 1.25rem 0.5rem 0.5rem;
            font-weight: 500;
            color: #374151;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
            margin-bottom: 0.25rem;
        }
        .fab-menu-item:hover {
            background: #f3f4f6;
            color: #111827;
        }
        .fab-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            margin-right: 0.75rem;
            color: white;
        }
    </style>
</div>
