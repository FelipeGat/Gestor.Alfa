<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Atendimento;
use App\Models\Feriado;
use App\Models\FuncionarioJornada;
use App\Models\Funcionario;
use App\Models\Jornada;
use App\Models\RegistroPontoPortal;
use App\Models\RhAjustePonto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PontoJornadaController extends Controller
{
    private const SEGUNDOS_META_DIARIA = 28800;

    public function index(Request $request)
    {
        $funcionarioId = $request->integer('funcionario_id');
        $inicio = $request->filled('inicio') ? Carbon::parse($request->input('inicio'))->startOfDay() : Carbon::now()->startOfMonth();
        $fim = $request->filled('fim') ? Carbon::parse($request->input('fim'))->endOfDay() : Carbon::now()->endOfMonth();

        if ($inicio->gt($fim)) {
            [$inicio, $fim] = [$fim->copy()->startOfDay(), $inicio->copy()->endOfDay()];
        }

        $funcionariosQuery = Funcionario::query()->where('ativo', true)->orderBy('nome');
        if ($funcionarioId) {
            $funcionariosQuery->where('id', $funcionarioId);
        }

        $funcionarios = Funcionario::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome']);
        $funcionariosEscopo = $funcionariosQuery->get(['id', 'nome']);

        $jornadaLegal = $this->montarJornadaLegal($funcionariosEscopo, $inicio, $fim, $request);
        $indicadores = $this->montarIndicadoresProdutividade($funcionariosEscopo, $inicio, $fim, $jornadaLegal['resumo']);

        $ajustesQuery = RhAjustePonto::query()
            ->with(['funcionario:id,nome', 'ajustadoPor:id,name', 'autorizadoPor:id,name'])
            ->whereBetween('ajustado_em', [$inicio, $fim]);

        if ($funcionarioId) {
            $ajustesQuery->where('funcionario_id', $funcionarioId);
        }

        $ajustes = $ajustesQuery->orderByDesc('ajustado_em')->limit(50)->get();

        $autorizadores = User::query()
            ->join('funcionarios', 'funcionarios.id', '=', 'users.funcionario_id')
            ->whereNotNull('users.funcionario_id')
            ->where('funcionarios.ativo', true)
            ->orderBy('funcionarios.nome')
            ->get([
                'users.id',
                DB::raw('funcionarios.nome as name'),
            ]);

        return view('rh.ponto-jornada', [
            'funcionarios' => $funcionarios,
            'jornadaLegal' => $jornadaLegal,
            'indicadores' => $indicadores,
            'ajustes' => $ajustes,
            'autorizadores' => $autorizadores,
            'tiposAjuste' => $this->tiposAjuste(),
            'filtros' => [
                'funcionario_id' => $funcionarioId,
                'inicio' => $inicio->toDateString(),
                'fim' => $fim->toDateString(),
            ],
        ]);
    }

    public function storeAjuste(Request $request)
    {
        $validated = $request->validate([
            'funcionario_id' => ['required', 'exists:funcionarios,id'],
            'atendimento_id' => ['nullable', 'exists:atendimentos,id'],
            'minutos_ajuste' => ['required', 'integer', 'between:-720,720'],
            'tipo_ajuste' => ['required', 'in:correcao_batida,hora_extra,desconto_falta,compensacao,outro'],
            'autorizado_por_user_id' => ['required', 'exists:users,id'],
            'justificativa' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'justificativa.required' => 'A justificativa do ajuste manual é obrigatória.',
            'justificativa.min' => 'A justificativa deve ter ao menos 10 caracteres.',
            'tipo_ajuste.required' => 'Selecione o tipo de ajuste.',
            'autorizado_por_user_id.required' => 'Informe o usuário que autorizou o ajuste.',
        ]);

        RhAjustePonto::create([
            'funcionario_id' => (int) $validated['funcionario_id'],
            'atendimento_id' => $validated['atendimento_id'] ?? null,
            'minutos_ajuste' => (int) $validated['minutos_ajuste'],
            'tipo_ajuste' => $validated['tipo_ajuste'],
            'justificativa' => trim($validated['justificativa']),
            'ajustado_por_user_id' => Auth::id(),
            'autorizado_por_user_id' => (int) $validated['autorizado_por_user_id'],
            'ajustado_em' => now(),
        ]);

        return redirect()
            ->route('rh.ponto-jornada.index', $request->only(['funcionario_id', 'inicio', 'fim']))
            ->with('success', 'Ajuste manual de ponto registrado com sucesso.');
    }

    private function montarJornadaLegal(Collection $funcionariosEscopo, Carbon $inicio, Carbon $fim, Request $request): array
    {
        $rows = collect();
        $resumo = [
            'dias_previstos' => 0,
            'dias_com_presenca' => 0,
            'dias_pontuais' => 0,
            'segundos_trabalhados' => 0,
            'segundos_previstos' => 0,
            'horas_extras_segundos' => 0,
            'banco_horas_segundos' => 0,
        ];

        if ($funcionariosEscopo->isEmpty()) {
            return [
                'rows' => new LengthAwarePaginator([], 0, 25),
                'resumo' => $resumo,
            ];
        }

        $funcionariosIds = $funcionariosEscopo->pluck('id')->all();

        $registros = Schema::hasTable('registro_pontos_portal')
            ? RegistroPontoPortal::query()
                ->whereIn('funcionario_id', $funcionariosIds)
                ->whereBetween('data_referencia', [$inicio->toDateString(), $fim->toDateString()])
                ->get()
                ->keyBy(fn (RegistroPontoPortal $registro) => $registro->funcionario_id . '|' . $registro->data_referencia->toDateString())
            : collect();

        $jornadasPorFuncionario = $this->jornadasAtivasPorFuncionario($funcionariosIds, $inicio, $fim);

        $cursor = $inicio->copy()->startOfDay();
        $fimDia = $fim->copy()->startOfDay();

        while ($cursor->lte($fimDia)) {
            foreach ($funcionariosEscopo as $funcionario) {
                $chave = $funcionario->id . '|' . $cursor->toDateString();
                $dataReferencia = $cursor->toDateString();
                $registro = $registros->get($chave);
                $jornadaVinculo = $this->jornadaVigenteNoDia($jornadasPorFuncionario->get($funcionario->id, collect()), $cursor);

                if (!$jornadaVinculo && !$registro) {
                    continue;
                }

                $regra = $this->resolverRegraDia($jornadaVinculo?->jornada, $cursor);

                if (!$regra['trabalha'] && !$registro) {
                    continue;
                }

                if ($regra['trabalha'] && $jornadaVinculo) {
                    $resumo['dias_previstos']++;
                }

                if (!$jornadaVinculo) {
                    $segundosTrabalhados = $this->calcularSegundosTrabalhados($registro);
                    $resumo['segundos_trabalhados'] += $segundosTrabalhados;

                    if ($segundosTrabalhados > 0) {
                        $resumo['banco_horas_segundos'] += $segundosTrabalhados;
                    }

                    $rows->push([
                        'funcionario' => $funcionario->nome,
                        'data' => $cursor->format('d/m/Y'),
                        'eh_domingo' => $regra['eh_domingo'],
                        'eh_feriado' => $regra['eh_feriado'],
                        'feriado_nome' => $regra['feriado_nome'],
                        'entrada' => $this->formatarHorario($registro?->entrada_em),
                        'intervalo_inicio' => $this->formatarHorario($registro?->intervalo_inicio_em),
                        'intervalo_fim' => $this->formatarHorario($registro?->intervalo_fim_em),
                        'saida' => $this->formatarHorario($registro?->saida_em),
                        'segundos_trabalhados' => $segundosTrabalhados,
                        'segundos_previstos' => 0,
                        'total' => $this->formatarSegundos($segundosTrabalhados),
                        'status' => 'Sem jornada',
                    ]);

                    continue;
                }

                $status = $this->calcularStatusLegal($registro, $cursor, $regra);
                $segundosTrabalhados = $this->calcularSegundosTrabalhados($registro);
                $segundosPrevistos = $regra['trabalha'] ? $this->segundosPrevistosDaRegra($regra) : 0;

                if ($regra['trabalha']) {
                    $segundosExtrasDia = max(0, $segundosTrabalhados - $segundosPrevistos);
                    if ($segundosExtrasDia >= ($regra['minimo_horas_para_extra'] * 60)) {
                        $resumo['horas_extras_segundos'] += $segundosExtrasDia;
                    }

                    $resumo['banco_horas_segundos'] += ($segundosTrabalhados - self::SEGUNDOS_META_DIARIA);
                } elseif ($segundosTrabalhados > 0) {
                    $resumo['horas_extras_segundos'] += $segundosTrabalhados;
                    $resumo['banco_horas_segundos'] += $segundosTrabalhados;
                }

                if (!$regra['trabalha'] && $registro) {
                    $status = $regra['eh_feriado'] ? 'Extra feriado' : 'Extra';
                }

                $resumo['segundos_previstos'] += $segundosPrevistos;
                $resumo['segundos_trabalhados'] += $segundosTrabalhados;

                if ($regra['trabalha'] && $status !== 'Falta') {
                    $resumo['dias_com_presenca']++;
                }

                if ($regra['trabalha'] && $status === 'OK') {
                    $resumo['dias_pontuais']++;
                }

                $rows->push([
                    'funcionario' => $funcionario->nome,
                    'data' => $cursor->format('d/m/Y'),
                    'eh_domingo' => $regra['eh_domingo'],
                    'eh_feriado' => $regra['eh_feriado'],
                    'feriado_nome' => $regra['feriado_nome'],
                    'entrada' => $this->formatarHorario($registro?->entrada_em),
                    'intervalo_inicio' => $this->formatarHorario($registro?->intervalo_inicio_em),
                    'intervalo_fim' => $this->formatarHorario($registro?->intervalo_fim_em),
                    'saida' => $this->formatarHorario($registro?->saida_em),
                    'segundos_trabalhados' => $segundosTrabalhados,
                    'segundos_previstos' => $segundosPrevistos,
                    'total' => $this->formatarSegundos($segundosTrabalhados),
                    'status' => $status,
                ]);
            }

            $cursor->addDay();
        }

        $paginaAtual = LengthAwarePaginator::resolveCurrentPage();
        $porPagina = 25;
        $itemsPagina = $rows->forPage($paginaAtual, $porPagina)->values();

        $paginator = new LengthAwarePaginator(
            $itemsPagina,
            $rows->count(),
            $porPagina,
            $paginaAtual,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return [
            'rows' => $paginator,
            'resumo' => $resumo,
        ];
    }

    private function mapaFeriadosNacionais(Carbon $inicio, Carbon $fim): array
    {
        $mapa = [];
        $anoInicio = (int) $inicio->year;
        $anoFim = (int) $fim->year;

        for ($ano = $anoInicio; $ano <= $anoFim; $ano++) {
            $fixos = [
                sprintf('%04d-01-01', $ano) => 'Confraternização Universal',
                sprintf('%04d-04-21', $ano) => 'Tiradentes',
                sprintf('%04d-05-01', $ano) => 'Dia do Trabalho',
                sprintf('%04d-09-07', $ano) => 'Independência do Brasil',
                sprintf('%04d-10-12', $ano) => 'Nossa Senhora Aparecida',
                sprintf('%04d-11-02', $ano) => 'Finados',
                sprintf('%04d-11-15', $ano) => 'Proclamação da República',
                sprintf('%04d-11-20', $ano) => 'Dia da Consciência Negra',
                sprintf('%04d-12-25', $ano) => 'Natal',
            ];

            $pascoa = Carbon::createFromTimestamp(easter_date($ano))->startOfDay();
            $moveis = [
                $pascoa->copy()->subDays(48)->toDateString() => 'Carnaval',
                $pascoa->copy()->subDays(47)->toDateString() => 'Carnaval',
                $pascoa->copy()->subDays(2)->toDateString() => 'Sexta-feira Santa',
                $pascoa->copy()->toDateString() => 'Páscoa',
                $pascoa->copy()->addDays(60)->toDateString() => 'Corpus Christi',
            ];

            foreach ($fixos + $moveis as $data => $nome) {
                if ($data >= $inicio->toDateString() && $data <= $fim->toDateString()) {
                    $mapa[$data] = $nome;
                }
            }
        }

        return $mapa;
    }

    private function montarIndicadoresProdutividade(Collection $funcionariosEscopo, Carbon $inicio, Carbon $fim, array $resumoJornadaLegal): array
    {
        if ($funcionariosEscopo->isEmpty()) {
            return [
                'total_tempo_atendimento_segundos' => 0,
                'tempo_medio_segundos' => 0,
                'total_atendimentos' => 0,
                'produtividade_percentual' => 0,
                'tempo_ocioso_segundos' => 0,
                'assiduidade_mensal' => 0,
                'pontualidade_mensal' => 0,
                'horas_extras_segundos' => 0,
                'banco_horas_acumulado_segundos' => 0,
            ];
        }

        $funcionariosIds = $funcionariosEscopo->pluck('id')->all();

        $baseAtendimentos = Atendimento::query()
            ->whereIn('funcionario_id', $funcionariosIds)
            ->whereBetween('created_at', [$inicio, $fim]);

        $totaisAtendimento = (clone $baseAtendimentos)
            ->selectRaw('COALESCE(SUM(tempo_execucao_segundos), 0) as total_segundos, COUNT(*) as total_atendimentos')
            ->first();

        $totalTempoAtendimento = (int) ($totaisAtendimento->total_segundos ?? 0);
        $totalAtendimentos = (int) ($totaisAtendimento->total_atendimentos ?? 0);
        $tempoMedio = $totalAtendimentos > 0 ? (int) floor($totalTempoAtendimento / $totalAtendimentos) : 0;

        $jornadaLegalTotal = (int) ($resumoJornadaLegal['segundos_trabalhados'] ?? 0);
        $produtividade = $jornadaLegalTotal > 0 ? round(($totalTempoAtendimento / $jornadaLegalTotal) * 100, 2) : 0;
        $tempoOcioso = max(0, $jornadaLegalTotal - $totalTempoAtendimento);

        $diasPrevistos = (int) ($resumoJornadaLegal['dias_previstos'] ?? 0);
        $diasComPresenca = (int) ($resumoJornadaLegal['dias_com_presenca'] ?? 0);
        $diasPontuais = (int) ($resumoJornadaLegal['dias_pontuais'] ?? 0);

        $assiduidadeMensal = $diasPrevistos > 0 ? round(($diasComPresenca / $diasPrevistos) * 100, 2) : 0;
        $pontualidadeMensal = $diasPrevistos > 0 ? round(($diasPontuais / $diasPrevistos) * 100, 2) : 0;

        $horasExtrasSegundos = (int) ($resumoJornadaLegal['horas_extras_segundos'] ?? 0);

        $ajustesSegundos = (int) RhAjustePonto::query()
            ->whereIn('funcionario_id', $funcionariosIds)
            ->whereBetween('ajustado_em', [$inicio, $fim])
            ->sum(DB::raw('minutos_ajuste * 60'));

        $bancoHorasAcumulado = (int) ($resumoJornadaLegal['banco_horas_segundos'] ?? 0) + $ajustesSegundos;

        return [
            'total_tempo_atendimento_segundos' => $totalTempoAtendimento,
            'tempo_medio_segundos' => $tempoMedio,
            'total_atendimentos' => $totalAtendimentos,
            'produtividade_percentual' => $produtividade,
            'tempo_ocioso_segundos' => $tempoOcioso,
            'assiduidade_mensal' => $assiduidadeMensal,
            'pontualidade_mensal' => $pontualidadeMensal,
            'horas_extras_segundos' => $horasExtrasSegundos,
            'banco_horas_acumulado_segundos' => $bancoHorasAcumulado,
        ];
    }

    private function jornadasAtivasPorFuncionario(array $funcionariosIds, Carbon $inicio, Carbon $fim): Collection
    {
        if (!Schema::hasTable('funcionario_jornadas') || !Schema::hasTable('jornadas') || empty($funcionariosIds)) {
            return collect();
        }

        return FuncionarioJornada::query()
            ->with(['jornada' => function ($query) {
                $query
                    ->with(['escalas', 'feriados' => fn ($feriados) => $feriados->where('ativo', true)])
                    ->select([
                        'id',
                        'tipo_jornada',
                        'dias_trabalhados',
                        'hora_entrada_padrao',
                        'hora_saida_padrao',
                        'hora_inicio',
                        'hora_fim',
                        'intervalo_minutos',
                        'tolerancia_entrada_min',
                        'tolerancia_saida_min',
                        'tolerancia_intervalo_min',
                        'minimo_horas_para_extra',
                    ]);
            }])
            ->whereIn('funcionario_id', $funcionariosIds)
            ->whereDate('data_inicio', '<=', $fim->toDateString())
            ->where(function ($query) use ($inicio) {
                $query->whereNull('data_fim')
                    ->orWhereDate('data_fim', '>=', $inicio->toDateString());
            })
            ->orderBy('data_inicio')
            ->get()
            ->groupBy('funcionario_id');
    }

    private function jornadaVigenteNoDia(Collection $vinculos, Carbon $dia): ?FuncionarioJornada
    {
        return $vinculos
            ->filter(function (FuncionarioJornada $vinculo) use ($dia) {
                $inicio = Carbon::parse($vinculo->data_inicio)->startOfDay();
                $fim = $vinculo->data_fim ? Carbon::parse($vinculo->data_fim)->endOfDay() : null;

                if ($dia->lt($inicio)) {
                    return false;
                }

                if ($fim && $dia->gt($fim)) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('data_inicio')
            ->first();
    }

    private function calcularStatusLegal(?RegistroPontoPortal $registro, Carbon $dia, array $regra): string
    {
        if (!$registro) {
            return 'Falta';
        }

        if (!$registro->entrada_em && !$registro->saida_em) {
            return 'Falta';
        }

        $intervaloIncompleto = ($registro->intervalo_inicio_em && !$registro->intervalo_fim_em)
            || (!$registro->intervalo_inicio_em && $registro->intervalo_fim_em);

        if (!$registro->entrada_em || !$registro->saida_em || $intervaloIncompleto) {
            return 'Incompleto';
        }

        $inicioPrevisto = Carbon::parse($dia->toDateString() . ' ' . $regra['hora_entrada']);
        $saidaPrevista = Carbon::parse($dia->toDateString() . ' ' . $regra['hora_saida']);
        if ($saidaPrevista->lessThanOrEqualTo($inicioPrevisto)) {
            $saidaPrevista->addDay();
        }

        $entrada = Carbon::parse($registro->entrada_em);
        $saida = Carbon::parse($registro->saida_em);

        $limiteEntrada = $inicioPrevisto->copy()->addMinutes((int) $regra['tolerancia_entrada_min']);
        if ($entrada->gt($limiteEntrada)) {
            return 'Atraso';
        }

        $limiteSaida = $saidaPrevista->copy()->subMinutes((int) $regra['tolerancia_saida_min']);
        if ($saida->lt($limiteSaida)) {
            return 'Saída antecipada';
        }

        if ($registro->intervalo_inicio_em && $registro->intervalo_fim_em) {
            $inicioIntervalo = Carbon::parse($registro->intervalo_inicio_em);
            $fimIntervalo = Carbon::parse($registro->intervalo_fim_em);
            $intervaloReal = max(0, $fimIntervalo->diffInMinutes($inicioIntervalo, false));
            $intervaloMinimoAceito = max(0, (int) $regra['intervalo_minutos'] - (int) $regra['tolerancia_intervalo_min']);

            if ($intervaloReal < $intervaloMinimoAceito) {
                return 'Alerta intervalo';
            }
        }

        return 'OK';
    }

    private function resolverRegraDia(?Jornada $jornada, Carbon $dia): array
    {
        $horaEntrada = $jornada?->hora_entrada_padrao ?: $jornada?->hora_inicio;
        $horaSaida = $jornada?->hora_saida_padrao ?: $jornada?->hora_fim;
        $intervalo = (int) ($jornada?->intervalo_minutos ?? 0);
        $diaSemana = (int) $dia->dayOfWeekIso;

        $feriado = $this->feriadoAtreladoNoDia($jornada, $dia);

        $trabalha = $dia->isWeekday();
        if ($jornada instanceof Jornada) {
            $trabalha = false;

            if (($jornada->tipo_jornada ?? 'fixa') === 'escala') {
                $escalaDia = $jornada->escalas->firstWhere('dia_semana', $diaSemana);
                if ($escalaDia) {
                    $trabalha = true;
                    $horaEntrada = $escalaDia->hora_entrada;
                    $horaSaida = $escalaDia->hora_saida;
                    $intervalo = (int) $escalaDia->intervalo_minutos;
                }
            } else {
                $dias = collect($jornada->dias_trabalhados ?? [1, 2, 3, 4, 5])
                    ->map(fn ($d) => (int) $d)
                    ->all();
                $trabalha = in_array($diaSemana, $dias, true);
            }

            if ($feriado) {
                $trabalha = false;
            }
        }

        return [
            'trabalha' => $trabalha,
            'eh_domingo' => $dia->isSunday(),
            'eh_feriado' => $feriado !== null,
            'feriado_nome' => $feriado?->nome,
            'hora_entrada' => $horaEntrada,
            'hora_saida' => $horaSaida,
            'intervalo_minutos' => $intervalo,
            'tolerancia_entrada_min' => (int) ($jornada?->tolerancia_entrada_min ?? 0),
            'tolerancia_saida_min' => (int) ($jornada?->tolerancia_saida_min ?? 0),
            'tolerancia_intervalo_min' => (int) ($jornada?->tolerancia_intervalo_min ?? 0),
            'minimo_horas_para_extra' => (int) ($jornada?->minimo_horas_para_extra ?? 0),
        ];
    }

    private function feriadoAtreladoNoDia(?Jornada $jornada, Carbon $dia): ?Feriado
    {
        if (!$jornada) {
            return null;
        }

        return $jornada->feriados->first(function (Feriado $feriado) use ($dia) {
            if (!$feriado->ativo) {
                return false;
            }

            if ($feriado->recorrente_anual) {
                return $feriado->data
                    && $feriado->data->format('m-d') === $dia->format('m-d');
            }

            return $feriado->data
                && $feriado->data->toDateString() === $dia->toDateString();
        });
    }

    private function segundosPrevistosDaRegra(array $regra): int
    {
        if (empty($regra['hora_entrada']) || empty($regra['hora_saida'])) {
            return 0;
        }

        $base = Carbon::today();
        $inicio = Carbon::parse($base->toDateString() . ' ' . $regra['hora_entrada']);
        $fim = Carbon::parse($base->toDateString() . ' ' . $regra['hora_saida']);

        if ($fim->lessThanOrEqualTo($inicio)) {
            $fim->addDay();
        }

        $segundos = $fim->diffInSeconds($inicio);

        return max(0, $segundos - (((int) $regra['intervalo_minutos']) * 60));
    }

    private function calcularSegundosTrabalhados(?RegistroPontoPortal $registro): int
    {
        if (!$registro || !$registro->entrada_em || !$registro->saida_em) {
            return 0;
        }

        $entrada = Carbon::parse($registro->entrada_em);
        $saida = Carbon::parse($registro->saida_em);

        if ($saida->lessThanOrEqualTo($entrada)) {
            return 0;
        }

        $segundos = $saida->diffInSeconds($entrada);

        if ($registro->intervalo_inicio_em && $registro->intervalo_fim_em) {
            $inicioIntervalo = Carbon::parse($registro->intervalo_inicio_em);
            $fimIntervalo = Carbon::parse($registro->intervalo_fim_em);

            if ($fimIntervalo->gt($inicioIntervalo)) {
                $segundos -= $fimIntervalo->diffInSeconds($inicioIntervalo);
            }
        }

        return max(0, $segundos);
    }

    private function segundosJornadaDiaria($jornada): int
    {
        if (!$jornada) {
            return 0;
        }

        $base = Carbon::today();
        $inicio = Carbon::parse($base->toDateString() . ' ' . $jornada->hora_inicio);
        $fim = Carbon::parse($base->toDateString() . ' ' . $jornada->hora_fim);

        if ($fim->lessThanOrEqualTo($inicio)) {
            $fim->addDay();
        }

        $segundos = $fim->diffInSeconds($inicio);

        return max(0, $segundos - (((int) $jornada->intervalo_minutos) * 60));
    }

    private function formatarHorario($valor): string
    {
        if (!$valor) {
            return '—';
        }

        return Carbon::parse($valor)->format('H:i');
    }

    private function formatarSegundos(int $segundos): string
    {
        $segundos = max(0, $segundos);
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);

        return sprintf('%02d:%02d', $horas, $minutos);
    }

    private function tiposAjuste(): array
    {
        return [
            'correcao_batida' => 'Correção de Batida',
            'hora_extra' => 'Hora Extra',
            'desconto_falta' => 'Desconto/Falta',
            'compensacao' => 'Compensação',
            'outro' => 'Outro',
        ];
    }
}
