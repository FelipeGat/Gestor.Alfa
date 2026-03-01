<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EquipamentoSetor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipamentoSetorController extends Controller
{
    /**
     * Lista todos os setores de equipamentos
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'ler'),
            403,
            'Acesso não autorizado'
        );

        $query = EquipamentoSetor::with(['cliente', 'equipamentos']);

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%");
        }

        $setores = $query->orderBy('nome')->paginate(15);

        $totalSetores = EquipamentoSetor::count();

        return view('admin.equipamentos.setores.index', compact('setores', 'totalSetores'));
    }

    /**
     * Formulário para criar novo setor
     */
    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        $clientes = Cliente::where('ativo', true)->orderBy('nome')->get();

        return view('admin.equipamentos.setores.create', compact('clientes'));
    }

    /**
     * Salva novo setor
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'incluir'),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        // Verificar se já existe setor com mesmo nome para este cliente
        $exists = EquipamentoSetor::where('cliente_id', $request->cliente_id)
            ->where('nome', $request->nome)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nome' => 'Já existe um setor com este nome para este cliente.'])
                ->withInput();
        }

        EquipamentoSetor::create([
            'cliente_id' => $request->cliente_id,
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);

        return redirect()->route('admin.equipamentos.setores.index')
            ->with('success', 'Setor cadastrado com sucesso!');
    }

    /**
     * Exibe detalhes do setor
     */
    public function show(EquipamentoSetor $setor)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'ler'),
            403,
            'Acesso não autorizado'
        );

        $setor->load(['cliente', 'equipamentos']);

        return view('admin.equipamentos.setores.show', compact('setor'));
    }

    /**
     * Formulário para editar setor
     */
    public function edit(EquipamentoSetor $setor)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'alterar'),
            403,
            'Acesso não autorizado'
        );

        $clientes = Cliente::where('ativo', true)->orderBy('nome')->get();

        return view('admin.equipamentos.setores.edit', compact('setor', 'clientes'));
    }

    /**
     * Atualiza setor
     */
    public function update(Request $request, EquipamentoSetor $setor)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'alterar'),
            403,
            'Acesso não autorizado'
        );

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        // Verificar se já existe setor com mesmo nome para este cliente (exceto o atual)
        $exists = EquipamentoSetor::where('cliente_id', $request->cliente_id)
            ->where('nome', $request->nome)
            ->where('id', '!=', $setor->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nome' => 'Já existe um setor com este nome para este cliente.'])
                ->withInput();
        }

        $setor->update([
            'cliente_id' => $request->cliente_id,
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);

        return redirect()->route('admin.equipamentos.setores.index')
            ->with('success', 'Setor atualizado com sucesso!');
    }

    /**
     * Exclui setor
     */
    public function destroy(EquipamentoSetor $setor)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'excluir'),
            403,
            'Acesso não autorizado'
        );

        // Verificar se há equipamentos vinculados
        if ($setor->equipamentos()->count() > 0) {
            return back()->withErrors(['delete' => 'Não é possível excluir o setor pois existem equipamentos vinculados a ele.']);
        }

        $setor->delete();

        return redirect()->route('admin.equipamentos.setores.index')
            ->with('success', 'Setor excluído com sucesso!');
    }
}
