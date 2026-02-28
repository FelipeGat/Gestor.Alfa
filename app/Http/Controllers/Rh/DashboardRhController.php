<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Atendimento;
use App\Models\Ferias;
use App\Models\Funcionario;
use App\Models\FuncionarioDocumento;
use App\Models\FuncionarioEpi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DashboardRhController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();
        $limite = $hoje->copy()->addDays(30);

        $funcionariosAtivos = Funcionario::where('ativo', true)->count();

        $documentosVencendo = Schema::hasTable('funcionario_documentos')
            ? FuncionarioDocumento::query()
                ->whereNotNull('data_vencimento')
                ->whereDate('data_vencimento', '<=', $limite)
                ->count()
            : 0;

        $episVencendo = Schema::hasTable('funcionario_epis')
            ? FuncionarioEpi::query()
                ->whereNotNull('data_prevista_troca')
                ->whereDate('data_prevista_troca', '<=', $limite)
                ->count()
            : 0;

        $feriasVencidas = Schema::hasTable('ferias')
            ? Ferias::query()
                ->whereNotNull('periodo_gozo_fim')
                ->whereDate('periodo_gozo_fim', '<', $hoje)
                ->where('status', '!=', 'concluida')
                ->count()
            : 0;

        $inicioMes = $hoje->copy()->startOfMonth();
        $fimMes = $hoje->copy()->endOfMonth();

        $totaisBancoHoras = Atendimento::query()
            ->whereNotNull('funcionario_id')
            ->whereBetween('created_at', [$inicioMes, $fimMes])
            ->selectRaw('COALESCE(SUM(tempo_execucao_segundos), 0) as total_segundos')
            ->value('total_segundos') ?? 0;

        return view('rh.dashboard', [
            'funcionariosAtivos' => $funcionariosAtivos,
            'documentosVencendo' => $documentosVencendo,
            'episVencendo' => $episVencendo,
            'feriasVencidas' => $feriasVencidas,
            'bancoHorasSegundos' => (int) $totaisBancoHoras,
        ]);
    }
}
