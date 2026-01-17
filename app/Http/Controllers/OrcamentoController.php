<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\Empresa;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Atendimento;



class OrcamentoController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );
        
        // ================= ORÇAMENTOS =================
        $query = Orcamento::with(['empresa', 'cliente'])
            ->orderByDesc('created_at');

        // Comercial: apenas empresas vinculadas
        if ($user->tipo === 'comercial') {
            $empresaIds = $user->empresas->pluck('id');

            // segurança extra
            if ($empresaIds->isEmpty()) {
                $orcamentos = collect();
                $atendimentosParaOrcamento = collect();
                return view('orcamentos.index', compact('orcamentos', 'atendimentosParaOrcamento'));
            }

            $query->whereIn('empresa_id', $empresaIds);
        }

        // Admin vê tudo
        $orcamentos = $query->get();
        
        // ================= ATENDIMENTOS AGUARDANDO ORÇAMENTO =================
            $atendimentosQuery = Atendimento::with(['cliente', 'empresa'])
                ->where('status_atual', 'orcamento')
                ->whereDoesntHave('orcamento');

            if ($user->tipo === 'comercial') {
                $atendimentosQuery->whereIn('empresa_id', $empresaIds);
            }

            $atendimentosParaOrcamento = $atendimentosQuery
                ->orderByDesc('created_at')
                ->get();

            return view(
                'orcamentos.index',
                compact('orcamentos', 'atendimentosParaOrcamento')
        );
    }



    public function create(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $empresas = Empresa::orderBy('nome_fantasia')->get();
        $clientes = Cliente::orderBy('nome')->get();

        $atendimento = null;

            if ($request->filled('atendimento')) {
            $atendimento = Atendimento::with(['empresa', 'cliente'])
                ->where('id', $request->atendimento)
                ->where('status_atual', 'orcamento')
                ->firstOrFail();
        }

        return view(
            'orcamentos.create',
            compact('empresas', 'clientes', 'atendimento')
        );
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        // ================= VALIDAÇÃO BASE =================
        $request->validate([
            'empresa_id'      => 'required|exists:empresas,id',
            'descricao'       => 'required|string|max:255',
            'validade'        => 'nullable|date|after_or_equal:' . now()->addDays(5)->format('Y-m-d'),
            'observacoes'     => 'nullable|string',
            'cliente_tipo'    => 'required|in:cliente,pre_cliente',

            'desconto'        => 'nullable|numeric|min:0',
            'taxas'           => 'nullable|numeric|min:0',
            'forma_pagamento' => 'nullable|string|max:50',
        ], [
            'cliente_tipo.required' => 'Selecione um cliente ou pré-cliente.',
        ]);

        // ================= REGRA CLIENTE / PRÉ-CLIENTE =================
        $clienteId = null;
        $preClienteId = null;

        if ($request->cliente_tipo === 'cliente') {
            abort_if(
                !$request->filled('cliente_id'),
                422,
                'Cliente inválido.'
            );

            $clienteId = $request->cliente_id;
        }

        if ($request->cliente_tipo === 'pre_cliente') {
            abort_if(
                !$request->filled('pre_cliente_id'),
                422,
                'Pré-cliente inválido.'
            );

            $preClienteId = $request->pre_cliente_id;
        }

        // ================= GERA NÚMERO =================
        $numero = Orcamento::gerarNumero($request->empresa_id);

        // ================= CÁLCULO DO VALOR =================
        $valorTotal = ($request->valor_total ?? 0)
            - ($request->desconto ?? 0)
            + ($request->taxas ?? 0);

        // ================= CRIA ORÇAMENTO =================
        $orcamento = Orcamento::create([
            'empresa_id'       => $request->empresa_id,
            'atendimento_id'   => $request->atendimento_id,
            'numero_orcamento' => $numero,
            'descricao'        => $request->descricao,
            'validade'         => $request->validade,
            'status'           => 'em_elaboracao',

            'cliente_id'       => $clienteId,
            'pre_cliente_id'   => $preClienteId,

            'valor_total'      => $valorTotal,
            'desconto'         => $request->desconto,
            'taxas'            => $request->taxas,
            'forma_pagamento'  => $request->forma_pagamento,
            'observacoes'      => $request->observacoes,
            'created_by'       => $user->id,
        ]);

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento criado com sucesso!');
    }



    public function edit(Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        return view('orcamentos.edit', compact('orcamento'));
    }

    public function update(Request $request, Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'status'      => 'required|string',
            'valor_total' => 'nullable|numeric|min:0',
            'observacoes' => 'nullable|string',
        ]);

        $orcamento->update([
            'status'      => $request->status,
            'valor_total' => $request->valor_total,
            'observacoes' => $request->observacoes,
        ]);

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento atualizado com sucesso!');
    }

    public function destroy(Orcamento $orcamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo,
            403,
            'Acesso não autorizado'
        );

        $orcamento->delete();

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento excluído com sucesso!');
    }

    public function gerarNumero($empresaId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() && $user->tipo !== 'comercial',
            403
        );

        return response()->json([
            'numero' => Orcamento::gerarNumero((int) $empresaId)
        ]);
    }

}