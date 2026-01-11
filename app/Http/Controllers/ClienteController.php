<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrimeiroAcessoMail;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::with(['emails', 'telefones']);

        // Filtro: nome ou e-mail
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                ->orWhereHas('emails', function ($emailQuery) use ($search) {
                    $emailQuery->where('valor', 'like', "%{$search}%");
                });
            });
        }

        // Filtro: status
        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->where('ativo', true);
            }

            if ($request->status === 'inativo') {
                $query->where('ativo', false);
            }
        }

        $clientes = $query
            ->orderBy('nome')
            ->get();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
        // Básicos
        'tipo_pessoa'  => 'required|in:PF,PJ',
        'cpf_cnpj'     => 'required|string|unique:clientes,cpf_cnpj',
        'nome'         => 'required|string|max:255',
        'razao_social' => 'nullable|string|max:255',
        'nome_fantasia'=> 'nullable|string|max:255',
        'tipo_cliente' => 'required|in:CONTRATO,AVULSO',
        'data_cadastro'=> 'required|date',

        // Endereço
        'cep'          => 'nullable|string|max:20',
        'logradouro'   => 'nullable|string|max:255',
        'numero'       => 'nullable|string|max:20',
        'complemento'  => 'nullable|string|max:255',
        'cidade'       => 'nullable|string|max:255',

        // Financeiro (somente contrato)
        'valor_mensal'   => 'nullable|numeric|min:0',
        'dia_vencimento' => 'nullable|integer|min:1|max:28',

        // Contatos
        'emails'        => 'required|array|min:1',
        'emails.*'      => 'required|email',

        'telefones'     => 'nullable|array',
        'telefones.*'   => 'nullable|string|max:50',

        'observacoes'   => 'nullable|string',
    ]);


        // Cria o cliente
        $cliente = Cliente::create([
            'nome'          => $request->nome,
            'ativo'         => $request->ativo ?? true,
            'tipo_pessoa'   => $request->tipo_pessoa,
            'cpf_cnpj'      => preg_replace('/\D/', '', $request->cpf_cnpj),
            'razao_social'  => $request->razao_social,
            'nome_fantasia' => $request->nome_fantasia,
            'tipo_cliente'  => $request->tipo_cliente,
            'data_cadastro' => $request->data_cadastro,
            'cep'           => $request->cep,
            'logradouro'    => $request->logradouro,
            'numero'        => $request->numero,
            'complemento'   => $request->complemento,
            'cidade'        => $request->cidade,
            'valor_mensal'   => $request->tipo_cliente === 'CONTRATO'
                ? $request->valor_mensal
                : null,
            'dia_vencimento' => $request->tipo_cliente === 'CONTRATO'
                ? $request->dia_vencimento
                : null,
            'observacoes'   => $request->observacoes,
        ]);


        // Salva os emails
        foreach ($request->emails as $i => $email) {
            $cliente->emails()->create([
                'valor'     => $email,
                'principal' => ($request->email_principal == $i),
            ]);
        }

        // Salva os telefones
        if ($request->filled('telefones')) {
            foreach ($request->telefones as $i => $telefone) {
                $cliente->telefones()->create([
                    'valor'     => $telefone,
                    'principal' => ($request->telefone_principal == $i),
                ]);
            }
        }

        // Recupera email principal
        $emailPrincipal = $cliente->emails()
            ->where('principal', true)
            ->first();

        if (!$emailPrincipal) {
            $emailPrincipal = $cliente->emails()->first();
        }

        // Cria usuário do cliente
        $user = User::create([
            'name'            => $cliente->nome,
            'email'           => $emailPrincipal->valor,
            'password'        => bcrypt(Str::random(40)), // senha temporária
            'tipo'            => 'cliente',
            'cliente_id'      => $cliente->id,
            'primeiro_acesso' => true,
        ]);

        // Envia e-mail de primeiro acesso
        Mail::to($user->email)->send(new PrimeiroAcessoMail($user));

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit(Cliente $cliente)
    {
        $cliente->load(['emails', 'telefones']);
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'valor_mensal' => 'required|numeric|min:0',
            'dia_vencimento' => 'required|integer|min:1|max:28',

            'emails' => 'required|array|min:1',
            'emails.*' => 'nullable|email',

            'telefones' => 'nullable|array',
            'telefones.*' => 'nullable|string|max:50',
        ]);

        // Atualiza cliente (CORRETO)
        $cliente->update([
            'nome'           => $request->nome,
            'ativo'          => $request->boolean('ativo'),
            'valor_mensal'   => $request->valor_mensal,
            'dia_vencimento' => $request->dia_vencimento,
        ]);

        // Remove contatos antigos
        $cliente->emails()->delete();
        $cliente->telefones()->delete();

        // Recria emails
        foreach ($request->emails as $id => $email) {
            if (!$email) continue;

            $cliente->emails()->create([
                'valor'     => $email,
                'principal' => ($request->email_principal == $id),
            ]);
        }

        // Recria telefones
        if ($request->filled('telefones')) {
            foreach ($request->telefones as $id => $telefone) {
                if (!$telefone) continue;

                $cliente->telefones()->create([
                    'valor'     => $telefone,
                    'principal' => ($request->telefone_principal == $id),
                ]);
            }
        }

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }


    public function destroy(Cliente $cliente)
    {
        // Soft delete do cliente
        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }
}