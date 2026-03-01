<x-app-layout>

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Equipamentos', 'url' => route('admin.equipamentos.index')],
            ['label' => 'Responsáveis', 'url' => route('admin.equipamentos.responsaveis.index')],
            ['label' => $responsavel->nome]
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Header --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-[#3f9cae] to-[#2d7a8a] px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-white">{{ $responsavel->nome }}</h2>
                            <p class="text-white/80 text-sm">{{ $responsavel->cliente->nome_exibicao }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.equipamentos.responsaveis.edit', $responsavel->id) }}" 
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
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $responsavel->cliente->nome_exibicao }}</p>
                        </div>

                        @if($responsavel->cargo)
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cargo</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $responsavel->cargo }}</p>
                        </div>
                        @endif

                        @if($responsavel->telefone)
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Telefone</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $responsavel->telefone }}</p>
                        </div>
                        @endif

                        @if($responsavel->email)
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">E-mail</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $responsavel->email }}</p>
                        </div>
                        @endif

                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Data de Cadastro</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $responsavel->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Última Atualização</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $responsavel->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Equipamentos do Responsável --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Equipamentos Sob Responsabilidade ({{ $responsavel->equipamentos->count() }})</h3>
                </div>
                <div class="p-6">
                    @if($responsavel->equipamentos->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($responsavel->equipamentos as $equipamento)
                            <a href="{{ route('admin.equipamentos.show', $equipamento->id) }}" 
                               class="group bg-gray-50 rounded-lg border border-gray-200 p-4 hover:shadow-md hover:border-blue-300 transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            {{ $equipamento->nome }}
                                        </h4>
                                        <p class="text-sm text-gray-500 mt-1">{{ $equipamento->modelo ?? 'Sem modelo' }}</p>
                                        @if($equipamento->setor)
                                        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $equipamento->setor->nome }}
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
                        <p class="text-sm text-gray-500 text-center py-8">Nenhum equipamento sob responsabilidade deste responsável.</p>
                    @endif
                </div>
            </div>

            {{-- Botão Voltar --}}
            <div class="flex justify-start">
                <a href="{{ route('admin.equipamentos.responsaveis.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar para lista
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
