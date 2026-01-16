<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        return view('usuarios.create', compact('perfis'));
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
        ]);

        $novoUsuario = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'tipo'            => $request->tipo,
            'primeiro_acesso' => true,
        ]);

        $novoUsuario->perfis()->sync($request->perfis);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso.');
    }
}