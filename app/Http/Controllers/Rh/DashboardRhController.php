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
        $funcionarioId = !empty($filtros['funcionario_id']) ? (int) $filtros['funcionario_id'] : null;

        $dataReferencia = $this->resolverDataReferencia($inicioPeriodo, $fimPeriodo);
        $limite = $dataReferencia->copy()->addDays(30);

        $data = $this->montarDadosDashboard($dataReferencia, $limite, $inicioPeriodo, $fimPeriodo, $funcionarioId);

        $funcionariosFiltro = Funcionario::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome']);

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
            'funcionariosFiltro' => $funcionariosFiltro,
            'periodoLabel' => $inicioPeriodo->format('d/m/Y') . ' até ' . $fimPeriodo->format('d/m/Y'),
        ]);
    }

    public function relatorio(Request $request, string $indicador)
    {
        [$inicioPeriodo, $fimPeriodo, $filtros, $filtrosQuery] = $this->resolverPeriodo($request);
        $funcionarioId = !empty($filtros['funcionario_id']) ? (int) $filtros['funcionario_id'] : null;

        $dataReferencia = $this->resolverDataReferencia($inicioPeriodo, $fimPeriodo);
        $limite = $dataReferencia->copy()->addDays(30);
        $data = $this->montarDadosDashboard($dataReferencia, $limite, $inicioPeriodo, $fimPeriodo, $funcionarioId);

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
            'funcionario_id' => ['nullable', 'integer', 'exists:funcionarios,id'],
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
            'funcionario_id' => $validated['funcionario_id'] ?? null,
        ];

        $filtrosQuery = array_filter([
            'inicio' => $inicioInformado,
            'fim' => $fimInformado,
            'funcionario_id' => $validated['funcionario_id'] ?? null,
        ]);

        return [$inicio, $fim, $filtros, $filtrosQuery];
    }

    private function montarDadosDashboard(Carbon $hoje, Carbon $limite, Carbon $inicioPeriodo, Carbon $fimPeriodo, ?int $funcionarioId = null): array
    {
        $inicioPeriodo = $inicioPeriodo->copy()->startOfDay();
        $fimPeriodo = $fimPeriodo->copy()->endOfDay();
        $fimApuracao = $fimPeriodo->copy()->min($hoje->copy()->endOfDay());

        $funcionariosAtivosQuery = Funcionario::query()->where('ativo', true);
        if ($funcionarioId) {
            $funcionariosAtivosQuery->where('id', $funcionarioId);
        }

        $funcionariosAtivos = $funcionariosAtivosQuery->get(['id', 'nome']);

        $funcionariosAtivosIds = $funcionariosAtivos->pluck('id')->all();
        $funcionariosComJornadaAtiva = $this->funcionariosComJornadaAtiva($hoje, $funcionariosAtivosIds);

        $monitoramento = $this->montarMonitoramento($hoje, $inicioPeriodo, $fimApuracao, $funcionariosAtivos, $funcionariosAtivosIds, $funcionariosComJornadaAtiva);
        $performance = $this->montarPerformance($inicioPeriodo, $fimPeriodo, $funcionariosAtivosIds);
        $risco = $this->montarRisco($hoje, $limite, $funcionariosAtivos, $funcionariosComJornadaAtiva, $funcionariosAtivosIds);
        $bancoHorasFuncionarios = $this->calcularBancoHorasPorFuncionario($inicioPeriodo, $fimApuracao, $funcionariosAtivosIds);

        $indicadoresMoverParaPerformance = ['atestados_mes', 'banco_horas_acima_20h', 'banco_horas_abaixo_menos_20h'];
        $performance = [
            ...$performance,
            ...Arr::only($monitoramento, $indicadoresMoverParaPerformance),
        ];
        $monitoramento = Arr::except($monitoramento, $indicadoresMoverParaPerformance);

        $totaisBancoHoras = (int) collect($bancoHorasFuncionarios)
            ->sum(fn (array $item) => max(0, (int) ($item['saldo_segundos'] ?? 0)));

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

    private function resolverDataReferencia(Carbon $inicioPeriodo, Carbon $fimPeriodo): Carbon
    {
        $hoje = Carbon::today()->startOfDay();

        if ($hoje->lt($inicioPeriodo)) {
            return $inicioPeriodo->copy()->startOfDay();
        }

        if ($hoje->gt($fimPeriodo)) {
            return $fimPeriodo->copy()->startOfDay();
        }

        return $hoje;
    }

    private function montarMonitoramento(Carbon $hoje, Carbon $inicioMes, Carbon $fimMes, Collection $funcionariosAtivos, array $funcionariosAtivosIds, array $funcionariosComJornadaAtiva): array
    {
        $baseFaltasIds = !empty($funcionariosComJornadaAtiva) ? $funcionariosComJornadaAtiva : $funcionariosAtivosIds;
        $jornadasHoje = $this->jornadasVigentesPorFuncionarioNoDia($hoje, $baseFaltasIds);
        $idsTrabalhaHoje = collect($jornadasHoje)
            ->filter(fn (FuncionarioJornada $vinculo) => $this->funcionarioTrabalhaNoDia($vinculo, $hoje))
            ->keys()
            ->all();

        if (empty($idsTrabalhaHoje)) {
            $idsTrabalhaHoje = $baseFaltasIds;
        }

        $usaRegistroLegal = Schema::hasTable('registro_pontos_portal')
            && Schema::hasTable('funcionario_jornadas')
            && Schema::hasTable('jornadas');

        if ($usaRegistroLegal) {
            $dataHoje = $hoje->toDateString();
            $filtroDiaAtual = function ($query) use ($dataHoje) {
                $query->whereDate('data_referencia', $dataHoje)
                    ->orWhereDate('entrada_em', $dataHoje)
                    ->orWhereDate('saida_em', $dataHoje);
            };

            $jornadasHojeTrabalha = collect($jornadasHoje)
                ->filter(fn (FuncionarioJornada $vinculo) => $this->funcionarioTrabalhaNoDia($vinculo, $hoje));

            $trabalhoDomingoFeriadoHoje = $this->calcularTrabalhoDomingoFeriadoNoPeriodo(
                $inicioMes,
                $fimMes,
                $funcionariosAtivos,
                $funcionariosAtivosIds
            );

            $registrosHoje = RegistroPontoPortal::query()
                ->whereIn('funcionario_id', $jornadasHojeTrabalha->keys()->all())
                ->where(function ($query) use ($filtroDiaAtual) {
                    $filtroDiaAtual($query);
                })
                ->get()
                ->keyBy('funcionario_id');

            $faltasHoje = $funcionariosAtivos
                ->whereIn('id', $jornadasHojeTrabalha->keys()->all())
                ->reject(function (Funcionario $funcionario) use ($registrosHoje) {
                    $registro = $registrosHoje->get($funcionario->id);

                    return (bool) ($registro?->entrada_em);
                })
                ->values();

            $atrasosHoje = $jornadasHojeTrabalha
                ->map(function (FuncionarioJornada $vinculo, int $funcionarioId) use ($registrosHoje, $hoje) {
                    $registro = $registrosHoje->get($funcionarioId);
                    if (!$registro || !$registro->entrada_em || !$vinculo->jornada) {
                        return null;
                    }

                    ['hora_entrada' => $horaEntrada] = $this->resolverHorariosJornadaNoDia($vinculo, $hoje);
                    if (!$horaEntrada) {
                        return null;
                    }

                    $previsto = Carbon::parse($hoje->toDateString() . ' ' . $horaEntrada);
                    $toleranciaEntradaMin = max(0, (int) ($vinculo->jornada->tolerancia_entrada_min ?? 0));
                    $limiteAtraso = $previsto->copy()->addMinutes($toleranciaEntradaMin);
                    $registrado = Carbon::parse($registro->entrada_em);
                    if (!$registrado->gt($limiteAtraso)) {
                        return null;
                    }

                    $atrasoMin = $registrado->diffInMinutes($limiteAtraso, true);

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

            $saidasAntecipadas = $jornadasHojeTrabalha
                ->map(function (FuncionarioJornada $vinculo, int $funcionarioId) use ($registrosHoje, $hoje) {
                    $registro = $registrosHoje->get($funcionarioId);
                    if (!$registro || !$registro->saida_em || !$vinculo->jornada) {
                        return null;
                    }

                    ['hora_entrada' => $horaEntrada, 'hora_saida' => $horaSaida] = $this->resolverHorariosJornadaNoDia($vinculo, $hoje);
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
            $trabalhoDomingoFeriadoHoje = collect();
            $registrosHojeIds = Atendimento::query()
                ->whereIn('funcionario_id', $idsTrabalhaHoje)
                ->whereDate('created_at', $hoje)
                ->pluck('funcionario_id')
                ->unique()
                ->values()
                ->all();

            $faltasHoje = $funcionariosAtivos
                ->whereIn('id', $idsTrabalhaHoje)
                ->reject(fn (Funcionario $funcionario) => in_array($funcionario->id, $registrosHojeIds, true))
                ->values();

            $atrasosHoje = Atendimento::query()
                ->with('funcionario:id,nome')
                ->whereIn('funcionario_id', $idsTrabalhaHoje)
                ->whereNotNull('data_inicio_agendamento')
                ->whereNotNull('iniciado_em')
                ->whereDate('data_inicio_agendamento', $hoje)
                ->whereColumn('iniciado_em', '>', 'data_inicio_agendamento')
                ->orderByDesc('iniciado_em')
                ->get();

            $saidasAntecipadas = Atendimento::query()
                ->with('funcionario:id,nome')
                ->whereIn('funcionario_id', $idsTrabalhaHoje)
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
                ->whereIn('funcionario_id', $funcionariosAtivosIds)
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

        $monitoramentoMensal = $this->calcularMonitoramentoMensal(
            $inicioMes,
            $fimMes,
            $funcionariosAtivos,
            $funcionariosAtivosIds,
            $usaRegistroLegal
        );

        return [
            'faltas_hoje' => [
                'title' => 'Monitoramento Diário • Faltas',
                'description' => 'Funcionários ativos sem registro de ponto no dia.',
                'count' => $faltasHoje->count(),
                'columns' => ['Funcionário'],
                'rows' => $faltasHoje->map(fn (Funcionario $funcionario) => [
                    $funcionario->nome,
                ])->all(),
            ],
            'atrasos_hoje' => [
                'title' => 'Monitoramento Diário • Atrasos',
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
                'title' => 'Monitoramento Diário • Saídas Antecipadas',
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
            'trabalho_domingo_feriado' => [
                'title' => 'Trabalho em domingo/feriado no período',
                'description' => 'Registros de ponto em dias não previstos na jornada dentro do período filtrado, com destaque para horas extras.',
                'count' => $trabalhoDomingoFeriadoHoje->count(),
                'columns' => ['Funcionário', 'Dia', 'Entrada', 'Atraso entrada', 'Saída almoço (atraso)', 'Retorno almoço (adiantado)', 'Saída (após horário)', 'Extra do dia'],
                'rows' => $trabalhoDomingoFeriadoHoje->map(fn (array $item) => [
                    $item['funcionario'],
                    $item['dia'],
                    $item['entrada'],
                    $item['atraso_entrada'] . ' min',
                    $item['intervalo_inicio'] . ' (+' . $item['atraso_intervalo_inicio'] . ' min)',
                    $item['intervalo_fim'] . ' (-' . $item['retorno_adiantado'] . ' min)',
                    $item['saida'] . ' (+' . $item['saida_apos_horario'] . ' min)',
                    $this->formatarSegundos($item['extra_segundos']) . ' @ ' . number_format($item['extra_percentual'], 0, ',', '.') . '%',
                ])->all(),
            ],
            'faltas_mes' => [
                'title' => 'Monitoramento Mensal • Faltas',
                'description' => 'Total de faltas no período mensal filtrado.',
                'count' => count($monitoramentoMensal['faltas_rows']),
                'columns' => ['Funcionário', 'Dia'],
                'rows' => $monitoramentoMensal['faltas_rows'],
            ],
            'atrasos_mes' => [
                'title' => 'Monitoramento Mensal • Atrasos',
                'description' => 'Total de atrasos no período mensal filtrado.',
                'count' => count($monitoramentoMensal['atrasos_rows']),
                'columns' => ['Funcionário', 'Dia', 'Previsto', 'Registrado', 'Atraso'],
                'rows' => $monitoramentoMensal['atrasos_rows'],
            ],
            'saidas_antecipadas_mes' => [
                'title' => 'Monitoramento Mensal • Saídas Antecipadas',
                'description' => 'Total de saídas antecipadas no período mensal filtrado.',
                'count' => count($monitoramentoMensal['saidas_rows']),
                'columns' => ['Funcionário', 'Dia', 'Previsto', 'Registrado', 'Antecipação'],
                'rows' => $monitoramentoMensal['saidas_rows'],
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

    private function calcularTrabalhoDomingoFeriadoNoPeriodo(
        Carbon $inicioPeriodo,
        Carbon $fimPeriodo,
        Collection $funcionariosAtivos,
        array $funcionariosAtivosIds
    ): \Illuminate\Support\Collection {
        if (empty($funcionariosAtivosIds)) {
            return collect();
        }

        $funcionariosAtivosPorId = $funcionariosAtivos->keyBy('id');
        $cacheJornadasPorDia = [];

        $registrosPeriodo = RegistroPontoPortal::query()
            ->whereIn('funcionario_id', $funcionariosAtivosIds)
            ->where(function ($query) use ($inicioPeriodo, $fimPeriodo) {
                $query->whereBetween('data_referencia', [$inicioPeriodo->toDateString(), $fimPeriodo->toDateString()])
                    ->orWhere(function ($sub) use ($inicioPeriodo, $fimPeriodo) {
                        $sub->whereNull('data_referencia')
                            ->whereBetween('entrada_em', [$inicioPeriodo, $fimPeriodo]);
                    });
            })
            ->orderBy('data_referencia')
            ->get();

        return $registrosPeriodo
            ->map(function (RegistroPontoPortal $registro) use (&$cacheJornadasPorDia, $funcionariosAtivosIds, $funcionariosAtivosPorId, $inicioPeriodo, $fimPeriodo) {
                $diaBase = $registro->data_referencia
                    ? Carbon::parse($registro->data_referencia)->startOfDay()
                    : ($registro->entrada_em
                        ? Carbon::parse($registro->entrada_em)->startOfDay()
                        : null);

                if (!$diaBase) {
                    return null;
                }

                if ($diaBase->lt($inicioPeriodo->copy()->startOfDay()) || $diaBase->gt($fimPeriodo->copy()->startOfDay())) {
                    return null;
                }

                $diaChave = $diaBase->toDateString();
                if (!array_key_exists($diaChave, $cacheJornadasPorDia)) {
                    $cacheJornadasPorDia[$diaChave] = $this->jornadasVigentesPorFuncionarioNoDia($diaBase, $funcionariosAtivosIds);
                }

                $funcionarioId = (int) $registro->funcionario_id;
                $vinculo = $cacheJornadasPorDia[$diaChave][$funcionarioId] ?? null;

                $ehDomingoOuFeriado = $diaBase->isSunday()
                    || ($vinculo instanceof FuncionarioJornada && $this->ehFeriadoAtreladoNaJornada($vinculo, $diaBase));

                if (!$ehDomingoOuFeriado) {
                    return null;
                }

                $segundosTrabalhados = $this->calcularSegundosTrabalhadosRegistroLegal($registro);
                if ($segundosTrabalhados <= 0) {
                    return null;
                }

                $jornada = $vinculo?->jornada;
                $horaEntradaBase = $jornada?->hora_entrada_padrao ?: $jornada?->hora_inicio;
                $horaSaidaBase = $jornada?->hora_saida_padrao ?: $jornada?->hora_fim;
                $intervaloBase = (int) ($jornada?->intervalo_minutos ?? 60);

                $entradaPrevista = $horaEntradaBase ? Carbon::parse($diaBase->toDateString() . ' ' . $horaEntradaBase) : null;
                $saidaPrevista = $horaSaidaBase ? Carbon::parse($diaBase->toDateString() . ' ' . $horaSaidaBase) : null;
                if ($entradaPrevista && $saidaPrevista && $saidaPrevista->lessThanOrEqualTo($entradaPrevista)) {
                    $saidaPrevista->addDay();
                }

                $intervaloInicioPrevisto = $entradaPrevista ? $entradaPrevista->copy()->addHours(4) : null;
                $intervaloFimPrevisto = $intervaloInicioPrevisto ? $intervaloInicioPrevisto->copy()->addMinutes($intervaloBase) : null;

                $entradaRegistrada = $registro->entrada_em ? Carbon::parse($registro->entrada_em) : null;
                $intervaloInicioRegistrado = $registro->intervalo_inicio_em ? Carbon::parse($registro->intervalo_inicio_em) : null;
                $intervaloFimRegistrado = $registro->intervalo_fim_em ? Carbon::parse($registro->intervalo_fim_em) : null;
                $saidaRegistrada = $registro->saida_em ? Carbon::parse($registro->saida_em) : null;

                $atrasoEntrada = ($entradaPrevista && $entradaRegistrada && $entradaRegistrada->gt($entradaPrevista))
                    ? $entradaRegistrada->diffInMinutes($entradaPrevista)
                    : 0;

                $atrasoIntervaloInicio = ($intervaloInicioPrevisto && $intervaloInicioRegistrado && $intervaloInicioRegistrado->gt($intervaloInicioPrevisto))
                    ? $intervaloInicioRegistrado->diffInMinutes($intervaloInicioPrevisto)
                    : 0;

                $retornoAdiantado = ($intervaloFimPrevisto && $intervaloFimRegistrado && $intervaloFimRegistrado->lt($intervaloFimPrevisto))
                    ? $intervaloFimPrevisto->diffInMinutes($intervaloFimRegistrado)
                    : 0;

                $saidaAposHorario = ($saidaPrevista && $saidaRegistrada && $saidaRegistrada->gt($saidaPrevista))
                    ? $saidaRegistrada->diffInMinutes($saidaPrevista)
                    : 0;

                $percentualExtra = (float) ($jornada?->percentual_hora_extra_domingo_feriado ?? 100);

                $nomeFuncionario = optional($funcionariosAtivosPorId->get($funcionarioId))->nome
                    ?? optional($vinculo?->funcionario)->nome
                    ?? '—';

                return [
                    'funcionario' => $nomeFuncionario,
                    'dia' => $diaBase->format('d/m/Y'),
                    'entrada' => $entradaRegistrada?->format('H:i') ?? '—',
                    'atraso_entrada' => $atrasoEntrada,
                    'intervalo_inicio' => $intervaloInicioRegistrado?->format('H:i') ?? '—',
                    'atraso_intervalo_inicio' => $atrasoIntervaloInicio,
                    'intervalo_fim' => $intervaloFimRegistrado?->format('H:i') ?? '—',
                    'retorno_adiantado' => $retornoAdiantado,
                    'saida' => $saidaRegistrada?->format('H:i') ?? '—',
                    'saida_apos_horario' => $saidaAposHorario,
                    'extra_segundos' => $segundosTrabalhados,
                    'extra_percentual' => $percentualExtra,
                ];
            })
            ->filter()
            ->values();
    }

    private function calcularMonitoramentoMensal(
        Carbon $inicioMes,
        Carbon $fimMes,
        Collection $funcionariosAtivos,
        array $funcionariosAtivosIds,
        bool $usaRegistroLegal
    ): array {
        if (empty($funcionariosAtivosIds)) {
            return [
                'faltas_rows' => [],
                'atrasos_rows' => [],
                'saidas_rows' => [],
            ];
        }

        $faltasRows = [];
        $atrasosRows = [];
        $saidasRows = [];
        $funcionariosPorId = $funcionariosAtivos->keyBy('id');

        if ($usaRegistroLegal) {
            $registrosMes = RegistroPontoPortal::query()
                ->whereIn('funcionario_id', $funcionariosAtivosIds)
                ->where(function ($query) use ($inicioMes, $fimMes) {
                    $query->whereBetween('data_referencia', [$inicioMes->toDateString(), $fimMes->toDateString()])
                        ->orWhereBetween('entrada_em', [$inicioMes, $fimMes])
                        ->orWhereBetween('saida_em', [$inicioMes, $fimMes]);
                })
                ->get();

            $registrosPorDiaFuncionario = $registrosMes
                ->map(function (RegistroPontoPortal $registro) {
                    $dia = $registro->data_referencia
                        ? Carbon::parse($registro->data_referencia)->toDateString()
                        : ($registro->entrada_em
                            ? Carbon::parse($registro->entrada_em)->toDateString()
                            : ($registro->saida_em ? Carbon::parse($registro->saida_em)->toDateString() : null));

                    if (!$dia) {
                        return null;
                    }

                    return [
                        'dia' => $dia,
                        'funcionario_id' => (int) $registro->funcionario_id,
                        'registro' => $registro,
                    ];
                })
                ->filter()
                ->groupBy(fn (array $item) => $item['dia'] . '|' . $item['funcionario_id'])
                ->map(fn ($itens) => $itens->first()['registro']);

            $cursor = $inicioMes->copy()->startOfDay();
            $fimCursor = $fimMes->copy()->startOfDay();

            while ($cursor->lte($fimCursor)) {
                $dia = $cursor->copy();
                $diaStr = $dia->toDateString();
                $jornadasDia = $this->jornadasVigentesPorFuncionarioNoDia($dia, $funcionariosAtivosIds);
                $idsTrabalhaDia = collect($jornadasDia)
                    ->filter(fn (FuncionarioJornada $vinculo) => $this->funcionarioTrabalhaNoDia($vinculo, $dia))
                    ->keys()
                    ->all();

                foreach ($idsTrabalhaDia as $funcionarioId) {
                    $chave = $diaStr . '|' . (int) $funcionarioId;
                    /** @var RegistroPontoPortal|null $registro */
                    $registro = $registrosPorDiaFuncionario->get($chave);
                    $nome = optional($funcionariosPorId->get((int) $funcionarioId))->nome ?? '—';

                    if (!$registro || !$registro->entrada_em) {
                        $faltasRows[] = [$nome, $dia->format('d/m/Y')];
                        continue;
                    }

                    $vinculo = $jornadasDia[(int) $funcionarioId] ?? null;
                    $horarios = $vinculo ? $this->resolverHorariosJornadaNoDia($vinculo, $dia) : ['hora_entrada' => null, 'hora_saida' => null];
                    $horaEntrada = $horarios['hora_entrada'];
                    $horaSaida = $horarios['hora_saida'];
                    $toleranciaEntradaMin = max(0, (int) ($vinculo?->jornada?->tolerancia_entrada_min ?? 0));

                    if ($horaEntrada) {
                        $previstoEntrada = Carbon::parse($diaStr . ' ' . $horaEntrada);
                        $limiteAtraso = $previstoEntrada->copy()->addMinutes($toleranciaEntradaMin);
                        $registradoEntrada = Carbon::parse($registro->entrada_em);
                        if (!$registradoEntrada->gt($limiteAtraso)) {
                            $atrasoMin = 0;
                        } else {
                            $atrasoMin = $registradoEntrada->diffInMinutes($limiteAtraso, true);
                        }

                        if ($atrasoMin > 0) {
                            $atrasosRows[] = [
                                $nome,
                                $dia->format('d/m/Y'),
                                $previstoEntrada->format('H:i'),
                                $registradoEntrada->format('H:i'),
                                $atrasoMin . ' min',
                            ];
                        }
                    }

                    if ($horaSaida && $horaEntrada && $registro->saida_em) {
                        $previstoSaida = Carbon::parse($diaStr . ' ' . $horaSaida);
                        if ($horaSaida <= $horaEntrada) {
                            $previstoSaida->addDay();
                        }

                        $registradoSaida = Carbon::parse($registro->saida_em);
                        if ($registradoSaida->lt($previstoSaida)) {
                            $saidasRows[] = [
                                $nome,
                                $dia->format('d/m/Y'),
                                $previstoSaida->format('H:i'),
                                $registradoSaida->format('H:i'),
                                $previstoSaida->diffInMinutes($registradoSaida) . ' min',
                            ];
                        }
                    }
                }

                $cursor->addDay();
            }
        } else {
            $atendimentosMes = Atendimento::query()
                ->with('funcionario:id,nome')
                ->whereIn('funcionario_id', $funcionariosAtivosIds)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->get();

            $atrasosRows = Atendimento::query()
                ->with('funcionario:id,nome')
                ->whereIn('funcionario_id', $funcionariosAtivosIds)
                ->whereNotNull('data_inicio_agendamento')
                ->whereNotNull('iniciado_em')
                ->whereBetween('data_inicio_agendamento', [$inicioMes, $fimMes])
                ->whereColumn('iniciado_em', '>', 'data_inicio_agendamento')
                ->get()
                ->map(function (Atendimento $atendimento) {
                    $previsto = Carbon::parse($atendimento->data_inicio_agendamento);
                    $registrado = Carbon::parse($atendimento->iniciado_em);
                    $atraso = max(0, $registrado->diffInMinutes($previsto, false));

                    return [
                        optional($atendimento->funcionario)->nome ?? '—',
                        $previsto->format('d/m/Y'),
                        $previsto->format('H:i'),
                        $registrado->format('H:i'),
                        $atraso . ' min',
                    ];
                })
                ->all();

            $saidasRows = Atendimento::query()
                ->with('funcionario:id,nome')
                ->whereIn('funcionario_id', $funcionariosAtivosIds)
                ->whereNotNull('data_fim_agendamento')
                ->whereNotNull('finalizado_em')
                ->whereBetween('data_fim_agendamento', [$inicioMes, $fimMes])
                ->whereColumn('finalizado_em', '<', 'data_fim_agendamento')
                ->get()
                ->map(function (Atendimento $atendimento) {
                    $previsto = Carbon::parse($atendimento->data_fim_agendamento);
                    $registrado = Carbon::parse($atendimento->finalizado_em);
                    $antecipacao = max(0, $previsto->diffInMinutes($registrado));

                    return [
                        optional($atendimento->funcionario)->nome ?? '—',
                        $previsto->format('d/m/Y'),
                        $previsto->format('H:i'),
                        $registrado->format('H:i'),
                        $antecipacao . ' min',
                    ];
                })
                ->all();

            $faltasRows = [];
            $atendimentosPorDiaFuncionario = $atendimentosMes
                ->groupBy(fn (Atendimento $atendimento) => Carbon::parse($atendimento->created_at)->toDateString() . '|' . (int) $atendimento->funcionario_id);

            $cursor = $inicioMes->copy()->startOfDay();
            $fimCursor = $fimMes->copy()->startOfDay();
            while ($cursor->lte($fimCursor)) {
                $dia = $cursor->copy();
                $diaStr = $dia->toDateString();
                $jornadasDia = $this->jornadasVigentesPorFuncionarioNoDia($dia, $funcionariosAtivosIds);
                $idsTrabalhaDia = collect($jornadasDia)
                    ->filter(fn (FuncionarioJornada $vinculo) => $this->funcionarioTrabalhaNoDia($vinculo, $dia))
                    ->keys()
                    ->all();

                foreach ($idsTrabalhaDia as $funcionarioId) {
                    $chave = $diaStr . '|' . (int) $funcionarioId;
                    if (!$atendimentosPorDiaFuncionario->has($chave)) {
                        $nome = optional($funcionariosPorId->get((int) $funcionarioId))->nome ?? '—';
                        $faltasRows[] = [$nome, $dia->format('d/m/Y')];
                    }
                }

                $cursor->addDay();
            }
        }

        return [
            'faltas_rows' => $faltasRows,
            'atrasos_rows' => $atrasosRows,
            'saidas_rows' => $saidasRows,
        ];
    }

    private function montarPerformance(Carbon $inicioPeriodo, Carbon $fimPeriodo, array $funcionariosIds): array
    {
        if (empty($funcionariosIds)) {
            return [
                'media_avaliacao_funcionario' => [
                    'title' => 'Média de avaliação por funcionário',
                    'description' => 'Score operacional (1-5) calculado a partir da pontualidade dos atendimentos agendados.',
                    'count' => 0,
                    'columns' => ['Funcionário', 'Média', 'Atendimentos Avaliados', 'Índice de Atraso'],
                    'rows' => [],
                ],
                'ranking_top5_tecnicos' => [
                    'title' => 'Ranking Top 5 técnicos',
                    'description' => 'Classificação por maior média de avaliação operacional no período filtrado.',
                    'count' => 0,
                    'columns' => ['Posição', 'Funcionário', 'Média', 'Atendimentos Avaliados'],
                    'rows' => [],
                ],
                'ranking_maior_indice_atraso' => [
                    'title' => 'Ranking de maior índice de atraso',
                    'description' => 'Percentual de atendimentos iniciados com atraso no período filtrado.',
                    'count' => 0,
                    'columns' => ['Posição', 'Funcionário', 'Índice de Atraso', 'Atendimentos Avaliados'],
                    'rows' => [],
                ],
                'ultima_avaliacao_negativa' => [
                    'title' => 'Última avaliação negativa recebida',
                    'description' => 'Último atendimento com nota operacional baixa (<= 2).',
                    'count' => 0,
                    'columns' => ['Atendimento', 'Funcionário', 'Data Prevista', 'Início Registrado', 'Atraso', 'Nota'],
                    'rows' => [],
                ],
            ];
        }

        $atendimentos = Atendimento::query()
            ->with('funcionario:id,nome')
            ->whereIn('funcionario_id', $funcionariosIds)
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

    private function montarRisco(Carbon $hoje, Carbon $limite, Collection $funcionariosAtivos, array $funcionariosComJornadaAtiva, array $funcionariosIds): array
    {
        $documentosVencendoRows = [];
        $asoVencidoRows = [];
        if (Schema::hasTable('funcionario_documentos')) {
            $documentosVencendoRows = FuncionarioDocumento::query()
                ->with('funcionario:id,nome')
                ->whereIn('funcionario_id', $funcionariosIds)
                ->whereNotNull('data_vencimento')
                ->whereBetween('data_vencimento', [$hoje->toDateString(), $limite->toDateString()])
                ->orderBy('data_vencimento')
                ->get();

            $asoVencidoRows = FuncionarioDocumento::query()
                ->with('funcionario:id,nome')
                ->whereIn('funcionario_id', $funcionariosIds)
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
                ->whereIn('funcionario_id', $funcionariosIds)
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
                ->whereIn('funcionario_id', $funcionariosIds)
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

    private function funcionariosComJornadaAtiva(Carbon $data, array $funcionariosIds = []): array
    {
        if (!Schema::hasTable('funcionario_jornadas')) {
            return [];
        }

        $query = FuncionarioJornada::query();
        if (!empty($funcionariosIds)) {
            $query->whereIn('funcionario_id', $funcionariosIds);
        }

        return $query
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
            ->with([
                'funcionario:id,nome',
                'jornada:id,hora_inicio,hora_fim,hora_entrada_padrao,hora_saida_padrao,intervalo_minutos,tipo_jornada,dias_trabalhados,tolerancia_entrada_min',
                'jornada.escalas:jornada_id,dia_semana,hora_entrada,hora_saida,intervalo_minutos',
                'jornada.feriados:id,nome,data,ativo,recorrente_anual',
            ])
            ->whereIn('funcionario_id', $funcionariosIds)
            ->whereDate('data_inicio', '<=', $data->toDateString())
            ->where(function ($query) use ($data) {
                $query->whereNull('data_fim')
                    ->orWhereDate('data_fim', '>=', $data->toDateString());
            })
            ->orderByDesc('data_inicio')
            ->get()
            ->groupBy('funcionario_id')
            ->map(fn ($itens) => $itens->first())
            ->all();
    }

    private function funcionarioTrabalhaNoDia(FuncionarioJornada $vinculo, Carbon $dia): bool
    {
        $jornada = $vinculo->jornada;
        if (!$jornada instanceof Jornada) {
            return $dia->isWeekday();
        }

        $diaSemana = (int) $dia->dayOfWeekIso;
        $trabalha = false;

        if (($jornada->tipo_jornada ?? 'fixa') === 'escala') {
            $trabalha = $jornada->escalas->contains(fn ($escala) => (int) $escala->dia_semana === $diaSemana);
        } else {
            $dias = collect($jornada->dias_trabalhados ?? [1, 2, 3, 4, 5])
                ->map(fn ($d) => (int) $d)
                ->all();
            $trabalha = in_array($diaSemana, $dias, true);
        }

        if (!$trabalha) {
            return false;
        }

        return !$jornada->feriados->contains(function ($feriado) use ($dia) {
            if (!$feriado->ativo || !$feriado->data) {
                return false;
            }

            if ($feriado->recorrente_anual) {
                return $feriado->data->format('m-d') === $dia->format('m-d');
            }

            return $feriado->data->toDateString() === $dia->toDateString();
        });
    }

    private function resolverHorariosJornadaNoDia(FuncionarioJornada $vinculo, Carbon $dia): array
    {
        $jornada = $vinculo->jornada;
        if (!$jornada instanceof Jornada) {
            return [
                'hora_entrada' => null,
                'hora_saida' => null,
            ];
        }

        if (($jornada->tipo_jornada ?? 'fixa') === 'escala') {
            $diaSemana = (int) $dia->dayOfWeekIso;
            $escalaDia = $jornada->escalas->first(fn ($escala) => (int) $escala->dia_semana === $diaSemana);

            if ($escalaDia) {
                return [
                    'hora_entrada' => $escalaDia->hora_entrada,
                    'hora_saida' => $escalaDia->hora_saida,
                ];
            }
        }

        return [
            'hora_entrada' => $jornada->hora_entrada_padrao ?: $jornada->hora_inicio,
            'hora_saida' => $jornada->hora_saida_padrao ?: $jornada->hora_fim,
        ];
    }

    private function ehFeriadoAtreladoNaJornada(FuncionarioJornada $vinculo, Carbon $dia): bool
    {
        $jornada = $vinculo->jornada;
        if (!$jornada instanceof Jornada) {
            return false;
        }

        return $jornada->feriados->contains(function ($feriado) use ($dia) {
            if (!$feriado->ativo || !$feriado->data) {
                return false;
            }

            if ($feriado->recorrente_anual) {
                return $feriado->data->format('m-d') === $dia->format('m-d');
            }

            return $feriado->data->toDateString() === $dia->toDateString();
        });
    }

    private function calcularBancoHorasPorFuncionario(Carbon $inicio, Carbon $fim, array $funcionariosIds): array
    {
        if (empty($funcionariosIds)) {
            return [];
        }

        $trabalhado = $this->obterTrabalhadoSegundosPorFuncionario($inicio, $fim, $funcionariosIds);

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
                ->map(fn ($itens) => $itens->first());
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

    private function obterTrabalhadoSegundosPorFuncionario(Carbon $inicio, Carbon $fim, array $funcionariosIds)
    {
        $usaRegistroLegal = Schema::hasTable('registro_pontos_portal')
            && Schema::hasTable('funcionario_jornadas')
            && Schema::hasTable('jornadas');

        if ($usaRegistroLegal) {
            $dataInicio = $inicio->toDateString();
            $dataFim = $fim->toDateString();

            $registros = RegistroPontoPortal::query()
                ->whereIn('funcionario_id', $funcionariosIds)
                ->where(function ($query) use ($dataInicio, $dataFim, $inicio, $fim) {
                    $query->whereBetween('data_referencia', [$dataInicio, $dataFim])
                        ->orWhereBetween('entrada_em', [$inicio, $fim])
                        ->orWhereBetween('saida_em', [$inicio, $fim]);
                })
                ->get(['funcionario_id', 'entrada_em', 'saida_em', 'intervalo_inicio_em', 'intervalo_fim_em']);

            return $registros
                ->groupBy('funcionario_id')
                ->map(function ($itens) {
                    return (int) $itens->sum(fn (RegistroPontoPortal $registro) => $this->calcularSegundosTrabalhadosRegistroLegal($registro));
                });
        }

        return Atendimento::query()
            ->whereIn('funcionario_id', $funcionariosIds)
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('funcionario_id')
            ->select('funcionario_id', DB::raw('COALESCE(SUM(tempo_execucao_segundos),0) as total'))
            ->pluck('total', 'funcionario_id');
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

        $segundos = $fim->diffInSeconds($inicio, true);
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

    private function calcularSegundosTrabalhadosRegistroLegal(RegistroPontoPortal $registro): int
    {
        if (!$registro->entrada_em || !$registro->saida_em) {
            return 0;
        }

        $entrada = Carbon::parse($registro->entrada_em);
        $saida = Carbon::parse($registro->saida_em);

        if ($saida->lessThanOrEqualTo($entrada)) {
            return 0;
        }

        $segundos = $saida->diffInSeconds($entrada, true);

        if ($registro->intervalo_inicio_em && $registro->intervalo_fim_em) {
            $inicioIntervalo = Carbon::parse($registro->intervalo_inicio_em);
            $fimIntervalo = Carbon::parse($registro->intervalo_fim_em);
            if ($fimIntervalo->gt($inicioIntervalo)) {
                $segundos -= $fimIntervalo->diffInSeconds($inicioIntervalo, true);
            }
        }

        return max(0, $segundos);
    }
}
