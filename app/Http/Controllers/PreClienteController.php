<?php

namespace App\Http\Controllers;

use App\Models\PreCliente;
use App\Models\Cliente;
use App\Models\Email;
use App\Models\Telefone;
use App\Models\Orcamento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PreClienteController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! in_array($user->tipo, ['admin', 'comercial']),
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
            ->paginate(15)
            ->withQueryString();

        return view('pre-clientes.index', compact('preClientes'));
    }

    public function create(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! in_array($user->tipo, ['admin', 'comercial']),
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
            ! in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'tipo_pessoa'   => 'required|in:PF,PJ',
            'cpf_cnpj'      => 'required|string|max:20|unique:pre_clientes,cpf_cnpj',
            'razao_social'  => 'required_if:tipo_pessoa,PJ|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'telefone'      => 'nullable|string|max:50',
            'cep'           => 'nullable|string|max:20',
            'logradouro'    => 'nullable|string|max:255',
            'numero'        => 'nullable|string|max:20',
            'complemento'   => 'nullable|string|max:255',
            'bairro'        => 'nullable|string|max:255',
            'cidade'        => 'nullable|string|max:255',
            'estado'        => 'nullable|string|size:2',
        ]);

        // Validar CPF/CNPJ já existente em clientes
        $cpfCnpjLimpo = preg_replace('/\D/', '', $request->cpf_cnpj);

        $existeEmCliente = Cliente::where('cpf_cnpj', $cpfCnpjLimpo)->exists();
        if ($existeEmCliente) {
            return back()
                ->withErrors(['cpf_cnpj' => 'Este CPF/CNPJ já está cadastrado como cliente.'])
                ->withInput();
        }

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
            ! in_array($user->tipo, ['admin', 'comercial']),
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
            ! in_array($user->tipo, ['admin', 'comercial']),
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
            ! in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        $preCliente->delete();

        return redirect()
            ->route('pre-clientes.index')
            ->with('success', 'Pré-cliente excluído com sucesso.');
    }

    /**
     * CONVERTE PRÉ-CLIENTE EM CLIENTE
     */
    public function converterParaCliente(PreCliente $preCliente)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! in_array($user->tipo, ['admin', 'comercial']),
            403,
            'Acesso não autorizado'
        );

        // Evita duplicar cliente
        if (Cliente::where('cpf_cnpj', $preCliente->cpf_cnpj)->exists()) {
            return back()->withErrors([
                'cpf_cnpj' => 'Já existe um cliente com este CPF/CNPJ.',
            ]);
        }

        DB::transaction(function () use ($preCliente, &$cliente) {

            //  Cria o cliente
            $cliente = Cliente::create([
                'tipo_pessoa'   => $preCliente->tipo_pessoa,
                'cpf_cnpj'      => $preCliente->cpf_cnpj,
                'razao_social'  => $preCliente->razao_social,
                'nome_fantasia' => $preCliente->nome_fantasia,
                'nome'          => $preCliente->nome_fantasia
                    ?: $preCliente->razao_social,
                'cep'           => $preCliente->cep,
                'logradouro'    => $preCliente->logradouro,
                'numero'        => $preCliente->numero,
                'complemento'   => $preCliente->complemento,
                'bairro'        => $preCliente->bairro,
                'cidade'        => $preCliente->cidade,
                'estado'        => $preCliente->estado,
                'ativo'         => true,
                'data_cadastro' => now(),
            ]);

            //  Cria o email do cliente (se existir)
            if (!empty($preCliente->email)) {
                Email::create([
                    'cliente_id' => $cliente->id,
                    'valor'      => $preCliente->email,
                    'principal'  => true,
                ]);
            }

            //  Cria o telefone do cliente (se existir)
            if (!empty($preCliente->telefone)) {
                Telefone::create([
                    'cliente_id' => $cliente->id,
                    'valor'      => $preCliente->telefone,
                    'principal'  => true,
                ]);
            }

            //  Atualiza orçamentos vinculados
            Orcamento::where('pre_cliente_id', $preCliente->id)
                ->update([
                    'cliente_id'     => $cliente->id,
                    'pre_cliente_id' => null,
                ]);

            //  Remove o pré-cliente
            $preCliente->delete();
        });

        return redirect()
            ->route('clientes.edit', $cliente)
            ->with('success', 'Pré-cliente convertido em cliente com sucesso.');
    }
}
