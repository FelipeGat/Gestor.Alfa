<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Atendimento;
use App\Models\Funcionario;
use App\Models\RhAjustePonto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PontoJornadaController extends Controller
{
    public function index(Request $request)
    {
        $funcionarioId = $request->integer('funcionario_id');
        $inicio = $request->filled('inicio') ? Carbon::parse($request->input('inicio'))->startOfDay() : Carbon::now()->startOfMonth();
        $fim = $request->filled('fim') ? Carbon::parse($request->input('fim'))->endOfDay() : Carbon::now()->endOfMonth();

        $funcionarios = Funcionario::query()->orderBy('nome')->get(['id', 'nome']);

        $registrosQuery = Atendimento::query()
            ->with('funcionario:id,nome')
            ->whereNotNull('funcionario_id')
            ->whereBetween('created_at', [$inicio, $fim]);

        if ($funcionarioId) {
            $registrosQuery->where('funcionario_id', $funcionarioId);
        }

        $registros = $registrosQuery
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->query());

        $ajustesQuery = RhAjustePonto::query()
            ->with(['funcionario:id,nome', 'ajustadoPor:id,name'])
            ->whereBetween('ajustado_em', [$inicio, $fim]);

        if ($funcionarioId) {
            $ajustesQuery->where('funcionario_id', $funcionarioId);
        }

        $ajustes = $ajustesQuery->orderByDesc('ajustado_em')->limit(50)->get();

        $baseHoras = Atendimento::query()
            ->selectRaw('funcionario_id, COALESCE(SUM(tempo_execucao_segundos), 0) as segundos_trabalhados')
            ->whereNotNull('funcionario_id')
            ->whereBetween('created_at', [$inicio, $fim])
            ->groupBy('funcionario_id');

        if ($funcionarioId) {
            $baseHoras->where('funcionario_id', $funcionarioId);
        }

        $horasExtras = $baseHoras->get()->map(function ($linha) use ($inicio, $fim) {
            $ajusteSegundos = (int) RhAjustePonto::query()
                ->where('funcionario_id', $linha->funcionario_id)
                ->whereBetween('ajustado_em', [$inicio, $fim])
                ->sum('minutos_ajuste') * 60;

            $saldo = (int) $linha->segundos_trabalhados + $ajusteSegundos;

            return [
                'funcionario' => Funcionario::find($linha->funcionario_id),
                'segundos_trabalhados' => (int) $linha->segundos_trabalhados,
                'segundos_ajuste' => $ajusteSegundos,
                'saldo_segundos' => $saldo,
            ];
        });

        return view('rh.ponto-jornada', [
            'funcionarios' => $funcionarios,
            'registros' => $registros,
            'ajustes' => $ajustes,
            'horasExtras' => $horasExtras,
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
            'justificativa' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'justificativa.required' => 'A justificativa do ajuste manual é obrigatória.',
            'justificativa.min' => 'A justificativa deve ter ao menos 10 caracteres.',
        ]);

        RhAjustePonto::create([
            'funcionario_id' => (int) $validated['funcionario_id'],
            'atendimento_id' => $validated['atendimento_id'] ?? null,
            'minutos_ajuste' => (int) $validated['minutos_ajuste'],
            'justificativa' => trim($validated['justificativa']),
            'ajustado_por_user_id' => Auth::id(),
            'ajustado_em' => now(),
        ]);

        return redirect()
            ->route('rh.ponto-jornada.index')
            ->with('success', 'Ajuste manual de ponto registrado com sucesso.');
    }
}
