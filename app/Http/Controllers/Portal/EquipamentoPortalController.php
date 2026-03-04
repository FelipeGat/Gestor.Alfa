<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Equipamento;
use App\Models\EquipamentoResponsavel;
use App\Models\EquipamentoSetor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

        $ativosTecnicos = $cliente->equipamentos()->with(['setor', 'responsavel'])->get();

        $totalAtivosTecnicos = $ativosTecnicos->count();

        $manutencoesEmDia = $ativosTecnicos->filter(function ($eq) {
            $status = $eq->status_manutencao;

            return $status['cor'] === 'verde' || $status['cor'] === 'gray';
        })->count();

        $manutencoesProximo = $ativosTecnicos->filter(function ($eq) {
            return $eq->status_manutencao['cor'] === 'amarelo';
        })->count();

        $manutencoesVencidas = $ativosTecnicos->filter(function ($eq) {
            return $eq->status_manutencao['cor'] === 'vermelho';
        })->count();

        $ativosOperando = $ativosTecnicos->where('status_ativo', 'operando')->count();
        $ativosEmManutencao = $ativosTecnicos->where('status_ativo', 'em_manutencao')->count();
        $ativosInativos = $ativosTecnicos->whereIn('status_ativo', ['inativo', 'descartado', 'substituido'])->count();
        $ativosSemStatus = $ativosTecnicos->filter(fn ($eq) => empty($eq->status_ativo))->count();

        $graficoManutencao = [
            'labels' => ['Em dia', 'Atenção', 'Vencidas'],
            'values' => [$manutencoesEmDia, $manutencoesProximo, $manutencoesVencidas],
        ];

        $graficoStatusAtivos = [
            'labels' => ['Operando', 'Em manutenção', 'Inativos', 'Sem status'],
            'values' => [$ativosOperando, $ativosEmManutencao, $ativosInativos, $ativosSemStatus],
        ];

        return view('portal.equipamentos.index', compact(
            'cliente',
            'totalAtivosTecnicos',
            'manutencoesEmDia',
            'manutencoesProximo',
            'manutencoesVencidas',
            'ativosOperando',
            'ativosEmManutencao',
            'ativosInativos',
            'ativosSemStatus',
            'graficoManutencao',
            'graficoStatusAtivos'
        ));
    }

    public function lista(Request $request)
    {
        $cliente = $this->getCliente();

        // Se retornou redirect, retorna ele
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $query = $cliente->equipamentos()
            ->with(['setor', 'responsavel'])
            ->orderBy('nome');

        $perPage = 10;

        $statusAtivoFiltro = $request->input('status_ativo');
        $manutencaoStatusFiltro = $request->input('manutencao_status');

        if ($statusAtivoFiltro) {
            $query->where('status_ativo', $statusAtivoFiltro);
        }

        $ativosTecnicos = null;

        if ($manutencaoStatusFiltro) {
            $ativosColecao = $query->get();

            $statusMap = [
                'em_dia' => ['verde', 'gray'],
                'atencao' => ['amarelo'],
                'vencida' => ['vermelho'],
            ];

            $statusSelecionado = $statusMap[$manutencaoStatusFiltro] ?? [];

            if (! empty($statusSelecionado)) {
                $ativosColecao = $ativosColecao->filter(function ($eq) use ($statusSelecionado) {
                    return in_array($eq->status_manutencao['cor'] ?? null, $statusSelecionado, true);
                })->values();
            }

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $ativosColecao
                ->slice(($currentPage - 1) * $perPage, $perPage)
                ->values();

            $ativosTecnicos = new LengthAwarePaginator(
                $currentItems,
                $ativosColecao->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        } else {
            $ativosTecnicos = $query->paginate($perPage)->withQueryString();
        }

        $statusAtivoLabels = [
            'operando' => 'Operando',
            'em_manutencao' => 'Em manutenção',
            'inativo' => 'Inativo',
            'aguardando_peca' => 'Aguardando peça',
            'descartado' => 'Descartado',
            'substituido' => 'Substituído',
        ];

        $manutencaoStatusLabels = [
            'em_dia' => 'Manutenção em dia',
            'atencao' => 'Manutenção em atenção',
            'vencida' => 'Manutenção vencida',
        ];

        $filtrosAtivos = array_filter([
            'status_ativo' => $statusAtivoLabels[$statusAtivoFiltro] ?? null,
            'manutencao_status' => $manutencaoStatusLabels[$manutencaoStatusFiltro] ?? null,
        ]);

        return view('portal.equipamentos.lista', compact(
            'cliente',
            'ativosTecnicos',
            'filtrosAtivos'
        ));
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
            ->paginate(10);

        return view('portal.equipamentos.setores', compact('cliente', 'setores'));
    }

    public function responsaveis(Request $request)
    {
        $cliente = $this->getCliente();

        // Se retornou redirect, retorna ele
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $busca = trim((string) $request->input('q', ''));

        $query = $cliente->equipamentoResponsaveis()
            ->withCount('equipamentos')
            ->orderBy('nome');

        if ($busca !== '') {
            $query->where(function ($subQuery) use ($busca) {
                $subQuery->where('nome', 'like', "%{$busca}%")
                    ->orWhere('cargo', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        $responsaveis = $query
            ->paginate(10)
            ->withQueryString();

        return view('portal.equipamentos.responsaveis', compact('cliente', 'responsaveis'));
    }

    public function pmoc()
    {
        $cliente = $this->getCliente();

        // Se retornou redirect, retorna ele
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $ativosTecnicos = $cliente->equipamentos()
            ->with(['setor', 'responsavel', 'manutencoes', 'limpezas'])
            ->orderBy('nome')
            ->get();

        return view('portal.equipamentos.pmoc', compact('cliente', 'ativosTecnicos'));
    }

    public function show(Equipamento $equipamento)
    {
        $cliente = $this->getCliente();

        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $equipamento->load(['setor', 'responsavel', 'fornecedor', 'manutencoes', 'limpezas', 'historicoManutencoes', 'documentos']);

        return view('portal.equipamentos.show', compact('cliente', 'equipamento'));
    }

    public function registrarManutencao(Request $request, Equipamento $equipamento)
    {
        $cliente = $this->getCliente();

        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $request->validate([
            'data' => 'required|date',
            'tipo' => 'required|in:preventiva,corretiva,correctiva,preditiva,emergencial',
            'descricao' => 'nullable|string',
            'realizado_por' => 'nullable|string|max:255',
        ]);

        $tipo = $request->tipo;
        if ($tipo === 'correctiva' || $tipo === 'emergencial') {
            $tipo = 'corretiva';
        }

        $equipamento->registrarManutencao([
            'data' => $request->data,
            'tipo' => $tipo,
            'descricao' => $request->descricao,
            'realizado_por' => $request->realizado_por,
        ]);

        return redirect()->route('portal.equipamentos.show', $equipamento->id)
            ->with('success', 'Manutenção registrada com sucesso!');
    }

    public function registrarLimpeza(Request $request, Equipamento $equipamento)
    {
        $cliente = $this->getCliente();

        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

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

        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $url = route('portal.equipamento.chamado', $equipamento->qrcode_token);

        $qrCodeUrl = 'https://quickchart.io/qr?size=300x300&text='.urlencode($url);

        return redirect($qrCodeUrl);
    }

    public function createAtivo()
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $setores = $cliente->equipamentoSetores()->orderBy('nome')->get();
        $responsaveis = $cliente->equipamentoResponsaveis()->orderBy('nome')->get();

        return view('portal.equipamentos.ativo-form', [
            'cliente' => $cliente,
            'equipamento' => new Equipamento,
            'setores' => $setores,
            'responsaveis' => $responsaveis,
            'isEdit' => false,
        ]);
    }

    public function storeAtivo(Request $request)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $data = $this->validateAtivo($request, $cliente->id);

        $equipamento = Equipamento::create($data + [
            'cliente_id' => $cliente->id,
            'ativo' => true,
            'status_ativo' => $data['status_ativo'] ?? 'operando',
            'periodicidade_manutencao_meses' => $data['periodicidade_manutencao_meses'] ?? 6,
            'periodicidade_limpeza_meses' => $data['periodicidade_limpeza_meses'] ?? 1,
        ]);

        $equipamento->update([
            'qr_code' => 'https://quickchart.io/qr?size=300x300&text='.urlencode(url('/portal/ativos/'.$equipamento->id)),
        ]);

        return redirect()->route('portal.ativos.index')
            ->with('success', 'Ativo técnico cadastrado com sucesso!');
    }

    public function editAtivo(Equipamento $equipamento)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $setores = $cliente->equipamentoSetores()->orderBy('nome')->get();
        $responsaveis = $cliente->equipamentoResponsaveis()->orderBy('nome')->get();

        return view('portal.equipamentos.ativo-form', [
            'cliente' => $cliente,
            'equipamento' => $equipamento,
            'setores' => $setores,
            'responsaveis' => $responsaveis,
            'isEdit' => true,
        ]);
    }

    public function updateAtivo(Request $request, Equipamento $equipamento)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ($equipamento->cliente_id !== $cliente->id) {
            abort(403);
        }

        $data = $this->validateAtivo($request, $cliente->id);
        $equipamento->update($data);

        return redirect()->route('portal.ativos.index')
            ->with('success', 'Ativo técnico atualizado com sucesso!');
    }

    public function storeSetor(Request $request)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
        ]);

        EquipamentoSetor::create([
            'cliente_id' => $cliente->id,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
        ]);

        return redirect()->route('portal.equipamentos.setores')
            ->with('success', 'Setor cadastrado com sucesso!');
    }

    public function editSetor(EquipamentoSetor $setor)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ((int) $setor->cliente_id !== (int) $cliente->id) {
            abort(403);
        }

        return view('portal.equipamentos.setor-edit', compact('cliente', 'setor'));
    }

    public function updateSetor(Request $request, EquipamentoSetor $setor)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ((int) $setor->cliente_id !== (int) $cliente->id) {
            abort(403);
        }

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
        ]);

        $setor->update($data);

        return redirect()->route('portal.equipamentos.setores')
            ->with('success', 'Setor atualizado com sucesso!');
    }

    public function storeResponsavel(Request $request)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        EquipamentoResponsavel::create([
            'cliente_id' => $cliente->id,
            'nome' => $data['nome'],
            'cargo' => $data['cargo'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);

        return redirect()->route('portal.equipamentos.responsaveis')
            ->with('success', 'Responsável cadastrado com sucesso!');
    }

    public function editResponsavel(EquipamentoResponsavel $responsavel)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ((int) $responsavel->cliente_id !== (int) $cliente->id) {
            abort(403);
        }

        return view('portal.equipamentos.responsavel-edit', compact('cliente', 'responsavel'));
    }

    public function updateResponsavel(Request $request, EquipamentoResponsavel $responsavel)
    {
        $cliente = $this->getCliente();
        if ($cliente instanceof \Illuminate\Http\RedirectResponse) {
            return $cliente;
        }

        if ((int) $responsavel->cliente_id !== (int) $cliente->id) {
            abort(403);
        }

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $responsavel->update($data);

        return redirect()->route('portal.equipamentos.responsaveis')
            ->with('success', 'Responsável atualizado com sucesso!');
    }

    private function validateAtivo(Request $request, int $clienteId): array
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'fabricante' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'codigo_ativo' => 'nullable|string|max:50',
            'tag_patrimonial' => 'nullable|string|max:50',
            'setor_id' => 'nullable|integer',
            'responsavel_id' => 'nullable|integer',
            'status_ativo' => 'nullable|in:operando,em_manutencao,inativo,aguardando_peca,descartado,substituido',
            'criticidade' => 'nullable|in:baixa,media,alta,critica',
            'capacidade' => 'nullable|string|max:100',
            'potencia' => 'nullable|string|max:100',
            'voltagem' => 'nullable|string|max:50',
            'periodicidade_manutencao_meses' => 'nullable|integer|min:1|max:120',
            'periodicidade_limpeza_meses' => 'nullable|integer|min:1|max:120',
            'observacoes' => 'nullable|string',
        ]);

        if (! empty($data['setor_id'])) {
            $setorValido = EquipamentoSetor::where('id', $data['setor_id'])
                ->where('cliente_id', $clienteId)
                ->exists();
            if (! $setorValido) {
                abort(403);
            }
        }

        if (! empty($data['responsavel_id'])) {
            $respValido = EquipamentoResponsavel::where('id', $data['responsavel_id'])
                ->where('cliente_id', $clienteId)
                ->exists();
            if (! $respValido) {
                abort(403);
            }
        }

        return $data;
    }
}
