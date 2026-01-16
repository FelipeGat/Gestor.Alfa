<x-app-layout>

    @push('styles')
    @vite('resources/css/orcamentos/index.css')
    @endpush

    {{-- ================= HEADER ================= --}}
    <x-slot name="header">
        <h1 class="page-title">📄 Orçamentos</h1>
    </x-slot>

    {{-- ================= CONTEÚDO ================= --}}
    <div class="page-wrapper">

        {{-- ================= AÇÕES ================= --}}
        <div class="page-actions">
            <a href="{{ route('orcamentos.create') }}" class="btn btn-edit">
                ➕ Novo Orçamento
            </a>
        </div>

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
                                {{ $orcamento->cliente?->nome ?? '—' }}
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

                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('orcamentos.edit', $orcamento) }}" class="btn btn-edit">
                                        ✏️
                                    </a>

                                    <form action="{{ route('orcamentos.destroy', $orcamento) }}" method="POST"
                                        onsubmit="return confirm('Deseja excluir este orçamento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-delete">
                                            🗑️
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