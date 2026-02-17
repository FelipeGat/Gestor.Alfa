<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\FornecedorContato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FornecedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Fornecedor::query()->with('contatos');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('razao_social', 'like', "%{$search}%")
                    ->orWhere('nome_fantasia', 'like', "%{$search}%")
                    ->orWhere('cpf_cnpj', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo === '1');
        }

        $fornecedores = $query->orderBy('razao_social')->paginate(15);

        return view('fornecedores.index', compact('fornecedores'));
    }

    public function create()
    {
        return view('fornecedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_pessoa' => 'nullable|in:PF,PJ',
            'cpf_cnpj' => 'nullable|string|max:18',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cep' => 'nullable|string|max:9',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'complemento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'contatos' => 'nullable|array',
            'contatos.*.nome' => 'nullable|string|max:255',
            'contatos.*.cargo' => 'nullable|string|max:100',
            'contatos.*.email' => 'nullable|email|max:255',
            'contatos.*.telefone' => 'nullable|string|max:20',
            'contatos.*.principal' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $fornecedor = Fornecedor::create($request->only([
                'tipo_pessoa',
                'cpf_cnpj',
                'razao_social',
                'nome_fantasia',
                'cep',
                'logradouro',
                'numero',
                'bairro',
                'cidade',
                'estado',
                'complemento',
                'observacoes',
            ]));

            // Criar contatos
            if ($request->filled('contatos')) {
                foreach ($request->contatos as $contatoData) {
                    if (! empty($contatoData['nome'])) {
                        FornecedorContato::create([
                            'fornecedor_id' => $fornecedor->id,
                            'nome' => $contatoData['nome'],
                            'cargo' => $contatoData['cargo'] ?? null,
                            'email' => $contatoData['email'] ?? null,
                            'telefone' => $contatoData['telefone'] ?? null,
                            'principal' => $contatoData['principal'] ?? false,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('fornecedores.index')
                ->with('success', 'Fornecedor cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Erro ao cadastrar fornecedor: '.$e->getMessage()]);
        }
    }

    public function edit(Fornecedor $fornecedor)
    {
        $fornecedor->load('contatos');

        return view('fornecedores.edit', compact('fornecedor'));
    }

    public function update(Request $request, Fornecedor $fornecedor)
    {
        $request->validate([
            'tipo_pessoa' => 'nullable|in:PF,PJ',
            'cpf_cnpj' => 'nullable|string|max:18',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cep' => 'nullable|string|max:9',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'complemento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'ativo' => 'required|boolean',
            'nome_contato' => 'nullable|string|max:255',
            'cargo_contato' => 'nullable|string|max:100',
            'emails' => 'nullable|array',
            'emails.*' => 'nullable|email|max:255',
            'telefones' => 'nullable|array',
            'telefones.*' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $fornecedor->update($request->only([
                'tipo_pessoa',
                'cpf_cnpj',
                'razao_social',
                'nome_fantasia',
                'cep',
                'logradouro',
                'numero',
                'bairro',
                'cidade',
                'estado',
                'complemento',
                'observacoes',
                'ativo',
            ]));

            $fornecedor->contatos()->delete();

            $nomeContato = $request->nome_contato;
            $cargoContato = $request->cargo_contato;
            $emails = $request->emails ?? [];
            $telefones = $request->telefones ?? [];
            $emailPrincipal = $request->input('email_principal', 0);
            $telefonePrincipal = $request->input('telefone_principal', 0);

            $contatosCount = max(count($emails), count($telefones), ($nomeContato ? 1 : 0));

            if ($contatosCount > 0) {
                for ($i = 0; $i < $contatosCount; $i++) {
                    $isPrincipal = ($i == $emailPrincipal) || ($i == $telefonePrincipal);

                    FornecedorContato::create([
                        'fornecedor_id' => $fornecedor->id,
                        'nome' => $nomeContato && $i == 0 ? $nomeContato : ($emails[$i] ?? null),
                        'cargo' => $cargoContato && $i == 0 ? $cargoContato : null,
                        'email' => $emails[$i] ?? null,
                        'telefone' => $telefones[$i] ?? null,
                        'principal' => $isPrincipal,
                    ]);
                }
            } elseif ($nomeContato) {
                FornecedorContato::create([
                    'fornecedor_id' => $fornecedor->id,
                    'nome' => $nomeContato,
                    'cargo' => $cargoContato,
                    'principal' => true,
                ]);
            }

            DB::commit();

            return redirect()->route('fornecedores.index')
                ->with('success', 'Fornecedor atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Erro ao atualizar fornecedor: '.$e->getMessage()]);
        }
    }

    public function destroy(Fornecedor $fornecedor)
    {
        try {
            $fornecedor->delete();

            return back()->with('success', 'Fornecedor excluído com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao excluir fornecedor: '.$e->getMessage()]);
        }
    }

    // API para buscar fornecedor por CNPJ/CPF
    public function buscarPorCnpj(Request $request)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);

        $fornecedor = Fornecedor::where('cpf_cnpj', $cnpj)->first();

        if ($fornecedor) {
            return response()->json([
                'exists' => true,
                'fornecedor' => $fornecedor,
            ]);
        }

        return response()->json(['exists' => false]);
    }
}
