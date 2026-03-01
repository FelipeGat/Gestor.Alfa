<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Abrir Novo Chamado
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $cliente->nome_exibicao }}
                </p>
            </div>
            <a href="{{ route('portal.atendimentos') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline">Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper">
        <div class="portal-table-card">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Informações do Chamado
                </h3>
            </div>

            <form action="{{ route('portal.chamado.store') }}" method="POST" class="p-6">
                @csrf

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="space-y-6">
                    {{-- Assunto --}}
                    <div class="portal-filter-group">
                        <label class="portal-filter-label">Assunto</label>
                        <select name="assunto_id" required
                            class="portal-filter-input w-full">
                            <option value="">Selecione um assunto</option>
                            @foreach($assuntos as $assunto)
                                <option value="{{ $assunto->id }}">{{ $assunto->nome }}</option>
                            @endforeach
                        </select>
                        @error('assunto_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Descrição --}}
                    <div class="portal-filter-group">
                        <label class="portal-filter-label">Descrição do Problema</label>
                        <textarea name="descricao" rows="5" required
                            class="portal-filter-input w-full"
                            placeholder="Descreva detalhadamente o problema..."></textarea>
                        @error('descricao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Prioridade --}}
                    <div class="portal-filter-group">
                        <label class="portal-filter-label">Prioridade</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-colors
                                {{ request()->old('prioridade') === 'baixa' ? 'border-[#3f9cae] bg-[#3f9cae]/10' : 'border-gray-200 hover:border-[#3f9cae] hover:bg-[#3f9cae]/5' }}">
                                <input type="radio" name="prioridade" value="baixa" class="sr-only"
                                    {{ request()->old('prioridade') === 'baixa' ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-700">Baixa</span>
                            </label>
                            <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-colors
                                {{ !request()->old('prioridade') || request()->old('prioridade') === 'media' ? 'border-[#3f9cae] bg-[#3f9cae]/10' : 'border-gray-200 hover:border-[#3f9cae] hover:bg-[#3f9cae]/5' }}">
                                <input type="radio" name="prioridade" value="media" class="sr-only" checked>
                                <span class="text-sm font-medium text-gray-700">Média</span>
                            </label>
                            <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-colors
                                {{ request()->old('prioridade') === 'alta' ? 'border-[#3f9cae] bg-[#3f9cae]/10' : 'border-gray-200 hover:border-[#3f9cae] hover:bg-[#3f9cae]/5' }}">
                                <input type="radio" name="prioridade" value="alta" class="sr-only">
                                <span class="text-sm font-medium text-gray-700">Alta</span>
                            </label>
                            <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer transition-colors
                                {{ request()->old('prioridade') === 'urgente' ? 'border-[#3f9cae] bg-[#3f9cae]/10' : 'border-gray-200 hover:border-[#3f9cae] hover:bg-[#3f9cae]/5' }}">
                                <input type="radio" name="prioridade" value="urgente" class="sr-only">
                                <span class="text-sm font-medium text-gray-700">Urgente</span>
                            </label>
                        </div>
                        @error('prioridade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Botão Submit --}}
                    <div class="pt-4">
                        <button type="submit"
                            class="portal-btn portal-btn--primary w-full justify-center py-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Abrir Chamado
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
