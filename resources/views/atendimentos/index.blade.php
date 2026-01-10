<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Atendimentos
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-50 py-6">
        <div class="w-full px-3 sm:px-4 md:px-6 lg:px-8 space-y-6 max-w-7xl mx-auto">

            {{-- ================= FILTROS ================= --}}
            <form method="GET" class="bg-white shadow-md rounded-lg p-4 sm:p-6 border-t-4 border-blue-600">
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-end flex-nowrap">

                    {{-- BUSCA --}}
                    <div class="flex flex-col sm:flex-1 sm:min-w-[220px]">
                        <label class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                            Buscar
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cliente ou solicitante"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- STATUS --}}
                    <div class="flex flex-col w-1/2 sm:w-auto sm:min-w-[130px]">
                        <label class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                            Status
                        </label>
                        <select name="status"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Todos</option>
                            @foreach([
                            'orcamento' => 'Orçamento',
                            'aberto' => 'Aberto',
                            'em_atendimento' => 'Em Atendimento',
                            'pendente_cliente' => 'Pendente Cliente',
                            'pendente_fornecedor' => 'Pendente Fornecedor',
                            'garantia' => 'Garantia',
                            'finalizacao' => 'Finalização',
                            'concluido' => 'Concluído'
                            ] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status')===$value)>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PRIORIDADE --}}
                    <div class="flex flex-col w-1/2 sm:w-auto sm:min-w-[110px]">
                        <label class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                            Prioridade
                        </label>
                        <select name="prioridade"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Todas</option>
                            <option value="alta" @selected(request('prioridade')=='alta' )>Alta</option>
                            <option value="media" @selected(request('prioridade')=='media' )>Média</option>
                            <option value="baixa" @selected(request('prioridade')=='baixa' )>Baixa</option>
                        </select>
                    </div>

                    {{-- PERÍODO --}}
                    <div class="flex flex-col w-1/2 sm:w-auto sm:min-w-[100px]">
                        <label class="text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                            Período
                        </label>
                        <select name="periodo"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="dia" @selected(request('periodo')=='dia' )>Hoje</option>
                            <option value="semana" @selected(request('periodo')=='semana' )>Semana</option>
                            <option value="mes" @selected(request('periodo','mes')=='mes' )>Mês</option>
                            <option value="ano" @selected(request('periodo')=='ano' )>Ano</option>
                        </select>
                    </div>

                    {{-- BOTÕES --}}
                    <div class="flex gap-2 w-1/2 sm:w-auto sm:gap-2">
                        <button type="submit"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-3 sm:px-3 py-2 bg-blue-600 hover:bg-blue-700 text-green-600 text-xs font-semibold rounded-md shadow-md transition-colors duration-200 whitespace-nowrap">
                            🔍 Filtrar
                        </button>

                        <a href="{{ route('atendimentos.create') }}"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-3 sm:px-3 py-2 bg-green-600 hover:bg-green-700 text-green-600 text-xs font-semibold rounded-md shadow-md transition-colors duration-200 whitespace-nowrap">
                            ➕ Novo
                        </a>
                    </div>

                </div>
            </form>

            {{-- ================= ESTÍLO RESPONSIVO ================= --}}
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
                    padding: 0 !important;
                }

                .table-container {
                    display: none !important;
                }
            }
            </style>

            {{-- ================= VISUALIZAÇÃO MOBILE (CARDS) ================= --}}
            @if($atendimentos->count() > 0)
            <div class="cards-container">
                <div class="space-y-6 px-0">
                    @foreach($atendimentos as $atendimento)
                    <div
                        class="bg-white shadow-md rounded-lg p-4 border-l-4 border-blue-500 hover:shadow-lg transition-shadow duration-200 mx-0">

                        {{-- Cabeçalho do Card --}}
                        <div class="flex justify-between items-start mb-3 pb-3 border-b border-gray-200">
                            <div>
                                <h3 class="font-bold text-base text-gray-900">
                                    Atendimento #{{ $atendimento->numero_atendimento }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $atendimento->data_atendimento->format('d/m/Y \à\s H:i') }}
                                </p>
                            </div>
                        </div>

                        {{-- Informações Principais --}}
                        <div class="space-y-3 mb-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Solicitante</p>
                                <p class="text-sm text-gray-900 font-medium">
                                    @if($atendimento->cliente)
                                    {{ $atendimento->cliente->nome }}
                                    @else
                                    {{ $atendimento->nome_solicitante }}
                                    @endif
                                </p>
                                @if($atendimento->telefone_solicitante)
                                <p class="text-xs text-gray-500">{{ $atendimento->telefone_solicitante }}</p>
                                @endif
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Assunto</p>
                                <p class="text-sm text-gray-900 font-medium">
                                    {{ $atendimento->assunto->nome }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Empresa</p>
                                <p class="text-sm text-gray-900 font-medium">
                                    {{ $atendimento->empresa->nome_fantasia }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Técnico</p>
                                <select data-id="{{ $atendimento->id }}" data-campo="funcionario_id"
                                    class="campo-editavel w-full border border-gray-300 rounded text-xs px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">—</option>
                                    @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}" @selected($atendimento->funcionario_id ==
                                        $funcionario->id)>
                                        {{ $funcionario->nome }}
                                    </option>
                                    @endforeach
                                </select>
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
                            @php
                            $statusColors = [
                            'orcamento' => 'purple',
                            'aberto' => 'blue',
                            'em_atendimento' => 'orange',
                            'pendente_cliente' => 'red',
                            'pendente_fornecedor' => 'red',
                            'garantia' => 'indigo',
                            'concluido' => 'green'
                            ];
                            $color = $statusColors[$atendimento->status_atual] ?? 'gray';
                            @endphp
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800">
                                <span class="w-2 h-2 bg-{{ $color }}-600 rounded-full mr-2"></span>
                                {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                            </span>
                        </div>

                        {{-- Ações --}}
                        <div class="flex gap-2 ">
                            <a href="{{ route('atendimentos.edit', $atendimento) }}"
                                class="flex-1 px-3 py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded text-xs font-semibold transition-colors duration-200">
                                Editar
                            </a>

                            <form action="{{ route('atendimentos.destroy', $atendimento) }}" method="POST"
                                onsubmit="return confirm('Deseja excluir este atendimento?')" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full px-3 py-2 border border-red-600 text-red-600 hover:bg-red-50 rounded text-xs font-semibold transition-colors duration-200">
                                    Excluir
                                </button>
                            </form>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= VISUALIZAÇÃO DESKTOP (TABELA) ================= --}}
            <div class="table-container">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden mx-0">
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
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Solicitante</span>
                                    </th>
                                    <th class="px-6 py-4 text-left">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Assunto</span>
                                    </th>
                                    <th class="px-6 py-4 text-left">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Empresa</span>
                                    </th>
                                    <th class="px-6 py-4 text-left">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Técnico</span>
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
                                    <th class="px-6 py-4 text-center">
                                        <span
                                            class="text-xs font-bold text-gray-700 uppercase tracking-wider">Ações</span>
                                    </th>
                                </tr>
                            </thead>

                            {{-- Corpo da Tabela --}}
                            <tbody class="divide-y divide-gray-200">
                                @foreach($atendimentos as $atendimento)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    {{-- Número --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                                            {{ $atendimento->numero_atendimento }}
                                        </span>
                                    </td>

                                    {{-- Solicitante --}}
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                @if($atendimento->cliente)
                                                {{ $atendimento->cliente->nome }}
                                                @else
                                                {{ $atendimento->nome_solicitante }}
                                                @endif
                                            </p>
                                            @if($atendimento->telefone_solicitante)
                                            <p class="text-xs text-gray-500">{{ $atendimento->telefone_solicitante }}
                                            </p>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Assunto --}}
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-700">
                                            {{ $atendimento->assunto->nome }}
                                        </p>
                                    </td>

                                    {{-- Empresa --}}
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-700">
                                            {{ $atendimento->empresa->nome_fantasia }}
                                        </p>
                                    </td>

                                    {{-- Técnico (Editável) --}}
                                    <td class="px-6 py-4">
                                        <select data-id="{{ $atendimento->id }}" data-campo="funcionario_id"
                                            class="campo-editavel border border-gray-300 rounded text-xs px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">—</option>
                                            @foreach($funcionarios as $funcionario)
                                            <option value="{{ $funcionario->id }}" @selected($atendimento->
                                                funcionario_id == $funcionario->id)>
                                                {{ $funcionario->nome }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    {{-- Prioridade (Editável) --}}
                                    <td class="px-6 py-4 text-center">
                                        <select data-id="{{ $atendimento->id }}" data-campo="prioridade"
                                            class="campo-editavel border border-gray-300 rounded text-xs px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="baixa" @selected($atendimento->prioridade === 'baixa')>Baixa
                                            </option>
                                            <option value="media" @selected($atendimento->prioridade === 'media')>Média
                                            </option>
                                            <option value="alta" @selected($atendimento->prioridade === 'alta')>Alta
                                            </option>
                                        </select>
                                    </td>

                                    {{-- Status (Editável) --}}
                                    <td class="px-6 py-4 text-center">
                                        <select data-id="{{ $atendimento->id }}" data-campo="status"
                                            class="campo-editavel border border-gray-300 rounded text-xs px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            @foreach([
                                            'orcamento' => 'Orçamento',
                                            'aberto' => 'Aberto',
                                            'em_atendimento' => 'Em Atendimento',
                                            'pendente_cliente' => 'Pendente Cliente',
                                            'pendente_fornecedor' => 'Pendente Fornecedor',
                                            'garantia' => 'Garantia',
                                            'finalizacao'=> 'Finalização',
                                            'concluido' => 'Concluído'
                                            ] as $value => $label)
                                            <option value="{{ $value }}" @selected($atendimento->status_atual ===
                                                $value)>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    {{-- Data --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="text-sm text-gray-600">
                                            {{ $atendimento->data_atendimento->format('d/m/Y') }}
                                        </p>
                                    </td>

                                    {{-- Ações --}}
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex gap-2 justify-center">
                                            <a href="{{ route('atendimentos.edit', $atendimento) }}"
                                                class="px-3 py-1 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded text-xs font-semibold transition-colors duration-200">
                                                Editar
                                            </a>

                                            <form action="{{ route('atendimentos.destroy', $atendimento) }}"
                                                method="POST"
                                                onsubmit="return confirm('Deseja excluir este atendimento?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1 border border-red-600 text-red-600 hover:bg-red-50 rounded text-xs font-semibold transition-colors duration-200">
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
            </div>

            @else
            {{-- Estado Vazio --}}
            <div class="bg-white shadow-lg rounded-lg p-8 sm:p-12 text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum atendimento registrado</h3>
                <p class="text-gray-500 mb-6">
                    Nenhum atendimento foi encontrado com os filtros aplicados.
                </p>
                <a href="{{ route('atendimentos.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md shadow-md transition-colors duration-200">
                    ➕ Criar Novo Atendimento
                </a>
            </div>
            @endif

        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
    document.querySelectorAll('.campo-editavel').forEach(el => {
        el.addEventListener('change', function() {

            fetch(`/atendimentos/${this.dataset.id}/atualizar-campo`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        campo: this.dataset.campo,
                        valor: this.value
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Erro ao atualizar');
                        location.reload();
                    }
                })
                .catch(() => {
                    alert('Erro de comunicação');
                    location.reload();
                });
        });
    });
    </script>

</x-app-layout>