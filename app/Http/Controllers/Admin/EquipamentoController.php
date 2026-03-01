<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Equipamento;
use App\Models\EquipamentoSetor;
use App\Models\EquipamentoResponsavel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipamentoController extends Controller
{
    /**
     * Lista todos os equipamentos
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

        // Equipamentos
        $equipamentosQuery = Equipamento::with(['cliente', 'setor', 'responsavel']);

        if ($request->filled('search_equipamento')) {
            $search = $request->search_equipamento;
            $equipamentosQuery->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhere('fabricante', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cliente_id')) {
            $equipamentosQuery->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('status')) {
            $equipamentosQuery->where('ativo', $request->status === 'ativo');
        }

        $equipamentos = $equipamentosQuery->orderByDesc('created_at')->get();

        // Setores
        $setoresQuery = EquipamentoSetor::with(['cliente', 'equipamentos']);

        if ($request->filled('search_setor')) {
            $search = $request->search_setor;
            $setoresQuery->where('nome', 'like', "%{$search}%");
        }

        if ($request->filled('cliente_id_setor')) {
            $setoresQuery->where('cliente_id', $request->cliente_id_setor);
        }

        $setores = $setoresQuery->orderBy('nome')->get();

        // Responsáveis
        $responsaveisQuery = EquipamentoResponsavel::with(['cliente', 'equipamentos']);

        if ($request->filled('search_responsavel')) {
            $search = $request->search_responsavel;
            $responsaveisQuery->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('cargo', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cliente_id_responsavel')) {
            $responsaveisQuery->where('cliente_id', $request->cliente_id_responsavel);
        }

        $responsaveis = $responsaveisQuery->orderBy('nome')->get();

        // Estatísticas
        $totalEquipamentos = Equipamento::count();
        $equipamentosAtivos = Equipamento::where('ativo', true)->count();
        $equipamentosInativos = Equipamento::where('ativo', false)->count();

        return view('admin.equipamentos.index', compact(
            'equipamentos',
            'setores',
            'responsaveis',
            'totalEquipamentos',
            'equipamentosAtivos',
            'equipamentosInativos'
        ));
    }

    /**
     * Formulário para criar novo equipamento
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
        $setores = EquipamentoSetor::orderBy('nome')->get();
        $responsaveis = EquipamentoResponsavel::orderBy('nome')->get();

        return view('admin.equipamentos.create', compact(
            'clientes',
            'setores',
            'responsaveis'
        ));
    }

    /**
     * Salva novo equipamento
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
            'modelo' => 'nullable|string|max:255',
            'fabricante' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'setor_id' => 'nullable|exists:equipamento_setores,id',
            'responsavel_id' => 'nullable|exists:equipamento_responsaveis,id',
            'ultima_manutencao' => 'nullable|date',
            'ultima_limpeza' => 'nullable|date',
            'periodicidade_manutencao_meses' => 'nullable|integer|min:1|max:120',
            'periodicidade_limpeza_meses' => 'nullable|integer|min:1|max:120',
            'observacoes' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        Equipamento::create([
            'cliente_id' => $request->cliente_id,
            'nome' => $request->nome,
            'modelo' => $request->modelo,
            'fabricante' => $request->fabricante,
            'numero_serie' => $request->numero_serie,
            'setor_id' => $request->setor_id,
            'responsavel_id' => $request->responsavel_id,
            'ultima_manutencao' => $request->ultima_manutencao,
            'ultima_limpeza' => $request->ultima_limpeza,
            'periodicidade_manutencao_meses' => $request->periodicidade_manutencao_meses ?? 6,
            'periodicidade_limpeza_meses' => $request->periodicidade_limpeza_meses ?? 1,
            'observacoes' => $request->observacoes,
            'ativo' => $request->has('ativo') ? true : false,
        ]);

        return redirect()->route('admin.equipamentos.index')
            ->with('success', 'Equipamento cadastrado com sucesso!');
    }

    /**
     * Exibe detalhes do equipamento
     */
    public function show(Equipamento $equipamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'ler'),
            403,
            'Acesso não autorizado'
        );

        $equipamento->load(['cliente', 'setor', 'responsavel', 'manutencoes', 'limpezas']);

        return view('admin.equipamentos.show', compact('equipamento'));
    }

    /**
     * Formulário para editar equipamento
     */
    public function edit(Equipamento $equipamento)
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
        $setores = EquipamentoSetor::orderBy('nome')->get();
        $responsaveis = EquipamentoResponsavel::orderBy('nome')->get();

        return view('admin.equipamentos.edit', compact(
            'equipamento',
            'clientes',
            'setores',
            'responsaveis'
        ));
    }

    /**
     * Atualiza equipamento
     */
    public function update(Request $request, Equipamento $equipamento)
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
            'modelo' => 'nullable|string|max:255',
            'fabricante' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'setor_id' => 'nullable|exists:equipamento_setores,id',
            'responsavel_id' => 'nullable|exists:equipamento_responsaveis,id',
            'ultima_manutencao' => 'nullable|date',
            'ultima_limpeza' => 'nullable|date',
            'periodicidade_manutencao_meses' => 'nullable|integer|min:1|max:120',
            'periodicidade_limpeza_meses' => 'nullable|integer|min:1|max:120',
            'observacoes' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        $equipamento->update([
            'cliente_id' => $request->cliente_id,
            'nome' => $request->nome,
            'modelo' => $request->modelo,
            'fabricante' => $request->fabricante,
            'numero_serie' => $request->numero_serie,
            'setor_id' => $request->setor_id,
            'responsavel_id' => $request->responsavel_id,
            'ultima_manutencao' => $request->ultima_manutencao,
            'ultima_limpeza' => $request->ultima_limpeza,
            'periodicidade_manutencao_meses' => $request->periodicidade_manutencao_meses ?? 6,
            'periodicidade_limpeza_meses' => $request->periodicidade_limpeza_meses ?? 1,
            'observacoes' => $request->observacoes,
            'ativo' => $request->has('ativo') ? true : false,
        ]);

        return redirect()->route('admin.equipamentos.index')
            ->with('success', 'Equipamento atualizado com sucesso!');
    }

    /**
     * Exclui equipamento
     */
    public function destroy(Equipamento $equipamento)
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if(
            ! $user->isAdminPanel() &&
                ! $user->canPermissao('equipamentos', 'excluir'),
            403,
            'Acesso não autorizado'
        );

        $equipamento->delete();

        return redirect()->route('admin.equipamentos.index')
            ->with('success', 'Equipamento excluído com sucesso!');
    }

    /**
     * API para buscar equipamentos por cliente
     */
    public function apiListByCliente($clienteId)
    {
        $equipamentos = Equipamento::where('cliente_id', $clienteId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return response()->json($equipamentos);
    }

    /**
     * Gera QR Code do equipamento
     */
    public function gerarQrCode(Equipamento $equipamento)
    {
        $url = route('portal.equipamento.chamado', $equipamento->qrcode_token);
        $qrCodeUrl = 'https://quickchart.io/qr?size=300x300&text='.urlencode($url);

        return redirect($qrCodeUrl);
    }
}
