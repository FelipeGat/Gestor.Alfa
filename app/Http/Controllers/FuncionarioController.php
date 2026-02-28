<?php

namespace App\Http\Controllers;

use App\Mail\PrimeiroAcessoMail;
use App\Models\Epi;
use App\Models\Funcionario;
use App\Models\Jornada;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class FuncionarioController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->canPermissao('funcionarios', 'ler'),
            403
        );
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
            ->paginate(15)
            ->appends($request->query());

        $totais = [
            'total' => Funcionario::count(),
            'ativos' => Funcionario::where('ativo', true)->count(),
            'inativos' => Funcionario::where('ativo', false)->count(),
        ];

        return view('funcionarios.index', compact('funcionarios', 'totais'));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->canPermissao('funcionarios', 'incluir'),
            403
        );

        return view('funcionarios.create');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'nome' => 'required|string|max:255',
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
            'nome' => $request->nome,
            'ativo' => $request->ativo ?? true,
        ]);

        // Cria usuário do funcionário
        $user = User::create([
            'name' => $funcionario->nome,
            'email' => $request->email,
            'password' => bcrypt($request->email), // senha temporária email
            'tipo' => 'funcionario',
            'funcionario_id' => $funcionario->id,
            'primeiro_acesso' => true,
        ]);

        // Envia e-mail de primeiro acesso
        Mail::to($user->email)->send(new PrimeiroAcessoMail($user));

        $indexRoute = $request->routeIs('rh.*')
            ? 'rh.funcionarios.index'
            : 'funcionarios.index';

        return redirect()
            ->route($indexRoute)
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    public function edit(Funcionario $funcionario)
    {
        $relacoes = ['user'];

        if (Schema::hasTable('funcionario_documentos')) {
            $relacoes['documentos'] = fn($query) => $query->orderByDesc('data_vencimento');
        }

        if (Schema::hasTable('funcionario_epis') && Schema::hasTable('epis')) {
            $relacoes['episVinculos.epi'] = fn($query) => $query->orderByDesc('data_prevista_troca');
        }

        if (Schema::hasTable('funcionario_beneficios')) {
            $relacoes['beneficios'] = fn($query) => $query->orderByDesc('data_inicio');
        }

        if (Schema::hasTable('funcionario_jornadas') && Schema::hasTable('jornadas')) {
            $relacoes['jornadasVinculos'] = fn($query) => $query->orderByDesc('data_inicio');
            $relacoes[] = 'jornadasVinculos.jornada';
        }

        if (Schema::hasTable('ferias')) {
            $relacoes['ferias'] = fn($query) => $query->orderByDesc('periodo_aquisitivo_fim');
        }

        if (Schema::hasTable('advertencias')) {
            $relacoes['advertencias'] = fn($query) => $query->orderByDesc('data');
        }

        $funcionario->load($relacoes);

        $epis = Schema::hasTable('epis')
            ? Epi::query()->orderBy('nome')->get(['id', 'nome', 'ca'])
            : collect();

        $jornadas = Schema::hasTable('jornadas')
            ? Jornada::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'carga_horaria_semanal'])
            : collect();

        return view('funcionarios.edit', compact('funcionario', 'epis', 'jornadas'));
    }

    public function update(Request $request, Funcionario $funcionario)
    {

        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->canPermissao('funcionarios', 'incluir'),
            403
        );

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($funcionario->user->id)],
        ]);

        $funcionario->update([
            'nome' => $request->nome,
            'ativo' => $request->boolean('ativo'),
        ]);

        $funcionario->user->update([
            'email' => $request->email,
        ]);

        $indexRoute = $request->routeIs('rh.*')
            ? 'rh.funcionarios.index'
            : 'funcionarios.index';

        return redirect()
            ->route($indexRoute)
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function destroy(Request $request, Funcionario $funcionario)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->canPermissao('funcionarios', 'excluir'),
            403
        );

        $funcionario->delete();

        $indexRoute = $request->routeIs('rh.*')
            ? 'rh.funcionarios.index'
            : 'funcionarios.index';

        return redirect()
            ->route($indexRoute)
            ->with('success', 'Funcionário excluído com sucesso!');
    }
}
