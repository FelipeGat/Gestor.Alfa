<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\PrimeiroAcessoMail;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() &&
            !$user->canPermissao('clientes', 'ler'),
            403,
            'Acesso não autorizado'
        );

        $query = Cliente::with(['emails', 'telefones']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhereHas('emails', function ($emailQuery) use ($search) {
                      $emailQuery->where('valor', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $clientes = $query->orderBy('nome')->get();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() &&
            !$user->canPermissao('clientes', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        return view('clientes.create');
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() &&
            !$user->canPermissao('clientes', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        $request->validate(
            [
                'tipo_pessoa'   => 'required|in:PF,PJ',
                'cpf_cnpj'      => [
                    'required',
                    'string',
                    Rule::unique('clientes', 'cpf_cnpj'),
                ],
                'nome'          => 'required|string|max:255',
                'nome_fantasia' => 'nullable|string|max:255',
                'tipo_cliente'  => 'required|in:CONTRATO,AVULSO',
                'data_cadastro' => 'required|date',

                'cep'           => 'nullable|string|max:20',
                'logradouro'    => 'nullable|string|max:255',
                'numero'        => 'nullable|string|max:20',
                'complemento'   => 'nullable|string|max:255',
                'cidade'        => 'nullable|string|max:255',
                'bairro'        => 'nullable|string|max:255',
                'estado'        => 'nullable|string|max:2',

                'inscricao_estadual'   => 'nullable|string|max:50',
                'inscricao_municipal' => 'nullable|string|max:50',

                'valor_mensal'   => 'nullable|numeric|min:0',
                'dia_vencimento' => 'nullable|integer|min:1|max:28',

                'emails'      => 'required|array|min:1',
                'emails.*'    => 'required|email',

                'telefones'   => 'nullable|array',
                'telefones.*' => 'nullable|string|max:50',

                'observacoes' => 'nullable|string',
            ],
            [
                'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado no sistema.',
                'cpf_cnpj.required' => 'Informe o CPF ou CNPJ.',
            ]
        );
        
        $cliente = Cliente::create([
            'nome'           => $request->nome,
            'ativo'          => $request->ativo ?? true,
            'tipo_pessoa'    => $request->tipo_pessoa,
            'cpf_cnpj'       => preg_replace('/\D/', '', $request->cpf_cnpj),
            'nome_fantasia'  => $request->nome_fantasia,
            'tipo_cliente'   => $request->tipo_cliente,
            'data_cadastro'  => $request->data_cadastro,
            'cep'            => $request->cep,
            'logradouro'     => $request->logradouro,
            'numero'         => $request->numero,
            'complemento'    => $request->complemento,
            'cidade'         => $request->cidade,
            'bairro'         => $request->bairro,
            'estado'         => $request->estado,
            'inscricao_estadual'   => $request->inscricao_estadual,
            'inscricao_municipal'  => $request->inscricao_municipal,
            'valor_mensal'   => $request->tipo_cliente === 'CONTRATO' ? $request->valor_mensal : null,
            'dia_vencimento' => $request->tipo_cliente === 'CONTRATO' ? $request->dia_vencimento : null,
            'observacoes'    => $request->observacoes,
        ]);

        foreach ($request->emails as $i => $email) {
            $cliente->emails()->create([
                'valor'     => $email,
                'principal' => ($request->email_principal == $i),
            ]);
        }

        if ($request->filled('telefones')) {
            foreach ($request->telefones as $i => $telefone) {
                $cliente->telefones()->create([
                    'valor'     => $telefone,
                    'principal' => ($request->telefone_principal == $i),
                ]);
            }
        }

        $emailPrincipal = $cliente->emails()->where('principal', true)->first()
            ?? $cliente->emails()->first();

        $userCliente = User::create([
            'name'            => $cliente->nome,
            'email'           => $emailPrincipal->valor,
            'password'        => bcrypt(Str::random(40)),
            'tipo'            => 'cliente',
            'cliente_id'      => $cliente->id,
            'primeiro_acesso' => true,
        ]);

        Mail::to($userCliente->email)->send(new PrimeiroAcessoMail($userCliente));

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit(Cliente $cliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() &&
            !$user->canPermissao('clientes', 'editar'),
            403,
            'Acesso não autorizado'
        );

        $cliente->load(['emails', 'telefones']);

        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() &&
            !$user->canPermissao('clientes', 'editar'),
            403,
            'Acesso não autorizado'
        );

        $request->validate(
            [
                'nome' => 'required|string|max:255',

                'cpf_cnpj' => [
                    'required',
                    'string',
                    Rule::unique('clientes', 'cpf_cnpj')->ignore($cliente->id),
                ],

                'valor_mensal'   => 'nullable|numeric|min:0',
                'dia_vencimento' => 'nullable|integer|min:1|max:28',

                'emails'   => 'required|array|min:1',
                'emails.*' => 'required|email',
            ],
            [
                'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado em outro cliente.',
            ]
        );

        $cliente->update($request->only([
            'nome',
            'ativo',
            'valor_mensal',
            'dia_vencimento',
            'bairro',
            'cidade',
            'estado',
            'complemento',
            'inscricao_estadual',
            'inscricao_municipal',
            'observacoes'
        ]));


        $cliente->emails()->delete();
        $cliente->telefones()->delete();

        foreach ($request->emails as $i => $email) {
            $cliente->emails()->create([
                'valor'     => $email,
                'principal' => ($request->email_principal == $i),
            ]);
        }

        if ($request->filled('telefones')) {
            foreach ($request->telefones as $i => $telefone) {
                $cliente->telefones()->create([
                    'valor'     => $telefone,
                    'principal' => ($request->telefone_principal == $i),
                ]);
            }
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !$user->isAdminPanel() &&
            !$user->canPermissao('clientes', 'excluir'),
            403,
            'Acesso não autorizado'
        );

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }
}