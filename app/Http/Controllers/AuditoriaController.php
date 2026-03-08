<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class AuditoriaController extends Controller
{
    /**
     * Nomes legíveis para cada model auditado.
     */
    private const MODEL_LABELS = [
        'App\Models\Orcamento'        => 'Orçamento',
        'App\Models\Cobranca'         => 'Cobrança',
        'App\Models\ContaPagar'       => 'Conta a Pagar',
        'App\Models\ContaFinanceira'  => 'Conta Financeira',
        'App\Models\Cliente'          => 'Cliente',
        'App\Models\Fornecedor'       => 'Fornecedor',
        'App\Models\User'             => 'Usuário',
        'App\Models\Empresa'          => 'Empresa',
        'App\Models\ContaFixa'        => 'Contrato Recorrente',
        'App\Models\ContaFixaPagar'   => 'Conta Fixa a Pagar',
        'App\Models\Atendimento'      => 'Atendimento',
        'App\Models\Funcionario'      => 'Funcionário',
    ];

    public function index(Request $request): \Illuminate\View\View
    {
        /** @var User $user */
        $authedUser = Auth::user();
        abort_unless($authedUser && $authedUser->isAdminPanel(), 403, 'Acesso restrito a administradores.');

        // ── Filtros ──────────────────────────────────────────────────────────
        $data    = $request->get('data', now()->toDateString());
        $userId  = $request->get('user_id');

        // Validar formato de data
        try {
            $dia = Carbon::createFromFormat('Y-m-d', $data);
        } catch (\Throwable) {
            $dia = now();
            $data = $dia->toDateString();
        }

        // ── Query principal ──────────────────────────────────────────────────
        $query = Activity::with(['causer'])
            ->whereDate('created_at', $data)
            ->orderBy('created_at', 'asc');

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where(function ($inner) use ($userId) {
                    $inner->where('causer_id', $userId)
                          ->where('causer_type', User::class);
                })->orWhere(function ($inner) use ($userId) {
                    // login/logout ficam em log_name='session' sem causer (antes do login completo)
                    $inner->where('causer_id', $userId);
                });
            });
        }

        $activities = $query->get();

        // ── Agrupamento por usuário (causer) ─────────────────────────────────
        $byUser = $activities->groupBy(function (Activity $activity) {
            if ($activity->causer) {
                return $activity->causer_id ?? 'sistema';
            }
            // Eventos de sistema sem causer
            return 'sistema';
        });

        // Ordenar: usuários com mais eventos primeiro; "sistema" vai ao final
        $byUser = $byUser->sortByDesc(fn ($group) => $group->count());
        if ($byUser->has('sistema')) {
            $sistema = $byUser->pull('sistema');
            $byUser->put('sistema', $sistema);
        }

        // ── Lista de usuários para o filtro ──────────────────────────────────
        $users = User::orderBy('name')->get();

        // ── Estatísticas do dia ───────────────────────────────────────────────
        $stats = [
            'total'   => $activities->count(),
            'criados' => $activities->where('event', 'created')->count(),
            'alterados' => $activities->where('event', 'updated')->count(),
            'deletados' => $activities->where('event', 'deleted')->count(),
            'logins'  => $activities->where('description', 'login')->count(),
            'logouts' => $activities->where('description', 'logout')->count(),
        ];

        return view('relatorios.auditoria', [
            'activities'  => $activities,
            'byUser'      => $byUser,
            'data'        => $data,
            'dia'         => $dia,
            'userId'      => $userId,
            'users'       => $users,
            'stats'       => $stats,
            'modelLabels' => self::MODEL_LABELS,
        ]);
    }
}
