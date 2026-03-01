<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Setores
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

        @if($setores->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($setores as $setor)
            <div class="portal-card">
                <div class="p-5">
                    <div class="flex items-start gap-3">
                        <div class="bg-[#3f9cae] bg-opacity-10 p-2.5 rounded-lg">
                            <svg class="w-6 h-6 text-[#3f9cae]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 text-base">{{ $setor->nome }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $setor->equipamentos_count }} {{ $setor->equipamentos_count === 1 ? 'equipamento' : 'equipamentos' }}</p>
                        </div>
                    </div>
                    @if($setor->descricao)
                    <p class="mt-4 text-sm text-gray-600 line-clamp-2">{{ $setor->descricao }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum setor cadastrado.</p>
            <p class="portal-empty-state-text">
                Os setores aparecerão aqui quando forem cadastrados.
            </p>
        </div>
        @endif

    </div>
</x-app-layout>
