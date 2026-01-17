<x-app-layout>

    @push('styles')
    @vite('resources/css/atendimentos/index.css')
    @endpush


    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <h1 class="page-title">📄 Orçamentos</h1>
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="page-wrapper">

        {{-- ================= AÇÕES ================= --}}
        <div class="page-actions flex justify-end">
            <a href="{{ route('orcamentos.create') }}" class="btn btn-success">
                <svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                        clip-rule="evenodd" />
                </svg>
                Novo Orçamento
            </a>
        </div>

        {{-- ================= ATENDIMENTOS AGUARDANDO ORÇAMENTO ================= --}}
        @if(isset($atendimentosParaOrcamento) && $atendimentosParaOrcamento->count())
        <div class="table-card mb-6 border-l-4 border-yellow-400 bg-yellow-50">
            <div class="table-wrapper">
                <h3 class="font-semibold mb-3 text-yellow-800">
                    🛠️ Atendimentos aguardando orçamento
                </h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Nº Atendimento</th>
                            <th>Cliente</th>
                            <th>Empresa</th>
                            <th>Data</th>
                            <th style="width:160px">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($atendimentosParaOrcamento as $atendimento)
                        <tr>
                            <td>#{{ $atendimento->numero_atendimento }}</td>

                            <td>
                                {{ $atendimento->cliente->nome ?? '—' }}
                            </td>

                            <td>
                                {{ $atendimento->empresa?->nome_fantasia ?? '—' }}
                            </td>

                            <td>
                                {{ $atendimento->created_at->format('d/m/Y') }}
                            </td>

                            <td>
                                <div class="page-actions flex justify-end">
                                    <a href="{{ route('orcamentos.create') }}" class="btn btn-pre">
                                        <svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Criar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif


        @if($orcamentos->count() > 0)
        <div class="table-card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Cliente</th>
                            <th>Empresa</th>
                            <th>Status</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th style="width:120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orcamentos as $orcamento)
                        <tr>
                            <td>{{ $orcamento->numero_orcamento }}</td>

                            <td>
                                {{ $orcamento->nome_cliente }}
                                @if($orcamento->pre_cliente_id)
                                <span class="text-xs text-orange-600 ml-1">(Pré)</span>
                                @endif
                            </td>

                            <td>
                                {{ $orcamento->empresa?->nome_fantasia ?? '—' }}
                            </td>

                            <td>
                                <span class="badge badge-{{ $orcamento->status }}">
                                    {{ ucfirst(str_replace('_',' ', $orcamento->status)) }}
                                </span>
                            </td>

                            <td>
                                @if($orcamento->valor_total)
                                R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                                @else
                                —
                                @endif
                            </td>

                            <td>
                                {{ $orcamento->created_at->format('d/m/Y') }}
                            </td>

                            <td style="text-align: center;">
                                <div class="table-actions">
                                    <a href="{{ route('orcamentos.edit', $orcamento) }}" class="btn btn-sm btn-edit">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                        Editar
                                    </a>

                                    <form action="{{ route('orcamentos.destroy', $orcamento) }}" method="POST"
                                        onsubmit="return confirm('Tem certeza que deseja excluir este cliente?')"
                                        style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-delete">
                                            <svg fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
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
        <div class="empty-state">
            <h3>Nenhum orçamento encontrado</h3>
            <p>Ainda não existem orçamentos cadastrados.</p>
        </div>
        @endif

    </div>

</x-app-layout>