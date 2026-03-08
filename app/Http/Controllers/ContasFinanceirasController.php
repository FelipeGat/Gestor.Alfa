<?php

namespace App\Http\Controllers;

use App\Models\ContaFinanceira;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContasFinanceirasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $query = ContaFinanceira::with('empresa');

        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $contas = $query
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        $totalContas    = ContaFinanceira::count();
        $contasAtivas   = ContaFinanceira::where('ativo', true)->count();
        $contasInativas = ContaFinanceira::where('ativo', false)->count();

        $empresas = Empresa::where('ativo', true)
            ->orderBy('nome_fantasia')
            ->get();

        return view('financeiro.contas-bancarias.index', compact(
            'contas',
            'empresas',
            'totalContas',
            'contasAtivas',
            'contasInativas'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $empresas = Empresa::where('ativo', true)
            ->orderBy('nome_fantasia')
            ->get();

        return view('financeiro.contas-bancarias.create', compact('empresas'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'empresa_id'               => 'required|exists:empresas,id',
            'nome'                     => 'required|string|max:150',
            'tipo' => 'required|in:corrente,poupanca,investimento,credito,pix,caixa',
            'saldo'                    => 'nullable|numeric',
            'limite_credito'           => 'nullable|numeric|min:0',
            'limite_credito_utilizado' => 'nullable|numeric|min:0',
            'limite_cheque_especial'   => 'nullable|numeric|min:0',
            'ativo'                    => 'required|boolean',
            // Campos exclusivos de cartão de crédito
            'bandeira'                 => 'nullable|in:VISA,MASTERCARD,ELO,AMEX,HIPERCARD,OTHER',
            'melhor_dia_compra'        => 'nullable|integer|min:1|max:28',
            'dia_fechamento_fatura'    => 'nullable|integer|min:1|max:31',
            'dia_vencimento_fatura'    => 'nullable|integer|min:1|max:31',
        ]);

        // Evita duplicidade por empresa + nome
        $existe = ContaFinanceira::where('empresa_id', $request->empresa_id)
            ->where('nome', $request->nome)
            ->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Já existe uma conta com esse nome para esta empresa.');
        }

        // Validação financeira crítica
        if (
            $request->limite_credito_utilizado > $request->limite_credito
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'O limite utilizado do cartão não pode ser maior que o limite total.');
        }

        $isCredito = $request->tipo === 'credito';

        ContaFinanceira::create([
            'empresa_id'               => $request->empresa_id,
            'nome'                     => $request->nome,
            'tipo'                     => $request->tipo,
            'saldo'                    => $isCredito ? 0 : ($request->saldo ?? 0),
            'limite_credito'           => $request->limite_credito ?? 0,
            'limite_credito_utilizado' => $request->limite_credito_utilizado ?? 0,
            'limite_cheque_especial'   => $isCredito ? 0 : ($request->limite_cheque_especial ?? 0),
            'ativo'                    => $request->ativo,
            // Campos de cartão de crédito
            'bandeira'                 => $isCredito ? $request->bandeira : null,
            'melhor_dia_compra'        => $isCredito ? $request->melhor_dia_compra : null,
            'dia_fechamento_fatura'    => $isCredito ? $request->dia_fechamento_fatura : null,
            'dia_vencimento_fatura'    => $isCredito ? $request->dia_vencimento_fatura : null,
        ]);

        return redirect()
            ->route('financeiro.contas-financeiras.index')
            ->with('success', 'Conta financeira cadastrada com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(ContaFinanceira $contaFinanceira)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $empresas = Empresa::where('ativo', true)
            ->orderBy('nome_fantasia')
            ->get();

        return view('financeiro.contas-bancarias.edit', compact(
            'contaFinanceira',
            'empresas'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, ContaFinanceira $contaFinanceira)
    {
        $request->validate([
            'empresa_id'               => 'required|exists:empresas,id',
            'nome'                     => 'required|string|max:150',
            'tipo'                     => 'required|in:corrente,poupanca,investimento,credito,pix,caixa',
            'saldo'                    => 'nullable|numeric',
            'limite_credito'           => 'nullable|numeric|min:0',
            'limite_credito_utilizado' => 'nullable|numeric|min:0',
            'limite_cheque_especial'   => 'nullable|numeric|min:0',
            'ativo'                    => 'required|boolean',
            // Campos exclusivos de cartão de crédito
            'bandeira'                 => 'nullable|in:VISA,MASTERCARD,ELO,AMEX,HIPERCARD,OTHER',
            'melhor_dia_compra'        => 'nullable|integer|min:1|max:28',
            'dia_fechamento_fatura'    => 'nullable|integer|min:1|max:31',
            'dia_vencimento_fatura'    => 'nullable|integer|min:1|max:31',
        ]);

        if (
            $request->limite_credito_utilizado > $request->limite_credito
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'O limite utilizado do cartão não pode ser maior que o limite total.');
        }

        $isCredito = $request->tipo === 'credito';

        $contaFinanceira->update([
            'empresa_id'               => $request->empresa_id,
            'nome'                     => $request->nome,
            'tipo'                     => $request->tipo,
            'saldo'                    => $isCredito ? 0 : ($request->saldo ?? 0),
            'limite_credito'           => $request->limite_credito ?? 0,
            'limite_credito_utilizado' => $request->limite_credito_utilizado ?? 0,
            'limite_cheque_especial'   => $isCredito ? 0 : ($request->limite_cheque_especial ?? 0),
            'ativo'                    => $request->ativo,
            // Campos de cartão de crédito
            'bandeira'                 => $isCredito ? $request->bandeira : null,
            'melhor_dia_compra'        => $isCredito ? $request->melhor_dia_compra : null,
            'dia_fechamento_fatura'    => $isCredito ? $request->dia_fechamento_fatura : null,
            'dia_vencimento_fatura'    => $isCredito ? $request->dia_vencimento_fatura : null,
        ]);

        return redirect()
            ->route('financeiro.contas-financeiras.index')
            ->with('success', 'Conta financeira atualizada com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */
    public function destroy(ContaFinanceira $contaFinanceira)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && !$user->perfis()->where('slug', 'financeiro')->exists(),
            403,
            'Acesso não autorizado'
        );

        $contaFinanceira->delete();

        return redirect()
            ->route('financeiro.contas-financeiras.index')
            ->with('success', 'Conta financeira removida com sucesso.');
    }

    /**
     * API - Retorna lista de contas financeiras por empresa
     */
    public function apiListByEmpresa($empresa_id)
    {
        $contas = \App\Models\ContaFinanceira::where('empresa_id', $empresa_id)
            ->where('ativo', true)
            ->select('id', 'nome', 'tipo')
            ->orderBy('nome')
            ->get();

        return response()->json($contas);
    }
}
