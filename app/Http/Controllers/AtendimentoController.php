<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Assunto;
use Illuminate\Http\Request;

use App\Models\AtendimentoStatusHistorico;
use Illuminate\Support\Facades\Auth;


class AtendimentoController extends Controller
{
    public function index()
    {
        $atendimentos = Atendimento::with([
                'cliente',
                'assunto',
                'empresa',
                'funcionario'
            ])
            ->orderByRaw("
                FIELD(prioridade, 'alta', 'media', 'baixa'),
                data_atendimento ASC,
                numero_atendimento ASC
            ")
            ->get();

        return view('atendimentos.index', compact('atendimentos'));
    }

    public function create()
    {
        $clientes     = Cliente::orderBy('nome')->get();
        $assuntos     = Assunto::where('ativo', true)->orderBy('nome')->get();
        $empresas     = Empresa::orderBy('nome_fantasia')->get();
        $funcionarios = Funcionario::where('ativo', true)->orderBy('nome')->get();

        return view('atendimentos.create', compact(
            'clientes',
            'assuntos',
            'empresas',
            'funcionarios'
        ));
    }

    public function edit(Atendimento $atendimento)
    {
        $clientes     = Cliente::orderBy('nome')->get();
        $assuntos     = Assunto::where('ativo', true)->orderBy('nome')->get();
        $empresas     = Empresa::orderBy('nome_fantasia')->get();
        $funcionarios = Funcionario::where('ativo', true)->orderBy('nome')->get();

        return view('atendimentos.edit', compact(
            'atendimento',
            'clientes',
            'assuntos',
            'empresas',
            'funcionarios'
        ));
    }

    public function update(Request $request, Atendimento $atendimento)
    {
        try {
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

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->withErrors([
                    'erro_sistema' =>
                        'Erro ao atualizar o atendimento. Verifique os dados ou contate o suporte.'
                ]);
        }

    }

    public function destroy(Atendimento $atendimento)
        {
            // Soft delete do cliente
            $atendimento->delete();

            return redirect()
                ->route('atendimentos.index')
                ->with('success', 'Atendimento excluído com sucesso!');
        }


        // public function store(Request $request)
        // {
        //     dd($request->all());
        // }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome_solicitante' => 'required|string|max:255',
                'assunto_id'       => 'required|exists:assuntos,id',
                'descricao'        => 'required|string',
                'prioridade'       => 'required|in:baixa,media,alta',
                'empresa_id'       => 'required|exists:empresas,id',
                'status_inicial'   => 'required|in:orcamento,aberto,garantia',
            ]);

            $ultimoNumero = Atendimento::max('numero_atendimento') ?? 0;

            // regra de orçamento
            $isOrcamento = $request->status_inicial === 'orcamento';

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
                'status_atual'         => $request->status_inicial,
                'is_orcamento'         => $isOrcamento,
                'data_atendimento'     => now(),
            ]);

            // registra histórico inicial
            AtendimentoStatusHistorico::create([
                'atendimento_id' => $atendimento->id,
                'status'         => $request->status_inicial,
                'observacao'     => 'Abertura do atendimento',
                'user_id'        => Auth::id(),
            ]);

            return redirect()
                ->route('atendimentos.index')
                ->with('success', 'Atendimento registrado com sucesso.');

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->withErrors([
                    'erro_sistema' =>
                        'Erro ao criar o atendimento. Verifique os dados ou contate o suporte.'
                ]);
        }
    }

 }