<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Equipamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipamentoPortalController extends Controller
{
    private function getCliente()
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            return redirect()->route('login');
        }

        // Se não tiver cliente ativo na sessão, tenta selecionar automaticamente
        if (! session()->has('cliente_id_ativo')) {
            // Se tiver mais de uma unidade, força seleção
            $clientesCount = $user->clientes()->count();

            if ($clientesCount > 1) {
                return redirect()->route('portal.unidade');
            }

            // Se tiver apenas uma, define automaticamente
            $unicoCliente = $user->clientes()->first();
            if ($unicoCliente) {
                session(['cliente_id_ativo' => $unicoCliente->id]);
            } else {
                // Se não tem clientes ativos, redireciona para selecionar
                return redirect()->route('portal.unidade');
            }
        }

        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }

        $cliente = $user->clientes()
            ->where('clientes.id', $clienteId)
            ->first();

        // Se não encontrou cliente ativo, força seleção de unidade
        if (! $cliente) {
            session()->forget('cliente_id_ativo');

            return redirect()->route('portal.unidade');
        }

        return $cliente;
    }

    public function index()
    {
        $cliente = $this->getCliente();

        // Se retornou redirect, retorna ele
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $equipamentos = $cliente->equipamentos()->with(['setor', 'responsavel'])->get();

        $totalEquipamentos = $equipamentos->count();

        $manutencoesEmDia = $equipamentos->filter(function ($eq) {
            $status = $eq->status_manutencao;

            return $status['cor'] === 'verde' || $status['cor'] === 'gray';
        })->count();

        $manutencoesProximo = $equipamentos->filter(function ($eq) {
            return $eq->status_manutencao['cor'] === 'amarelo';
        })->count();

        $manutencoesVencidas = $equipamentos->filter(function ($eq) {
            return $eq->status_manutencao['cor'] === 'vermelho';
        })->count();

        return view('portal.equipamentos.index', compact(
            'cliente',
            'totalEquipamentos',
            'manutencoesEmDia',
            'manutencoesProximo',
            'manutencoesVencidas'
        ));
    }

    public function lista()
    {
        $cliente = $this->getCliente();

        // Se retornou redirect, retorna ele
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $equipamentos = $cliente->equipamentos()
            ->with(['setor', 'responsavel'])
            ->orderBy('nome')
            ->get();

        return view('portal.equipamentos.lista', compact('cliente', 'equipamentos'));
    }

    public function setores()
    {
        $cliente = $this->getCliente();

        // Se retornou redirect, retorna ele
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $setores = $cliente->equipamentoSetores()
            ->withCount('equipamentos')
            ->orderBy('nome')
            ->get();

        return view('portal.equipamentos.setores', compact('cliente', 'setores'));
    }

    public function responsaveis()
    {
        $cliente = $this->getCliente();

        // Se retornou redirect, retorna ele
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $responsaveis = $cliente->equipamentoResponsaveis()
            ->withCount('equipamentos')
            ->orderBy('nome')
            ->get();

        return view('portal.equipamentos.responsaveis', compact('cliente', 'responsaveis'));
    }

    public function pmoc()
    {
        $cliente = $this->getCliente();

        // Se retornou redirect, retorna ele
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $equipamentos = $cliente->equipamentos()
            ->with(['setor', 'responsavel', 'manutencoes', 'limpezas'])
            ->orderBy('nome')
            ->get();

        return view('portal.equipamentos.pmoc', compact('cliente', 'equipamentos'));
    }

    public function show(Equipamento $equipamento)
    {
        $cliente = $this->getCliente();

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $equipamento->load(['setor', 'responsavel', 'manutencoes', 'limpezas']);

        return view('portal.equipamentos.show', compact('cliente', 'equipamento'));
    }

    public function registrarManutencao(Request $request, Equipamento $equipamento)
    {
        $cliente = $this->getCliente();

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $request->validate([
            'data' => 'required|date',
            'tipo' => 'required|in:preventiva,correctiva,emergencial',
            'descricao' => 'nullable|string',
            'realizado_por' => 'nullable|string|max:255',
        ]);

        $equipamento->registrarManutencao([
            'data' => $request->data,
            'tipo' => $request->tipo,
            'descricao' => $request->descricao,
            'realizado_por' => $request->realizado_por,
        ]);

        return redirect()->route('portal.equipamentos.show', $equipamento->id)
            ->with('success', 'Manutenção registrada com sucesso!');
    }

    public function registrarLimpeza(Request $request, Equipamento $equipamento)
    {
        $cliente = $this->getCliente();

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $request->validate([
            'data' => 'required|date',
            'descricao' => 'nullable|string',
            'realizado_por' => 'nullable|string|max:255',
        ]);

        $equipamento->registrarLimpeza([
            'data' => $request->data,
            'descricao' => $request->descricao,
            'realizado_por' => $request->realizado_por,
        ]);

        return redirect()->route('portal.equipamentos.show', $equipamento->id)
            ->with('success', 'Limpeza registrada com sucesso!');
    }

    public function qrcode(Equipamento $equipamento)
    {
        $cliente = $this->getCliente();

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $url = route('portal.equipamento.chamado', $equipamento->qrcode_token);

        $qrCodeUrl = 'https://quickchart.io/qr?size=300x300&text='.urlencode($url);

        return redirect($qrCodeUrl);
    }
}
