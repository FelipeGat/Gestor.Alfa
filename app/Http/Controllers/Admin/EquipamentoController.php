<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtivoDocumento;
use App\Models\AtivoManutencao;
use App\Models\Cliente;
use App\Models\Equipamento;
use App\Models\EquipamentoResponsavel;
use App\Models\EquipamentoSetor;
use App\Models\Fornecedor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EquipamentoController extends Controller
{
    /**
    * Lista todos os ativos técnicos
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

        $query = Equipamento::with(['cliente', 'setor', 'responsavel']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhere('fabricante', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $ativosTecnicos = $query->orderByDesc('created_at')->paginate(15);

        // Estatísticas
        $totalAtivosTecnicos = Equipamento::count();
        $ativosTecnicosAtivos = Equipamento::where('ativo', true)->count();
        $ativosTecnicosInativos = Equipamento::where('ativo', false)->count();

        return view('admin.equipamentos.index', compact(
            'ativosTecnicos',
            'totalAtivosTecnicos',
            'ativosTecnicosAtivos',
            'ativosTecnicosInativos'
        ));
    }

    /**
     * Formulário para criar novo ativo técnico
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
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->orderBy('razao_social')->get();

        return view('admin.equipamentos.create', compact('clientes', 'fornecedores'));
    }

    /**
     * Salva novo ativo técnico
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

        $request->validate($this->rules());

        $setorId = $this->resolverSetorId($request->cliente_id, $request->setor_nome);
        $responsavelId = $this->resolverResponsavelId($request->cliente_id, $request->responsavel_nome);

        $payload = $this->buildPayload($request, $setorId, $responsavelId);

        if ($request->hasFile('foto_principal')) {
            $payload['foto_principal'] = $request->file('foto_principal')->store('ativos/fotos', 'public');
        }

        $equipamento = Equipamento::create($payload);

        $equipamento->update([
            'qr_code' => 'https://quickchart.io/qr?size=300x300&text='.urlencode(url('/portal/ativos/'.$equipamento->id)),
        ]);

        return redirect()->route('admin.equipamentos.index')
            ->with('success', 'Ativo técnico cadastrado com sucesso!');
    }

    /**
     * Exibe detalhes do ativo técnico
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

        $equipamento->load(['cliente', 'setor', 'responsavel', 'fornecedor', 'manutencoes', 'limpezas', 'historicoManutencoes', 'documentos']);

        return view('admin.equipamentos.show', compact('equipamento'));
    }

    /**
     * Formulário para editar ativo técnico
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
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->orderBy('razao_social')->get();

        $equipamento->load(['historicoManutencoes', 'documentos']);

        return view('admin.equipamentos.edit', compact('equipamento', 'clientes', 'fornecedores'));
    }

    /**
     * Atualiza ativo técnico
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

        $request->validate($this->rules($equipamento->id));

        $setorId = $this->resolverSetorId($request->cliente_id, $request->setor_nome);
        $responsavelId = $this->resolverResponsavelId($request->cliente_id, $request->responsavel_nome);

        $payload = $this->buildPayload($request, $setorId, $responsavelId);

        if ($request->hasFile('foto_principal')) {
            if ($equipamento->foto_principal && Storage::disk('public')->exists($equipamento->foto_principal)) {
                Storage::disk('public')->delete($equipamento->foto_principal);
            }

            $payload['foto_principal'] = $request->file('foto_principal')->store('ativos/fotos', 'public');
        }

        $equipamento->update($payload);

        if (! $equipamento->qr_code) {
            $equipamento->update([
                'qr_code' => 'https://quickchart.io/qr?size=300x300&text='.urlencode(url('/portal/ativos/'.$equipamento->id)),
            ]);
        }

        return redirect()->route('admin.equipamentos.index')
            ->with('success', 'Ativo técnico atualizado com sucesso!');
    }

    /**
     * Exclui ativo técnico
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
            ->with('success', 'Ativo técnico excluído com sucesso!');
    }

    /**
     * API para buscar ativos técnicos por cliente
     */
    public function apiListByCliente($clienteId)
    {
        $ativosTecnicos = Equipamento::where('cliente_id', $clienteId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return response()->json($ativosTecnicos);
    }

    /**
     * API para buscar setores por cliente
     */
    public function apiListSetores($clienteId)
    {
        $setores = EquipamentoSetor::where('cliente_id', $clienteId)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return response()->json($setores);
    }

    /**
     * API para buscar responsáveis por cliente
     */
    public function apiListResponsaveis($clienteId)
    {
        $responsaveis = EquipamentoResponsavel::where('cliente_id', $clienteId)
            ->orderBy('nome')
            ->get(['id', 'nome', 'cargo']);

        return response()->json($responsaveis);
    }

    /**
     * Gera QR Code do ativo técnico
     */
    public function gerarQrCode(Equipamento $equipamento)
    {
        $url = route('portal.ativos.show', $equipamento);
        $qrCodeUrl = 'https://quickchart.io/qr?size=300x300&text='.urlencode($url);

        return redirect($qrCodeUrl);
    }

    public function adicionarManutencao(Request $request, Equipamento $equipamento)
    {
        $request->validate([
            'data_manutencao' => 'required|date',
            'tipo' => 'required|in:preventiva,corretiva,limpeza',
            'descricao' => 'nullable|string',
            'tecnico_responsavel' => 'nullable|string|max:150',
            'custo' => 'nullable|numeric|min:0',
            'pecas_trocadas' => 'nullable|string',
            'tempo_parado_horas' => 'nullable|integer|min:0',
        ]);

        AtivoManutencao::create([
            'ativo_id' => $equipamento->id,
            'data_manutencao' => $request->data_manutencao,
            'tipo' => $request->tipo,
            'descricao' => $request->descricao,
            'tecnico_responsavel' => $request->tecnico_responsavel,
            'custo' => $request->custo,
            'pecas_trocadas' => $request->pecas_trocadas,
            'tempo_parado_horas' => $request->tempo_parado_horas,
        ]);

        if ($request->tipo === 'limpeza') {
            $equipamento->update(['ultima_limpeza' => $request->data_manutencao]);
        } else {
            $equipamento->update(['ultima_manutencao' => $request->data_manutencao]);
        }

        return redirect()->route('admin.equipamentos.edit', $equipamento)
            ->with('success', 'Histórico de manutenção adicionado com sucesso!');
    }

    public function uploadDocumento(Request $request, Equipamento $equipamento)
    {
        $request->validate([
            'nome_documento' => 'required|string|max:255',
            'tipo_documento' => 'required|in:manual,nota_fiscal,garantia,pmoc,foto',
            'arquivo' => 'required|file|max:10240',
        ]);

        $path = $request->file('arquivo')->store('ativos/documentos', 'public');

        AtivoDocumento::create([
            'ativo_id' => $equipamento->id,
            'nome_documento' => $request->nome_documento,
            'tipo_documento' => $request->tipo_documento,
            'arquivo' => $path,
        ]);

        return redirect()->route('admin.equipamentos.edit', $equipamento)
            ->with('success', 'Documento anexado com sucesso!');
    }

    public function downloadDocumento(Equipamento $equipamento, AtivoDocumento $documento)
    {
        abort_unless($documento->ativo_id === $equipamento->id, 404);
        abort_unless(Storage::disk('public')->exists($documento->arquivo), 404);

        $arquivoFisico = Storage::disk('public')->path($documento->arquivo);

        return response()->download($arquivoFisico, $documento->nome_documento);
    }

    public function excluirDocumento(Equipamento $equipamento, AtivoDocumento $documento)
    {
        abort_unless($documento->ativo_id === $equipamento->id, 404);

        if (Storage::disk('public')->exists($documento->arquivo)) {
            Storage::disk('public')->delete($documento->arquivo);
        }

        $documento->delete();

        return redirect()->route('admin.equipamentos.edit', $equipamento)
            ->with('success', 'Documento excluído com sucesso!');
    }

    private function rules(?int $equipamentoId = null): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'nome' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'fabricante' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'codigo_ativo' => 'nullable|string|max:50',
            'tag_patrimonial' => 'nullable|string|max:50',
            'setor_nome' => 'nullable|string|max:255',
            'responsavel_nome' => 'nullable|string|max:255',
            'unidade' => 'nullable|string|max:150',
            'andar' => 'nullable|string|max:50',
            'sala' => 'nullable|string|max:50',
            'localizacao_detalhada' => 'nullable|string',
            'capacidade' => 'nullable|string|max:100',
            'potencia' => 'nullable|string|max:100',
            'voltagem' => 'nullable|string|max:50',
            'vida_util_anos' => 'nullable|integer|min:1|max:100',
            'data_aquisicao' => 'nullable|date',
            'data_instalacao' => 'nullable|date',
            'valor_aquisicao' => 'nullable|numeric|min:0',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'possui_garantia' => 'nullable|boolean',
            'garantia_inicio' => 'nullable|date',
            'garantia_fim' => 'nullable|date',
            'status_ativo' => 'nullable|in:operando,em_manutencao,inativo,aguardando_peca,descartado,substituido',
            'criticidade' => 'nullable|in:baixa,media,alta,critica',
            'ultima_manutencao' => 'nullable|date',
            'ultima_limpeza' => 'nullable|date',
            'periodicidade_manutencao_meses' => 'nullable|integer|min:1|max:120',
            'periodicidade_limpeza_meses' => 'nullable|integer|min:1|max:120',
            'observacoes' => 'nullable|string',
            'ativo' => 'nullable|boolean',
            'foto_principal' => 'nullable|image|max:5120',
        ];
    }

    private function resolverSetorId(int $clienteId, ?string $setorNome): ?int
    {
        if (! $setorNome) {
            return null;
        }

        $setor = EquipamentoSetor::firstOrCreate(
            ['cliente_id' => $clienteId, 'nome' => $setorNome],
            ['cliente_id' => $clienteId]
        );

        return $setor->id;
    }

    private function resolverResponsavelId(int $clienteId, ?string $responsavelNome): ?int
    {
        if (! $responsavelNome) {
            return null;
        }

        $responsavel = EquipamentoResponsavel::firstOrCreate(
            ['cliente_id' => $clienteId, 'nome' => $responsavelNome],
            ['cliente_id' => $clienteId]
        );

        return $responsavel->id;
    }

    private function buildPayload(Request $request, ?int $setorId, ?int $responsavelId): array
    {
        return [
            'cliente_id' => $request->cliente_id,
            'nome' => $request->nome,
            'modelo' => $request->modelo,
            'fabricante' => $request->fabricante,
            'numero_serie' => $request->numero_serie,
            'codigo_ativo' => $request->codigo_ativo,
            'tag_patrimonial' => $request->tag_patrimonial,
            'setor_id' => $setorId,
            'responsavel_id' => $responsavelId,
            'unidade' => $request->unidade,
            'andar' => $request->andar,
            'sala' => $request->sala,
            'localizacao_detalhada' => $request->localizacao_detalhada,
            'capacidade' => $request->capacidade,
            'potencia' => $request->potencia,
            'voltagem' => $request->voltagem,
            'vida_util_anos' => $request->vida_util_anos,
            'data_aquisicao' => $request->data_aquisicao,
            'data_instalacao' => $request->data_instalacao,
            'valor_aquisicao' => $request->valor_aquisicao,
            'fornecedor_id' => $request->fornecedor_id,
            'possui_garantia' => $request->has('possui_garantia'),
            'garantia_inicio' => $request->garantia_inicio,
            'garantia_fim' => $request->garantia_fim,
            'status_ativo' => $request->status_ativo,
            'criticidade' => $request->criticidade,
            'ultima_manutencao' => $request->ultima_manutencao,
            'ultima_limpeza' => $request->ultima_limpeza,
            'periodicidade_manutencao_meses' => $request->periodicidade_manutencao_meses ?? 6,
            'periodicidade_limpeza_meses' => $request->periodicidade_limpeza_meses ?? 1,
            'observacoes' => $request->observacoes,
            'ativo' => $request->has('ativo'),
        ];
    }
}
