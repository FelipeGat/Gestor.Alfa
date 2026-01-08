<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrimeiroAcessoMail;

class FuncionarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Funcionario::query();

        // Filtro por nome
        if ($request->filled('search')) {
            $query->where('nome', 'like', "%{$request->search}%");
        }

        // Filtro por status
        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->where('ativo', true);
            }

            if ($request->status === 'inativo') {
                $query->where('ativo', false);
            }
        }

        $funcionarios = $query
            ->orderBy('nome')
            ->get();

        return view('funcionarios.index', compact('funcionarios'));
    }

    public function create()
    {
        return view('funcionarios.create');
    }

    public function store(Request $request)
    {
            $request->validate(
        [
            'nome'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ],
        [
            'email.unique' => 'Este e-mail já está em uso por outro usuário.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'nome.required' => 'O nome é obrigatório.',
        ]
    );

        // Cria funcionário
        $funcionario = Funcionario::create([
            'nome'  => $request->nome,
            'ativo' => $request->ativo ?? true,
        ]);

        // Cria usuário do funcionário
        $user = User::create([
            'name'            => $funcionario->nome,
            'email'           => $request->email,
            'password' => bcrypt($request->email), // senha temporária email
            'tipo'            => 'funcionario',
            'funcionario_id'  => $funcionario->id,
            'primeiro_acesso' => true,
        ]);

        // Envia e-mail de primeiro acesso
        Mail::to($user->email)->send(new PrimeiroAcessoMail($user));

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    public function edit(Funcionario $funcionario)
    {
        $funcionario->load('user');
        return view('funcionarios.edit', compact('funcionario'));
    }

    public function update(Request $request, Funcionario $funcionario)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($funcionario->user->id),],
        ]);

        $funcionario->update([
            'nome'  => $request->nome,
            'ativo' => $request->boolean('ativo'),
        ]);

        $funcionario->user->update([
            'email' => $request->email,
        ]);

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function destroy(Funcionario $funcionario)
    {
        $funcionario->delete();

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário excluído com sucesso!');
    }
}