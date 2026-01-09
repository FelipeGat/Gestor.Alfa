<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Assunto;
use App\Models\AtendimentoStatusHistorico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtendimentoController extends Controller
{
    public function index(Request $request)
    {
        $query = Atendimento::with([
            'cliente',
            'assunto',
            'empresa',
            'funcionario'
        ]);

        // BUSCA (cliente ou solicitante)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nome_solicitante', 'like', "%{$search}%")
                ->orWhereHas('cliente', function ($c) use ($search) {
                    $c->where('nome', 'like', "%{$search}%");
                });
            });
        }

        // PRIORIDADE
        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        // STATUS
        if ($request->filled('status')) {
            $query->where('status_atual', $request->status);
        }

        // PERÍODO
        $periodo = $request->periodo ?? 'mes';

        match ($periodo) {
            'dia' => $query->whereDate('data_atendimento', today()),
            'semana' => $query->whereBetween('data_atendimento', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]),
            'ano' => $query->whereYear('data_atendimento', now()->year),
            default => $query->whereMonth('data_atendimento', now()->month),
        };

        $atendimentos = $query
            ->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderByDesc('data_atendimento')
            ->get();

        $funcionarios = Funcionario::where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('atendimentos.index', compact(
            'atendimentos',
            'funcionarios'
        ));
    }


    public function create()
    {
        return view('atendimentos.create', [
            'clientes'     => Cliente::orderBy('nome')->get(),
            'assuntos'     => Assunto::where('ativo', true)->orderBy('nome')->get(),
            'empresas'     => Empresa::orderBy('nome_fantasia')->get(),
            'funcionarios' => Funcionario::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function edit(Atendimento $atendimento)
    {
        $atendimento->load([
            'andamentos.user'
        ]);

        return view('atendimentos.edit', compact('atendimento'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome_solicitante' => 'required|string|max:255',
            'assunto_id'       => 'required|exists:assuntos,id',
            'descricao'        => 'required|string',
            'prioridade'       => 'required|in:baixa,media,alta',
            'empresa_id'       => 'required|exists:empresas,id',
            'status_inicial'   => 'required|in:orcamento,aberto,garantia',
        ]);

        $ultimoNumero = Atendimento::max('numero_atendimento') ?? 0;

        $statusInicial = $request->status_inicial;

        $atendimento = Atendimento::create([
            'numero_atendimento'   => $ultimoNumero + 1,
            'cliente_id'           => $request->cliente_id,
            'nome_solicitante'     => $request->nome_solicitante,
            'telefone_solicitante' => $request->telefone_solicitante,
            'email_solicitante'    => $request->email_solicitante,
            'assunto_id'           => $request->assunto_id,
            'descricao'            => $request->descricao,
            'prioridade'           => $request->prioridade,
            'empresa_id'           => $request->empresa_id,
            'funcionario_id'       => $request->funcionario_id,
            'status_atual'         => $statusInicial,
            'is_orcamento'         => $statusInicial === 'orcamento',
            'data_atendimento'     => now(),
        ]);

        AtendimentoStatusHistorico::create([
            'atendimento_id' => $atendimento->id,
            'status'         => $statusInicial,
            'observacao'     => 'Abertura do atendimento',
            'user_id'        => Auth::id(),
        ]);

        return redirect()
            ->route('atendimentos.index')
            ->with('success', 'Atendimento registrado com sucesso.');
    }

    public function update(Request $request, Atendimento $atendimento)
    {
        $request->validate([
            'nome_solicitante' => 'required|string|max:255',
            'assunto_id'       => 'required|exists:assuntos,id',
            'descricao'        => 'required|string',
            'prioridade'       => 'required|in:baixa,media,alta',
            'empresa_id'       => 'required|exists:empresas,id',
        ]);

        $atendimento->update([
            'cliente_id'           => $request->cliente_id,
            'nome_solicitante'     => $request->nome_solicitante,
            'telefone_solicitante' => $request->telefone_solicitante,
            'email_solicitante'    => $request->email_solicitante,
            'assunto_id'           => $request->assunto_id,
            'descricao'            => $request->descricao,
            'prioridade'           => $request->prioridade,
            'empresa_id'           => $request->empresa_id,
            'funcionario_id'       => $request->funcionario_id,
        ]);

        return redirect()
            ->route('atendimentos.index')
            ->with('success', 'Atendimento atualizado com sucesso.');
    }

    // METODO ATUALIZAR CAMPO
    public function atualizarCampo(Request $request, Atendimento $atendimento)
    {
        $request->validate([
            'campo' => 'required|in:status,prioridade,funcionario_id',
            'valor' => 'nullable'
        ]);

        // STATUS → HISTÓRICO
        if ($request->campo === 'status') {

            if ($atendimento->status_atual === 'concluido') {
                return response()->json([
                    'success' => false,
                    'message' => 'Atendimento concluído não pode ser alterado.'
                ]);
            }

            $atendimento->update([
                'status_atual' => $request->valor
            ]);

            AtendimentoStatusHistorico::create([
                'atendimento_id' => $atendimento->id,
                'status'         => $request->valor,
                'observacao'     => 'Alteração via fila',
                'user_id'        => Auth::id(),
            ]);

            return response()->json(['success' => true]);
        }

        // PRIORIDADE ou TÉCNICO
        $atendimento->update([
            $request->campo => $request->valor
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Atendimento $atendimento)
    {
        $atendimento->delete();

        return redirect()
            ->route('atendimentos.index')
            ->with('success', 'Atendimento excluído com sucesso!');
    }
}