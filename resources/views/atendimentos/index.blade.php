<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Atendimentos
        </h2>
    </x-slot>

    <div class="flex justify-center min-h-screen bg-gray-100">
        <div class="w-3/4 py-12 space-y-6">

            {{-- FILTROS --}}
            <form method="GET" class="bg-white shadow rounded-lg p-6">
                <div class="flex flex-wrap gap-4 items-end">

                    {{-- Buscar --}}
                    <div class="flex flex-col flex-1 min-w-[240px]">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Pesquisar Solicitante
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nome do solicitante" class="border border-gray-300 rounded-md px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Prioridade --}}
                    <div class="flex flex-col w-48">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            Prioridade
                        </label>
                        <select name="prioridade" class="border border-gray-300 rounded-md px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas</option>
                            <option value="alta" @selected(request('prioridade')=='alta' )>Alta</option>
                            <option value="media" @selected(request('prioridade')=='media' )>Média</option>
                            <option value="baixa" @selected(request('prioridade')=='baixa' )>Baixa</option>
                        </select>
                    </div>

                    {{-- Botões --}}
                    <div class="flex gap-3 items-end">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2
                                   bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            🔍 Filtrar
                        </button>

                        <a href="{{ route('atendimentos.create') }}" class="inline-flex items-center justify-center px-4 py-2
                                   bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md shadow">
                            ➕ Novo Atendimento
                        </a>
                    </div>

                </div>
            </form>

            {{-- TABELA --}}
            @if($atendimentos->count() > 0)
            <div class="bg-white shadow-md rounded-lg overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nº</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">
                                    Solicitante</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Assunto
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Empresa
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Técnico
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Prioridade
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Data</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @foreach($atendimentos as $atendimento)
                            <tr class="hover:bg-gray-50 transition duration-150">

                                <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                    #{{ $atendimento->numero_atendimento }}
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-900">
                                    {{ $atendimento->cliente->nome }} <br>
                                    <span class="text-gray-500">
                                        {{ $atendimento->telefone_solicitante ?? '—' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-900">
                                    {{ $atendimento->assunto->nome }}
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-900">
                                    {{ $atendimento->empresa->nome_fantasia }}
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-900">
                                    {{ $atendimento->funcionario->nome ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    @if($atendimento->prioridade === 'alta')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Alta</span>
                                    @elseif($atendimento->prioridade === 'media')
                                    <span
                                        class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Média</span>
                                    @else
                                    <span
                                        class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Baixa</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                        {{ ucfirst(str_replace('_', ' ', $atendimento->status)) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-900">
                                    {{ $atendimento->data_atendimento->format('d/m/Y') }}
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    <div class="flex gap-2 items-center">
                                        <a href="{{ route('atendimentos.edit', $atendimento) }}"
                                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-medium rounded-md transition duration-200">
                                            Editar
                                        </a>

                                        <form action="{{ route('atendimentos.destroy', $atendimento) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este atendimento?')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1 border border-red-600 text-red-600 hover:bg-red-50 text-xs font-medium rounded-md transition duration-200">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
            @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-500 text-lg">Nenhum atendimento registrado.</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>