<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                Minhas Ordens de Serviço
            </h2>
            <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Voltar
            </a>
        </div>
        <p class="text-sm text-gray-600 mt-2">
            Unidade: <span class="font-semibold text-blue-600">{{ $cliente->nome_exibicao }}</span>
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Meus Atendimentos</h1>
            @if($atendimentos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Empresa</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Assunto</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Criado em</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($atendimentos as $atendimento)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $atendimento->id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $atendimento->empresa->nome_fantasia ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $atendimento->assunto->nome ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                            {{ $atendimento->status_atual ?? 'Indefinido' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $atendimento->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <button type="button" class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all" onclick="window.showHistorico{{ $atendimento->id }}.showModal()">Ver Detalhes</button>
                                        <dialog id="showHistorico{{ $atendimento->id }}" class="rounded-xl shadow-xl p-0 w-full max-w-2xl">
                                            <form method="dialog" class="p-6">
                                                <h3 class="text-lg font-bold mb-4">Histórico do Atendimento #{{ $atendimento->id }}</h3>
                                                @if($atendimento->andamentos && $atendimento->andamentos->count())
                                                    <ul class="space-y-3">
                                                        @foreach($atendimento->andamentos as $andamento)
                                                            <li class="border-b pb-2">
                                                                <div class="text-sm text-gray-800">{{ $andamento->descricao ?? '-' }}</div>
                                                                <div class="text-xs text-gray-500 mt-1">{{ $andamento->created_at->format('d/m/Y H:i') }}</div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div class="text-gray-500">Nenhum andamento disponível.</div>
                                                @endif
                                                <div class="mt-6 text-right">
                                                    <button type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg" onclick="window.showHistorico{{ $atendimento->id }}.close()">Fechar</button>
                                                </div>
                                            </form>
                                        </dialog>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Nenhum atendimento encontrado.</p>
                    <p class="text-gray-400 text-sm mt-2">Suas ordens de serviço aparecerão aqui quando forem cadastradas.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>