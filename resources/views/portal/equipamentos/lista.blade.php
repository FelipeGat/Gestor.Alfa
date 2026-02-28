<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Lista de Equipamentos
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $cliente->nome_exibicao }}
                </p>
            </div>
            <a href="{{ route('portal.equipamentos.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline">Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper">

        @if($equipamentos->count() > 0)
        <!-- Grid de Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($equipamentos as $equipamento)
            <a href="{{ route('portal.equipamentos.show', $equipamento->id) }}"
                class="group bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-md transition-all cursor-pointer">

                <!-- Header do Card -->
                <div class="bg-gradient-to-r from-[#3f9cae] to-[#2d7a8a] p-4">
                    <h3 class="text-base font-bold text-white truncate">
                        {{ $equipamento->nome }}
                    </h3>
                    <p class="text-white/80 text-sm">
                        {{ $equipamento->modelo ?? 'Sem modelo' }}
                    </p>
                </div>

                <!-- Corpo do Card -->
                <div class="p-4 space-y-3">
                    <!-- Setor e Responsável -->
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2 text-gray-600">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate">{{ $equipamento->setor->nome ?? 'Sem setor' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="truncate">{{ $equipamento->responsavel->nome ?? 'Sem responsável' }}</span>
                    </div>

                    <!-- Status Manutenção -->
                    <div class="pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Manutenção</span>
                            <span class="text-xs text-gray-400">
                                {{ $equipamento->periodicidade_manutencao_meses }} meses
                            </span>
                        </div>
                        <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold {{ $equipamento->status_manutencao['classe'] }}">
                            {{ $equipamento->status_manutencao['mensagem'] }}
                        </span>
                    </div>

                    <!-- Status Limpeza -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Limpeza</span>
                            <span class="text-xs text-gray-400">
                                {{ $equipamento->periodicidade_limpeza_meses }} {{ $equipamento->periodicidade_limpeza_meses == 1 ? 'mês' : 'meses' }}
                            </span>
                        </div>
                        <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold {{ $equipamento->status_limpeza['classe'] }}">
                            {{ $equipamento->status_limpeza['mensagem'] }}
                        </span>
                    </div>

                    <!-- Fabricante -->
                    @if($equipamento->fabricante)
                    <div class="pt-3 border-t border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="text-sm text-gray-600">{{ $equipamento->fabricante }}</span>
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                    <span class="text-sm text-[#3f9cae] font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                        Ver detalhes
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <!-- Estado Vazio -->
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum equipamento cadastrado.</p>
            <p class="portal-empty-state-text">
                Entre em contato para cadastrar seus equipamentos.
            </p>
        </div>
        @endif

    </div>
</x-app-layout>
