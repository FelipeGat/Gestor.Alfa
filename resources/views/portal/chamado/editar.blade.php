<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                    Editar Atendimento #{{ $atendimento->id }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $cliente->nome_exibicao }}
                </p>
            </div>
            <a href="{{ route('portal.atendimentos') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline">Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper">
        <div class="portal-table-card overflow-hidden">
            <div class="portal-table-header">
                <h3 class="portal-table-title">Atualizar dados do atendimento</h3>
            </div>

            <form action="{{ route('portal.chamado.update', $atendimento) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="portal-filter-group">
                    <label for="assunto" class="portal-filter-label">Assunto</label>
                    <input
                        id="assunto"
                        name="assunto"
                        type="text"
                        required
                        maxlength="255"
                        value="{{ old('assunto', $assuntoAtual) }}"
                        class="portal-filter-input w-full"
                        placeholder="Ex.: Ar-condicionado parou de funcionar"
                    >
                    @error('assunto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="portal-filter-group">
                    <label class="portal-filter-label">Descrição do Problema</label>
                    <textarea name="descricao" rows="6" required
                        class="portal-filter-input w-full"
                        placeholder="Descreva detalhadamente o problema...">{{ old('descricao', $descricaoAtual) }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="portal-filter-group">
                        <label for="melhor_horario_contato" class="portal-filter-label">Melhor horário para contato</label>
                        <input
                            id="melhor_horario_contato"
                            name="melhor_horario_contato"
                            type="text"
                            maxlength="100"
                            value="{{ old('melhor_horario_contato', $melhorHorarioAtual) }}"
                            class="portal-filter-input w-full"
                            placeholder="Ex.: 09:00 às 11:00"
                        >
                        @error('melhor_horario_contato')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="portal-filter-group">
                        <label class="portal-filter-label">Prioridade</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="prioridade-group">
                            @php
                                $prioridadeSelecionada = old('prioridade', $atendimento->prioridade ?? 'media');
                            @endphp
                            @foreach(['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente'] as $valor => $label)
                                <label class="prioridade-option flex items-center justify-center p-3 border-2 rounded-full cursor-pointer transition-colors {{ $prioridadeSelecionada === $valor ? 'border-[#3f9cae] bg-[#3f9cae]/10' : 'border-gray-200 hover:border-[#3f9cae] hover:bg-[#3f9cae]/5' }}">
                                    <input type="radio" name="prioridade" value="{{ $valor }}" class="sr-only" {{ $prioridadeSelecionada === $valor ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('prioridade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="portal-filter-group">
                        <label for="novo_andamento" class="portal-filter-label">Novo andamento (opcional)</label>
                        <textarea
                            id="novo_andamento"
                            name="novo_andamento"
                            rows="4"
                            maxlength="3000"
                            class="portal-filter-input w-full"
                            placeholder="Ex.: Ajustei o horário para contato e segue foto do defeito atualizado.">{{ old('novo_andamento') }}</textarea>
                        @error('novo_andamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="portal-filter-group">
                        <label for="fotos_andamento" class="portal-filter-label">Anexar fotos do problema (opcional)</label>
                        <input
                            id="fotos_andamento"
                            name="fotos_andamento[]"
                            type="file"
                            multiple
                            accept="image/*"
                            class="portal-filter-input w-full file:mr-3 file:px-3 file:py-2 file:border-0 file:rounded-md file:bg-gray-100 file:text-gray-700"
                        >
                        <p class="text-xs text-gray-500 mt-1">Você pode enviar até 5 fotos. Formatos: JPG, PNG, WEBP. Máx. 5MB por foto.</p>
                        @error('fotos_andamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('fotos_andamento.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="portal-btn portal-btn--primary px-6 py-3">
                        Salvar alterações
                    </button>
                    <a href="{{ route('portal.atendimentos') }}" class="inline-flex items-center px-4 py-3 text-sm font-semibold text-gray-700 hover:text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                        Cancelar
                    </a>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-base font-semibold text-gray-900 mb-3">Histórico de anotações e fotos</h4>

                    @if($atendimento->andamentos && $atendimento->andamentos->count())
                        <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                            @foreach($atendimento->andamentos as $andamento)
                                <div class="rounded-lg border border-gray-200 bg-white p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-sm text-gray-800">{{ $andamento->descricao ?? '—' }}</p>
                                        <span class="text-xs text-gray-500 whitespace-nowrap">{{ $andamento->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>

                                    <p class="text-xs text-gray-500 mt-1">Por: {{ $andamento->user?->name ?? 'Sistema' }}</p>

                                    @if($andamento->fotos && $andamento->fotos->count())
                                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            @foreach($andamento->fotos as $foto)
                                                <a href="{{ $foto->arquivo_url }}" target="_blank" rel="noopener noreferrer" class="block rounded-md overflow-hidden border border-gray-200 hover:border-[#3f9cae] transition-colors">
                                                    <img src="{{ $foto->arquivo_url }}" alt="Foto do andamento" class="w-full h-24 object-cover">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Ainda não há andamentos registrados para este atendimento.</p>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('input[name="prioridade"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.prioridade-option').forEach(function(label) {
                    label.classList.remove('border-[#3f9cae]', 'bg-[#3f9cae]/10');
                    label.classList.add('border-gray-200');
                });

                this.closest('.prioridade-option')?.classList.remove('border-gray-200');
                this.closest('.prioridade-option')?.classList.add('border-[#3f9cae]', 'bg-[#3f9cae]/10');
            });
        });
    </script>
</x-app-layout>
