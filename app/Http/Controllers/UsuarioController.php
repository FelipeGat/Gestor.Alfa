<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Perfil;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(! $user || ! $user->isAdminPanel(), 403, 'Acesso não autorizado');

        /*
        |--------------------------------------------------------------------------
        | Query base
        |--------------------------------------------------------------------------
        */
        $query = User::with('perfis');

        // (Opcional) Busca futura
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->where('primeiro_acesso', false);
            } elseif ($request->status === 'inativo' || $request->status === 'primeiro acesso') {
                $query->where('primeiro_acesso', true);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Paginação
        |--------------------------------------------------------------------------
        */
        $usuarios = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Resumo (dados REAIS do banco)
        |--------------------------------------------------------------------------
        */
        $totalUsuarios = User::count();
        $usuariosAdmins = User::where('tipo', 'admin')->count();
        $usuariosClientes = User::where('tipo', 'cliente')->count();
        $usuariosAtivos = User::where('primeiro_acesso', false)->count();
        $usuariosInativos = User::where('primeiro_acesso', true)->count();

        return view('usuarios.index', compact(
            'usuarios',
            'totalUsuarios',
            'usuariosAdmins',
            'usuariosAtivos',
            'usuariosInativos',
            'usuariosClientes'
        ));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(! $user || ! $user->isAdminPanel(), 403, 'Acesso não autorizado');

        $perfis = Perfil::orderBy('nome')->get();
        $empresas = Empresa::orderBy('nome_fantasia')->get();

        return view('usuarios.create', compact('perfis', 'empresas'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(! $user || ! $user->isAdminPanel(), 403, 'Acesso não autorizado');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'tipo' => 'required|in:admin,administrativo,comercial,cliente,funcionario',
            'perfis' => 'required|array',
            'perfis.*' => 'exists:perfis,id',
            'empresas' => 'nullable|array',
            'empresas.*' => 'exists:empresas,id',
        ]);

        $novoUsuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tipo' => $request->tipo,
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

        abort_if(! $user || ! $user->isAdminPanel(), 403, 'Acesso não autorizado');

        $perfis = Perfil::orderBy('nome')->get();
        $empresas = Empresa::orderBy('nome_fantasia')->get();

        $usuario->load(['perfis', 'empresas']);

        return view('usuarios.edit', compact('usuario', 'perfis', 'empresas'));
    }

    public function update(Request $request, User $usuario)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(! $user || ! $user->isAdminPanel(), 403, 'Acesso não autorizado');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$usuario->id,
            'password' => 'nullable|min:6',
            'tipo' => 'required|in:admin,administrativo,comercial,cliente,funcionario',
            'perfis' => 'required|array',
            'perfis.*' => 'exists:perfis,id',
            'empresas' => 'nullable|array',
            'empresas.*' => 'exists:empresas,id',
        ]);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'tipo' => $request->tipo,
        ]);

        if ($request->filled('password')) {
            $usuario->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $usuario->perfis()->sync($request->perfis);

        if ($request->filled('empresas')) {
            $usuario->empresas()->sync($request->empresas);
        } else {
            $usuario->empresas()->detach();
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }
}
