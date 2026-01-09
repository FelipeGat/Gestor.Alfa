<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Assunto;
use Illuminate\Http\Request;

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




    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome_solicitante' => 'required|string|max:255',
                'assunto_id'       => 'required|exists:assuntos,id',
                'descricao'        => 'required|string',
                'prioridade'       => 'required|in:baixa,media,alta',
                'empresa_id'       => 'required|exists:empresas,id',
            ]);

            $ultimoNumero = Atendimento::max('numero_atendimento') ?? 0;

            Atendimento::create([
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
                'status'               => 'aberto',
                'data_atendimento'     => now(),
            ]);

            return redirect()
                ->route('atendimentos.index')
                ->with('success', 'Atendimento registrado com sucesso.');

            } catch (\Throwable $e) {

                return back()
                    ->withInput()
                    ->withErrors([
                        'erro_sistema' =>
                            'Ocorreu um erro inesperado ao salvar o atendimento. ' .
                            'Verifique os dados ou contate o suporte técnico.'
                    ]);
            }
        }
 }