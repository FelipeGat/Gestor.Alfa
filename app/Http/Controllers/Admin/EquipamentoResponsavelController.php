<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EquipamentoResponsavel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipamentoResponsavelController extends Controller
{
    /**
     * Lista todos os responsáveis por equipamentos
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

        $query = EquipamentoResponsavel::with(['cliente', 'equipamentos']);

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('cargo', 'like', "%{$search}%");
            });
        }

        $responsaveis = $query->orderBy('nome')->paginate(15);

        $totalResponsaveis = EquipamentoResponsavel::count();

        return view('admin.equipamentos.responsaveis.index', compact('responsaveis', 'totalResponsaveis'));
    }

    /**
     * Formulário para criar novo responsável
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

        return view('admin.equipamentos.responsaveis.create', compact('clientes'));
    }

    /**
     * Salva novo responsável
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
            'cargo' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        // Verificar se já existe responsável com mesmo nome para este cliente
        $exists = EquipamentoResponsavel::where('cliente_id', $request->cliente_id)
            ->where('nome', $request->nome)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nome' => 'Já existe um responsável com este nome para este cliente.'])
                ->withInput();
        }

        EquipamentoResponsavel::create([
            'cliente_id' => $request->cliente_id,
            'nome' => $request->nome,
            'cargo' => $request->cargo,
            'telefone' => $request->telefone,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.equipamentos.responsaveis.index')
            ->with('success', 'Responsável cadastrado com sucesso!');
    }

    /**
     * Exibe detalhes do responsável
     */
    public function show(EquipamentoResponsavel $responsavel)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'ler'),
            403,
            'Acesso não autorizado'
        );

        $responsavel->load(['cliente', 'equipamentos']);

        return view('admin.equipamentos.responsaveis.show', compact('responsavel'));
    }

    /**
     * Formulário para editar responsável
     */
    public function edit(EquipamentoResponsavel $responsavel)
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

        return view('admin.equipamentos.responsaveis.edit', compact('responsavel', 'clientes'));
    }

    /**
     * Atualiza responsável
     */
    public function update(Request $request, EquipamentoResponsavel $responsavel)
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
            'cargo' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        // Verificar se já existe responsável com mesmo nome para este cliente (exceto o atual)
        $exists = EquipamentoResponsavel::where('cliente_id', $request->cliente_id)
            ->where('nome', $request->nome)
            ->where('id', '!=', $responsavel->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nome' => 'Já existe um responsável com este nome para este cliente.'])
                ->withInput();
        }

        $responsavel->update([
            'cliente_id' => $request->cliente_id,
            'nome' => $request->nome,
            'cargo' => $request->cargo,
            'telefone' => $request->telefone,
            'email' => $request->email,
        ]);

        return redirect()->route('admin.equipamentos.responsaveis.index')
            ->with('success', 'Responsável atualizado com sucesso!');
    }

    /**
     * Exclui responsável
     */
    public function destroy(EquipamentoResponsavel $responsavel)
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
        if ($responsavel->equipamentos()->count() > 0) {
            return back()->withErrors(['delete' => 'Não é possível excluir o responsável pois existem equipamentos vinculados a ele.']);
        }

        $responsavel->delete();

        return redirect()->route('admin.equipamentos.responsaveis.index')
            ->with('success', 'Responsável excluído com sucesso!');
    }
}
