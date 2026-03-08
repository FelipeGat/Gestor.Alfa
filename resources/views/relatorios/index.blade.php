<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    <style>
        .rel-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #3f9cae;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            text-decoration: none;
            transition: box-shadow .15s, border-color .15s, background .15s;
        }
        .rel-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,.12);
            border-left-color: #2d7a8c;
            background: #f0fbfd;
        }
        .rel-card .rel-icon {
            flex-shrink: 0;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            background: rgba(63,156,174,.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3f9cae;
        }
        .rel-card .rel-body {
            flex: 1;
            min-width: 0;
        }
        .rel-card .rel-title {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rel-card .rel-desc {
            font-size: 0.7rem;
            color: #6b7280;
            line-height: 1.3;
            margin-top: 0.1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rel-card .rel-arrow {
            flex-shrink: 0;
            color: #d1d5db;
            transition: color .15s, transform .15s;
        }
        .rel-card:hover .rel-arrow {
            color: #3f9cae;
            transform: translateX(3px);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        .section-header .badge {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.2rem 0.55rem;
            background: rgba(63,156,174,.15);
            color: #2d7a8c;
            border-radius: 9999px;
        }
        .section-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 1.5rem 0 1.25rem;
        }
    </style>
    @endpush

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Relatórios']
        ]" />
    </x-slot>

    <div class="pb-10 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Cabeçalho de página --}}
            <div class="mb-6">
                <h1 class="text-xl font-bold text-gray-800">Central de Relatórios</h1>
                <p class="text-sm text-gray-500 mt-0.5">Selecione o relatório para visualizar ou exportar os dados</p>
            </div>

            {{-- ===== FINANCEIRO ===== --}}
            <div class="section-header">
                <svg class="w-4 h-4 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v2a3 3 0 006 0v-2c0-1.657-1.343-3-3-3zm0 0V6a2 2 0 10-4 0M3 12h18"/></svg>
                <span class="text-sm font-bold text-gray-700">Financeiro</span>
                <span class="badge">4 relatórios</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5">

                <a href="{{ route('relatorios.contas-receber') }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v2a3 3 0 006 0v-2c0-1.657-1.343-3-3-3zm0 0V6a2 2 0 10-4 0"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Contas a Receber</div>
                        <div class="rel-desc">Cobranças por período, cliente e status</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('relatorios.contas-pagar') }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Contas a Pagar</div>
                        <div class="rel-desc">Despesas por período, fornecedor e categoria</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('relatorios.custos-orcamentos') }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Custos x Orçamentos</div>
                        <div class="rel-desc">Margens, receitas e custos por orçamento</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('relatorios.custos-gerencial') }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Gerencial de Custos</div>
                        <div class="rel-desc">Cenário, risco e decisão executiva</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

            </div>

            <div class="section-divider"></div>

            {{-- ===== COMERCIAL ===== --}}
            <div class="section-header">
                <svg class="w-4 h-4 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7v11a2 2 0 002 2h10a2 2 0 002-2V7m-4 4h.01M12 11h.01M8 11h.01"/></svg>
                <span class="text-sm font-bold text-gray-700">Comercial</span>
                <span class="badge">1 relatório</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5">

                <a href="{{ route('relatorios.comercial') }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7v11a2 2 0 002 2h10a2 2 0 002-2V7"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Relatório Comercial</div>
                        <div class="rel-desc">Pipeline, conversão e lucratividade</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

            </div>

            <div class="section-divider"></div>

            {{-- ===== EXECUTIVO / MÓDULO ===== --}}
            <div class="section-header">
                <svg class="w-4 h-4 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-4m4 6H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z"/></svg>
                <span class="text-sm font-bold text-gray-700">Executivo &amp; Módulos</span>
                <span class="badge">6 relatórios</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5">

                <a href="{{ route('relatorios.modulo') }}" class="rel-card group">
                    <div class="rel-icon" style="background:rgba(99,102,241,.12);color:#4f46e5;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-4"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Módulo Completo</div>
                        <div class="rel-desc">Financeiro, Técnico, Comercial, RH e Executivo</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('relatorios.modulo', ['tipo' => 'financeiro']) }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6M9 12h6M9 16h4"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Relatório Financeiro</div>
                        <div class="rel-desc">Receita, despesa, lucro e margem</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('relatorios.modulo', ['tipo' => 'tecnico']) }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Relatório Técnico</div>
                        <div class="rel-desc">Chamados, SLA e produtividade</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('relatorios.modulo', ['tipo' => 'comercial']) }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Relatório Comercial (Módulo)</div>
                        <div class="rel-desc">Conversão, ticket médio e performance</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('relatorios.modulo', ['tipo' => 'rh']) }}" class="rel-card group">
                    <div class="rel-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Relatório RH</div>
                        <div class="rel-desc">Atrasos, faltas e banco de horas</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('relatorios.modulo', ['tipo' => 'painel-executivo']) }}" class="rel-card group">
                    <div class="rel-icon" style="background:rgba(245,158,11,.12);color:#d97706;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div class="rel-body">
                        <div class="rel-title">Painel Executivo</div>
                        <div class="rel-desc">Consolidado Financeiro, Técnico, Comercial e RH</div>
                    </div>
                    <svg class="rel-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

            </div>

        </div>
    </div>
</x-app-layout>
