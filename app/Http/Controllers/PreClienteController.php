<?php

namespace App\Http\Controllers;

use App\Models\PreCliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreClienteController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        $query = PreCliente::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('cpf_cnpj', 'like', "%{$search}%")
                  ->orWhere('razao_social', 'like', "%{$search}%")
                  ->orWhere('nome_fantasia', 'like', "%{$search}%");
            });
        }

        $preClientes = $query
            ->orderByDesc('created_at')
            ->get();

        return view('pre-clientes.index', compact('preClientes'));
    }

    public function create(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        return view('pre-clientes.create', [
            'valorBusca' => $request->query('q'),
            'origem'     => $request->query('from'),
        ]);
    }


    public function store(Request $request)
    {

        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'tipo_pessoa'   => 'required|in:PF,PJ',
            'cpf_cnpj'      => 'required|string|max:20',
            'razao_social'  => 'nullable|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'telefone'      => 'nullable|string|max:50',

            'cep'           => 'nullable|string|max:20',
            'logradouro'    => 'nullable|string|max:255',
            'numero'        => 'nullable|string|max:20',
            'complemento'   => 'nullable|string|max:255',
            'bairro'        => 'nullable|string|max:255',
            'cidade'        => 'nullable|string|max:255',
            'estado'        => 'nullable|string|max:2',
        ]);

        PreCliente::create([
            'tipo_pessoa'   => $request->tipo_pessoa,
            'cpf_cnpj'      => preg_replace('/\D/', '', $request->cpf_cnpj),
            'razao_social'  => $request->razao_social,
            'nome_fantasia' => $request->nome_fantasia,
            'email'         => $request->email,
            'telefone'      => $request->telefone,

            'cep'           => $request->cep,
            'logradouro'    => $request->logradouro,
            'numero'        => $request->numero,
            'complemento'   => $request->complemento,
            'bairro'        => $request->bairro,
            'cidade'        => $request->cidade,
            'estado'        => $request->estado,

            'origem'        => 'manual',
            'created_by'    => $user->id,
        ]);

        return redirect()
            ->route('pre-clientes.index')
            ->with('success', 'Pré-cliente cadastrado com sucesso.');
    }

    public function edit(PreCliente $preCliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        return view('pre-clientes.edit', compact('preCliente'));
    }

    public function update(Request $request, PreCliente $preCliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'tipo_pessoa'   => 'required|in:PF,PJ',
            'cpf_cnpj'      => 'required|string|max:20',
            'razao_social'  => 'nullable|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'telefone'      => 'nullable|string|max:50',
        ]);

        $preCliente->update([
            'tipo_pessoa'   => $request->tipo_pessoa,
            'cpf_cnpj'      => preg_replace('/\D/', '', $request->cpf_cnpj),
            'razao_social'  => $request->razao_social,
            'nome_fantasia' => $request->nome_fantasia,
            'email'         => $request->email,
            'telefone'      => $request->telefone,
        ]);

        return redirect()
            ->route('pre-clientes.index')
            ->with('success', 'Pré-cliente atualizado com sucesso.');
    }

    public function destroy(PreCliente $preCliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            !in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        $preCliente->delete();

        return redirect()
            ->route('pre-clientes.index')
            ->with('success', 'Pré-cliente excluído com sucesso.');
    }
}