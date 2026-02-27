<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('clientes', 'ler'),
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

        $totalClientes = Cliente::count();
        $clientesAtivos = Cliente::where('ativo', true)->count();
        $clientesInativos = Cliente::where('ativo', false)->count();
        $receitaMensal = Cliente::where('ativo', true)->sum('valor_mensal');

        $clientes = $query->orderBy('nome')->paginate(10)->withQueryString();

        return view('clientes.index', compact(
            'clientes',
            'totalClientes',
            'clientesAtivos',
            'clientesInativos',
            'receitaMensal'
        ));
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('clientes', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        return view('clientes.create');
    }

    public function store(Request $request)
    {
        // Validação extra: impedir cadastro duplicado após busca Receita
        $cpfCnpjLimpo = preg_replace('/\D/', '', $request->cpf_cnpj);
        if (Cliente::where('cpf_cnpj', $cpfCnpjLimpo)->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => ['cpf_cnpj' => ['Este CPF/CNPJ já está cadastrado no sistema.']],
                ], 422);
            }

            return back()->withErrors(['cpf_cnpj' => 'Este CPF/CNPJ já está cadastrado no sistema.'])->withInput();
        }

        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('clientes', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        $request->validate(
            [
                'tipo_pessoa' => 'required|in:PF,PJ',
                'cpf_cnpj' => [
                    'required',
                    'string',
                    Rule::unique('clientes', 'cpf_cnpj'),
                ],
                'nome' => 'required_if:tipo_pessoa,PF|string|max:255',
                'razao_social' => 'required_if:tipo_pessoa,PJ|string|max:255|',
                'nome_fantasia' => 'required_if:tipo_pessoa,PJ|string|max:255|',
                'tipo_cliente' => 'required|in:CONTRATO,AVULSO',
                'data_cadastro' => 'required|date',

                'valor_mensal' => 'required_if:tipo_cliente,CONTRATO|nullable|numeric|min:0.01',
                'dia_vencimento' => 'required_if:tipo_cliente,CONTRATO|nullable|integer|min:1|max:28',

                'cep' => 'nullable|string|max:20',
                'logradouro' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:255',
                'cidade' => 'nullable|string|max:255',
                'bairro' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:2',

                'inscricao_estadual' => 'nullable|string|max:50',
                'inscricao_municipal' => 'nullable|string|max:50',

                'emails' => 'required|array|min:1',
                'emails.*' => 'required|email',

                'telefones' => 'nullable|array',
                'telefones.*' => 'nullable|string|max:50',

                'observacoes' => 'nullable|string',
            ],
            [
                'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado no sistema.',
                'cpf_cnpj.required' => 'Informe o CPF ou CNPJ.',
                'valor_mensal.required_if' => 'Informe o valor mensal para clientes do tipo CONTRATO.',
                'dia_vencimento.required_if' => 'Informe o dia de vencimento para clientes do tipo CONTRATO.',
            ]
        );

        $dadosNome = $this->resolverNomeCliente($request);

        $cliente = Cliente::create([
            'nome' => $dadosNome['nome'],
            'nome_fantasia' => $dadosNome['nome_fantasia'],
            'razao_social' => $dadosNome['razao_social'],
            'ativo' => $request->ativo ?? true,
            'tipo_pessoa' => $request->tipo_pessoa,
            'cpf_cnpj' => preg_replace('/\D/', '', $request->cpf_cnpj),
            'tipo_cliente' => $request->tipo_cliente,
            'nota_fiscal' => $request->nota_fiscal ?? false,
            'data_cadastro' => $request->data_cadastro,
            'cep' => $request->cep,
            'logradouro' => $request->logradouro,
            'numero' => $request->numero,
            'complemento' => $request->complemento,
            'cidade' => $request->cidade,
            'bairro' => $request->bairro,
            'estado' => $request->estado,
            'inscricao_estadual' => $request->inscricao_estadual,
            'inscricao_municipal' => $request->inscricao_municipal,
            'valor_mensal' => $request->tipo_cliente === 'CONTRATO' ? $request->valor_mensal : null,
            'dia_vencimento' => $request->tipo_cliente === 'CONTRATO' ? $request->dia_vencimento : null,
            'observacoes' => $request->observacoes,
        ]);

        foreach ($request->emails as $i => $email) {
            $cliente->emails()->create([
                'valor' => $email,
                'principal' => ($request->email_principal == $i),
            ]);
        }

        if ($request->filled('telefones')) {
            foreach ($request->telefones as $i => $telefone) {
                if (trim($telefone) !== '') {
                    $cliente->telefones()->create([
                        'valor' => $telefone,
                        'principal' => ($request->telefone_principal == $i),
                    ]);
                }
            }
        }

        $emailPrincipal = $cliente->emails()->where('principal', true)->first()
            ?? $cliente->emails()->first();

        $userCliente = User::create([
            'name' => $cliente->nome,
            'email' => $emailPrincipal->valor,
            'password' => bcrypt(Str::random(40)),
            'tipo' => 'cliente',
            'cliente_id' => $cliente->id,
            'primeiro_acesso' => true,
        ]);

        // Vincular perfil 'cliente' ao usuário
        $perfilCliente = \App\Models\Perfil::where('slug', 'cliente')->first();
        if ($perfilCliente) {
            $userCliente->perfis()->sync([$perfilCliente->id]);
        }

        // Vincular usuário ao cliente na tabela cliente_user
        $userCliente->clientes()->syncWithoutDetaching([$cliente->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente cadastrado com sucesso!',
                'redirect' => route('clientes.index'),
            ]);
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit(Cliente $cliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('clientes', 'editar'),
            403,
            'Acesso não autorizado'
        );

        $usuarios = User::where('tipo', 'cliente')->orderBy('name')->get();
        $usuariosVinculados = $cliente->users->pluck('id')->toArray();

        $cliente->load(['emails', 'telefones']);

        return view('clientes.edit', compact('cliente', 'usuarios', 'usuariosVinculados'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('clientes', 'editar'),
            403,
            'Acesso não autorizado'
        );

        $cpfCnpjNormalizado = preg_replace('/\D/', '', $request->cpf_cnpj);
        $request->merge(['cpf_cnpj' => $cpfCnpjNormalizado]);

        $request->validate(
            [
                'nome' => 'required|string|max:255',
                'cpf_cnpj' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($cliente) {
                        $existe = \App\Models\Cliente::where('cpf_cnpj', $value)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $cliente->id)
                            ->exists();
                        if ($existe) {
                            $fail('Este CPF/CNPJ já está cadastrado em outro cliente.');
                        }
                    },
                ],
                'tipo_pessoa' => 'required|in:PF,PJ',
                'razao_social' => 'nullable|required_if:tipo_pessoa,PJ|string|max:255',
                'valor_mensal' => 'nullable|numeric|min:0',
                'dia_vencimento' => 'nullable|integer|min:1|max:28',
                'emails' => 'required|array|min:1',
                'emails.*' => 'required|email',
            ]
        );

        $dadosNome = $this->resolverNomeCliente($request);

        $cliente->update([
            'nome' => $dadosNome['nome'],
            'nome_fantasia' => $dadosNome['nome_fantasia'],
            'razao_social' => $dadosNome['razao_social'],
            'tipo_pessoa' => $request->tipo_pessoa,
            'cpf_cnpj' => preg_replace('/\D/', '', $request->cpf_cnpj),
            'tipo_cliente' => $request->tipo_cliente,
            'ativo' => $request->boolean('ativo'),
            'valor_mensal' => $request->valor_mensal,
            'dia_vencimento' => $request->dia_vencimento,
            'bairro' => $request->bairro,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'complemento' => $request->complemento,
            'inscricao_estadual' => $request->inscricao_estadual,
            'inscricao_municipal' => $request->inscricao_municipal,
            'observacoes' => $request->observacoes,
            'nota_fiscal' => $request->nota_fiscal ?? false,
        ]);

        $cliente->emails()->delete();
        $cliente->telefones()->delete();

        foreach ($request->emails as $i => $email) {
            $cliente->emails()->create([
                'valor' => $email,
                'principal' => ($request->email_principal == $i),
            ]);
        }

        if ($request->filled('telefones')) {
            foreach ($request->telefones as $i => $telefone) {
                $cliente->telefones()->create([
                    'valor' => $telefone,
                    'principal' => ($request->telefone_principal == $i),
                ]);
            }
        }

        // Responsáveis do Portal (vínculo usuário / cliente)
        if ($request->has('usuarios_portal')) {
            $cliente->users()->sync($request->usuarios_portal);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente atualizado com sucesso!',
                'redirect' => route('clientes.index'),
            ]);
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function buscar(Request $request)
    {
        try {
            $search = trim((string) $request->query('q'));

            $clientes = Cliente::query()
                ->whereNull('deleted_at')
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('cpf_cnpj', 'like', "%{$search}%")
                            ->orWhere('razao_social', 'like', "%{$search}%")
                            ->orWhere('nome_fantasia', 'like', "%{$search}%");
                    });
                })
                ->orderBy('nome_fantasia')
                ->limit(10)
                ->get()
                ->map(fn ($cliente) => [
                    'id' => $cliente->id,
                    'cpf_cnpj' => $cliente->cpf_cnpj,
                    'nome_fantasia' => $cliente->nome_fantasia,
                    'razao_social' => $cliente->razao_social,
                ]);

            return response()->json($clientes);
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar cliente', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([], 500);
        }
    }

    private function resolverNomeCliente(Request $request): array
    {
        // Pessoa Física
        if ($request->tipo_pessoa === 'PF') {
            return [
                'nome' => trim((string) $request->nome),
                'nome_fantasia' => null,
                'razao_social' => trim((string) $request->nome),
            ];
        }

        // Pessoa Jurídica
        $nomeFantasia = trim((string) $request->nome_fantasia);
        $razaoSocial = trim((string) $request->razao_social);

        return [
            'nome' => $nomeFantasia !== '' ? $nomeFantasia : $razaoSocial,
            'nome_fantasia' => $nomeFantasia !== '' ? $nomeFantasia : $razaoSocial,
            'razao_social' => $razaoSocial,
        ];
    }

    public function destroy(Cliente $cliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('clientes', 'excluir'),
            403,
            'Acesso não autorizado'
        );

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }

    /**
     * API - Retorna lista de clientes para select
     */
    public function apiList()
    {
        $clientes = \App\Models\Cliente::where('ativo', true)
            ->select('id', 'nome', 'nome_fantasia', 'razao_social')
            ->orderBy('nome')
            ->get();

        return response()->json($clientes);
    }
}
