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
use App\Models\RhFechamentoPonto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PontoJornadaController extends Controller
{
    private const SEGUNDOS_META_DIARIA = 28800;
    private const SEGUNDOS_META_MEIO_PERIODO = 14400;
    private const SEGUNDOS_META_SEMANAL = 158400;

    public function index(Request $request)
    {
        $funcionarioId = $request->filled('funcionario_id') ? $request->integer('funcionario_id') : null;

        $inicioInformado = $request->filled('inicio');
        $fimInformado = $request->filled('fim');

        if ($inicioInformado) {
            $inicio = Carbon::parse($request->input('inicio'))->startOfDay();
        } elseif (!$funcionarioId) {
            $inicio = Carbon::today()->startOfDay();
        } else {
            $inicio = Carbon::now()->startOfMonth();
        }

        if ($fimInformado) {
            $fim = Carbon::parse($request->input('fim'))->endOfDay();
        } elseif (!$funcionarioId) {
            $fim = Carbon::today()->endOfDay();
        } else {
            $fim = Carbon::now()->endOfMonth();
        }

        if (
            !$funcionarioId
            && $inicioInformado
            && $fimInformado
            && $inicio->isSameDay(Carbon::today()->startOfMonth())
            && $fim->isSameDay(Carbon::today()->endOfMonth())
        ) {
            $inicio = Carbon::today()->startOfDay();
            $fim = Carbon::today()->endOfDay();
        }

        if ($inicio->gt($fim)) {
            [$inicio, $fim] = [$fim->copy()->startOfDay(), $inicio->copy()->endOfDay()];
        }

        $funcionariosQuery = Funcionario::query()->where('ativo', true)->orderBy('nome');
        if ($funcionarioId) {
            $funcionariosQuery->where('id', $funcionarioId);
        }

        $funcionarios = Funcionario::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome']);
        $funcionariosEscopo = $funcionariosQuery->get(['id', 'nome']);

        $funcionariosEscopoIds = $funcionariosEscopo->pluck('id')->all();
        $ajustesSecaoUmColecao = RhAjustePonto::query()
            ->whereIn('funcionario_id', $funcionariosEscopoIds)
            ->whereBetween('ajustado_em', [$inicio, $fim])
            ->get(['funcionario_id', 'ajustado_em', 'justificativa']);

        $ajustesSecaoUmLinhas = collect();
        $ajustesSecaoUmCampos = collect();
        $ajustesSecaoUmColecao->each(function (RhAjustePonto $ajuste) use (&$ajustesSecaoUmLinhas, &$ajustesSecaoUmCampos) {
                $data = optional($ajuste->ajustado_em)?->toDateString();
                if (!$data) {
                    return;
                }

            $chaveLinha = ((int) $ajuste->funcionario_id) . '|' . $data;
            $ajustesSecaoUmLinhas->put($chaveLinha, true);

            $campoBatida = $this->extrairCampoBatidaDeAjuste($ajuste);
            if ($campoBatida) {
                $ajustesSecaoUmCampos->put($chaveLinha . '|' . $campoBatida, true);
            }
        });

        $jornadaLegal = $this->montarJornadaLegal($funcionariosEscopo, $inicio, $fim, $request, $ajustesSecaoUmLinhas, $ajustesSecaoUmCampos);
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

        $competenciaAtual = $inicio->copy()->startOfMonth();
        $fechamentoDisponivel = Schema::hasTable('rh_fechamentos_ponto');
        $fechamentosCompetencia = $fechamentoDisponivel
            ? RhFechamentoPonto::query()
                ->where('competencia', $competenciaAtual->toDateString())
                ->whereIn('funcionario_id', $funcionarios->pluck('id')->all())
                ->get(['funcionario_id', 'fechado_em'])
                ->keyBy(fn (RhFechamentoPonto $item) => (int) $item->funcionario_id)
            : collect();

        return view('rh.ponto-jornada', [
            'funcionarios' => $funcionarios,
            'jornadaLegal' => $jornadaLegal,
            'indicadores' => $indicadores,
            'ajustes' => $ajustes,
            'autorizadores' => $autorizadores,
            'fechamentosCompetencia' => $fechamentosCompetencia,
            'competenciaAtualFechamento' => $competenciaAtual->toDateString(),
            'fechamentoDisponivel' => $fechamentoDisponivel,
            'tiposAjuste' => $this->tiposAjuste(),
            'filtros' => [
                'funcionario_id' => $funcionarioId,
                'inicio' => $inicio->toDateString(),
                'fim' => $fim->toDateString(),
            ],
        ]);
    }

    public function storeAjusteSecaoUm(Request $request)
    {
        $validated = $request->validate([
            'funcionario_id' => ['required', 'exists:funcionarios,id'],
            'data_referencia' => ['required', 'date'],
            'campo_batida' => ['required', 'in:entrada_em,intervalo_inicio_em,intervalo_fim_em,saida_em'],
            'horario_batida' => ['required', 'date_format:H:i'],
            'tipo_ajuste' => ['required', 'in:esquecimento,batida_duplicidade,atestado_medico,acompanhamento_medico'],
            'motivo_lancamento_manual' => ['required', 'string', 'min:5', 'max:1000'],
            'autorizado_por_user_id' => ['required', 'exists:users,id'],
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date'],
            'funcionario_id_filtro' => ['nullable', 'integer', 'exists:funcionarios,id'],
        ], [
            'tipo_ajuste.required' => 'Selecione o tipo do ajuste.',
            'horario_batida.required' => 'Informe o horário da batida.',
            'horario_batida.date_format' => 'O horário da batida deve estar no formato HH:MM.',
            'motivo_lancamento_manual.required' => 'Informe o motivo do lançamento manual.',
            'autorizado_por_user_id.required' => 'Informe quem autorizou.',
        ]);

        $funcionarioId = (int) $validated['funcionario_id'];
        $dataReferencia = Carbon::parse($validated['data_referencia'])->startOfDay();
        $campoBatida = $validated['campo_batida'];

        if ($this->edicaoPontoBloqueada($funcionarioId, $dataReferencia)) {
            throw ValidationException::withMessages([
                'horario_batida' => 'Prazo para alterar este ponto expirado após o fechamento da competência.',
            ]);
        }

        $registro = RegistroPontoPortal::query()->firstOrNew([
            'funcionario_id' => $funcionarioId,
            'data_referencia' => $dataReferencia->toDateString(),
        ]);

        $horarioBatida = Carbon::createFromFormat(
            'Y-m-d H:i',
            $dataReferencia->toDateString() . ' ' . $validated['horario_batida']
        );

        $registro->{$campoBatida} = $horarioBatida;
        $registro->registrado_por_user_id = Auth::id();
        $registro->save();

        $labelsCampos = [
            'entrada_em' => 'Entrada',
            'intervalo_inicio_em' => 'Saída almoço',
            'intervalo_fim_em' => 'Retorno almoço',
            'saida_em' => 'Saída',
        ];

        RhAjustePonto::create([
            'funcionario_id' => $funcionarioId,
            'atendimento_id' => null,
            'minutos_ajuste' => 0,
            'tipo_ajuste' => $validated['tipo_ajuste'],
            'justificativa' => sprintf(
                'Lançamento manual na Seção 1 (%s em %s às %s) [campo:%s]. Motivo: %s',
                $labelsCampos[$campoBatida] ?? $campoBatida,
                $dataReferencia->format('d/m/Y'),
                $horarioBatida->format('H:i'),
                $campoBatida,
                trim($validated['motivo_lancamento_manual'])
            ),
            'ajustado_por_user_id' => Auth::id(),
            'autorizado_por_user_id' => (int) $validated['autorizado_por_user_id'],
            'ajustado_em' => $dataReferencia->copy()->setTimeFrom(now()),
        ]);

        return redirect()
            ->route('rh.ponto-jornada.index', array_filter([
                'inicio' => $validated['inicio'] ?? null,
                'fim' => $validated['fim'] ?? null,
                'funcionario_id' => $validated['funcionario_id_filtro'] ?? null,
            ]))
            ->with('success', 'Ajuste manual lançado com sucesso na Seção 1.');
    }

    public function storeAjusteLote(Request $request)
    {
        $validated = $request->validate([
            'funcionario_id' => ['required', 'exists:funcionarios,id'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'tipo_lote' => ['required', 'in:atestado,batidas'],
            'tipo_ajuste' => ['required', 'in:esquecimento,batida_duplicidade,atestado_medico,acompanhamento_medico'],
            'horario_entrada' => ['nullable', 'date_format:H:i'],
            'horario_intervalo_inicio' => ['nullable', 'date_format:H:i'],
            'horario_intervalo_fim' => ['nullable', 'date_format:H:i'],
            'horario_saida' => ['nullable', 'date_format:H:i'],
            'sobrescrever_campos' => ['nullable', 'boolean'],
            'motivo_lancamento_manual' => ['required', 'string', 'min:5', 'max:1000'],
            'autorizado_por_user_id' => ['required', 'exists:users,id'],
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date'],
            'funcionario_id_filtro' => ['nullable', 'integer', 'exists:funcionarios,id'],
        ], [
            'tipo_lote.required' => 'Selecione o tipo do lançamento em lote.',
            'tipo_ajuste.required' => 'Selecione o tipo do ajuste.',
            'motivo_lancamento_manual.required' => 'Informe o motivo do lançamento manual.',
            'autorizado_por_user_id.required' => 'Informe quem autorizou.',
        ]);

        $horariosBatida = [
            'entrada_em' => $validated['horario_entrada'] ?? null,
            'intervalo_inicio_em' => $validated['horario_intervalo_inicio'] ?? null,
            'intervalo_fim_em' => $validated['horario_intervalo_fim'] ?? null,
            'saida_em' => $validated['horario_saida'] ?? null,
        ];

        if ($validated['tipo_lote'] === 'batidas' && collect($horariosBatida)->filter()->isEmpty()) {
            throw ValidationException::withMessages([
                'horario_entrada' => 'Informe pelo menos uma batida para o lançamento em lote.',
            ]);
        }

        $funcionarioId = (int) $validated['funcionario_id'];
        $dataInicio = Carbon::parse($validated['data_inicio'])->startOfDay();
        $dataFim = Carbon::parse($validated['data_fim'])->startOfDay();
        $sobrescreverCampos = (bool) ($validated['sobrescrever_campos'] ?? false);
        $motivo = trim($validated['motivo_lancamento_manual']);

        $labelsCampos = [
            'entrada_em' => 'Entrada',
            'intervalo_inicio_em' => 'Saída almoço',
            'intervalo_fim_em' => 'Retorno almoço',
            'saida_em' => 'Saída',
        ];

        $aplicados = 0;
        $bloqueados = 0;
        $semAlteracao = 0;

        $cursor = $dataInicio->copy();
        while ($cursor->lte($dataFim)) {
            $dia = $cursor->copy();

            if ($this->edicaoPontoBloqueada($funcionarioId, $dia)) {
                $bloqueados++;
                $cursor->addDay();
                continue;
            }

            $registro = RegistroPontoPortal::query()->firstOrNew([
                'funcionario_id' => $funcionarioId,
                'data_referencia' => $dia->toDateString(),
            ]);

            if ($validated['tipo_lote'] === 'atestado') {
                $jaSemBatidas = !$registro->entrada_em
                    && !$registro->intervalo_inicio_em
                    && !$registro->intervalo_fim_em
                    && !$registro->saida_em;

                if ($jaSemBatidas) {
                    $semAlteracao++;
                    $cursor->addDay();
                    continue;
                }

                $registro->entrada_em = null;
                $registro->intervalo_inicio_em = null;
                $registro->intervalo_fim_em = null;
                $registro->saida_em = null;
                $registro->registrado_por_user_id = Auth::id();
                $registro->save();

                RhAjustePonto::create([
                    'funcionario_id' => $funcionarioId,
                    'atendimento_id' => null,
                    'minutos_ajuste' => 0,
                    'tipo_ajuste' => $validated['tipo_ajuste'],
                    'justificativa' => sprintf(
                        'Lançamento em lote (Atestado) em %s. Motivo: %s',
                        $dia->format('d/m/Y'),
                        $motivo
                    ),
                    'ajustado_por_user_id' => Auth::id(),
                    'autorizado_por_user_id' => (int) $validated['autorizado_por_user_id'],
                    'ajustado_em' => $dia->copy()->setTimeFrom(now()),
                ]);

                $aplicados++;
                $cursor->addDay();
                continue;
            }

            $camposAlterados = [];
            foreach ($horariosBatida as $campo => $horario) {
                if (!$horario) {
                    continue;
                }

                if (!$sobrescreverCampos && !empty($registro->{$campo})) {
                    continue;
                }

                $batida = Carbon::createFromFormat('Y-m-d H:i', $dia->toDateString() . ' ' . $horario);
                $registro->{$campo} = $batida;
                $camposAlterados[] = ($labelsCampos[$campo] ?? $campo) . ' ' . $batida->format('H:i');
            }

            if (empty($camposAlterados)) {
                $semAlteracao++;
                $cursor->addDay();
                continue;
            }

            $registro->registrado_por_user_id = Auth::id();
            $registro->save();

            RhAjustePonto::create([
                'funcionario_id' => $funcionarioId,
                'atendimento_id' => null,
                'minutos_ajuste' => 0,
                'tipo_ajuste' => $validated['tipo_ajuste'],
                'justificativa' => sprintf(
                    'Lançamento em lote (Batidas) em %s [%s]. Motivo: %s',
                    $dia->format('d/m/Y'),
                    implode(' | ', $camposAlterados),
                    $motivo
                ),
                'ajustado_por_user_id' => Auth::id(),
                'autorizado_por_user_id' => (int) $validated['autorizado_por_user_id'],
                'ajustado_em' => $dia->copy()->setTimeFrom(now()),
            ]);

            $aplicados++;
            $cursor->addDay();
        }

        $mensagem = sprintf(
            'Lançamento em lote concluído: %d dia(s) aplicado(s), %d bloqueado(s), %d sem alteração.',
            $aplicados,
            $bloqueados,
            $semAlteracao
        );

        return redirect()
            ->route('rh.ponto-jornada.index', array_filter([
                'inicio' => $validated['inicio'] ?? null,
                'fim' => $validated['fim'] ?? null,
                'funcionario_id' => $validated['funcionario_id_filtro'] ?? null,
            ]))
            ->with($aplicados > 0 ? 'success' : 'error', $mensagem);
    }

    public function storeFechamentoPonto(Request $request)
    {
        if (!Schema::hasTable('rh_fechamentos_ponto')) {
            return redirect()
                ->back()
                ->with('error', 'Fechamento de ponto indisponível até aplicar as migrações pendentes.');
        }

        $validated = $request->validate([
            'funcionario_id' => ['required', 'exists:funcionarios,id'],
            'competencia' => ['required', 'date'],
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date'],
            'funcionario_id_filtro' => ['nullable', 'integer', 'exists:funcionarios,id'],
        ]);

        $competencia = Carbon::parse($validated['competencia'])->startOfMonth();

        RhFechamentoPonto::query()->updateOrCreate(
            [
                'funcionario_id' => (int) $validated['funcionario_id'],
                'competencia' => $competencia->toDateString(),
            ],
            [
                'fechado_em' => now(),
                'fechado_por_user_id' => Auth::id(),
            ]
        );

        return redirect()
            ->route('rh.ponto-jornada.index', array_filter([
                'inicio' => $validated['inicio'] ?? null,
                'fim' => $validated['fim'] ?? null,
                'funcionario_id' => $validated['funcionario_id_filtro'] ?? null,
            ]))
            ->with('success', 'Fechamento do ponto registrado com sucesso.');
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

    private function montarJornadaLegal(
        Collection $funcionariosEscopo,
        Carbon $inicio,
        Carbon $fim,
        Request $request,
        Collection $ajustesSecaoUmLinhas,
        Collection $ajustesSecaoUmCampos
    ): array
    {
        $mostrarTodosNoDiaAtual = !$request->filled('funcionario_id')
            && !$request->filled('inicio')
            && !$request->filled('fim');

        $rows = collect();
        $totaisSemanais = [];
        $totaisSecaoUm = [
            'faltas_qtd' => 0,
            'atrasos_qtd' => 0,
            'extras_50_segundos' => 0,
            'extras_100_segundos' => 0,
            'atrasos_segundos' => 0,
        ];
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
                'rows' => collect(),
                'resumo' => $resumo,
                'totais_secao_1' => $totaisSecaoUm,
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
        $fechamentosPorCompetencia = $this->mapaFechamentosPorCompetencia($funcionariosIds, $inicio, $fim);

        $cursor = $inicio->copy()->startOfDay();
        $fimDia = $fim->copy()->startOfDay();

        while ($cursor->lte($fimDia)) {
            foreach ($funcionariosEscopo as $funcionario) {
                $chave = $funcionario->id . '|' . $cursor->toDateString();
                $dataReferencia = $cursor->toDateString();
                $registro = $registros->get($chave);
                $jornadaVinculo = $this->jornadaVigenteNoDia($jornadasPorFuncionario->get($funcionario->id, collect()), $cursor);

                if (!$jornadaVinculo && !$registro && !$mostrarTodosNoDiaAtual) {
                    continue;
                }

                $regra = $this->resolverRegraDia($jornadaVinculo?->jornada, $cursor);
                $horarioSugeridoEntrada = $this->resolverHorarioPadraoBatidaPorRegra($cursor, $regra, 'entrada_em')->format('H:i');
                $horarioSugeridoIntervaloInicio = $this->resolverHorarioPadraoBatidaPorRegra($cursor, $regra, 'intervalo_inicio_em')->format('H:i');
                $horarioSugeridoIntervaloFim = $this->resolverHorarioPadraoBatidaPorRegra($cursor, $regra, 'intervalo_fim_em')->format('H:i');
                $horarioSugeridoSaida = $this->resolverHorarioPadraoBatidaPorRegra($cursor, $regra, 'saida_em')->format('H:i');
                $prazoDiasAlteracao = (int) ($jornadaVinculo?->jornada?->dias_permitidos_alteracao_apos_fechamento ?? 0);
                $edicaoBloqueada = $this->calcularBloqueioEdicaoPorFechamento(
                    $fechamentosPorCompetencia,
                    (int) $funcionario->id,
                    $cursor,
                    $prazoDiasAlteracao
                );

                if (!$regra['trabalha'] && !$registro && !$mostrarTodosNoDiaAtual) {
                    continue;
                }

                if ($regra['trabalha'] && $jornadaVinculo) {
                    $resumo['dias_previstos']++;
                }

                if (!$jornadaVinculo) {
                    $segundosTrabalhados = $this->calcularSegundosTrabalhados($registro);
                    $possuiBatidas = $this->possuiBatidasNoDia($registro);
                    $extrasPercentuais = $this->calcularExtrasPorPercentual($segundosTrabalhados, 0, $regra);
                    $chaveAjuste = (int) $funcionario->id . '|' . $cursor->toDateString();
                    $corrigidoManual = (bool) $ajustesSecaoUmLinhas->get($chaveAjuste, false);
                    $totaisSecaoUm['extras_50_segundos'] += (int) ($extrasPercentuais['extra_50'] ?? 0);
                    $totaisSecaoUm['extras_100_segundos'] += (int) ($extrasPercentuais['extra_100'] ?? 0);
                    $resumo['segundos_trabalhados'] += $segundosTrabalhados;
                    $this->acumularSegundosSemana($totaisSemanais, (int) $funcionario->id, $cursor, $segundosTrabalhados);

                    $rows->push([
                        'funcionario' => $funcionario->nome,
                        'data' => $cursor->format('d/m/Y'),
                        'data_iso' => $cursor->toDateString(),
                        'funcionario_id' => (int) $funcionario->id,
                        'corrigido_manual' => $corrigidoManual,
                        'entrada_corrigida_manual' => (bool) $ajustesSecaoUmCampos->get($chaveAjuste . '|entrada_em', false),
                        'intervalo_inicio_corrigida_manual' => (bool) $ajustesSecaoUmCampos->get($chaveAjuste . '|intervalo_inicio_em', false),
                        'intervalo_fim_corrigida_manual' => (bool) $ajustesSecaoUmCampos->get($chaveAjuste . '|intervalo_fim_em', false),
                        'saida_corrigida_manual' => (bool) $ajustesSecaoUmCampos->get($chaveAjuste . '|saida_em', false),
                        'entrada_sugerida' => $horarioSugeridoEntrada,
                        'intervalo_inicio_sugerida' => $horarioSugeridoIntervaloInicio,
                        'intervalo_fim_sugerida' => $horarioSugeridoIntervaloFim,
                        'saida_sugerida' => $horarioSugeridoSaida,
                        'edicao_bloqueada' => $edicaoBloqueada,
                        'dia' => $this->nomeDiaSemana($cursor),
                        'eh_domingo' => $regra['eh_domingo'],
                        'eh_feriado' => $regra['eh_feriado'],
                        'feriado_nome' => $regra['feriado_nome'],
                        'entrada' => $this->formatarHorario($registro?->entrada_em),
                        'intervalo_inicio' => $this->formatarHorario($registro?->intervalo_inicio_em),
                        'intervalo_fim' => $this->formatarHorario($registro?->intervalo_fim_em),
                        'saida' => $this->formatarHorario($registro?->saida_em),
                        'segundos_trabalhados' => $segundosTrabalhados,
                        'segundos_previstos' => 0,
                        'saldo_segundos' => 0,
                        'tolerancia_segundos' => 0,
                        'atraso' => '—',
                        'total' => $this->formatarSegundos($segundosTrabalhados),
                        'extra_50' => $this->formatarSegundosOpcional($extrasPercentuais['extra_50']),
                        'extra_100' => $this->formatarSegundosOpcional($extrasPercentuais['extra_100']),
                        'status' => $possuiBatidas ? 'Sem jornada' : '',
                        'detalhes_batida' => $this->montarDetalhesBatida($registro),
                    ]);

                    continue;
                }

                $apuracao = $this->calcularApuracaoJornadaDia($registro, $cursor, $regra);
                $status = $apuracao['status'];
                $segundosTrabalhados = (int) $apuracao['segundos_trabalhados'];
                $possuiBatidas = $this->possuiBatidasNoDia($registro);
                $segundosPrevistos = (int) $apuracao['segundos_previstos'];
                $extrasPercentuais = [
                    'extra_50' => (int) $apuracao['extra_50_segundos'],
                    'extra_100' => (int) $apuracao['extra_100_segundos'],
                ];
                $toleranciaSegundos = max(
                    0,
                    (int) ($regra['tolerancia_entrada_min'] ?? 0),
                    (int) ($regra['tolerancia_saida_min'] ?? 0),
                    (int) ($regra['tolerancia_intervalo_min'] ?? 0)
                ) * 60;
                $saldoSegundos = $segundosTrabalhados - $segundosPrevistos;

                $resumo['horas_extras_segundos'] += $extrasPercentuais['extra_50'] + $extrasPercentuais['extra_100'];
                $totaisSecaoUm['extras_50_segundos'] += (int) ($extrasPercentuais['extra_50'] ?? 0);
                $totaisSecaoUm['extras_100_segundos'] += (int) ($extrasPercentuais['extra_100'] ?? 0);
                $chaveAjuste = (int) $funcionario->id . '|' . $cursor->toDateString();
                $corrigidoManual = (bool) $ajustesSecaoUmLinhas->get($chaveAjuste, false);

                $this->acumularSegundosSemana($totaisSemanais, (int) $funcionario->id, $cursor, $segundosTrabalhados);

                $resumo['segundos_previstos'] += $segundosPrevistos;
                $resumo['segundos_trabalhados'] += $segundosTrabalhados;

                if ($regra['trabalha'] && $segundosTrabalhados > 0) {
                    $resumo['dias_com_presenca']++;
                }

                if ($regra['trabalha'] && $status === 'OK') {
                    $resumo['dias_pontuais']++;
                }

                if ($status === 'Falta') {
                    $totaisSecaoUm['faltas_qtd']++;
                    $totaisSecaoUm['atrasos_segundos'] += $segundosPrevistos;
                }

                if ($status !== 'Falta' && $saldoSegundos < 0 && abs($saldoSegundos) > $toleranciaSegundos) {
                    $totaisSecaoUm['atrasos_qtd']++;
                    $totaisSecaoUm['atrasos_segundos'] += abs($saldoSegundos);
                }

                $rows->push([
                    'funcionario' => $funcionario->nome,
                    'data' => $cursor->format('d/m/Y'),
                    'data_iso' => $cursor->toDateString(),
                    'funcionario_id' => (int) $funcionario->id,
                    'corrigido_manual' => $corrigidoManual,
                    'entrada_corrigida_manual' => (bool) $ajustesSecaoUmCampos->get($chaveAjuste . '|entrada_em', false),
                    'intervalo_inicio_corrigida_manual' => (bool) $ajustesSecaoUmCampos->get($chaveAjuste . '|intervalo_inicio_em', false),
                    'intervalo_fim_corrigida_manual' => (bool) $ajustesSecaoUmCampos->get($chaveAjuste . '|intervalo_fim_em', false),
                    'saida_corrigida_manual' => (bool) $ajustesSecaoUmCampos->get($chaveAjuste . '|saida_em', false),
                    'entrada_sugerida' => $horarioSugeridoEntrada,
                    'intervalo_inicio_sugerida' => $horarioSugeridoIntervaloInicio,
                    'intervalo_fim_sugerida' => $horarioSugeridoIntervaloFim,
                    'saida_sugerida' => $horarioSugeridoSaida,
                    'edicao_bloqueada' => $edicaoBloqueada,
                    'dia' => $this->nomeDiaSemana($cursor),
                    'eh_domingo' => $regra['eh_domingo'],
                    'eh_feriado' => $regra['eh_feriado'],
                    'feriado_nome' => $regra['feriado_nome'],
                    'entrada' => $this->formatarHorario($registro?->entrada_em),
                    'intervalo_inicio' => $this->formatarHorario($registro?->intervalo_inicio_em),
                    'intervalo_fim' => $this->formatarHorario($registro?->intervalo_fim_em),
                    'saida' => $this->formatarHorario($registro?->saida_em),
                    'segundos_trabalhados' => $segundosTrabalhados,
                    'segundos_previstos' => $segundosPrevistos,
                    'saldo_segundos' => $saldoSegundos,
                    'tolerancia_segundos' => $toleranciaSegundos,
                    'atraso' => ($saldoSegundos < 0 && abs($saldoSegundos) > $toleranciaSegundos)
                        ? $this->formatarSegundos(abs($saldoSegundos))
                        : '—',
                    'total' => $this->formatarSegundos($segundosTrabalhados),
                    'extra_50' => $this->formatarSegundosOpcional($extrasPercentuais['extra_50']),
                    'extra_100' => $this->formatarSegundosOpcional($extrasPercentuais['extra_100']),
                    'status' => $status,
                    'detalhes_batida' => $this->montarDetalhesBatida($registro),
                ]);
            }

            $cursor->addDay();
        }

        $resumo['banco_horas_segundos'] = collect($totaisSemanais)
            ->reduce(fn (int $saldo, int $segundosTrabalhadosSemana) => $saldo + ($segundosTrabalhadosSemana - self::SEGUNDOS_META_SEMANAL), 0);

        return [
            'rows' => $rows->values(),
            'resumo' => $resumo,
            'totais_secao_1' => $totaisSecaoUm,
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

        $entrada = $this->normalizarBatidaParaMinuto($registro->entrada_em);
        $saida = $this->normalizarBatidaParaMinuto($registro->saida_em);

        $limiteEntrada = $inicioPrevisto->copy()->addMinutes((int) $regra['tolerancia_entrada_min']);
        if ($entrada->gt($limiteEntrada)) {
            return 'Atraso';
        }

        $limiteSaida = $saidaPrevista->copy()->subMinutes((int) $regra['tolerancia_saida_min']);
        if ($saida->lt($limiteSaida)) {
            return 'Saída antecipada';
        }

        if ($registro->intervalo_inicio_em && $registro->intervalo_fim_em) {
            $inicioIntervalo = $this->normalizarBatidaParaMinuto($registro->intervalo_inicio_em);
            $fimIntervalo = $this->normalizarBatidaParaMinuto($registro->intervalo_fim_em);
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
        $feriadoNacionalNome = $this->nomeFeriadoNacionalNoDia($dia);
        $ehFeriado = $feriado !== null || $feriadoNacionalNome !== null;

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

            if ($ehFeriado) {
                $trabalha = false;
            }
        }

        return [
            'trabalha' => $trabalha,
            'eh_domingo' => $dia->isSunday(),
            'eh_feriado' => $ehFeriado,
            'feriado_nome' => $feriado?->nome ?? $feriadoNacionalNome,
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

        $segundos = $fim->diffInSeconds($inicio, true);

        return max(0, $segundos - (((int) $regra['intervalo_minutos']) * 60));
    }

    private function calcularSegundosTrabalhados(?RegistroPontoPortal $registro): int
    {
        if (!$registro) {
            return 0;
        }

        $segundos = 0;

        if ($registro->entrada_em && $registro->intervalo_inicio_em) {
            $entrada = $this->normalizarBatidaParaMinuto($registro->entrada_em);
            $intervaloInicio = $this->normalizarBatidaParaMinuto($registro->intervalo_inicio_em);

            if ($entrada && $intervaloInicio && $intervaloInicio->gt($entrada)) {
                $segundos += $intervaloInicio->diffInSeconds($entrada, true);
            }
        }

        if ($registro->intervalo_fim_em && $registro->saida_em) {
            $intervaloFim = $this->normalizarBatidaParaMinuto($registro->intervalo_fim_em);
            $saida = $this->normalizarBatidaParaMinuto($registro->saida_em);

            if ($intervaloFim && $saida && $saida->gt($intervaloFim)) {
                $segundos += $saida->diffInSeconds($intervaloFim, true);
            }
        }

        if ($segundos > 0) {
            return $segundos;
        }

        if ($registro->entrada_em && $registro->saida_em) {
            $entrada = $this->normalizarBatidaParaMinuto($registro->entrada_em);
            $saida = $this->normalizarBatidaParaMinuto($registro->saida_em);

            if ($entrada && $saida && $saida->gt($entrada)) {
                return $saida->diffInSeconds($entrada, true);
            }
        }

        return 0;
    }

    private function calcularApuracaoJornadaDia(?RegistroPontoPortal $registro, Carbon $dia, array $regra): array
    {
        if (!empty($regra['eh_domingo']) || !empty($regra['eh_feriado'])) {
            $segundosTrabalhados = $this->calcularSegundosTrabalhados($registro);

            return [
                'segundos_trabalhados' => $segundosTrabalhados,
                'segundos_previstos' => 0,
                'extra_50_segundos' => 0,
                'extra_100_segundos' => $segundosTrabalhados,
                'status' => $segundosTrabalhados > 0 ? 'Extra feriado/domingo' : '',
            ];
        }

        if (!$regra['trabalha']) {
            $segundosTrabalhados = $this->calcularSegundosTrabalhados($registro);

            return [
                'segundos_trabalhados' => $segundosTrabalhados,
                'segundos_previstos' => 0,
                'extra_50_segundos' => $segundosTrabalhados,
                'extra_100_segundos' => 0,
                'status' => $segundosTrabalhados > 0
                    ? 'Extra'
                    : '',
            ];
        }

        $entrada = $registro?->entrada_em ? $this->normalizarBatidaParaMinuto($registro->entrada_em) : null;
        $intervaloInicio = $registro?->intervalo_inicio_em ? $this->normalizarBatidaParaMinuto($registro->intervalo_inicio_em) : null;
        $intervaloFim = $registro?->intervalo_fim_em ? $this->normalizarBatidaParaMinuto($registro->intervalo_fim_em) : null;
        $saida = $registro?->saida_em ? $this->normalizarBatidaParaMinuto($registro->saida_em) : null;

        $segundosPrevistos = $this->segundosPrevistosDaRegra($regra);
        if ($segundosPrevistos <= 0) {
            $segundosPrevistos = self::SEGUNDOS_META_DIARIA;
        }

        $segundosTrabalhados = $this->calcularSegundosTrabalhados($registro);

        $toleranciaSegundos = max(
            0,
            (int) ($regra['tolerancia_entrada_min'] ?? 0),
            (int) ($regra['tolerancia_saida_min'] ?? 0),
            (int) ($regra['tolerancia_intervalo_min'] ?? 0)
        ) * 60;

        $saldoSegundos = $segundosTrabalhados - $segundosPrevistos;
        $extra50Segundos = $saldoSegundos > $toleranciaSegundos ? $saldoSegundos : 0;

        $segundosAtraso = max(0, $segundosPrevistos - $segundosTrabalhados);

        $status = 'OK';
        if ($segundosTrabalhados <= 0) {
            $status = 'Falta';
        } elseif ($segundosAtraso > 0 && $extra50Segundos < $segundosAtraso) {
            $status = 'Atraso';
        }

        return [
            'segundos_trabalhados' => $segundosTrabalhados,
            'segundos_previstos' => $segundosPrevistos,
            'extra_50_segundos' => $extra50Segundos,
            'extra_100_segundos' => 0,
            'status' => $status,
        ];
    }

    private function possuiBatidasNoDia(?RegistroPontoPortal $registro): bool
    {
        if (!$registro) {
            return false;
        }

        return (bool) ($registro->entrada_em || $registro->intervalo_inicio_em || $registro->intervalo_fim_em || $registro->saida_em);
    }

    private function acumularSegundosSemana(array &$totaisSemanais, int $funcionarioId, Carbon $dia, int $segundosTrabalhados): void
    {
        $chaveSemana = sprintf('%d|%s-W%s', $funcionarioId, $dia->format('o'), $dia->format('W'));
        $totaisSemanais[$chaveSemana] = ($totaisSemanais[$chaveSemana] ?? 0) + max(0, $segundosTrabalhados);
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

    private function formatarSegundosOpcional(int $segundos): string
    {
        if ($segundos <= 0) {
            return '—';
        }

        return $this->formatarSegundos($segundos);
    }

    private function montarDetalhesBatida(?RegistroPontoPortal $registro): array
    {
        return [
            'entrada' => [
                'horario' => $this->formatarHorario($registro?->entrada_em),
                'foto_url' => $this->resolverUrlFotoBatida($registro?->entrada_foto_path),
                'latitude' => $registro?->entrada_latitude,
                'longitude' => $registro?->entrada_longitude,
            ],
            'intervalo_inicio' => [
                'horario' => $this->formatarHorario($registro?->intervalo_inicio_em),
                'foto_url' => null,
                'latitude' => $registro?->intervalo_inicio_latitude,
                'longitude' => $registro?->intervalo_inicio_longitude,
            ],
            'intervalo_fim' => [
                'horario' => $this->formatarHorario($registro?->intervalo_fim_em),
                'foto_url' => null,
                'latitude' => $registro?->intervalo_fim_latitude,
                'longitude' => $registro?->intervalo_fim_longitude,
            ],
            'saida' => [
                'horario' => $this->formatarHorario($registro?->saida_em),
                'foto_url' => $this->resolverUrlFotoBatida($registro?->saida_foto_path),
                'latitude' => $registro?->saida_latitude,
                'longitude' => $registro?->saida_longitude,
            ],
        ];
    }

    private function resolverUrlFotoBatida(?string $caminho): ?string
    {
        if (!$caminho) {
            return null;
        }

        if (str_starts_with($caminho, 'http://') || str_starts_with($caminho, 'https://')) {
            return $caminho;
        }

        return Storage::url($caminho);
    }

    private function normalizarBatidaParaMinuto($valor): ?Carbon
    {
        if (!$valor) {
            return null;
        }

        return Carbon::parse($valor)->copy()->setSecond(0);
    }

    private function calcularExtrasPorPercentual(int $segundosTrabalhados, int $segundosPrevistos, array $regra): array
    {
        if ($segundosTrabalhados <= 0) {
            return ['extra_50' => 0, 'extra_100' => 0];
        }

        if (!empty($regra['eh_domingo']) || !empty($regra['eh_feriado'])) {
            return ['extra_50' => 0, 'extra_100' => $segundosTrabalhados];
        }

        if (isset($regra['trabalha']) && $regra['trabalha'] === false) {
            return ['extra_50' => $segundosTrabalhados, 'extra_100' => 0];
        }

        $toleranciaMaximaMinutos = max(
            (int) ($regra['tolerancia_entrada_min'] ?? 0),
            (int) ($regra['tolerancia_saida_min'] ?? 0),
            (int) ($regra['tolerancia_intervalo_min'] ?? 0)
        );

        $limiteComTolerancia = self::SEGUNDOS_META_DIARIA + ($toleranciaMaximaMinutos * 60);

        if ($segundosTrabalhados > $limiteComTolerancia) {
            return ['extra_50' => $segundosTrabalhados - $limiteComTolerancia, 'extra_100' => 0];
        }

        return ['extra_50' => 0, 'extra_100' => 0];
    }

    private function nomeFeriadoNacionalNoDia(Carbon $dia): ?string
    {
        $fixos = [
            '01-01' => 'Confraternização Universal',
            '04-21' => 'Tiradentes',
            '05-01' => 'Dia do Trabalho',
            '09-07' => 'Independência do Brasil',
            '10-12' => 'Nossa Senhora Aparecida',
            '11-02' => 'Finados',
            '11-15' => 'Proclamação da República',
            '11-20' => 'Dia da Consciência Negra',
            '12-25' => 'Natal',
        ];

        $chaveFixa = $dia->format('m-d');
        if (isset($fixos[$chaveFixa])) {
            return $fixos[$chaveFixa];
        }

        $ano = (int) $dia->year;
        $pascoa = Carbon::createFromTimestamp(easter_date($ano))->startOfDay();
        $moveis = [
            $pascoa->copy()->subDays(48)->toDateString() => 'Carnaval',
            $pascoa->copy()->subDays(47)->toDateString() => 'Carnaval',
            $pascoa->copy()->subDays(2)->toDateString() => 'Sexta-feira Santa',
            $pascoa->copy()->toDateString() => 'Páscoa',
            $pascoa->copy()->addDays(60)->toDateString() => 'Corpus Christi',
        ];

        return $moveis[$dia->toDateString()] ?? null;
    }

    private function mapaFechamentosPorCompetencia(array $funcionariosIds, Carbon $inicio, Carbon $fim): Collection
    {
        if (empty($funcionariosIds) || !Schema::hasTable('rh_fechamentos_ponto')) {
            return collect();
        }

        $inicioCompetencia = $inicio->copy()->startOfMonth()->toDateString();
        $fimCompetencia = $fim->copy()->startOfMonth()->toDateString();

        return RhFechamentoPonto::query()
            ->whereIn('funcionario_id', $funcionariosIds)
            ->whereBetween('competencia', [$inicioCompetencia, $fimCompetencia])
            ->get(['funcionario_id', 'competencia', 'fechado_em'])
            ->keyBy(function (RhFechamentoPonto $fechamento) {
                return (int) $fechamento->funcionario_id . '|' . $fechamento->competencia->copy()->startOfMonth()->toDateString();
            });
    }

    private function calcularBloqueioEdicaoPorFechamento(Collection $fechamentosPorCompetencia, int $funcionarioId, Carbon $dia, int $prazoDias): bool
    {
        $chave = $funcionarioId . '|' . $dia->copy()->startOfMonth()->toDateString();
        /** @var RhFechamentoPonto|null $fechamento */
        $fechamento = $fechamentosPorCompetencia->get($chave);

        if (!$fechamento || !$fechamento->fechado_em) {
            return false;
        }

        $limite = $fechamento->fechado_em->copy()->addDays(max(0, $prazoDias))->endOfDay();

        return now()->gt($limite);
    }

    private function edicaoPontoBloqueada(int $funcionarioId, Carbon $dataReferencia): bool
    {
        if (!Schema::hasTable('rh_fechamentos_ponto')) {
            return false;
        }

        $competencia = $dataReferencia->copy()->startOfMonth()->toDateString();

        /** @var RhFechamentoPonto|null $fechamento */
        $fechamento = RhFechamentoPonto::query()
            ->where('funcionario_id', $funcionarioId)
            ->where('competencia', $competencia)
            ->first(['funcionario_id', 'competencia', 'fechado_em']);

        if (!$fechamento || !$fechamento->fechado_em) {
            return false;
        }

        $jornadas = $this->jornadasAtivasPorFuncionario([$funcionarioId], $dataReferencia->copy()->startOfDay(), $dataReferencia->copy()->endOfDay());
        $vinculo = $this->jornadaVigenteNoDia($jornadas->get($funcionarioId, collect()), $dataReferencia);
        $prazoDias = (int) ($vinculo?->jornada?->dias_permitidos_alteracao_apos_fechamento ?? 0);

        $limite = $fechamento->fechado_em->copy()->addDays(max(0, $prazoDias))->endOfDay();

        return now()->gt($limite);
    }

    private function nomeDiaSemana(Carbon $dia): string
    {
        return [
            1 => 'SEG',
            2 => 'TER',
            3 => 'QUA',
            4 => 'QUI',
            5 => 'SEX',
            6 => 'SAB',
            7 => 'DOM',
        ][(int) $dia->dayOfWeekIso] ?? '—';
    }

    private function tiposAjuste(): array
    {
        return [
            'correcao_batida' => 'Correção de Batida',
            'hora_extra' => 'Hora Extra',
            'desconto_falta' => 'Desconto/Falta',
            'compensacao' => 'Compensação',
            'esquecimento' => 'Esquecimento',
            'batida_duplicidade' => 'Batida em Duplicidade',
            'atestado_medico' => 'Atestado Médico',
            'acompanhamento_medico' => 'Acompanhamento Médico',
            'outro' => 'Outro',
        ];
    }

    private function resolverHorarioPadraoBatida(int $funcionarioId, Carbon $dia, string $campoBatida): Carbon
    {
        $jornadas = $this->jornadasAtivasPorFuncionario([$funcionarioId], $dia->copy()->startOfDay(), $dia->copy()->endOfDay());
        $vinculo = $this->jornadaVigenteNoDia($jornadas->get($funcionarioId, collect()), $dia);
        $regra = $this->resolverRegraDia($vinculo?->jornada, $dia);

        return $this->resolverHorarioPadraoBatidaPorRegra($dia, $regra, $campoBatida);
    }

    private function resolverHorarioPadraoBatidaPorRegra(Carbon $dia, array $regra, string $campoBatida): Carbon
    {

        $horaEntrada = $regra['hora_entrada'] ?: '08:00:00';
        $horaSaida = $regra['hora_saida'] ?: '18:00:00';
        $intervaloMinutos = max(0, (int) ($regra['intervalo_minutos'] ?? 60));

        $entrada = Carbon::parse($dia->toDateString() . ' ' . $horaEntrada);
        $intervaloInicio = $entrada->copy()->addHours(4);
        $intervaloFim = $intervaloInicio->copy()->addMinutes($intervaloMinutos);
        $saida = Carbon::parse($dia->toDateString() . ' ' . $horaSaida);

        if ($saida->lessThanOrEqualTo($entrada)) {
            $saida->addDay();
        }

        return match ($campoBatida) {
            'entrada_em' => $entrada,
            'intervalo_inicio_em' => $intervaloInicio,
            'intervalo_fim_em' => $intervaloFim,
            'saida_em' => $saida,
            default => $entrada,
        };
    }

    private function extrairCampoBatidaDeAjuste(RhAjustePonto $ajuste): ?string
    {
        $justificativa = (string) ($ajuste->justificativa ?? '');

        if (preg_match('/\[campo:(entrada_em|intervalo_inicio_em|intervalo_fim_em|saida_em)\]/', $justificativa, $matches)) {
            return $matches[1] ?? null;
        }

        if (stripos($justificativa, '(Entrada') !== false) {
            return 'entrada_em';
        }
        if (stripos($justificativa, '(Saída almoço') !== false) {
            return 'intervalo_inicio_em';
        }
        if (stripos($justificativa, '(Retorno almoço') !== false) {
            return 'intervalo_fim_em';
        }
        if (stripos($justificativa, '(Saída') !== false) {
            return 'saida_em';
        }

        return null;
    }
}
