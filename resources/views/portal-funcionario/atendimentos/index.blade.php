<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📋 Meus Atendimentos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($atendimentos->count() > 0)

            {{-- =========================================================
                     CONTROLE EXPLÍCITO DE VISUALIZAÇÃO (CSS GLOBAL SAFE)
                ========================================================== --}}
            <style>
            @media (min-width: 768px) {
                .pf-cards-container {
                    display: none !important;
                }

                .pf-table-container {
                    display: block !important;
                }
            }

            @media (max-width: 767px) {
                .pf-cards-container {
                    display: block !important;
                }

                .pf-table-container {
                    display: none !important;
                }
            }
            </style>

            {{-- =========================================================
                     MOBILE — CARDS (PRIORIDADE TOTAL)
                ========================================================== --}}
            <div class="pf-cards-container">
                <div style="display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:1.5rem;">

                    @foreach($atendimentos as $atendimento)
                    <div onclick="window.location.href='{{ route('portal-funcionario.atendimentos.show', ['atendimento' => $atendimento->id]) }}'"
                        class="cursor-pointer bg-white shadow-md rounded-lg p-4 border-l-4 border-blue-500 hover:shadow-lg transition-shadow duration-200">

                        {{-- Cabeçalho --}}
                        <div style="display:grid;grid-template-columns:1fr auto;gap:0.5rem;" class="mb-3">
                            <div>
                                <h3 class="font-bold text-base text-gray-900">
                                    Atendimento #{{ $atendimento->numero_atendimento }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $atendimento->data_atendimento->format('d/m/Y \à\s H:i') }}
                                </p>
                            </div>
                        </div>

                        {{-- Informações --}}
                        <div style="display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:0.75rem;"
                            class="mb-3">

                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                    Cliente
                                </p>
                                <p class="text-sm text-gray-900 font-medium">
                                    {{ $atendimento->cliente->nome ?? $atendimento->nome_solicitante }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                    Assunto
                                </p>
                                <p class="text-sm text-gray-900 font-medium">
                                    {{ $atendimento->assunto->nome }}
                                </p>
                            </div>

                        </div>

                        {{-- Badges --}}
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;" class="pt-3 border-t border-gray-200">

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

            {{-- =========================================================
                     DESKTOP — TABELA
                ========================================================== --}}
            <div class="pf-table-container">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">
                                        Nº
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">
                                        Assunto
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase">
                                        Prioridade
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">
                                        Data
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @foreach($atendimentos as $atendimento)
                                <tr onclick="window.location.href='{{ route('portal-funcionario.atendimentos.show', ['atendimento' => $atendimento->id]) }}'"
                                    class="cursor-pointer hover:bg-gray-50 transition">

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                                            {{ $atendimento->numero_atendimento }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $atendimento->cliente->nome ?? $atendimento->nome_solicitante }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $atendimento->assunto->nome }}
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        @if($atendimento->prioridade === 'alta')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            Alta
                                        </span>
                                        @elseif($atendimento->prioridade === 'media')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            Média
                                        </span>
                                        @else
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            Baixa
                                        </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $atendimento->data_atendimento->format('d/m/Y') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @else
            {{-- =========================================================
                     ESTADO VAZIO
                ========================================================== --}}
            <div class="bg-white shadow-lg rounded-lg p-12 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    Nenhum atendimento
                </h3>
                <p class="text-gray-500">
                    Você não possui atendimentos atribuídos no momento.
                </p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>