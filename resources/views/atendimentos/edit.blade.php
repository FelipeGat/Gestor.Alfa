<x-app-layout>
    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between md:items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Atendimento <span class="text-blue-600">#{{ $atendimento->numero_atendimento }}</span>
            </h2>

            <div class="flex items-center space-x-4 mt-2 md:mt-0">
                <div>
                    <span class="text-gray-500 text-xs uppercase font-semibold mr-1">Status:</span>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 shadow-sm">
                        {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs uppercase font-semibold mr-1">Prioridade:</span>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 shadow-sm">
                        {{ ucfirst($atendimento->prioridade) }}
                    </span>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- ================= COLUNA ESQUERDA (ANDAMENTOS) ================= --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- ===== FORMULÁRIO: NOVO ANDAMENTO ===== --}}
                    @if($atendimento->status_atual !== 'concluido')
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Adicionar Novo Andamento
                            </h3>

                            @if(session('success'))
                            <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
                                {{ session('success') }}
                            </div>
                            @endif

                            <form method="POST" action="{{ route('atendimentos.andamentos.store', $atendimento) }}">
                                @csrf
                                <textarea name="descricao" rows="3"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Descreva o que foi feito ou a próxima etapa...">{{ old('descricao') }}</textarea>

                                <div class="mt-3 flex justify-end">
                                    <button type="submit"
                                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-green-600 text-sm font-bold rounded-md transition shadow-md">
                                        Salvar Andamento
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- ===== LISTA: HISTÓRICO / TIMELINE ===== --}}
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Histórico do Atendimento</h3>

                            @if($atendimento->andamentos->count())
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    @foreach($atendimento->andamentos as $index => $andamento)
                                    <li>
                                        <div class="relative pb-8">
                                            @if($index !== $atendimento->andamentos->count() - 1)
                                            <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200"
                                                aria-hidden="true"></span>
                                            @endif

                                            <div class="relative flex items-start space-x-3">
                                                <div class="relative">
                                                    <span
                                                        class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-6 w-6 text-blue-600" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                    </span>
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <div class="text-sm font-bold text-gray-900">
                                                            {{ $andamento->user->name ?? 'Sistema' }}
                                                        </div>
                                                        <p class="mt-0.5 text-xs text-gray-500">
                                                            {{ $andamento->created_at->format('d/m/Y \à\s H:i') }}
                                                        </p>
                                                    </div>

                                                    <div
                                                        class="mt-2 text-sm text-gray-700 bg-gray-50 rounded-lg p-4 border border-gray-100 shadow-sm leading-relaxed">
                                                        <p class="whitespace-pre-line">{{ $andamento->descricao }}</p>
                                                    </div>

                                                    {{-- FORMULÁRIO DE ANEXO DENTRO DO ITEM --}}
                                                    @if($atendimento->status_atual !== 'concluido')
                                                    <div class="mt-3 border-t border-gray-100 pt-3">
                                                        <form method="POST"
                                                            action="{{ route('andamentos.fotos.store', $andamento) }}"
                                                            enctype="multipart/form-data"
                                                            class="flex items-center gap-2">
                                                            @csrf
                                                            <input type="file" name="fotos[]" multiple accept="image/*"
                                                                class="block w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                            <button type="submit"
                                                                class="p-1.5 bg-gray-800 text-white rounded hover:bg-black transition">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    @endif

                                                    {{-- EXIBIÇÃO DAS FOTOS --}}
                                                    @if($andamento->fotos->count())
                                                    <div class="mt-4">
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($andamento->fotos as $foto)
                                                            <a href="{{ asset('storage/'.$foto->arquivo) }}"
                                                                target="_blank" class="block group">
                                                                <img src="{{ asset('storage/'.$foto->arquivo) }}"
                                                                    class="w-[85px] h-[85px] object-cover rounded-md border border-gray-200 shadow-sm group-hover:ring-2 group-hover:ring-blue-500 transition-all"
                                                                    alt="Anexo">
                                                            </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @else
                            <div class="text-center py-10 border-2 border-dashed border-gray-200 rounded-xl">
                                <p class="text-gray-500 text-sm italic">Nenhum andamento registrado até o momento.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <br>

                {{-- ================= COLUNA DIREITA (AÇÕES E DETALHES) ================= --}}
                <div class="lg:col-span-1 space-y-8"> {{-- space-y-8 garante o espaçamento vertical --}}


                    {{-- ===== BLOCO: AÇÕES ===== --}}
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Ações Disponíveis</h3>
                        </div>
                        <div class="p-4">
                            <a href="{{ route('atendimentos.index') }}"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 rounded-md text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Voltar para a Lista
                            </a>
                        </div>
                    </div>
                    <br>

                    {{-- ===== BLOCO: STATUS / PRIORIDADE ===== --}}
                    @if($atendimento->status_atual !== 'concluido')
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="text-sm font-bold text-gray-800 uppercase">Atualizar Status</h3>
                        </div>
                        <div class="p-4">
                            <form method="POST" action="{{ route('atendimentos.status.update', $atendimento) }}"
                                class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Novo
                                        Status</label>
                                    <select name="status"
                                        class="w-full border-gray-300 rounded-md text-sm focus:ring-blue-500">
                                        @foreach(['orcamento' => 'Orçamento', 'aberto' => 'Aberto', 'em_atendimento' =>
                                        'Em Atendimento', 'pendente_cliente' => 'Pendente Cliente',
                                        'pendente_fornecedor' => 'Pendente Fornecedor', 'garantia' => 'Garantia',
                                        'concluido' => 'Concluído'] as $v => $l)
                                        <option value="{{ $v }}" @selected($atendimento->status_atual === $v)>{{ $l }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-600 uppercase mb-1">Prioridade</label>
                                    <select name="prioridade" class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="baixa" @selected($atendimento->prioridade === 'baixa')>Baixa
                                        </option>
                                        <option value="media" @selected($atendimento->prioridade === 'media')>Média
                                        </option>
                                        <option value="alta" @selected($atendimento->prioridade === 'alta')>Alta
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Justificativa
                                        <span class="text-red-500">*</span></label>
                                    <textarea name="descricao" rows="2"
                                        class="w-full border-gray-300 rounded-md text-sm"
                                        placeholder="Obrigatório para mudar status..."></textarea>
                                </div>

                                <button type="submit"
                                    class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-md shadow transition">
                                    Atualizar Dados
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <br>

                    {{-- ===== BLOCO: DETALHES ===== --}}
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-tight">Detalhes do Chamado
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                                <p class="text-[10px] uppercase text-blue-500 font-bold">Cliente</p>
                                <p class="text-sm font-bold text-blue-900 leading-tight">
                                    {{ $atendimento->cliente?->nome ?? $atendimento->nome_solicitante }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <p class="text-[10px] uppercase text-gray-500 font-bold">Assunto</p>
                                <p class="text-sm font-medium text-gray-900">{{ $atendimento->assunto->nome }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <p class="text-[10px] uppercase text-gray-500 font-bold">Técnico</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $atendimento->funcionario?->nome ?? 'Aguardando Atribuição' }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>