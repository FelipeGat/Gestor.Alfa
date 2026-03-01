<x-app-layout>

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Cadastros', 'url' => route('cadastros.index')],
            ['label' => 'Setores', 'url' => route('admin.setores.index')],
            ['label' => $setor->nome]
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Header --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-[#3f9cae] to-[#2d7a8a] px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-white">{{ $setor->nome }}</h2>
                            <p class="text-white/80 text-sm">{{ $setor->cliente->nome_exibicao }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.setores.edit', $setor->id) }}" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Editar
                            </a>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</h4>
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $setor->cliente->nome_exibicao }}</p>
                        </div>

                        @if($setor->descricao)
                        <div class="md:col-span-2">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Descrição</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $setor->descricao }}</p>
                        </div>
                        @endif

                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Data de Cadastro</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $setor->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Última Atualização</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $setor->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Equipamentos do Setor --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Equipamentos Vinculados ({{ $setor->equipamentos->count() }})</h3>
                </div>
                <div class="p-6">
                    @if($setor->equipamentos->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($setor->equipamentos as $equipamento)
                            <a href="{{ route('admin.equipamentos.show', $equipamento->id) }}" 
                               class="group bg-gray-50 rounded-lg border border-gray-200 p-4 hover:shadow-md hover:border-blue-300 transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            {{ $equipamento->nome }}
                                        </h4>
                                        <p class="text-sm text-gray-500 mt-1">{{ $equipamento->modelo ?? 'Sem modelo' }}</p>
                                        @if($equipamento->responsavel)
                                        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ $equipamento->responsavel->nome }}
                                        </p>
                                        @endif
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-8">Nenhum equipamento vinculado a este setor.</p>
                    @endif
                </div>
            </div>

            {{-- Botão Voltar --}}
            <div class="flex justify-start">
                <a href="{{ route('admin.setores.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar para lista
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
