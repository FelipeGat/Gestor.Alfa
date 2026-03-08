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
            $userTipo      = $causerUser?->tipo ?? null;
            $tipoLabel     = match($userTipo) {
                'admin'          => 'Administrador',
                'administrativo' => 'Administrativo',
                'financeiro'     => 'Financeiro',
                'comercial'      => 'Comercial',
                'funcionario'    => 'Técnico',
                'cliente'        => 'Cliente',
                default          => null,
            };
            // Detectar acesso via app mobile neste bloco
            $viaMobile = $userActivities->contains(fn($a) =>
                ($a->properties['via'] ?? null) === 'app_mobile'
            );
        @endphp
        <div class="user-block">
            {{-- Header do bloco de usuário --}}
            <div class="user-block-header">
                <div class="user-avatar">{{ $userInitials }}</div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-gray-900 dark:text-white text-sm truncate flex items-center gap-2">
                        {{ $userName }}
                        @if($tipoLabel)
                        <span class="text-xs font-normal px-1.5 py-0.5 rounded-full
                            {{ $userTipo === 'cliente' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' :
                               ($userTipo === 'funcionario' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' :
                               'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300') }}">
                            {{ $tipoLabel }}
                        </span>
                        @endif
                        @if($viaMobile)
                        <span class="text-xs font-normal px-1.5 py-0.5 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300" title="Acesso via App Mobile">
                            App Mobile
                        </span>
                        @endif
                    </div>
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
                            $subjectType ? class_basename($subjectType) : null
                        );

                        // Propriedades brutas
                        $props    = $activity->properties ?? collect();
                        $propsArr = is_array($props) ? $props : (is_object($props) ? $props->toArray() : []);
                        $totalLote = $propsArr['total'] ?? null;

                        // Badge e dot baseados no evento e descrição
                        $badge = match(true) {
                            $description === 'login'                                          => 'login',
                            $description === 'logout'                                         => 'logout',
                            $event === 'created'                                              => 'created',
                            $event === 'updated' || str_contains($description, 'renegoci')   => 'updated',
                            $event === 'deleted' || str_contains($description, 'exclus')     => 'deleted',
                            default                                                           => 'default',
                        };

                        $label = match(true) {
                            $badge === 'created'                                              => 'Inserção',
                            $badge === 'updated' && str_contains($description, 'renegoci')   => 'Renegociação',
                            $badge === 'updated' && str_contains($description, 'inici')      => 'Início',
                            $badge === 'updated' && str_contains($description, 'finaliz')    => 'Finalização',
                            $badge === 'updated' && str_contains($description, 'paus')       => 'Pausa',
                            $badge === 'updated' && str_contains($description, 'retom')      => 'Retomada',
                            $badge === 'updated' && str_contains($description, 'andamento')  => 'Andamento',
                            $badge === 'updated'                                              => 'Alteração',
                            $badge === 'deleted' && str_contains($description, 'lote')       => 'Excl. Lote',
                            $badge === 'deleted'                                              => 'Exclusão',
                            $badge === 'login'                                                => 'Login',
                            $badge === 'logout'                                               => 'Logout',
                            default => ucfirst(mb_strtolower(preg_replace('/\s+/', ' ', $description) ?: $event ?: 'Ação')),
                        };

                        $actionText = match(true) {
                            $badge === 'login'                                               => 'entrou no sistema',
                            $badge === 'logout'                                              => 'saiu do sistema',
                            $badge === 'created'                                             => 'criou',
                            $badge === 'updated' && str_contains($description, 'renegoci')  => 'renegociou',
                            $badge === 'updated' && str_contains($description, 'inici')     => 'iniciou atendimento em',
                            $badge === 'updated' && str_contains($description, 'finaliz')   => 'finalizou atendimento em',
                            $badge === 'updated' && str_contains($description, 'paus')      => 'pausou atendimento em',
                            $badge === 'updated' && str_contains($description, 'retom')     => 'retomou atendimento em',
                            $badge === 'updated' && str_contains($description, 'andamento') => 'adicionou andamento em',
                            $badge === 'updated'                                             => 'alterou',
                            $badge === 'deleted' && str_contains($description, 'lote')      => 'excluiu em lote',
                            $badge === 'deleted'                                             => 'excluiu',
                            default => $description ?: $event ?: 'executou ação em',
                        };

                        // Nota extra para descrições customizadas
                        $stdDescriptions = ['created','updated','deleted','login','logout'];
                        $extraNote = !in_array($description, $stdDescriptions) && !empty($description)
                                     && !in_array($badge, ['login','logout'])
                            ? $description : null;

                        // ── Mapeamentos para diff de campos ─────────────────────
                        $fieldLabels2 = [
                            'status'           => 'Status',
                            'status_atual'     => 'Status',
                            'valor'            => 'Valor',
                            'juros_multa'      => 'Juros/Multa',
                            'desconto'         => 'Desconto',
                            'data_vencimento'  => 'Vencimento',
                            'data_pagamento'   => 'Pagamento',
                            'pago_em'          => 'Pago em',
                            'forma_pagamento'  => 'Forma Pgto',
                            'prioridade'       => 'Prioridade',
                            'descricao'        => 'Descrição',
                            'nome'             => 'Nome',
                            'nome_fantasia'    => 'Nome Fantasia',
                            'email'            => 'E-mail',
                            'ativo'            => 'Ativo',
                            'iniciado_em'      => 'Iniciado em',
                            'finalizado_em'    => 'Finalizado em',
                            'em_execucao'      => 'Em execução',
                            'em_pausa'         => 'Em pausa',
                            'tipo'             => 'Tipo',
                            'dia_vencimento'   => 'Dia venc.',
                            'valor_mensal'     => 'Valor mensal',
                            'observacoes'      => 'Observações',
                            'tipo_pessoa'      => 'Tipo pessoa',
                            'tipo_cliente'     => 'Tipo cliente',
                            'periodicidade'    => 'Periodicidade',
                            // Ponto
                            'data_referencia'           => 'Data',
                            'entrada_em'                => 'Entrada',
                            'saida_em'                  => 'Saída',
                            'intervalo_inicio_em'       => 'Início Intervalo',
                            'intervalo_fim_em'          => 'Fim Intervalo',
                            'registrado_fora_atendimento' => 'Fora do Atendimento',
                        ];
                        $valueMappings2 = [
                            'a_vencer'       => 'A Vencer',   'vencido'        => 'Vencido',
                            'pago'           => 'Pago',       'em_aberto'      => 'Em Aberto',
                            'vence_hoje'     => 'Vence Hoje', 'pendente'       => 'Pendente',
                            'cancelado'      => 'Cancelado',  'aberto'         => 'Aberto',
                            'em_atendimento' => 'Em Atendimento',
                            'finalizacao'    => 'Em Finalização',
                            'concluido'      => 'Concluído',  'agendado'       => 'Agendado',
                            '1'              => 'Sim',        '0'              => 'Não',
                            'true'           => 'Sim',        'false'          => 'Não',
                            'baixa'          => 'Baixa',      'media'          => 'Média',
                            'alta'           => 'Alta',       'urgente'        => 'Urgente',
                            'boleto'         => 'Boleto',     'pix'            => 'PIX',
                            'cartao'         => 'Cartão',     'dinheiro'       => 'Dinheiro',
                            'transferencia'  => 'Transferência','debito'       => 'Débito',
                            'credito'        => 'Crédito',    'PF'             => 'Pessoa Física',
                            'PJ'             => 'Pessoa Jurídica',
                            'CONTRATO'       => 'Contrato',   'AVULSO'         => 'Avulso',
                            'mensal'         => 'Mensal',     'trimestral'     => 'Trimestral',
                            'semestral'      => 'Semestral',  'anual'          => 'Anual',
                        ];
                        $fmtVal = function($k, $v) use ($valueMappings2) {
                            if ($v === null || $v === '') return '—';
                            $s = (string)$v;
                            if (isset($valueMappings2[$s])) return $valueMappings2[$s];
                            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $s)) {
                                try { $dt = \Carbon\Carbon::parse($s);
                                      return strlen($s) > 10 ? $dt->format('d/m/Y H:i') : $dt->format('d/m/Y');
                                } catch (\Exception $e) {}
                            }
                            if (in_array($k, ['valor','juros_multa','desconto','valor_mensal']) && is_numeric($v)) {
                                return 'R$ ' . number_format((float)$v, 2, ',', '.');
                            }
                            return \Illuminate\Support\Str::limit($s, 40);
                        };
                        $ignoreFields2 = [
                            'updated_at','created_at','deleted_at','remember_token','password',
                            'iniciado_por_user_id','finalizado_por_user_id','user_id','cliente_id',
                            'conta_financeira_id','orcamento_id','conta_fixa_id','conta_fixa_pagar_id',
                            'assinatura_cliente_path','assinatura_cliente_nome','assinatura_cliente_cargo',
                            'tempo_execucao_segundos','tempo_pausa_segundos','atendimento_id',
                        ];

                        $fieldDiffs    = [];
                        $createdFields = [];
                        $deletedFields = [];

                        $oldProps = $propsArr['old'] ?? [];
                        $newProps = $propsArr['attributes'] ?? [];

                        if (!empty($oldProps) && !empty($newProps)) {
                            foreach ($fieldLabels2 as $k => $fLbl) {
                                if (in_array($k, $ignoreFields2)) continue;
                                if (!array_key_exists($k, $oldProps) || !array_key_exists($k, $newProps)) continue;
                                if ((string)$oldProps[$k] === (string)$newProps[$k]) continue;
                                $fieldDiffs[] = [$fLbl, $fmtVal($k, $oldProps[$k]), $fmtVal($k, $newProps[$k])];
                            }
                        } elseif ($event === 'created' && !empty($newProps)) {
                            $showCreate = ['status','status_atual','valor','data_vencimento','descricao','prioridade','forma_pagamento','nome','tipo'];
                            foreach ($showCreate as $k) {
                                if (!isset($newProps[$k]) || $newProps[$k] === null || $newProps[$k] === '') continue;
                                if (!isset($fieldLabels2[$k])) continue;
                                $createdFields[] = [$fieldLabels2[$k], $fmtVal($k, $newProps[$k])];
                            }
                        } elseif ($event === 'deleted' && !empty($oldProps)) {
                            $showDelete = ['descricao','nome','valor','status','status_atual','data_vencimento'];
                            foreach ($showDelete as $k) {
                                if (!isset($oldProps[$k]) || $oldProps[$k] === null) continue;
                                if (!isset($fieldLabels2[$k])) continue;
                                $deletedFields[] = [$fieldLabels2[$k], $fmtVal($k, $oldProps[$k])];
                            }
                        }

                        // Para andamentos (created + descricao na propriedade)
                        $andamentoNota = null;
                        if (str_contains($subjectType ?? '', 'Andamento') && !empty($newProps['descricao'])) {
                            $andamentoNota = \Illuminate\Support\Str::limit($newProps['descricao'], 120);
                        }
                        // Nota de andamento via properties customizadas
                        if (!$andamentoNota && !empty($propsArr['nota'])) {
                            $andamentoNota = \Illuminate\Support\Str::limit($propsArr['nota'], 120);
                        }
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
                        {{-- Diffs de campos: updated --}}
                        @if(!empty($fieldDiffs))
                        <div class="ml-12 mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($fieldDiffs as [$fLbl, $oldV, $newV])
                            <span class="inline-flex items-center gap-1 text-xs bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/50 rounded-md px-2 py-0.5">
                                <span class="font-medium text-gray-500 dark:text-gray-400">{{ $fLbl }}:</span>
                                <span class="text-red-400 dark:text-red-300 line-through opacity-75">{{ $oldV }}</span>
                                <span class="text-gray-400 mx-0.5">→</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $newV }}</span>
                            </span>
                            @endforeach
                        </div>
                        @endif
                        {{-- Campos de criação --}}
                        @if(!empty($createdFields))
                        <div class="ml-12 mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($createdFields as [$fLbl, $val])
                            <span class="inline-flex items-center gap-1 text-xs bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/50 rounded-md px-2 py-0.5">
                                <span class="font-medium text-gray-500 dark:text-gray-400">{{ $fLbl }}:</span>
                                <span class="text-emerald-700 dark:text-emerald-300 font-medium">{{ $val }}</span>
                            </span>
                            @endforeach
                        </div>
                        @endif
                        {{-- Campos de exclusão --}}
                        @if(!empty($deletedFields))
                        <div class="ml-12 mt-1.5 flex flex-wrap gap-1.5">
                            @foreach($deletedFields as [$fLbl, $val])
                            <span class="inline-flex items-center gap-1 text-xs bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-md px-2 py-0.5">
                                <span class="font-medium text-gray-500 dark:text-gray-400">{{ $fLbl }}:</span>
                                <span class="text-red-500 dark:text-red-300 line-through">{{ $val }}</span>
                            </span>
                            @endforeach
                        </div>
                        @endif
                        {{-- Nota de andamento --}}
                        @if($andamentoNota)
                        <div class="mt-1 ml-12 text-xs text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-700/40 rounded px-2 py-1 border-l-2 border-gray-300 dark:border-gray-600">
                            "{{ $andamentoNota }}"
                        </div>
                        @endif
                        {{-- Nota extra com descrição personalizada --}}
                        @if($extraNote && !$andamentoNota)
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
