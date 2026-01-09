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

                    {{-- BUSCA --}}
                    <div class="flex flex-col flex-1 min-w-[240px]">
                        <label class="text-sm font-medium text-gray-700 mb-1">
                            Buscar
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cliente ou solicitante"
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>

                    {{-- STATUS --}}
                    <div class="flex flex-col w-48">
                        <label class="text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            @foreach([
                            'orcamento' => 'Orçamento',
                            'aberto' => 'Aberto',
                            'em_atendimento' => 'Em Atendimento',
                            'pendente_cliente' => 'Pendente Cliente',
                            'pendente_fornecedor' => 'Pendente Fornecedor',
                            'garantia' => 'Garantia',
                            'concluido' => 'Concluído'
                            ] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status')===$value)>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PRIORIDADE --}}
                    <div class="flex flex-col w-40">
                        <label class="text-sm font-medium text-gray-700 mb-1">
                            Prioridade
                        </label>
                        <select name="prioridade" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">Todas</option>
                            <option value="alta" @selected(request('prioridade')=='alta' )>Alta</option>
                            <option value="media" @selected(request('prioridade')=='media' )>Média</option>
                            <option value="baixa" @selected(request('prioridade')=='baixa' )>Baixa</option>
                        </select>
                    </div>

                    {{-- PERÍODO --}}
                    <div class="flex flex-col w-40">
                        <label class="text-sm font-medium text-gray-700 mb-1">
                            Período
                        </label>
                        <select name="periodo" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="dia" @selected(request('periodo')=='dia' )>Hoje</option>
                            <option value="semana" @selected(request('periodo')=='semana' )>Semana</option>
                            <option value="mes" @selected(request('periodo','mes')=='mes' )>Mês</option>
                            <option value="ano" @selected(request('periodo')=='ano' )>Ano</option>
                        </select>
                    </div>

                    {{-- BOTÕES --}}
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
                                <th class="px-4 py-3 text-xs">Nº</th>
                                <th class="px-4 py-3 text-xs">Solicitante</th>
                                <th class="px-4 py-3 text-xs">Assunto</th>
                                <th class="px-4 py-3 text-xs">Empresa</th>
                                <th class="px-4 py-3 text-xs">Técnico</th>
                                <th class="px-4 py-3 text-xs">Prioridade</th>
                                <th class="px-4 py-3 text-xs">Status</th>
                                <th class="px-4 py-3 text-xs">Data</th>
                                <th class="px-4 py-3 text-xs">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach($atendimentos as $atendimento)
                            <tr class="hover:bg-gray-50">

                                {{-- Nº --}}
                                <td class="px-4 py-3 text-xs font-medium">
                                    #{{ $atendimento->numero_atendimento }}
                                </td>

                                {{-- SOLICITANTE --}}
                                <td class="px-4 py-3 text-xs">
                                    @if($atendimento->cliente)
                                    <strong>{{ $atendimento->cliente->nome }}</strong>
                                    @else
                                    {{ $atendimento->nome_solicitante }}
                                    @endif
                                    <br>
                                    <span class="text-gray-500">
                                        {{ $atendimento->telefone_solicitante ?? '—' }}
                                    </span>
                                </td>

                                {{-- ASSUNTO --}}
                                <td class="px-4 py-3 text-xs">
                                    {{ $atendimento->assunto->nome }}
                                </td>

                                {{-- EMPRESA --}}
                                <td class="px-4 py-3 text-xs">
                                    {{ $atendimento->empresa->nome_fantasia }}
                                </td>

                                {{-- TÉCNICO (EDITÁVEL) --}}
                                <td class="px-4 py-3 text-xs">
                                    <select data-id="{{ $atendimento->id }}" data-campo="funcionario_id"
                                        class="campo-editavel border-gray-300 rounded text-xs px-2 py-1">

                                        <option value="">—</option>
                                        @foreach($funcionarios as $funcionario)
                                        <option value="{{ $funcionario->id }}" @selected($atendimento->funcionario_id ==
                                            $funcionario->id)>
                                            {{ $funcionario->nome }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- PRIORIDADE (EDITÁVEL) --}}
                                <td class="px-4 py-3 text-xs">
                                    <select data-id="{{ $atendimento->id }}" data-campo="prioridade"
                                        class="campo-editavel border-gray-300 rounded text-xs px-2 py-1">
                                        <option value="baixa" @selected($atendimento->prioridade === 'baixa')>Baixa
                                        </option>
                                        <option value="media" @selected($atendimento->prioridade === 'media')>Média
                                        </option>
                                        <option value="alta" @selected($atendimento->prioridade === 'alta')>Alta
                                        </option>
                                    </select>
                                </td>

                                {{-- STATUS (EDITÁVEL + HISTÓRICO) --}}
                                <td class="px-4 py-3 text-xs">
                                    <select data-id="{{ $atendimento->id }}" data-campo="status"
                                        class="campo-editavel border-gray-300 rounded text-xs px-2 py-1">
                                        @foreach([
                                        'orcamento' => 'Orçamento',
                                        'aberto' => 'Aberto',
                                        'em_atendimento' => 'Em Atendimento',
                                        'pendente_cliente' => 'Pendente Cliente',
                                        'pendente_fornecedor' => 'Pendente Fornecedor',
                                        'garantia' => 'Garantia',
                                        'concluido' => 'Concluído'
                                        ] as $value => $label)
                                        <option value="{{ $value }}" @selected($atendimento->status_atual === $value)>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- DATA --}}
                                <td class="px-4 py-3 text-xs">
                                    {{ $atendimento->data_atendimento->format('d/m/Y') }}
                                </td>

                                {{-- AÇÕES --}}
                                <td class="px-4 py-3 text-xs">
                                    <div class="flex gap-2">
                                        <a href="{{ route('atendimentos.edit', $atendimento) }}"
                                            class="px-3 py-1 bg-blue-600 text-green-600 rounded text-xs">
                                            Editar
                                        </a>

                                        <form action="{{ route('atendimentos.destroy', $atendimento) }}" method="POST"
                                            onsubmit="return confirm('Deseja excluir este atendimento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1 border border-red-600 text-red-600 rounded text-xs">
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
            <div class="bg-white p-12 rounded shadow text-center">
                <p class="text-gray-500">Nenhum atendimento registrado.</p>
            </div>
            @endif

        </div>
    </div>

    {{-- SCRIPT AJAX --}}
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