<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Empresa;


class UsuarioController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(!$user || !$user->isAdminPanel(), 403);

        $usuarios = User::with('perfis')
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(!$user || !$user->isAdminPanel(), 403);

        $perfis = Perfil::orderBy('nome')->get();
        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('usuarios.create', compact('perfis', 'empresas'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(!$user || !$user->isAdminPanel(), 403);

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'tipo'      => 'required|in:admin,administrativo,comercial,cliente,funcionario',
            'perfis'    => 'required|array',
            'empresas'  => 'nullable|array',
            'empresas.*'=> 'exists:empresas,id',
        ]);

        $novoUsuario = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'tipo'            => $request->tipo,
            'primeiro_acesso' => true,
        ]);

        $novoUsuario->perfis()->sync($request->perfis);

        if ($request->filled('empresas')) {
        $novoUsuario->empresas()->sync($request->empresas);
}

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(User $usuario)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(!$user || !$user->isAdminPanel(), 403);

        $perfis   = Perfil::orderBy('nome')->get();
        $empresas = \App\Models\Empresa::orderBy('nome_fantasia')->get();

        // carrega relações necessárias
        $usuario->load(['perfis', 'empresas']);

        return view('usuarios.edit', compact('usuario', 'perfis', 'empresas'));
    }

    public function update(Request $request, User $usuario)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(!$user || !$user->isAdminPanel(), 403);

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $usuario->id,
            'password'  => 'nullable|min:6',
            'tipo'      => 'required|in:admin,administrativo,comercial,cliente,funcionario',
            'perfis'    => 'required|array',
            'perfis.*'  => 'exists:perfis,id',
            'empresas'  => 'nullable|array',
            'empresas.*'=> 'exists:empresas,id',
        ]);

        // Atualiza dados básicos
        $usuario->update([
            'name'  => $request->name,
            'email' => $request->email,
            'tipo'  => $request->tipo,
        ]);

        // Atualiza senha somente se informada
        if ($request->filled('password')) {
            $usuario->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Sincroniza perfis
        $usuario->perfis()->sync($request->perfis);

        // Sincroniza empresas (multiempresa)
        if ($request->filled('empresas')) {
            $usuario->empresas()->sync($request->empresas);
        } else {
            // se não marcou nenhuma empresa, remove vínculos
            $usuario->empresas()->detach();
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }


}