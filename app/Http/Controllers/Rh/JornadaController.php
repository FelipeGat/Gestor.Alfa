<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Feriado;
use App\Models\Jornada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class JornadaController extends Controller
{
    public function index()
    {
        $jornadas = Jornada::query()
            ->with(['escalas' => fn ($query) => $query->orderBy('dia_semana'), 'feriados'])
            ->orderBy('nome')
            ->get();

        $feriados = Feriado::query()
            ->where('ativo', true)
            ->orderBy('data')
            ->get(['id', 'nome', 'data']);

        $feriadosNacionaisIds = Feriado::query()
            ->where('ativo', true)
            ->where('tipo', 'nacional')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('rh.jornadas.index', compact('jornadas', 'feriados', 'feriadosNacionaisIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarDados($request);

        DB::transaction(function () use ($dados) {
            $jornada = Jornada::create($this->montarPayloadJornada($dados));
            $this->salvarEscala($jornada, $dados);
            $jornada->feriados()->sync($this->comporFeriadosComNacionais($dados['feriado_ids'] ?? []));
        });

        return back()->with('success', 'Jornada cadastrada com sucesso.');
    }

    public function update(Request $request, Jornada $jornada): RedirectResponse
    {
        $dados = $this->validarDados($request);

        DB::transaction(function () use ($jornada, $dados) {
            $jornada->update($this->montarPayloadJornada($dados));
            $this->salvarEscala($jornada, $dados);
            $jornada->feriados()->sync($this->comporFeriadosComNacionais($dados['feriado_ids'] ?? []));
        });

        return back()->with('success', 'Jornada atualizada com sucesso.');
    }

    public function toggleAtivo(Jornada $jornada): RedirectResponse
    {
        $jornada->update([
            'ativo' => !$jornada->ativo,
        ]);

        return back()->with('success', $jornada->ativo ? 'Jornada ativada com sucesso.' : 'Jornada desativada com sucesso.');
    }

    private function validarDados(Request $request): array
    {
        $this->normalizarDecimaisNoRequest($request);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'tipo_jornada' => ['required', 'in:fixa,escala'],
            'dias_trabalhados' => ['nullable', 'array'],
            'dias_trabalhados.*' => ['integer', 'between:1,7'],
            'hora_entrada_padrao' => ['nullable', 'date_format:H:i'],
            'hora_saida_padrao' => ['nullable', 'date_format:H:i'],
            'intervalo_minutos' => ['required', 'integer', 'min:0', 'max:300'],
            'carga_horaria_semanal' => ['required', 'numeric', 'min:0', 'max:120'],
            'tolerancia_entrada_min' => ['required', 'integer', 'min:0', 'max:180'],
            'tolerancia_saida_min' => ['required', 'integer', 'min:0', 'max:180'],
            'tolerancia_intervalo_min' => ['required', 'integer', 'min:0', 'max:120'],
            'minimo_horas_para_extra' => ['required', 'integer', 'min:0', 'max:600'],
            'permitir_ponto_fora_horario' => ['nullable', 'boolean'],
            'ativo' => ['nullable', 'boolean'],
            'feriado_ids' => ['nullable', 'array'],
            'feriado_ids.*' => ['integer', 'exists:feriados,id'],
            'escala' => ['nullable', 'array'],
            'escala.*.hora_entrada' => ['nullable', 'date_format:H:i'],
            'escala.*.hora_saida' => ['nullable', 'date_format:H:i'],
            'escala.*.intervalo_minutos' => ['nullable', 'integer', 'min:0', 'max:300'],
            'escala.*.carga_horaria_dia' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ]);

        if ($dados['tipo_jornada'] === 'fixa') {
            if (empty($dados['dias_trabalhados'])) {
                abort(422, 'Selecione ao menos um dia trabalhado para jornada fixa.');
            }

            if (empty($dados['hora_entrada_padrao']) || empty($dados['hora_saida_padrao'])) {
                abort(422, 'Informe entrada e saída padrão para jornada fixa.');
            }
        }

        if ($dados['tipo_jornada'] === 'escala') {
            $linhasEscala = collect($dados['escala'] ?? [])->filter(function (array $item) {
                return !empty($item['hora_entrada']) && !empty($item['hora_saida']);
            });

            if ($linhasEscala->isEmpty()) {
                abort(422, 'Informe ao menos um dia na escala personalizada.');
            }
        }

        return $dados;
    }

    private function normalizarDecimaisNoRequest(Request $request): void
    {
        $payload = $request->all();

        if (isset($payload['carga_horaria_semanal']) && is_string($payload['carga_horaria_semanal'])) {
            $payload['carga_horaria_semanal'] = str_replace(',', '.', trim($payload['carga_horaria_semanal']));
        }

        if (!empty($payload['escala']) && is_array($payload['escala'])) {
            foreach ($payload['escala'] as $dia => $dadosDia) {
                if (isset($dadosDia['carga_horaria_dia']) && is_string($dadosDia['carga_horaria_dia'])) {
                    $payload['escala'][$dia]['carga_horaria_dia'] = str_replace(',', '.', trim($dadosDia['carga_horaria_dia']));
                }
            }
        }

        $request->merge($payload);
    }

    private function montarPayloadJornada(array $dados): array
    {
        $legadoInicio = $dados['hora_entrada_padrao'] ?? null;
        $legadoFim = $dados['hora_saida_padrao'] ?? null;

        if ($dados['tipo_jornada'] === 'escala') {
            $primeiroDia = collect($dados['escala'] ?? [])->filter(function (array $item) {
                return !empty($item['hora_entrada']) && !empty($item['hora_saida']);
            })->sortKeys()->first();

            $legadoInicio = $primeiroDia['hora_entrada'] ?? $legadoInicio;
            $legadoFim = $primeiroDia['hora_saida'] ?? $legadoFim;
        }

        return [
            'nome' => $dados['nome'],
            'tipo_jornada' => $dados['tipo_jornada'],
            'dias_trabalhados' => $dados['tipo_jornada'] === 'fixa'
                ? array_values(array_unique(array_map('intval', $dados['dias_trabalhados'] ?? [])))
                : null,
            'hora_entrada_padrao' => $dados['hora_entrada_padrao'] ?? null,
            'hora_saida_padrao' => $dados['hora_saida_padrao'] ?? null,
            'intervalo_minutos' => (int) $dados['intervalo_minutos'],
            'carga_horaria_semanal' => (float) $dados['carga_horaria_semanal'],
            'tolerancia_entrada_min' => (int) $dados['tolerancia_entrada_min'],
            'tolerancia_saida_min' => (int) $dados['tolerancia_saida_min'],
            'tolerancia_intervalo_min' => (int) $dados['tolerancia_intervalo_min'],
            'minimo_horas_para_extra' => (int) $dados['minimo_horas_para_extra'],
            'permitir_ponto_fora_horario' => (bool) Arr::get($dados, 'permitir_ponto_fora_horario', false),
            'ativo' => (bool) Arr::get($dados, 'ativo', true),
            'hora_inicio' => $legadoInicio,
            'hora_fim' => $legadoFim,
        ];
    }

    private function salvarEscala(Jornada $jornada, array $dados): void
    {
        $jornada->escalas()->delete();

        if ($dados['tipo_jornada'] !== 'escala') {
            return;
        }

        $linhas = collect($dados['escala'] ?? [])->map(function (array $item, $dia) {
            if (empty($item['hora_entrada']) || empty($item['hora_saida'])) {
                return null;
            }

            return [
                'dia_semana' => (int) $dia,
                'hora_entrada' => $item['hora_entrada'],
                'hora_saida' => $item['hora_saida'],
                'intervalo_minutos' => (int) ($item['intervalo_minutos'] ?? 0),
                'carga_horaria_dia' => (float) ($item['carga_horaria_dia'] ?? 0),
            ];
        })->filter()->values()->all();

        if (!empty($linhas)) {
            $jornada->escalas()->createMany($linhas);
        }
    }

    private function comporFeriadosComNacionais(array $feriadoIds): array
    {
        $nacionais = Feriado::query()
            ->where('ativo', true)
            ->where('tipo', 'nacional')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_map('intval', array_merge($feriadoIds, $nacionais))));
    }
}
