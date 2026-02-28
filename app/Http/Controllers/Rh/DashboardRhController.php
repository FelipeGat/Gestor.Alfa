<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Afastamento;
use App\Models\Atendimento;
use App\Models\Ferias;
use App\Models\Funcionario;
use App\Models\FuncionarioDocumento;
use App\Models\FuncionarioEpi;
use App\Models\FuncionarioJornada;
use App\Models\Jornada;
use App\Models\RegistroPontoPortal;
use App\Models\RhAjustePonto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardRhController extends Controller
{
    public function index(Request $request)
    {
        [$inicioPeriodo, $fimPeriodo, $filtros, $filtrosQuery] = $this->resolverPeriodo($request);

        $hoje = $fimPeriodo->copy()->startOfDay();
        $limite = $hoje->copy()->addDays(30);

        $data = $this->montarDadosDashboard($hoje, $limite, $inicioPeriodo, $fimPeriodo);

        return view('rh.dashboard', [
            'funcionariosAtivos' => $data['resumo']['funcionarios_ativos'],
            'documentosVencendo' => $data['risco']['documentos_vencendo_30']['count'],
            'episVencendo' => $data['risco']['epi_vencido']['count'],
            'feriasVencidas' => $data['risco']['ferias_vencidas']['count'],
            'bancoHorasSegundos' => (int) $data['resumo']['banco_horas_segundos'],
            'monitoramento' => $data['monitoramento'],
            'performance' => $data['performance'],
            'risco' => $data['risco'],
            'filtros' => $filtros,
            'filtrosQuery' => $filtrosQuery,
            'periodoLabel' => $inicioPeriodo->format('d/m/Y') . ' até ' . $fimPeriodo->format('d/m/Y'),
        ]);
    }

    public function relatorio(Request $request, string $indicador)
    {
        [$inicioPeriodo, $fimPeriodo, $filtros, $filtrosQuery] = $this->resolverPeriodo($request);

        $hoje = $fimPeriodo->copy()->startOfDay();
        $limite = $hoje->copy()->addDays(30);
        $data = $this->montarDadosDashboard($hoje, $limite, $inicioPeriodo, $fimPeriodo);

        $relatorios = [
            ...$data['monitoramento'],
            ...$data['performance'],
            ...$data['risco'],
        ];

        if (!Arr::has($relatorios, $indicador)) {
            abort(404);
        }

        $item = $relatorios[$indicador];

        return view('rh.dashboard-relatorio', [
            'titulo' => $item['title'],
            'descricao' => $item['description'] ?? null,
            'count' => $item['count'] ?? 0,
            'columns' => $item['columns'] ?? [],
            'rows' => $item['rows'] ?? [],
            'updatedAt' => now(),
            'filtros' => $filtros,
            'filtrosQuery' => $filtrosQuery,
            'indicador' => $indicador,
            'periodoLabel' => $inicioPeriodo->format('d/m/Y') . ' até ' . $fimPeriodo->format('d/m/Y'),
        ]);
    }

    private function resolverPeriodo(Request $request): array
    {
        $validated = $request->validate([
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date'],
        ]);

        $inicioInformado = $validated['inicio'] ?? null;
        $fimInformado = $validated['fim'] ?? null;

        $inicio = $inicioInformado
            ? Carbon::parse($inicioInformado)->startOfDay()
            : Carbon::today()->startOfMonth();

        $fim = $fimInformado
            ? Carbon::parse($fimInformado)->endOfDay()
            : Carbon::today()->endOfMonth();

        if ($inicio->gt($fim)) {
            [$inicio, $fim] = [$fim->copy()->startOfDay(), $inicio->copy()->endOfDay()];
        }

        $filtros = [
            'inicio' => $inicio->toDateString(),
            'fim' => $fim->toDateString(),
        ];

        $filtrosQuery = array_filter([
            'inicio' => $inicioInformado,
            'fim' => $fimInformado,
        ]);

        return [$inicio, $fim, $filtros, $filtrosQuery];
    }

    private function montarDadosDashboard(Carbon $hoje, Carbon $limite, Carbon $inicioPeriodo, Carbon $fimPeriodo): array
    {
        $inicioPeriodo = $inicioPeriodo->copy()->startOfDay();
        $fimPeriodo = $fimPeriodo->copy()->endOfDay();

        $funcionariosAtivos = Funcionario::query()
            ->where('ativo', true)
            ->get(['id', 'nome']);

        $funcionariosAtivosIds = $funcionariosAtivos->pluck('id')->all();
        $funcionariosComJornadaAtiva = $this->funcionariosComJornadaAtiva($hoje);

        $monitoramento = $this->montarMonitoramento($hoje, $inicioPeriodo, $fimPeriodo, $funcionariosAtivos, $funcionariosAtivosIds, $funcionariosComJornadaAtiva);
        $performance = $this->montarPerformance($inicioPeriodo, $fimPeriodo);
        $risco = $this->montarRisco($hoje, $limite, $funcionariosAtivos, $funcionariosComJornadaAtiva);

        $totaisBancoHoras = Atendimento::query()
            ->whereNotNull('funcionario_id')
            ->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])
            ->selectRaw('COALESCE(SUM(tempo_execucao_segundos), 0) as total_segundos')
            ->value('total_segundos') ?? 0;

        return [
            'resumo' => [
                'funcionarios_ativos' => $funcionariosAtivos->count(),
                'banco_horas_segundos' => (int) $totaisBancoHoras,
            ],
            'monitoramento' => $monitoramento,
            'performance' => $performance,
            'risco' => $risco,
        ];
    }

    private function montarMonitoramento(Carbon $hoje, Carbon $inicioMes, Carbon $fimMes, Collection $funcionariosAtivos, array $funcionariosAtivosIds, array $funcionariosComJornadaAtiva): array
    {
        $baseFaltasIds = !empty($funcionariosComJornadaAtiva) ? $funcionariosComJornadaAtiva : $funcionariosAtivosIds;

        $usaRegistroLegal = Schema::hasTable('registro_pontos_portal')
            && Schema::hasTable('funcionario_jornadas')
            && Schema::hasTable('jornadas');

        if ($usaRegistroLegal) {
            $jornadasHoje = $this->jornadasVigentesPorFuncionarioNoDia($hoje, $baseFaltasIds);
            $registrosHoje = RegistroPontoPortal::query()
                ->whereIn('funcionario_id', array_keys($jornadasHoje))
                ->whereDate('data_referencia', $hoje->toDateString())
                ->get()
                ->keyBy('funcionario_id');

            $faltasHoje = $funcionariosAtivos
                ->whereIn('id', array_keys($jornadasHoje))
                ->reject(function (Funcionario $funcionario) use ($registrosHoje) {
                    $registro = $registrosHoje->get($funcionario->id);

                    return (bool) ($registro?->entrada_em);
                })
                ->values();

            $atrasosHoje = collect($jornadasHoje)
                ->map(function (FuncionarioJornada $vinculo, int $funcionarioId) use ($registrosHoje, $hoje) {
                    $registro = $registrosHoje->get($funcionarioId);
                    if (!$registro || !$registro->entrada_em || !$vinculo->jornada) {
                        return null;
                    }

                    $horaEntrada = $vinculo->jornada->hora_entrada_padrao ?: $vinculo->jornada->hora_inicio;
                    if (!$horaEntrada) {
                        return null;
                    }

                    $previsto = Carbon::parse($hoje->toDateString() . ' ' . $horaEntrada);
                    $registrado = Carbon::parse($registro->entrada_em);
                    $atrasoMin = max(0, $registrado->diffInMinutes($previsto, false));

                    if ($atrasoMin <= 0) {
                        return null;
                    }

                    return [
                        'funcionario' => optional($vinculo->funcionario)->nome ?? '—',
                        'previsto' => $previsto,
                        'registrado' => $registrado,
                        'atraso_minutos' => $atrasoMin,
                    ];
                })
                ->filter()
                ->sortByDesc('atraso_minutos')
                ->values();

            $saidasAntecipadas = collect($jornadasHoje)
                ->map(function (FuncionarioJornada $vinculo, int $funcionarioId) use ($registrosHoje, $hoje) {
                    $registro = $registrosHoje->get($funcionarioId);
                    if (!$registro || !$registro->saida_em || !$vinculo->jornada) {
                        return null;
                    }

                    $horaEntrada = $vinculo->jornada->hora_entrada_padrao ?: $vinculo->jornada->hora_inicio;
                    $horaSaida = $vinculo->jornada->hora_saida_padrao ?: $vinculo->jornada->hora_fim;
                    if (!$horaEntrada || !$horaSaida) {
                        return null;
                    }

                    $previsto = Carbon::parse($hoje->toDateString() . ' ' . $horaSaida);
                    if ($horaSaida <= $horaEntrada) {
                        $previsto->addDay();
                    }

                    $registrado = Carbon::parse($registro->saida_em);
                    if ($registrado->gte($previsto)) {
                        return null;
                    }

                    return [
                        'funcionario' => optional($vinculo->funcionario)->nome ?? '—',
                        'previsto' => $previsto,
                        'registrado' => $registrado,
                        'antecipacao_minutos' => $previsto->diffInMinutes($registrado),
                    ];
                })
                ->filter()
                ->sortByDesc('antecipacao_minutos')
                ->values();
        } else {
            $registrosHojeIds = Atendimento::query()
                ->whereNotNull('funcionario_id')
                ->whereDate('created_at', $hoje)
                ->pluck('funcionario_id')
                ->unique()
                ->values()
                ->all();

            $faltasHoje = $funcionariosAtivos
                ->whereIn('id', $baseFaltasIds)
                ->reject(fn (Funcionario $funcionario) => in_array($funcionario->id, $registrosHojeIds, true))
                ->values();

            $atrasosHoje = Atendimento::query()
                ->with('funcionario:id,nome')
                ->whereNotNull('funcionario_id')
                ->whereNotNull('data_inicio_agendamento')
                ->whereNotNull('iniciado_em')
                ->whereDate('data_inicio_agendamento', $hoje)
                ->whereColumn('iniciado_em', '>', 'data_inicio_agendamento')
                ->orderByDesc('iniciado_em')
                ->get();

            $saidasAntecipadas = Atendimento::query()
                ->with('funcionario:id,nome')
                ->whereNotNull('funcionario_id')
                ->whereNotNull('data_fim_agendamento')
                ->whereNotNull('finalizado_em')
                ->whereDate('data_fim_agendamento', $hoje)
                ->whereColumn('finalizado_em', '<', 'data_fim_agendamento')
                ->orderByDesc('finalizado_em')
                ->get();
        }

        $atestadosMesRows = [];
        if (Schema::hasTable('afastamentos')) {
            $atestadosMesRows = Afastamento::query()
                ->with('funcionario:id,nome')
                ->whereRaw('LOWER(tipo) like ?', ['%atestado%'])
                ->whereBetween('data_inicio', [$inicioMes->toDateString(), $fimMes->toDateString()])
                ->orderByDesc('data_inicio')
                ->get();
        }

        $bancoHorasFuncionarios = $this->calcularBancoHorasPorFuncionario($inicioMes, $fimMes, $funcionariosAtivosIds);

        $bancoPositivo = collect($bancoHorasFuncionarios)
            ->filter(fn (array $item) => $item['saldo_segundos'] > 20 * 3600)
            ->sortByDesc('saldo_segundos')
            ->values();

        $bancoNegativo = collect($bancoHorasFuncionarios)
            ->filter(fn (array $item) => $item['saldo_segundos'] < -20 * 3600)
            ->sortBy('saldo_segundos')
            ->values();

        return [
            'faltas_hoje' => [
                'title' => 'Faltas Hoje',
                'description' => 'Funcionários ativos sem registro de ponto no dia.',
                'count' => $faltasHoje->count(),
                'columns' => ['Funcionário'],
                'rows' => $faltasHoje->map(fn (Funcionario $funcionario) => [
                    $funcionario->nome,
                ])->all(),
            ],
            'atrasos_hoje' => [
                'title' => 'Atrasos Hoje',
                'description' => 'Comparação entre início previsto e início registrado.',
                'count' => $atrasosHoje->count(),
                'columns' => $usaRegistroLegal
                    ? ['Funcionário', 'Entrada Prevista', 'Entrada Registrada', 'Atraso']
                    : ['Atendimento', 'Funcionário', 'Previsto', 'Registrado', 'Atraso'],
                'rows' => $usaRegistroLegal
                    ? $atrasosHoje->map(fn (array $item) => [
                        $item['funcionario'],
                        $item['previsto']->format('d/m/Y H:i'),
                        $item['registrado']->format('d/m/Y H:i'),
                        $item['atraso_minutos'] . ' min',
                    ])->all()
                    : $atrasosHoje->map(function (Atendimento $atendimento) {
                        $minutosAtraso = max(0, Carbon::parse($atendimento->data_inicio_agendamento)->diffInMinutes(Carbon::parse($atendimento->iniciado_em)));

                        return [
                            $atendimento->numero_atendimento,
                            optional($atendimento->funcionario)->nome ?? '—',
                            optional($atendimento->data_inicio_agendamento)?->format('d/m/Y H:i') ?? '—',
                            optional($atendimento->iniciado_em)?->format('d/m/Y H:i') ?? '—',
                            $minutosAtraso . ' min',
                        ];
                    })->all(),
            ],
            'saidas_antecipadas' => [
                'title' => 'Saídas Antecipadas',
                'description' => 'Atendimentos finalizados antes do horário previsto de término.',
                'count' => $saidasAntecipadas->count(),
                'columns' => $usaRegistroLegal
                    ? ['Funcionário', 'Saída Prevista', 'Saída Registrada', 'Antecipação']
                    : ['Atendimento', 'Funcionário', 'Previsto', 'Finalizado', 'Antecipação'],
                'rows' => $usaRegistroLegal
                    ? $saidasAntecipadas->map(fn (array $item) => [
                        $item['funcionario'],
                        $item['previsto']->format('d/m/Y H:i'),
                        $item['registrado']->format('d/m/Y H:i'),
                        $item['antecipacao_minutos'] . ' min',
                    ])->all()
                    : $saidasAntecipadas->map(function (Atendimento $atendimento) {
                        $minutosAntecipacao = max(0, Carbon::parse($atendimento->finalizado_em)->diffInMinutes(Carbon::parse($atendimento->data_fim_agendamento)));

                        return [
                            $atendimento->numero_atendimento,
                            optional($atendimento->funcionario)->nome ?? '—',
                            optional($atendimento->data_fim_agendamento)?->format('d/m/Y H:i') ?? '—',
                            optional($atendimento->finalizado_em)?->format('d/m/Y H:i') ?? '—',
                            $minutosAntecipacao . ' min',
                        ];
                    })->all(),
            ],
            'atestados_mes' => [
                'title' => 'Atestados no Mês',
                'description' => 'Afastamentos do tipo atestado no mês corrente.',
                'count' => count($atestadosMesRows),
                'columns' => ['Funcionário', 'Tipo', 'Início', 'Fim', 'Motivo'],
                'rows' => collect($atestadosMesRows)->map(function (Afastamento $afastamento) {
                    return [
                        optional($afastamento->funcionario)->nome ?? '—',
                        $afastamento->tipo,
                        optional($afastamento->data_inicio)?->format('d/m/Y') ?? '—',
                        optional($afastamento->data_fim)?->format('d/m/Y') ?? '—',
                        $afastamento->motivo ?: '—',
                    ];
                })->all(),
            ],
            'banco_horas_acima_20h' => [
                'title' => 'Funcionários com Banco de Horas acima de 20h',
                'description' => 'Saldo calculado considerando jornada prevista, tempo executado e ajustes.',
                'count' => $bancoPositivo->count(),
                'columns' => ['Funcionário', 'Trabalhado', 'Previsto', 'Ajustes', 'Saldo'],
                'rows' => $bancoPositivo->map(fn (array $item) => [
                    $item['nome'],
                    $this->formatarSegundos((int) $item['trabalhado_segundos']),
                    $this->formatarSegundos((int) $item['previsto_segundos']),
                    $this->formatarSegundos((int) $item['ajuste_segundos'], true),
                    $this->formatarSegundos((int) $item['saldo_segundos'], true),
                ])->all(),
            ],
            'banco_horas_abaixo_menos_20h' => [
                'title' => 'Funcionários com Banco de Horas abaixo de -20h',
                'description' => 'Saldo calculado considerando jornada prevista, tempo executado e ajustes.',
                'count' => $bancoNegativo->count(),
                'columns' => ['Funcionário', 'Trabalhado', 'Previsto', 'Ajustes', 'Saldo'],
                'rows' => $bancoNegativo->map(fn (array $item) => [
                    $item['nome'],
                    $this->formatarSegundos((int) $item['trabalhado_segundos']),
                    $this->formatarSegundos((int) $item['previsto_segundos']),
                    $this->formatarSegundos((int) $item['ajuste_segundos'], true),
                    $this->formatarSegundos((int) $item['saldo_segundos'], true),
                ])->all(),
            ],
        ];
    }

    private function montarPerformance(Carbon $inicioPeriodo, Carbon $fimPeriodo): array
    {
        $atendimentos = Atendimento::query()
            ->with('funcionario:id,nome')
            ->whereNotNull('funcionario_id')
            ->whereNotNull('data_inicio_agendamento')
            ->whereBetween('data_inicio_agendamento', [$inicioPeriodo, $fimPeriodo])
            ->orderByDesc('data_inicio_agendamento')
            ->get();

        $agregado = [];
        $ultimaNegativa = null;

        foreach ($atendimentos as $atendimento) {
            $funcionarioId = (int) $atendimento->funcionario_id;
            $nome = optional($atendimento->funcionario)->nome ?? 'Sem nome';

            $avaliacao = $this->calcularAvaliacaoAtendimento($atendimento);

            if (!isset($agregado[$funcionarioId])) {
                $agregado[$funcionarioId] = [
                    'funcionario_id' => $funcionarioId,
                    'nome' => $nome,
                    'qtd' => 0,
                    'soma_avaliacao' => 0,
                    'atrasos' => 0,
                ];
            }

            if ($avaliacao !== null) {
                $agregado[$funcionarioId]['qtd']++;
                $agregado[$funcionarioId]['soma_avaliacao'] += $avaliacao['nota'];

                if ($avaliacao['atraso_minutos'] > 0) {
                    $agregado[$funcionarioId]['atrasos']++;
                }

                if ($avaliacao['nota'] <= 2 && $ultimaNegativa === null) {
                    $ultimaNegativa = [
                        'atendimento' => $atendimento,
                        'avaliacao' => $avaliacao,
                    ];
                }
            }
        }

        $linhas = collect($agregado)
            ->map(function (array $item) {
                $media = $item['qtd'] > 0 ? round($item['soma_avaliacao'] / $item['qtd'], 2) : 0;
                $indiceAtraso = $item['qtd'] > 0 ? round(($item['atrasos'] / $item['qtd']) * 100, 2) : 0;

                return [
                    ...$item,
                    'media' => $media,
                    'indice_atraso' => $indiceAtraso,
                ];
            })
            ->values();

        $top5 = $linhas
            ->sortByDesc('media')
            ->sortByDesc('qtd')
            ->take(5)
            ->values();

        $rankingAtraso = $linhas
            ->filter(fn (array $item) => $item['qtd'] > 0)
            ->sortByDesc('indice_atraso')
            ->take(5)
            ->values();

        $countComMedia = $linhas->filter(fn (array $item) => $item['qtd'] > 0)->count();

        return [
            'media_avaliacao_funcionario' => [
                'title' => 'Média de avaliação por funcionário',
                'description' => 'Score operacional (1-5) calculado a partir da pontualidade dos atendimentos agendados.',
                'count' => $countComMedia,
                'columns' => ['Funcionário', 'Média', 'Atendimentos Avaliados', 'Índice de Atraso'],
                'rows' => $linhas
                    ->sortByDesc('media')
                    ->map(fn (array $item) => [
                        $item['nome'],
                        number_format((float) $item['media'], 2, ',', '.'),
                        (string) $item['qtd'],
                        number_format((float) $item['indice_atraso'], 2, ',', '.') . '%',
                    ])->all(),
            ],
            'ranking_top5_tecnicos' => [
                'title' => 'Ranking Top 5 técnicos',
                'description' => 'Classificação por maior média de avaliação operacional no período filtrado.',
                'count' => $top5->count(),
                'columns' => ['Posição', 'Funcionário', 'Média', 'Atendimentos Avaliados'],
                'rows' => $top5->values()->map(fn (array $item, int $index) => [
                    '#' . ($index + 1),
                    $item['nome'],
                    number_format((float) $item['media'], 2, ',', '.'),
                    (string) $item['qtd'],
                ])->all(),
            ],
            'ranking_maior_indice_atraso' => [
                'title' => 'Ranking de maior índice de atraso',
                'description' => 'Percentual de atendimentos iniciados com atraso no período filtrado.',
                'count' => $rankingAtraso->count(),
                'columns' => ['Posição', 'Funcionário', 'Índice de Atraso', 'Atendimentos Avaliados'],
                'rows' => $rankingAtraso->values()->map(fn (array $item, int $index) => [
                    '#' . ($index + 1),
                    $item['nome'],
                    number_format((float) $item['indice_atraso'], 2, ',', '.') . '%',
                    (string) $item['qtd'],
                ])->all(),
            ],
            'ultima_avaliacao_negativa' => [
                'title' => 'Última avaliação negativa recebida',
                'description' => 'Último atendimento com nota operacional baixa (<= 2).',
                'count' => $ultimaNegativa ? 1 : 0,
                'columns' => ['Atendimento', 'Funcionário', 'Data Prevista', 'Início Registrado', 'Atraso', 'Nota'],
                'rows' => $ultimaNegativa ? [[
                    $ultimaNegativa['atendimento']->numero_atendimento,
                    optional($ultimaNegativa['atendimento']->funcionario)->nome ?? '—',
                    optional($ultimaNegativa['atendimento']->data_inicio_agendamento)?->format('d/m/Y H:i') ?? '—',
                    optional($ultimaNegativa['atendimento']->iniciado_em)?->format('d/m/Y H:i') ?? '—',
                    $ultimaNegativa['avaliacao']['atraso_minutos'] . ' min',
                    number_format((float) $ultimaNegativa['avaliacao']['nota'], 2, ',', '.'),
                ]] : [],
            ],
        ];
    }

    private function montarRisco(Carbon $hoje, Carbon $limite, Collection $funcionariosAtivos, array $funcionariosComJornadaAtiva): array
    {
        $documentosVencendoRows = [];
        $asoVencidoRows = [];
        if (Schema::hasTable('funcionario_documentos')) {
            $documentosVencendoRows = FuncionarioDocumento::query()
                ->with('funcionario:id,nome')
                ->whereNotNull('data_vencimento')
                ->whereBetween('data_vencimento', [$hoje->toDateString(), $limite->toDateString()])
                ->orderBy('data_vencimento')
                ->get();

            $asoVencidoRows = FuncionarioDocumento::query()
                ->with('funcionario:id,nome')
                ->whereRaw('LOWER(tipo) like ?', ['%aso%'])
                ->whereNotNull('data_vencimento')
                ->whereDate('data_vencimento', '<', $hoje)
                ->orderBy('data_vencimento')
                ->get();
        }

        $episVencidosRows = [];
        if (Schema::hasTable('funcionario_epis')) {
            $episVencidosRows = FuncionarioEpi::query()
                ->with(['funcionario:id,nome', 'epi:id,nome,validade_ca'])
                ->where(function ($query) use ($hoje) {
                    $query->where(function ($sub) use ($hoje) {
                        $sub->whereNotNull('data_prevista_troca')
                            ->whereDate('data_prevista_troca', '<', $hoje);
                    })->orWhereHas('epi', function ($epiQuery) use ($hoje) {
                        $epiQuery->whereNotNull('validade_ca')
                            ->whereDate('validade_ca', '<', $hoje);
                    });
                })
                ->orderBy('data_prevista_troca')
                ->get();
        }

        $feriasVencidasRows = [];
        if (Schema::hasTable('ferias')) {
            $feriasVencidasRows = Ferias::query()
                ->with('funcionario:id,nome')
                ->whereNotNull('periodo_gozo_fim')
                ->whereDate('periodo_gozo_fim', '<', $hoje)
                ->where('status', '!=', 'concluida')
                ->orderBy('periodo_gozo_fim')
                ->get();
        }

        $semJornadaRows = $funcionariosAtivos
            ->reject(fn (Funcionario $funcionario) => in_array($funcionario->id, $funcionariosComJornadaAtiva, true))
            ->values();

        return [
            'documentos_vencendo_30' => [
                'title' => 'Documento vencendo em 30 dias',
                'description' => 'Documentos com vencimento dentro do período de alerta.',
                'count' => count($documentosVencendoRows),
                'columns' => ['Funcionário', 'Tipo', 'Número', 'Vencimento', 'Status'],
                'rows' => collect($documentosVencendoRows)->map(fn (FuncionarioDocumento $documento) => [
                    optional($documento->funcionario)->nome ?? '—',
                    $documento->tipo,
                    $documento->numero ?: '—',
                    optional($documento->data_vencimento)?->format('d/m/Y') ?? '—',
                    $documento->status,
                ])->all(),
            ],
            'epi_vencido' => [
                'title' => 'EPI vencido',
                'description' => 'EPIs com troca prevista vencida ou CA vencido.',
                'count' => count($episVencidosRows),
                'columns' => ['Funcionário', 'EPI', 'Troca Prevista', 'Validade CA', 'Status'],
                'rows' => collect($episVencidosRows)->map(fn (FuncionarioEpi $vinculo) => [
                    optional($vinculo->funcionario)->nome ?? '—',
                    optional($vinculo->epi)->nome ?? '—',
                    optional($vinculo->data_prevista_troca)?->format('d/m/Y') ?? '—',
                    optional($vinculo->epi?->validade_ca)?->format('d/m/Y') ?? '—',
                    $vinculo->status,
                ])->all(),
            ],
            'ferias_vencidas' => [
                'title' => 'Férias vencidas',
                'description' => 'Períodos de férias não concluídos e já vencidos.',
                'count' => count($feriasVencidasRows),
                'columns' => ['Funcionário', 'Período Aquisitivo', 'Fim Gozo', 'Status'],
                'rows' => collect($feriasVencidasRows)->map(fn (Ferias $ferias) => [
                    optional($ferias->funcionario)->nome ?? '—',
                    optional($ferias->periodo_aquisitivo_inicio)?->format('d/m/Y') . ' - ' . optional($ferias->periodo_aquisitivo_fim)?->format('d/m/Y'),
                    optional($ferias->periodo_gozo_fim)?->format('d/m/Y') ?? '—',
                    $ferias->status,
                ])->all(),
            ],
            'aso_vencido' => [
                'title' => 'ASO vencido',
                'description' => 'Documentos ASO com data de vencimento expirada.',
                'count' => count($asoVencidoRows),
                'columns' => ['Funcionário', 'Número', 'Vencimento', 'Status'],
                'rows' => collect($asoVencidoRows)->map(fn (FuncionarioDocumento $documento) => [
                    optional($documento->funcionario)->nome ?? '—',
                    $documento->numero ?: '—',
                    optional($documento->data_vencimento)?->format('d/m/Y') ?? '—',
                    $documento->status,
                ])->all(),
            ],
            'funcionario_sem_jornada' => [
                'title' => 'Funcionário sem jornada cadastrada',
                'description' => 'Funcionários ativos sem vínculo de jornada vigente.',
                'count' => $semJornadaRows->count(),
                'columns' => ['Funcionário'],
                'rows' => $semJornadaRows->map(fn (Funcionario $funcionario) => [
                    $funcionario->nome,
                ])->all(),
            ],
        ];
    }

    private function funcionariosComJornadaAtiva(Carbon $data): array
    {
        if (!Schema::hasTable('funcionario_jornadas')) {
            return [];
        }

        return FuncionarioJornada::query()
            ->whereDate('data_inicio', '<=', $data->toDateString())
            ->where(function ($query) use ($data) {
                $query->whereNull('data_fim')
                    ->orWhereDate('data_fim', '>=', $data->toDateString());
            })
            ->pluck('funcionario_id')
            ->unique()
            ->values()
            ->all();
    }

    private function jornadasVigentesPorFuncionarioNoDia(Carbon $data, array $funcionariosIds): array
    {
        if (empty($funcionariosIds) || !Schema::hasTable('funcionario_jornadas') || !Schema::hasTable('jornadas')) {
            return [];
        }

        return FuncionarioJornada::query()
            ->with(['funcionario:id,nome', 'jornada:id,hora_inicio,hora_fim,hora_entrada_padrao,hora_saida_padrao,intervalo_minutos'])
            ->whereIn('funcionario_id', $funcionariosIds)
            ->whereDate('data_inicio', '<=', $data->toDateString())
            ->where(function ($query) use ($data) {
                $query->whereNull('data_fim')
                    ->orWhereDate('data_fim', '>=', $data->toDateString());
            })
            ->orderByDesc('data_inicio')
            ->get()
            ->groupBy('funcionario_id')
            ->map(fn (Collection $itens) => $itens->first())
            ->all();
    }

    private function calcularBancoHorasPorFuncionario(Carbon $inicio, Carbon $fim, array $funcionariosIds): array
    {
        if (empty($funcionariosIds)) {
            return [];
        }

        $trabalhado = Atendimento::query()
            ->whereIn('funcionario_id', $funcionariosIds)
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('funcionario_id')
            ->select('funcionario_id', DB::raw('COALESCE(SUM(tempo_execucao_segundos),0) as total'))
            ->pluck('total', 'funcionario_id');

        $ajustes = Schema::hasTable('rh_ajustes_ponto')
            ? RhAjustePonto::query()
                ->whereIn('funcionario_id', $funcionariosIds)
                ->whereBetween('ajustado_em', [$inicio, $fim])
                ->groupBy('funcionario_id')
                ->select('funcionario_id', DB::raw('COALESCE(SUM(minutos_ajuste),0) * 60 as total'))
                ->pluck('total', 'funcionario_id')
            : collect();

        $jornadas = collect();
        if (Schema::hasTable('funcionario_jornadas') && Schema::hasTable('jornadas')) {
            $jornadas = FuncionarioJornada::query()
                ->with('jornada:id,hora_inicio,hora_fim,hora_entrada_padrao,hora_saida_padrao,intervalo_minutos')
                ->whereIn('funcionario_id', $funcionariosIds)
                ->whereDate('data_inicio', '<=', $fim->toDateString())
                ->where(function ($query) use ($inicio) {
                    $query->whereNull('data_fim')
                        ->orWhereDate('data_fim', '>=', $inicio->toDateString());
                })
                ->orderByDesc('data_inicio')
                ->get()
                ->groupBy('funcionario_id')
                ->map(fn (Collection $itens) => $itens->first());
        }

        $funcionarios = Funcionario::query()
            ->whereIn('id', $funcionariosIds)
            ->get(['id', 'nome'])
            ->keyBy('id');

        $resultado = [];
        foreach ($funcionariosIds as $funcionarioId) {
            $vinculo = $jornadas->get($funcionarioId);
            $previsto = $this->segundosPrevistosNoPeriodo($inicio, $fim, $vinculo);
            $trabalhadoSegundos = (int) ($trabalhado[$funcionarioId] ?? 0);
            $ajusteSegundos = (int) ($ajustes[$funcionarioId] ?? 0);
            $saldo = $trabalhadoSegundos + $ajusteSegundos - $previsto;

            $resultado[] = [
                'funcionario_id' => $funcionarioId,
                'nome' => optional($funcionarios->get($funcionarioId))->nome ?? '—',
                'trabalhado_segundos' => $trabalhadoSegundos,
                'ajuste_segundos' => $ajusteSegundos,
                'previsto_segundos' => $previsto,
                'saldo_segundos' => $saldo,
            ];
        }

        return $resultado;
    }

    private function segundosPrevistosNoPeriodo(Carbon $inicio, Carbon $fim, ?FuncionarioJornada $vinculo): int
    {
        if (!$vinculo || !$vinculo->jornada instanceof Jornada) {
            return 0;
        }

        $inicioVigencia = $vinculo->data_inicio ? Carbon::parse($vinculo->data_inicio)->startOfDay() : $inicio->copy();
        $fimVigencia = $vinculo->data_fim ? Carbon::parse($vinculo->data_fim)->endOfDay() : $fim->copy();

        $inicioEfetivo = $inicio->copy()->max($inicioVigencia);
        $fimEfetivo = $fim->copy()->min($fimVigencia);

        if ($inicioEfetivo->gt($fimEfetivo)) {
            return 0;
        }

        $segundosDia = $this->segundosDiariosJornada($vinculo->jornada);
        if ($segundosDia <= 0) {
            return 0;
        }

        $diasUteis = $this->contarDiasUteis($inicioEfetivo, $fimEfetivo);

        return $diasUteis * $segundosDia;
    }

    private function segundosDiariosJornada(Jornada $jornada): int
    {
        $base = Carbon::today();
        $horaEntrada = $jornada->hora_entrada_padrao ?: $jornada->hora_inicio;
        $horaSaida = $jornada->hora_saida_padrao ?: $jornada->hora_fim;

        if (!$horaEntrada || !$horaSaida) {
            return 0;
        }

        $inicio = Carbon::parse($base->toDateString() . ' ' . $horaEntrada);
        $fim = Carbon::parse($base->toDateString() . ' ' . $horaSaida);

        if ($fim->lessThanOrEqualTo($inicio)) {
            $fim->addDay();
        }

        $segundos = $fim->diffInSeconds($inicio);
        $intervalo = ((int) $jornada->intervalo_minutos) * 60;

        return max(0, $segundos - $intervalo);
    }

    private function contarDiasUteis(Carbon $inicio, Carbon $fim): int
    {
        $cursor = $inicio->copy()->startOfDay();
        $fimDia = $fim->copy()->startOfDay();
        $dias = 0;

        while ($cursor->lte($fimDia)) {
            if ($cursor->isWeekday()) {
                $dias++;
            }

            $cursor->addDay();
        }

        return $dias;
    }

    private function calcularAvaliacaoAtendimento(Atendimento $atendimento): ?array
    {
        if (!$atendimento->data_inicio_agendamento || !$atendimento->iniciado_em) {
            return null;
        }

        $previsto = Carbon::parse($atendimento->data_inicio_agendamento);
        $registrado = Carbon::parse($atendimento->iniciado_em);

        $atraso = max(0, $registrado->diffInMinutes($previsto, false) * -1);

        $nota = match (true) {
            $atraso <= 0 => 5,
            $atraso <= 15 => 4,
            $atraso <= 30 => 3,
            $atraso <= 60 => 2,
            default => 1,
        };

        return [
            'atraso_minutos' => $atraso,
            'nota' => $nota,
        ];
    }

    private function formatarSegundos(int $segundos, bool $comSinal = false): string
    {
        $sinal = '';
        if ($comSinal && $segundos !== 0) {
            $sinal = $segundos > 0 ? '+' : '-';
        }

        $valor = abs($segundos);
        $horas = intdiv($valor, 3600);
        $minutos = intdiv($valor % 3600, 60);

        return sprintf('%s%02d:%02d', $sinal, $horas, $minutos);
    }
}
