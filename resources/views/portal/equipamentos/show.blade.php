<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">{{ $equipamento->nome }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $cliente->nome_exibicao }}</p>
            </div>
            <a href="{{ route('portal.ativos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper space-y-6">
        <div class="portal-table-card">
            <div class="portal-table-header"><h3 class="portal-table-title">Dados Básicos</h3></div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <p><strong>Nome:</strong> {{ $equipamento->nome }}</p>
                <p><strong>Modelo:</strong> {{ $equipamento->modelo ?: '-' }}</p>
                <p><strong>Fabricante:</strong> {{ $equipamento->fabricante ?: '-' }}</p>
                <p><strong>Número de série:</strong> {{ $equipamento->numero_serie ?: '-' }}</p>
                <p><strong>Código do ativo:</strong> {{ $equipamento->codigo_ativo ?: '-' }}</p>
                <p><strong>TAG patrimonial:</strong> {{ $equipamento->tag_patrimonial ?: '-' }}</p>
            </div>
        </div>

        <div class="portal-table-card">
            <div class="portal-table-header"><h3 class="portal-table-title">Localização</h3></div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <p><strong>Unidade:</strong> {{ $equipamento->unidade ?: '-' }}</p>
                <p><strong>Setor:</strong> {{ $equipamento->setor->nome ?? '-' }}</p>
                <p><strong>Sala:</strong> {{ $equipamento->sala ?: '-' }}</p>
                <p><strong>Andar:</strong> {{ $equipamento->andar ?: '-' }}</p>
                <p class="md:col-span-2"><strong>Localização detalhada:</strong> {{ $equipamento->localizacao_detalhada ?: '-' }}</p>
            </div>
        </div>

        <div class="portal-table-card">
            <div class="portal-table-header"><h3 class="portal-table-title">Especificações</h3></div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <p><strong>Capacidade:</strong> {{ $equipamento->capacidade ?: '-' }}</p>
                <p><strong>Potência:</strong> {{ $equipamento->potencia ?: '-' }}</p>
                <p><strong>Voltagem:</strong> {{ $equipamento->voltagem ?: '-' }}</p>
                <p><strong>Vida útil:</strong> {{ $equipamento->vida_util_anos ? $equipamento->vida_util_anos.' anos' : '-' }}</p>
            </div>
        </div>

        <div class="portal-table-card">
            <div class="portal-table-header"><h3 class="portal-table-title">Manutenção</h3></div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <p><strong>Última manutenção:</strong> {{ $equipamento->ultima_manutencao?->format('d/m/Y') ?: '-' }}</p>
                <p><strong>Última limpeza:</strong> {{ $equipamento->ultima_limpeza?->format('d/m/Y') ?: '-' }}</p>
                <p><strong>Próxima manutenção:</strong> {{ $equipamento->proxima_manutencao?->format('d/m/Y') ?: '-' }}</p>
                <p><strong>Custo total de manutenção:</strong> R$ {{ number_format((float) $equipamento->custo_total_manutencao, 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="portal-table-card">
            <div class="portal-table-header"><h3 class="portal-table-title">Status e Garantia</h3></div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <p><strong>Status atual:</strong> {{ $equipamento->status_ativo ? str_replace('_', ' ', ucfirst($equipamento->status_ativo)) : '-' }}</p>
                <p><strong>Criticidade:</strong> {{ $equipamento->criticidade ? ucfirst($equipamento->criticidade) : '-' }}</p>
                <p><strong>Garantia início:</strong> {{ $equipamento->garantia_inicio?->format('d/m/Y') ?: '-' }}</p>
                <p><strong>Garantia fim:</strong> {{ $equipamento->garantia_fim?->format('d/m/Y') ?: '-' }}</p>
            </div>
        </div>

        <div class="portal-table-card">
            <div class="portal-table-header"><h3 class="portal-table-title">Documentos</h3></div>
            <div class="p-6">
                @if($equipamento->documentos->count())
                    <div class="overflow-x-auto">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipamento->documentos as $doc)
                                <tr>
                                    <td>{{ $doc->nome_documento }}</td>
                                    <td>{{ strtoupper(str_replace('_', ' ', $doc->tipo_documento)) }}</td>
                                    <td>{{ $doc->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhum documento anexado.</p>
                @endif
            </div>
        </div>

        <div class="portal-table-card">
            <div class="portal-table-header"><h3 class="portal-table-title">Histórico de Manutenção</h3></div>
            <div class="p-6">
                @if($equipamento->historicoManutencoes->count())
                    <div class="overflow-x-auto">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Técnico</th>
                                    <th>Custo</th>
                                    <th>Peças</th>
                                    <th>Tempo parado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipamento->historicoManutencoes as $hist)
                                <tr>
                                    <td>{{ $hist->data_manutencao?->format('d/m/Y') }}</td>
                                    <td>{{ ucfirst($hist->tipo) }}</td>
                                    <td>{{ $hist->descricao ?: '-' }}</td>
                                    <td>{{ $hist->tecnico_responsavel ?: '-' }}</td>
                                    <td>{{ $hist->custo ? 'R$ '.number_format((float) $hist->custo, 2, ',', '.') : '-' }}</td>
                                    <td>{{ $hist->pecas_trocadas ?: '-' }}</td>
                                    <td>{{ $hist->tempo_parado_horas ? $hist->tempo_parado_horas.'h' : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhum histórico de manutenção encontrado.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
