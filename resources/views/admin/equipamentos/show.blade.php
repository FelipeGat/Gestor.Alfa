<x-app-layout>

    <x-slot name="breadcrumb">
        <x-breadcrumb-tabs :items="[
            ['label' => 'Gestão', 'url' => route('gestao.index')],
            ['label' => 'Ativos Técnicos', 'url' => route('admin.equipamentos.index')],
            ['label' => $equipamento->nome]
        ]" />
    </x-slot>

    <div class="pb-8 pt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Header --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-[#3f9cae] to-[#2d7a8a] px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-white">{{ $equipamento->nome }}</h2>
                            <p class="text-white/80 text-sm">{{ $equipamento->modelo ?? 'Sem modelo' }} - {{ $equipamento->fabricante ?? 'Sem fabricante' }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($equipamento->ativo)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-500 text-white">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-500 text-white">
                                    Inativo
                                </span>
                            @endif
                            <a href="{{ route('admin.equipamentos.edit', $equipamento->id) }}"
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Informações do Cliente --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</h4>
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $equipamento->cliente->nome_exibicao }}</p>
                        </div>

                        {{-- Setor --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Setor</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $equipamento->setor->nome ?? '-' }}</p>
                        </div>

                        {{-- Responsável --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Responsável</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $equipamento->responsavel->nome ?? '-' }}</p>
                        </div>

                        {{-- Número de Série --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Nº Série</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $equipamento->numero_serie ?? '-' }}</p>
                        </div>

                        {{-- Periodicidade Manutenção --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Manutenção</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($equipamento->ultima_manutencao)
                                    Última: {{ $equipamento->ultima_manutencao->format('d/m/Y') }}
                                @else
                                    Não registrada
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">Periodicidade: {{ $equipamento->periodicidade_manutencao_meses }} meses</p>
                        </div>

                        {{-- Periodicidade Limpeza --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Limpeza</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($equipamento->ultima_limpeza)
                                    Última: {{ $equipamento->ultima_limpeza->format('d/m/Y') }}
                                @else
                                    Não registrada
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">Periodicidade: {{ $equipamento->periodicidade_limpeza_meses }} {{ $equipamento->periodicidade_limpeza_meses == 1 ? 'mês' : 'meses' }}</p>
                        </div>
                    </div>

                    @if($equipamento->observacoes)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Observações</h4>
                        <p class="text-sm text-gray-700">{{ $equipamento->observacoes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Status de Manutenção e Limpeza --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Status Manutenção --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Status da Manutenção</h3>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-lg {{ $equipamento->status_manutencao['classe'] }}">
                            <span class="text-sm font-medium">{{ $equipamento->status_manutencao['mensagem'] }}</span>
                        </div>

                        @if($equipamento->proxima_manutencao)
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Próxima manutenção prevista:</span>
                            {{ $equipamento->proxima_manutencao->format('d/m/Y') }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Status Limpeza --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Status da Limpeza</h3>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-lg {{ $equipamento->status_limpeza['classe'] }}">
                            <span class="text-sm font-medium">{{ $equipamento->status_limpeza['mensagem'] }}</span>
                        </div>

                        @if($equipamento->proxima_limpeza)
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Próxima limpeza prevista:</span>
                            {{ $equipamento->proxima_limpeza->format('d/m/Y') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Histórico de Manutenções --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Histórico de Manutenções</h3>
                </div>
                <div class="p-6">
                    @if($equipamento->manutencoes->count())
                        <div class="space-y-3">
                            @foreach($equipamento->manutencoes->take(10) as $manutencao)
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-gray-900">
                                            {{ ucfirst($manutencao->tipo) }}
                                        </h4>
                                        <span class="text-xs text-gray-500">{{ $manutencao->data->format('d/m/Y') }}</span>
                                    </div>
                                    @if($manutencao->descricao)
                                    <p class="text-sm text-gray-600 mt-1">{{ $manutencao->descricao }}</p>
                                    @endif
                                    @if($manutencao->realizado_por)
                                    <p class="text-xs text-gray-500 mt-1">Realizado por: {{ $manutencao->realizado_por }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-8">Nenhuma manutenção registrada.</p>
                    @endif
                </div>
            </div>

            {{-- Histórico de Limpezas --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Histórico de Limpezas</h3>
                </div>
                <div class="p-6">
                    @if($equipamento->limpezas->count())
                        <div class="space-y-3">
                            @foreach($equipamento->limpezas->take(10) as $limpeza)
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-gray-900">Limpeza</h4>
                                        <span class="text-xs text-gray-500">{{ $limpeza->data->format('d/m/Y') }}</span>
                                    </div>
                                    @if($limpeza->descricao)
                                    <p class="text-sm text-gray-600 mt-1">{{ $limpeza->descricao }}</p>
                                    @endif
                                    @if($limpeza->realizado_por)
                                    <p class="text-xs text-gray-500 mt-1">Realizado por: {{ $limpeza->realizado_por }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-8">Nenhuma limpeza registrada.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
