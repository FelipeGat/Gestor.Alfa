<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Meus Atendimentos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($atendimentos->count() > 0)

            {{-- ================= VISUALIZAÇÃO MOBILE (CARDS) ================= --}}
            <style>
            @media (min-width: 768px) {
                .cards-container {
                    display: none !important;
                }

                .table-container {
                    display: block !important;
                }
            }

            @media (max-width: 767px) {
                .cards-container {
                    display: block !important;
                }

                .table-container {
                    display: none !important;
                }
            }
            </style>

            <div class="cards-container">
                <div class="space-y-6">
                    @foreach($atendimentos as $atendimento)
                    <div onclick="window.location.href='{{ route('portal-funcionario.atendimentos.show', ['atendimento' => $atendimento->id]) }}';"
                        class="cursor-pointer bg-white shadow-md rounded-lg p-4 border-l-4 border-blue-500 hover:shadow-lg transition-shadow duration-200">


                        {{-- Cabeçalho do Card --}}
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-base text-gray-900">
                                    Atendimento #{{ $atendimento->numero_atendimento }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $atendimento->data_atendimento->format('d/m/Y \à\s H:i') }}
                                </p>
                            </div>
                        </div>

                        {{-- Informações do Card --}}
                        <div class="space-y-2 mb-3">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</p>
                                <p class="text-sm text-gray-900 font-medium">
                                    {{ $atendimento->cliente->nome ?? $atendimento->nome_solicitante }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Assunto</p>
                                <p class="text-sm text-gray-900 font-medium">
                                    {{ $atendimento->assunto->nome }}
                                </p>
                            </div>
                        </div>

                        {{-- Badges de Status --}}
                        <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200">
                            {{-- Prioridade --}}
                            @if($atendimento->prioridade === 'alta')
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                <span class="w-2 h-2 bg-red-600 rounded-full mr-2"></span>
                                Alta
                            </span>
                            @elseif($atendimento->prioridade === 'media')
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                <span class="w-2 h-2 bg-yellow-600 rounded-full mr-2"></span>
                                Média
                            </span>
                            @else
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                                Baixa
                            </span>
                            @endif

                            {{-- Status --}}
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                <span class="w-2 h-2 bg-blue-600 rounded-full mr-2"></span>
                                {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                            </span>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= VISUALIZAÇÃO DESKTOP (TABELA) ================= --}}
            <div class="table-container">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            {{-- Cabeçalho da Tabela --}}
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                                    <th class="px-6 py-4 text-left">
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Nº</span>
                                    </th>
                                    <th class="px-6 py-4 text-left">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Cliente</span>
                                    </th>
                                    <th class="px-6 py-4 text-left">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Assunto</span>
                                    </th>
                                    <th class="px-6 py-4 text-center">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Prioridade</span>
                                    </th>
                                    <th class="px-6 py-4 text-center">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Status</span>
                                    </th>
                                    <th class="px-6 py-4 text-left">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Data</span>
                                    </th>
                                </tr>
                            </thead>

                            {{-- Corpo da Tabela --}}
                            <tbody class="divide-y divide-gray-200">
                                @foreach($atendimentos as $atendimento)
                                <tr onclick="window.location.href = '{{ route('portal-funcionario.atendimentos.show', ['atendimento' => $atendimento->id]) }}'"
                                    class="cursor-pointer hover:bg-gray-50 transition-colors duration-150 border-b border-gray-200">

                                    {{-- Número do Atendimento --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                                            {{ $atendimento->numero_atendimento }}
                                        </span>
                                    </td>

                                    {{-- Cliente --}}
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $atendimento->cliente->nome ?? $atendimento->nome_solicitante }}
                                        </p>
                                    </td>

                                    {{-- Assunto --}}
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-700">
                                            {{ $atendimento->assunto->nome }}
                                        </p>
                                    </td>

                                    {{-- Prioridade --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($atendimento->prioridade === 'alta')
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            <span class="w-2 h-2 bg-red-600 rounded-full mr-2"></span>
                                            Alta
                                        </span>
                                        @elseif($atendimento->prioridade === 'media')
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            <span class="w-2 h-2 bg-yellow-600 rounded-full mr-2"></span>
                                            Média
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                                            Baixa
                                        </span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            <span class="w-2 h-2 bg-blue-600 rounded-full mr-2"></span>
                                            {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                                        </span>
                                    </td>

                                    {{-- Data --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="text-sm text-gray-600">
                                            {{ $atendimento->data_atendimento->format('d/m/Y') }}
                                        </p>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @else
            {{-- Estado Vazio --}}
            <div class="bg-white shadow-lg rounded-lg p-12 text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum atendimento</h3>
                <p class="text-gray-500">
                    Você não possui atendimentos atribuídos no momento.
                </p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>