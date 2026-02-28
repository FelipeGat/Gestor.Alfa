<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                    Lista de Equipamentos
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $cliente->nome_exibicao }}
                </p>
            </div>
            <a href="{{ route('portal.equipamentos.index') }}" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-green-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            @if($equipamentos->count() > 0)
                <!-- Grid de Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($equipamentos as $equipamento)
                        <a href="{{ route('portal.equipamentos.show', $equipamento->id) }}"
                            class="group bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100
                                   hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.02] cursor-pointer">

                            <!-- Header do Card -->
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4">
                                <h3 class="text-lg font-bold text-white truncate">
                                    {{ $equipamento->nome }}
                                </h3>
                                <p class="text-blue-100 text-sm">
                                    {{ $equipamento->modelo ?? 'Sem modelo' }}
                                </p>
                            </div>

                            <!-- Corpo do Card -->
                            <div class="p-5 space-y-4">
                                <!-- Setor e Responsável -->
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="truncate">{{ $equipamento->setor->nome ?? 'Sem setor' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="truncate">{{ $equipamento->responsavel->nome ?? 'Sem responsável' }}</span>
                                    </div>
                                </div>

                                <!-- Status Manutenção -->
                                <div class="pt-3 border-t border-gray-100">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-semibold text-gray-500 uppercase">Manutenção</span>
                                        <span class="text-xs text-gray-400">
                                            {{ $equipamento->periodicidade_manutencao_meses }} meses
                                        </span>
                                    </div>
                                    <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $equipamento->status_manutencao['classe'] }}">
                                        {{ $equipamento->status_manutencao['mensagem'] }}
                                    </div>
                                </div>

                                <!-- Status Limpeza -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-semibold text-gray-500 uppercase">Limpeza</span>
                                        <span class="text-xs text-gray-400">
                                            {{ $equipamento->periodicidade_limpeza_meses }} {{ $equipamento->periodicidade_limpeza_meses == 1 ? 'mês' : 'meses' }}
                                        </span>
                                    </div>
                                    <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $equipamento->status_limpeza['classe'] }}">
                                        {{ $equipamento->status_limpeza['mensagem'] }}
                                    </div>
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
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                                <span class="text-sm text-blue-600 font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
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
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center border border-gray-100">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Nenhum equipamento cadastrado.</p>
                    <p class="text-gray-400 text-sm mt-2">Entre em contato para cadastrar seus equipamentos.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
