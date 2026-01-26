<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContasReceberController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel()
                && ! $user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $hoje = Carbon::today();

        $kpis = [
            'a_receber' => Cobranca::where('status', '!=', 'pago')->sum('valor'),

            'recebido' => Cobranca::where('status', 'pago')->sum('valor'),

            'vencido' => Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', '<', $hoje)
                ->sum('valor'),

            'vence_hoje' => Cobranca::where('status', '!=', 'pago')
                ->whereDate('data_vencimento', $hoje)
                ->sum('valor'),
        ];

        $query = Cobranca::with([
            'cliente.telefones',
            'orcamento',
        ]);

        /*
        |--------------------------------------------------------------------------
        | BUSCA
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('descricao', 'like', "%{$search}%")
                ->orWhereHas(
                    'cliente',
                    fn($q) =>
                    $q->where('nome', 'like', "%{$search}%")
                );
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | PERÍODO
        |--------------------------------------------------------------------------
        */
        if ($request->filled('periodo')) {
            match ($request->periodo) {
                'vencidos' => $query->whereDate('data_vencimento', '<', $hoje),
                'hoje'     => $query->whereDate('data_vencimento', $hoje),
                'futuros'  => $query->whereDate('data_vencimento', '>', $hoje),
                default    => null,
            };
        }

        $cobrancas = $query
            ->orderBy('data_vencimento')
            ->paginate(10)
            ->withQueryString();

        return view('financeiro.contasareceber', compact(
            'cobrancas',
            'hoje',
            'kpis'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | MARCAR COMO PAGO
    |--------------------------------------------------------------------------
    */
    public function pagar(Cobranca $cobranca)
    {
        DB::transaction(function () use ($cobranca) {
            $cobranca->update([
                'status'  => 'pago',
                'pago_em' => now(),
            ]);
        });

        return back()->with('success', 'Cobrança marcada como paga.');
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUIR COBRANÇA
    |--------------------------------------------------------------------------
    */
    public function destroy(Cobranca $cobranca)
    {
        DB::transaction(function () use ($cobranca) {

            if (
                $cobranca->orcamento &&
                $cobranca->orcamento->status === 'aguardando_pagamento'
            ) {
                $cobranca->orcamento->update([
                    'status' => 'financeiro',
                ]);
            }

            $cobranca->delete();
        });

        return back()->with(
            'success',
            'Cobrança excluída e devolvida ao financeiro.'
        );
    }
}
