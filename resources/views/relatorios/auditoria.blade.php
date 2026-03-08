<x-app-layout>
@push('styles')
<style>
/* ── Audit timeline ─────────────────────────────────────────── */
.audit-timeline {
    position: relative;
    padding-left: 2.5rem;
}
.audit-timeline::before {
    content: '';
    position: absolute;
    left: 0.875rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #6366f1, #8b5cf6 60%, #e5e7eb);
    border-radius: 9999px;
}
.audit-entry {
    position: relative;
    padding: 0.5rem 0 0.5rem 1.25rem;
    margin-bottom: 0.25rem;
}
.audit-dot {
    position: absolute;
    left: -2.065rem;
    top: 0.65rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 9999px;
    border: 2px solid white;
    box-shadow: 0 0 0 2px currentColor;
}
.badge-created  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.badge-updated  { background: #fef9c3; color: #713f12; border: 1px solid #fde047; }
.badge-deleted  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.badge-login    { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
.badge-logout   { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
.badge-default  { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }

.dot-created  { background: #22c55e; }
.dot-updated  { background: #eab308; }
.dot-deleted  { background: #ef4444; }
.dot-login    { background: #3b82f6; }
.dot-logout   { background: #9ca3af; }
.dot-default  { background: #8b5cf6; }

.user-block {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.dark .user-block {
    background: #1f2937;
    border-color: #374151;
}
.user-block-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}
.dark .user-block-header {
    background: #111827;
    border-color: #374151;
}
.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    flex-shrink: 0;
}
.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.875rem 1.25rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.dark .stat-card {
    background: #1f2937;
    border-color: #374151;
}
@media print {
    .no-print { display: none !important; }
    .audit-timeline::before { border-left: 2px solid #6366f1; }
    .user-block { break-inside: avoid; border: 1px solid #d1d5db; }
}
</style>
@endpush

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ══ CABEÇALHO ══════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Auditoria do Sistema
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Linha do tempo de todas as ações realizadas — {{ $dia->translatedFormat('l, d \\d\\e F \\d\\e Y') }}
            </p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Imprimir / PDF
        </button>
    </div>

    {{-- ══ FILTROS ══════════════════════════════════════════════════════ --}}
    <form method="GET" action="{{ route('relatorios.auditoria') }}" class="no-print">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-6 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                {{-- Filtro: Data --}}
                <div class="flex flex-col gap-1 min-w-[180px]">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Data</label>
                    <input type="date" name="data" value="{{ $data }}"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                {{-- Filtro: Usuário --}}
                <div class="flex flex-col gap-1 min-w-[220px]">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Usuário</label>
                    <select name="user_id" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Todos os usuários —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Ações --}}
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-colors">
                        Filtrar
                    </button>
                    @if($userId || $data !== now()->toDateString())
                    <a href="{{ route('relatorios.auditoria') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-semibold transition-colors">
                        Limpar
                    </a>
                    @endif
                    {{-- Navegação rápida de dias --}}
                    <a href="{{ route('relatorios.auditoria', ['data' => now()->subDay()->toDateString()]) }}"
                       class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-600 dark:text-gray-300 rounded-lg text-sm transition-colors" title="Dia anterior">
                        ‹ Ontem
                    </a>
                    <a href="{{ route('relatorios.auditoria', ['data' => now()->toDateString()]) }}"
                       class="px-3 py-2 {{ $data === now()->toDateString() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }} hover:bg-indigo-100 rounded-lg text-sm font-semibold transition-colors">
                        Hoje
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- ══ ESTATÍSTICAS DO DIA ══════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
        <div class="stat-card">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total de eventos</div>
        </div>
        <div class="stat-card border-l-4 border-emerald-400">
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['criados'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Inserções</div>
        </div>
        <div class="stat-card border-l-4 border-yellow-400">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['alterados'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Alterações</div>
        </div>
        <div class="stat-card border-l-4 border-red-400">
            <div class="text-2xl font-bold text-red-600">{{ $stats['deletados'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Exclusões</div>
        </div>
        <div class="stat-card border-l-4 border-blue-400">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['logins'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Acessos</div>
        </div>
        <div class="stat-card border-l-4 border-gray-400">
            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['logouts'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Saídas</div>
        </div>
    </div>

    {{-- ══ LEGENDA ══════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 text-xs no-print">
        <span class="font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Legenda:</span>
        <span class="badge-created px-2 py-0.5 rounded-full font-semibold">Inserção</span>
        <span class="badge-updated px-2 py-0.5 rounded-full font-semibold">Alteração</span>
        <span class="badge-deleted px-2 py-0.5 rounded-full font-semibold">Exclusão</span>
        <span class="badge-login px-2 py-0.5 rounded-full font-semibold">Login</span>
        <span class="badge-logout px-2 py-0.5 rounded-full font-semibold">Logout</span>
        <span class="badge-default px-2 py-0.5 rounded-full font-semibold">Outro</span>
    </div>

    {{-- ══ CONTEÚDO PRINCIPAL ═══════════════════════════════════════════ --}}
    @if($byUser->isEmpty())
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-12 text-center shadow-sm">
            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-400">Nenhuma atividade registrada</h3>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Não há registros de auditoria para o dia {{ $dia->format('d/m/Y') }}.</p>
        </div>
    @else
        {{-- Blocos por usuário --}}
        @foreach($byUser as $causerId => $userActivities)
        @php
            $firstActivity = $userActivities->first();
            $causerUser    = $firstActivity->causer;
            $userName      = $causerUser ? $causerUser->name : ($causerId === 'sistema' ? 'Sistema' : 'Usuário desconhecido');
            $userInitials  = collect(explode(' ', $userName))->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
            $countUser     = $userActivities->count();
        @endphp
        <div class="user-block">
            {{-- Header do bloco de usuário --}}
            <div class="user-block-header">
                <div class="user-avatar">{{ $userInitials }}</div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ $userName }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        @if($causerUser && $causerUser->email)
                            {{ $causerUser->email }} &bull;
                        @endif
                        {{ $countUser }} {{ $countUser === 1 ? 'evento' : 'eventos' }}
                        &bull; {{ $userActivities->first()->created_at->format('H:i') }} – {{ $userActivities->last()->created_at->format('H:i') }}
                    </div>
                </div>
                {{-- Mini contadores do usuário --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($userActivities->where('event', 'created')->count() > 0)
                        <span class="badge-created px-2 py-0.5 rounded-full text-xs font-bold">
                            +{{ $userActivities->where('event', 'created')->count() }}
                        </span>
                    @endif
                    @if($userActivities->where('event', 'updated')->count() > 0)
                        <span class="badge-updated px-2 py-0.5 rounded-full text-xs font-bold">
                            ~{{ $userActivities->where('event', 'updated')->count() }}
                        </span>
                    @endif
                    @if($userActivities->where('event', 'deleted')->count() > 0)
                        <span class="badge-deleted px-2 py-0.5 rounded-full text-xs font-bold">
                            -{{ $userActivities->where('event', 'deleted')->count() }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Timeline do usuário --}}
            <div class="p-4 sm:p-6">
                <div class="audit-timeline">
                    @foreach($userActivities->sortBy('created_at') as $activity)
                    @php
                        $event       = $activity->event ?? $activity->description;
                        $description = $activity->description;
                        $subjectType = $activity->subject_type;
                        $subjectId   = $activity->subject_id;

                        // Traduzir nome do modelo
                        $modelLabel = $modelLabels[$subjectType] ?? (
                            $subjectType
                                ? class_basename($subjectType)
                                : null
                        );

                        // Badge e dot baseados no evento e descrição
                        $badge = match(true) {
                            $description === 'login'                                        => 'login',
                            $description === 'logout'                                       => 'logout',
                            $event === 'created'                                            => 'created',
                            $event === 'updated' || str_contains($description,'renegoci')  => 'updated',
                            $event === 'deleted' || str_contains($description,'exclus')    => 'deleted',
                            default                                                         => 'default',
                        };

                        $label = match(true) {
                            $badge === 'created'                       => 'Inserção',
                            $badge === 'updated'
                                && str_contains($description,'renegoci') => 'Renegociação',
                            $badge === 'updated'                       => 'Alteração',
                            $badge === 'deleted'
                                && str_contains($description,'lote')   => 'Excl. Lote',
                            $badge === 'deleted'                       => 'Exclusão',
                            $badge === 'login'                         => 'Login',
                            $badge === 'logout'                        => 'Logout',
                            default                                    => ucfirst(
                                mb_strtolower(preg_replace('/\s+/',' ', $description) ?: $event ?: 'Ação')
                            ),
                        };

                        $actionText = match(true) {
                            $badge === 'login'                                         => 'entrou no sistema',
                            $badge === 'logout'                                        => 'saiu do sistema',
                            $badge === 'created'                                       => 'criou',
                            $badge === 'updated'
                                && str_contains($description,'renegoci')               => 'renegociou',
                            $badge === 'updated'                                       => 'alterou',
                            $badge === 'deleted'
                                && str_contains($description,'lote')                   => 'excluiu em lote',
                            $badge === 'deleted'                                       => 'excluiu',
                            default                                                    => $description ?: $event ?: 'executou ação em',
                        };

                        // Mostrar nota extra para operações que têm descrição customizada
                        $stdDescriptions = ['created','updated','deleted','login','logout'];
                        $extraNote = !in_array($description, $stdDescriptions) && !empty($description)
                                     && !in_array($badge, ['login','logout'])
                            ? $description
                            : null;

                        // Contexto de propriedades relevante
                        $props = $activity->properties ?? collect();
                        $propsArr = is_array($props) ? $props : (is_object($props) ? $props->toArray() : []);
                        $totalLote = $propsArr['total'] ?? null;
                    @endphp
                    <div class="audit-entry">
                        <div class="audit-dot dot-{{ $badge }}"></div>
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Horário --}}
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-mono w-10 flex-shrink-0">
                                {{ $activity->created_at->format('H:i') }}
                            </span>
                            {{-- Badge de tipo --}}
                            <span class="badge-{{ $badge }} px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0">
                                {{ $label }}
                            </span>
                            {{-- Ação + modelo + ID --}}
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $actionText }}
                                @if($modelLabel)
                                    <span class="font-semibold">{{ $modelLabel }}</span>
                                @endif
                                @if($subjectId)
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">#{{ $subjectId }}</span>
                                @endif
                                @if($totalLote)
                                    <span class="text-red-500 dark:text-red-400 text-xs font-semibold"> ({{ $totalLote }} registros)</span>
                                @endif
                            </span>
                        </div>
                        {{-- Nota extra com descrição personalizada (ex: "exclusão em lote — cobranças do contrato") --}}
                        @if($extraNote)
                        <div class="mt-0.5 ml-12 text-xs text-gray-400 dark:text-gray-500 italic">
                            {{ $extraNote }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    @endif

    {{-- ══ RODAPÉ DA AUDITORIA ═══════════════════════════════════════════ --}}
    <div class="mt-6 text-center text-xs text-gray-400 dark:text-gray-600 no-print">
        Relatório gerado em {{ now()->format('d/m/Y H:i:s') }} por {{ Auth::user()->name }}.
        Todos os eventos são registrados automaticamente e não podem ser alterados.
    </div>
</div>
</x-app-layout>
