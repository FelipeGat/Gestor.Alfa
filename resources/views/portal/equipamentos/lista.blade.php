<x-app-layout>
    @push('styles')
    @vite('resources/css/portal/index.css')
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900 leading-tight">Meus Ativos</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $cliente->nome_exibicao }}</p>
            </div>
            <a href="{{ route('portal.equipamentos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#3f9cae] hover:bg-[#2d7a8a] text-white text-sm font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="hidden sm:inline">Voltar</span>
            </a>
        </div>
    </x-slot>

    <div class="portal-wrapper">
        @php
            $temFiltros = !empty($filtrosAtivos) && count($filtrosAtivos) > 0;
            $totalResultados = method_exists($ativosTecnicos, 'total') ? $ativosTecnicos->total() : $ativosTecnicos->count();
            $statusAtivoAtual = request('status_ativo');
            $manutencaoAtual = request('manutencao_status');
            $buildUrl = fn ($status, $manutencao) => route('portal.ativos.index', array_filter([
                'status_ativo' => $status,
                'manutencao_status' => $manutencao,
            ], fn ($valor) => filled($valor)));
        @endphp

        <div class="mb-4 bg-white rounded-lg px-4 py-3 border border-gray-200 shadow-sm flex flex-col gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-semibold text-gray-700">Status do ativo:</span>
                <a href="{{ $buildUrl(null, $manutencaoAtual) }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ !$statusAtivoAtual ? 'bg-[#3f9cae]/10 text-[#2d7a8a] border-[#3f9cae]/30' : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100' }}">Todos</a>
                <a href="{{ $buildUrl('operando', $manutencaoAtual) }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusAtivoAtual === 'operando' ? 'bg-[#3f9cae]/10 text-[#2d7a8a] border-[#3f9cae]/30' : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100' }}">Operando</a>
                <a href="{{ $buildUrl('em_manutencao', $manutencaoAtual) }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusAtivoAtual === 'em_manutencao' ? 'bg-[#3f9cae]/10 text-[#2d7a8a] border-[#3f9cae]/30' : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100' }}">Em manutenção</a>
                <a href="{{ $buildUrl('inativo', $manutencaoAtual) }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusAtivoAtual === 'inativo' ? 'bg-[#3f9cae]/10 text-[#2d7a8a] border-[#3f9cae]/30' : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100' }}">Inativo</a>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-semibold text-gray-700">Saúde da manutenção:</span>
                <a href="{{ $buildUrl($statusAtivoAtual, null) }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ !$manutencaoAtual ? 'bg-[#3f9cae]/10 text-[#2d7a8a] border-[#3f9cae]/30' : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100' }}">Todos</a>
                <a href="{{ $buildUrl($statusAtivoAtual, 'em_dia') }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $manutencaoAtual === 'em_dia' ? 'bg-[#3f9cae]/10 text-[#2d7a8a] border-[#3f9cae]/30' : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100' }}">Em dia</a>
                <a href="{{ $buildUrl($statusAtivoAtual, 'atencao') }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $manutencaoAtual === 'atencao' ? 'bg-[#3f9cae]/10 text-[#2d7a8a] border-[#3f9cae]/30' : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100' }}">Atenção</a>
                <a href="{{ $buildUrl($statusAtivoAtual, 'vencida') }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $manutencaoAtual === 'vencida' ? 'bg-[#3f9cae]/10 text-[#2d7a8a] border-[#3f9cae]/30' : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100' }}">Vencida</a>
            </div>
        </div>

        @if(!empty($filtrosAtivos) && count($filtrosAtivos) > 0)
        <div class="mb-4 bg-white rounded-lg px-4 py-3 border border-[#3f9cae] shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-semibold text-gray-700">Filtros ativos:</span>
                @foreach($filtrosAtivos as $filtro)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[#3f9cae]/10 text-[#2d7a8a] border border-[#3f9cae]/30">{{ $filtro }}</span>
                @endforeach
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-300">{{ $totalResultados }} resultado(s)</span>
            </div>
            <a href="{{ route('portal.ativos.index') }}" class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-semibold rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all">
                Limpar filtro
            </a>
        </div>
        @endif

        @if($ativosTecnicos->count())
        <div class="portal-table-card overflow-hidden">
            <div class="portal-table-header">
                <h3 class="portal-table-title">
                    {{ $temFiltros ? 'Portal > Meus Ativos Filtrados' : 'Portal > Meus Ativos' }}
                    <span class="text-sm font-normal text-gray-600">({{ $totalResultados }})</span>
                </h3>
            </div>
            <div class="portal-table-wrapper">
                <table class="portal-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Modelo</th>
                            <th>Localização</th>
                            <th>Status</th>
                            <th>Última manutenção</th>
                            <th>Próxima manutenção</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ativosTecnicos as $equipamento)
                        <tr>
                            <td>{{ $equipamento->nome }}</td>
                            <td>{{ $equipamento->modelo ?: '-' }}</td>
                            <td>{{ $equipamento->localizacao_resumo }}</td>
                            <td>{{ $equipamento->status_ativo ? str_replace('_', ' ', ucfirst($equipamento->status_ativo)) : 'Não informado' }}</td>
                            <td>{{ $equipamento->ultima_manutencao?->format('d/m/Y') ?: '-' }}</td>
                            <td>{{ $equipamento->proxima_manutencao?->format('d/m/Y') ?: '-' }}</td>
                            <td>
                                <a href="{{ route('portal.ativos.show', $equipamento) }}" class="portal-btn portal-btn--primary">Ver Detalhes</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($ativosTecnicos, 'links'))
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $ativosTecnicos->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="portal-empty-state">
            <svg class="portal-empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
            <p class="portal-empty-state-title">Nenhum ativo técnico encontrado.</p>
            @if($temFiltros)
                <p class="portal-empty-state-text">Não há ativos para os filtros atuais. Use "Limpar filtro" para ver todos.</p>
            @else
                <p class="portal-empty-state-text">Os ativos aparecerão aqui quando forem vinculados ao seu cliente.</p>
            @endif
        </div>
        @endif
    </div>
</x-app-layout>
