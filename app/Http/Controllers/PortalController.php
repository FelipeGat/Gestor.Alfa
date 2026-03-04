<?php

namespace App\Http\Controllers;

use App\Models\Assunto;
use App\Models\Atendimento;
use App\Models\Equipamento;
use App\Models\Boleto;
use App\Models\Cobranca;
use App\Models\Orcamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PortalController extends Controller
{
    /**
     * Lista de Atendimentos do Cliente
     */
    public function atendimentos(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }
        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }

        $cliente = $user->clientes()->where('clientes.id', $clienteId)->firstOrFail();
        $query = $cliente->atendimentos()
            ->with(['assunto', 'funcionario', 'andamentos.fotos'])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $termo = trim((string) $request->input('q'));

            $query->where(function ($subQuery) use ($termo) {
                $subQuery->where('descricao', 'like', "%{$termo}%")
                    ->orWhere('status_atual', 'like', "%{$termo}%")
                    ->orWhere('numero_atendimento', 'like', "%{$termo}%")
                    ->orWhereHas('assunto', function ($assuntoQuery) use ($termo) {
                        $assuntoQuery->where('nome', 'like', "%{$termo}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status_atual', (string) $request->input('status'));
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', (string) $request->input('prioridade'));
        }

        $atendimentos = $query->paginate(10)->withQueryString();

        return view('portal.atendimentos', compact('cliente', 'atendimentos'));
    }

    /**
     * Formulário para Novo Chamado
     */
    public function novoChamado()
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }
        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }
        $cliente = $user->clientes()->where('clientes.id', $clienteId)->firstOrFail();

        $equipamentoSelecionado = null;
        if (old('equipamento_id')) {
            $equipamentoSelecionado = Equipamento::query()
                ->with(['setor', 'responsavel'])
                ->where('cliente_id', $clienteId)
                ->find(old('equipamento_id'));
        }

        return view('portal.chamado.novo', compact('cliente', 'equipamentoSelecionado'));
    }

    /**
     * Salvar Novo Chamado
     */
    public function storeChamado(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }
        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }

        $cliente = $user->clientes()->where('clientes.id', $clienteId)->firstOrFail();

        $request->validate([
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string|max:3000',
            'prioridade' => 'required|in:baixa,media,alta,urgente',
            'melhor_horario_contato' => 'nullable|string|max:100',
            'equipamento_id' => [
                'nullable',
                'integer',
                'exists:equipamentos,id',
                function ($attribute, $value, $fail) use ($clienteId) {
                    if (! $value) {
                        return;
                    }

                    $ehDoCliente = Equipamento::query()
                        ->where('id', $value)
                        ->where('cliente_id', $clienteId)
                        ->exists();

                    if (! $ehDoCliente) {
                        $fail('O equipamento informado não pertence ao cliente ativo.');
                    }
                },
            ],
            'foto_problema' => 'nullable|image|max:5120',
        ]);

        $assuntoNome = trim((string) $request->input('assunto'));
        $empresaAssuntoId = (int) (optional($cliente->empresas()->first())->id ?? optional($user->empresas()->first())->id);
        $assuntoId = $this->resolverAssuntoIdPortal($assuntoNome, $empresaAssuntoId);

        if (! $assuntoId) {
            return back()
                ->withErrors(['assunto' => 'Não foi possível vincular o assunto. Entre em contato com o suporte.'])
                ->withInput();
        }

        $descricaoCompleta = "Assunto: {$assuntoNome}\n\n".trim((string) $request->input('descricao'));
        if ($request->filled('melhor_horario_contato')) {
            $descricaoCompleta .= "\n\nMelhor horário para contato: ".trim((string) $request->input('melhor_horario_contato'));
        }
        $prioridadeNormalizada = $this->normalizarPrioridadePortal((string) $request->input('prioridade'));

        // Gerar número de atendimento
        $ultimoRegistro = Atendimento::orderBy('numero_atendimento', 'desc')->first();
        $numeroAtendimento = $ultimoRegistro ? $ultimoRegistro->numero_atendimento + 1 : 1;

        DB::transaction(function () use ($request, $numeroAtendimento, $clienteId, $user, $descricaoCompleta, $assuntoId, $prioridadeNormalizada) {
            $atendimento = Atendimento::create([
                'numero_atendimento' => $numeroAtendimento,
                'cliente_id' => $clienteId,
                'equipamento_id' => $request->input('equipamento_id'),
                'nome_solicitante' => $user->name,
                'telefone_solicitante' => $user->telefone ?? null,
                'email_solicitante' => $user->email,
                'assunto_id' => $assuntoId,
                'descricao' => $descricaoCompleta,
                'prioridade' => $prioridadeNormalizada,
                'empresa_id' => null,
                'funcionario_id' => null,
                'status_atual' => 'aberto',
                'data_atendimento' => now(),
            ]);

            if ($request->hasFile('foto_problema')) {
                $andamento = $atendimento->andamentos()->create([
                    'user_id' => Auth::id(),
                    'descricao' => 'Foto enviada na abertura do chamado pelo cliente.',
                ]);

                $path = $request->file('foto_problema')->store('atendimentos/fotos', 'public');
                $relativePath = ltrim(str_replace(['public/', 'storage/'], '', $path), '/');

                $andamento->fotos()->create([
                    'arquivo' => $relativePath,
                ]);
            }
        });

        return redirect()->route('portal.atendimentos')
            ->with('success', 'Chamado aberto com sucesso!')
            ->with('atendimento_criado_numero', $numeroAtendimento);
    }

    /**
     * Formulário de edição de chamado do cliente
     */
    public function editChamado(Atendimento $atendimento)
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }

        if ((int) $atendimento->cliente_id !== (int) $clienteId) {
            abort(403);
        }

        $atendimento->load(['andamentos.user', 'andamentos.fotos']);

        $cliente = $user->clientes()->where('clientes.id', $clienteId)->firstOrFail();

        $dadosDescricao = $this->extrairDadosDescricaoChamadoPortal(
            (string) $atendimento->descricao,
            optional($atendimento->assunto)->nome
        );

        return view('portal.chamado.editar', [
            'cliente' => $cliente,
            'atendimento' => $atendimento,
            'assuntoAtual' => $dadosDescricao['assunto'],
            'descricaoAtual' => $dadosDescricao['descricao'],
            'melhorHorarioAtual' => $dadosDescricao['melhor_horario_contato'],
        ]);
    }

    /**
     * Atualiza chamado do cliente
     */
    public function updateChamado(Request $request, Atendimento $atendimento)
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }

        if ((int) $atendimento->cliente_id !== (int) $clienteId) {
            abort(403);
        }

        $cliente = $user->clientes()->where('clientes.id', $clienteId)->firstOrFail();

        $request->validate([
            'assunto' => 'required|string|max:255',
            'descricao' => 'required|string|max:3000',
            'prioridade' => 'required|in:baixa,media,alta,urgente',
            'melhor_horario_contato' => 'nullable|string|max:100',
            'novo_andamento' => 'nullable|string|max:3000',
            'fotos_andamento' => 'nullable|array|max:5',
            'fotos_andamento.*' => 'nullable|image|max:5120',
        ]);

        $assuntoNome = trim((string) $request->input('assunto'));
        $empresaAssuntoId = (int) (optional($cliente->empresas()->first())->id ?? optional($user->empresas()->first())->id);
        $assuntoId = $this->resolverAssuntoIdPortal($assuntoNome, $empresaAssuntoId);

        if (! $assuntoId) {
            return back()
                ->withErrors(['assunto' => 'Não foi possível vincular o assunto. Entre em contato com o suporte.'])
                ->withInput();
        }

        $descricaoCompleta = "Assunto: {$assuntoNome}\n\n".trim((string) $request->input('descricao'));
        if ($request->filled('melhor_horario_contato')) {
            $descricaoCompleta .= "\n\nMelhor horário para contato: ".trim((string) $request->input('melhor_horario_contato'));
        }

        DB::transaction(function () use ($request, $atendimento, $assuntoId, $descricaoCompleta) {
            $atendimento->update([
                'assunto_id' => $assuntoId,
                'descricao' => $descricaoCompleta,
                'prioridade' => $this->normalizarPrioridadePortal((string) $request->input('prioridade')),
            ]);

            $temNovoAndamento = $request->filled('novo_andamento') || $request->hasFile('fotos_andamento');
            if (! $temNovoAndamento) {
                return;
            }

            $descricaoAndamento = trim((string) $request->input('novo_andamento'));
            if ($descricaoAndamento === '') {
                $descricaoAndamento = 'Cliente anexou novas fotos do problema.';
            }

            $andamento = $atendimento->andamentos()->create([
                'user_id' => Auth::id(),
                'descricao' => $descricaoAndamento,
            ]);

            if ($request->hasFile('fotos_andamento')) {
                foreach ((array) $request->file('fotos_andamento') as $foto) {
                    if (! $foto) {
                        continue;
                    }

                    $path = $foto->store('atendimentos/fotos', 'public');
                    $relativePath = ltrim(str_replace(['public/', 'storage/'], '', $path), '/');

                    $andamento->fotos()->create([
                        'arquivo' => $relativePath,
                    ]);
                }
            }
        });

        return redirect()->route('portal.atendimentos')
            ->with('success', 'Atendimento atualizado com sucesso!');
    }

    /**
     * Resolve token de QR Code para dados do equipamento (uso no portal autenticado)
     */
    public function equipamentoPorToken(Request $request, string $token)
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return response()->json([
                'message' => 'Selecione uma unidade ativa para abrir chamado.'
            ], 422);
        }

        $equipamento = Equipamento::query()
            ->with(['setor', 'responsavel'])
            ->where('qrcode_token', $token)
            ->where('cliente_id', $clienteId)
            ->first();

        if (! $equipamento) {
            return response()->json([
                'message' => 'QR Code inválido ou equipamento não pertence à unidade selecionada.'
            ], 404);
        }

        return response()->json($this->mapearDadosEquipamento($equipamento));
    }

    /**
     * Busca equipamento por identificador com suporte a busca parcial.
     * Campos suportados: token QR, código do ativo, TAG patrimonial, nome,
     * setor e responsável.
     */
    public function buscarEquipamento(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return response()->json([
                'message' => 'Selecione uma unidade ativa para abrir chamado.'
            ], 422);
        }

        $request->validate([
            'identificador' => 'required|string|max:255',
        ]);

        $identificador = trim((string) $request->input('identificador'));
        $token = $this->extrairTokenDoIdentificador($identificador) ?? $identificador;
        $codigoNormalizado = Str::lower($identificador);

        $queryBase = Equipamento::query()
            ->with(['setor', 'responsavel'])
            ->where('cliente_id', $clienteId);

        $equipamentoExato = (clone $queryBase)
            ->where(function ($query) use ($token, $codigoNormalizado) {
                $query->where('qrcode_token', $token)
                    ->orWhereRaw('LOWER(codigo_ativo) = ?', [$codigoNormalizado])
                    ->orWhereRaw('LOWER(tag_patrimonial) = ?', [$codigoNormalizado]);
            })
            ->first();

        if ($equipamentoExato) {
            return response()->json([
                'items' => [$this->mapearDadosEquipamento($equipamentoExato)],
                'total' => 1,
                'match_type' => 'exato',
            ]);
        }

        $equipamentos = (clone $queryBase)
            ->where(function ($query) use ($identificador) {
                $like = '%'.$identificador.'%';

                $query->where('nome', 'like', $like)
                    ->orWhere('codigo_ativo', 'like', $like)
                    ->orWhere('tag_patrimonial', 'like', $like)
                    ->orWhere('numero_serie', 'like', $like)
                    ->orWhereHas('setor', function ($setorQuery) use ($like) {
                        $setorQuery->where('nome', 'like', $like);
                    })
                    ->orWhereHas('responsavel', function ($respQuery) use ($like) {
                        $respQuery->where('nome', 'like', $like);
                    });
            })
            ->orderBy('nome')
            ->limit(20)
            ->get();

        if ($equipamentos->isEmpty()) {
            return response()->json([
                'message' => 'Nenhum equipamento encontrado para esse identificador.'
            ], 404);
        }

        return response()->json([
            'items' => $equipamentos->map(fn ($equipamento) => $this->mapearDadosEquipamento($equipamento))->values(),
            'total' => $equipamentos->count(),
            'match_type' => 'parcial',
        ]);
    }

    private function extrairTokenDoIdentificador(string $identificador): ?string
    {
        if (! filter_var($identificador, FILTER_VALIDATE_URL)) {
            return null;
        }

        $path = parse_url($identificador, PHP_URL_PATH);
        if (! $path) {
            return null;
        }

        $partes = array_values(array_filter(explode('/', $path)));
        $idxChamado = array_search('chamado', $partes, true);

        if ($idxChamado === false || ! isset($partes[$idxChamado + 1])) {
            return null;
        }

        return $partes[$idxChamado + 1];
    }

    private function mapearDadosEquipamento(Equipamento $equipamento): array
    {
        return [
            'id' => $equipamento->id,
            'token' => $equipamento->qrcode_token,
            'nome' => $equipamento->nome,
            'modelo' => $equipamento->modelo,
            'fabricante' => $equipamento->fabricante,
            'numero_serie' => $equipamento->numero_serie,
            'codigo_ativo' => $equipamento->codigo_ativo,
            'tag_patrimonial' => $equipamento->tag_patrimonial,
            'localizacao' => $equipamento->localizacao_resumo,
            'setor' => $equipamento->setor?->nome,
            'responsavel' => $equipamento->responsavel?->nome,
            'status_ativo' => $equipamento->status_ativo,
        ];
    }

    /**
     * Lista de Boletos
     */
    public function boletos()
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }
        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }
        $cliente = $user->clientes()->where('clientes.id', $clienteId)->firstOrFail();
        $boletos = $cliente->boletos()->with('cobranca')->orderByDesc('created_at')->get();

        return view('portal.boletos', compact('cliente', 'boletos'));
    }

    /**
     * Lista de Notas Fiscais
     */
    public function notas()
    {
        $user = Auth::user();
        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }
        $clienteId = session('cliente_id_ativo');
        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }
        $cliente = $user->clientes()->where('clientes.id', $clienteId)->firstOrFail();

        $notas = \App\Models\NotaFiscal::where('cliente_id', $clienteId)
            ->orderByDesc('created_at')
            ->get();

        return view('portal.notas', compact('cliente', 'notas'));
    }

    /**
     * Dashboard do Portal do Cliente
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        // unidade esteja selecionada
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
                // Se não tem clientes ativos vinculados, redireciona para selecionar
                return redirect()->route('portal.unidade');
            }
        }

        $clienteId = session('cliente_id_ativo');

        // cliente só acessa unidade dele (busca apenas clientes ativos)
        $cliente = $user->clientes()
            ->where('clientes.id', $clienteId)
            ->first();

        // Se não encontrou cliente ativo, força seleção de unidade
        if (! $cliente) {
            session()->forget('cliente_id_ativo');

            return redirect()->route('portal.unidade');
        }

        // Boletos
        $boletos = $cliente->boletos()->with('cobranca')->get();
        $totalBoletos = $boletos->count();

        // Notas fiscais
        $pastaNotas = storage_path("app/boletos/cliente_{$cliente->id}");
        $arquivosNotas = is_dir($pastaNotas) ? glob($pastaNotas.DIRECTORY_SEPARATOR.'*.pdf') : [];
        $totalNotas = count($arquivosNotas);

        // Atendimentos
        $atendimentos = $cliente->atendimentos()->get();
        $totalAtendimentosAbertos = $atendimentos->where('status_atual', 'aberto')->count();
        $totalAtendimentosExecucao = $atendimentos->where('status_atual', 'execucao')->count();
        $totalAtendimentosFinalizados = $atendimentos->where('status_atual', 'concluido')->count();
        $totalBoletos = $boletos->count();
        $totalNotas = count($arquivosNotas);

        // Ativos Técnicos
        $ativosTecnicos = $cliente->equipamentos()->with(['setor', 'responsavel'])->get();
        $totalAtivosTecnicos = $ativosTecnicos->count();

        $ativosOperando = $ativosTecnicos->where('status_ativo', 'operando')->count();
        $ativosEmManutencao = $ativosTecnicos->where('status_ativo', 'em_manutencao')->count();
        $ativosInativos = $ativosTecnicos->whereIn('status_ativo', ['inativo', 'descartado', 'substituido'])->count();
        $ativosSemStatus = $ativosTecnicos->filter(fn ($eq) => empty($eq->status_ativo))->count();

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

        $graficoStatusAtivos = [
            'labels' => ['Operando', 'Em manutenção', 'Inativos', 'Sem status'],
            'values' => [$ativosOperando, $ativosEmManutencao, $ativosInativos, $ativosSemStatus],
        ];

        $graficoManutencaoAtivos = [
            'labels' => ['Em dia', 'Atenção', 'Vencidas'],
            'values' => [$manutencoesEmDia, $manutencoesProximo, $manutencoesVencidas],
        ];

        return view('portal.index', compact(
            'cliente',
            'boletos',
            'totalBoletos',
            'totalNotas',
            'totalAtendimentosAbertos',
            'totalAtendimentosExecucao',
            'totalAtendimentosFinalizados',
            'totalAtivosTecnicos',
            'ativosOperando',
            'ativosEmManutencao',
            'ativosInativos',
            'ativosSemStatus',
            'manutencoesEmDia',
            'manutencoesProximo',
            'manutencoesVencidas',
            'graficoStatusAtivos',
            'graficoManutencaoAtivos'
        ));
    }

    public function financeiro(Request $request)
    {
        $user = Auth::user();

        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        // Unidade ativa obrigatória
        if (! session()->has('cliente_id_ativo')) {
            return redirect()->route('portal.unidade');
        }

        $clienteId = session('cliente_id_ativo');

        // Garante que o cliente pertence ao usuário
        $cliente = $user->clientes()
            ->where('clientes.id', $clienteId)
            ->firstOrFail();

        // ================= FILTROS =================
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Validação de datas
        try {
            $dataInicioCarbon = Carbon::parse($dataInicio);
            $dataFimCarbon = Carbon::parse($dataFim);

            if ($dataInicioCarbon->gt($dataFimCarbon)) {
                return back()->with('error', 'A data de início não pode ser maior que a data fim.');
            }

            // Limitar período máximo de consulta (1 ano)
            if ($dataInicioCarbon->diffInDays($dataFimCarbon) > 365) {
                return back()->with('error', 'Período máximo de consulta é de 1 ano.');
            }
        } catch (\Exception $e) {
            $dataInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
            $dataFim = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        // ================= COBRANÇAS COM ANEXOS =================
        $query = Cobranca::with(['boleto', 'anexos'])
            ->where('cliente_id', $cliente->id);

        // Aplicar filtro de período
        if ($dataInicio) {
            $query->where('data_vencimento', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_vencimento', '<=', $dataFim);
        }

        $cobrancas = $query->orderByDesc('data_vencimento')->get();

        // ================= RESUMO/KPIs (otimizado) =================
        $queryBase = Cobranca::where('cliente_id', $cliente->id);

        if ($dataInicio) {
            $queryBase->where('data_vencimento', '>=', $dataInicio);
        }
        if ($dataFim) {
            $queryBase->where('data_vencimento', '<=', $dataFim);
        }

        $resumo = [
            'total_pago' => (clone $queryBase)->where('status', 'pago')->sum('valor'),
            'total_pendente' => (clone $queryBase)->where('status', '!=', 'pago')->whereDate('data_vencimento', '>=', today())->sum('valor'),
            'total_vencido' => (clone $queryBase)->where('status', '!=', 'pago')->whereDate('data_vencimento', '<', today())->sum('valor'),
            'total_geral' => (clone $queryBase)->sum('valor'),
        ];

        return view('portal.financeiro.index', compact(
            'cliente',
            'cobrancas',
            'resumo',
            'dataInicio',
            'dataFim'
        ));
    }

    /**
     * Download de boleto (restrito à unidade ativa)
     */
    public function downloadBoleto(Boleto $boleto)
    {
        $user = Auth::user();

        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $clienteId = session('cliente_id_ativo');

        // boleto precisa ser da unidade ativa
        if ($boleto->cliente_id !== $clienteId) {
            abort(403);
        }

        // Marca como baixado (uma única vez)
        DB::table('boletos')
            ->where('id', $boleto->id)
            ->whereNull('baixado_em')
            ->update([
                'baixado_em' => now(),
            ]);

        $filePath = storage_path('app/'.$boleto->arquivo);

        if (! file_exists($filePath)) {
            abort(404, 'Boleto não encontrado.');
        }

        return response()->download($filePath);
    }

    /**
     * Download de Nota Fiscal (restrito à unidade ativa)
     */
    public function downloadNotaFiscal(string $arquivo)
    {
        $user = Auth::user();

        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $clienteId = session('cliente_id_ativo');

        // cliente ativo pertence ao usuário
        $cliente = $user->clientes()
            ->where('clientes.id', $clienteId)
            ->firstOrFail();

        $caminho = storage_path("app/boletos/cliente_{$cliente->id}/{$arquivo}");

        if (! file_exists($caminho)) {
            abort(404, 'Nota fiscal não encontrada.');
        }

        return response()->download($caminho);
    }

    /**
     * Tela de seleção de unidade
     */
    public function selecionarUnidade()
    {
        $user = Auth::user();

        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $clientes = $user->clientes;

        // Se só houver uma unidade, define e segue
        if ($clientes->count() === 1) {
            session(['cliente_id_ativo' => $clientes->first()->id]);

            return redirect()->route('portal.index');
        }

        return view('portal.selecionar-unidade', compact('clientes'));
    }

    /**
     * Define a unidade ativa na sessão
     */
    public function definirUnidade(Request $request)
    {
        $user = Auth::user();

        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $request->validate([
            'cliente_id' => 'required|integer',
        ]);

        // Cliente só pode escolher unidade vinculada a ele
        $existe = $user->clientes()
            ->where('clientes.id', $request->cliente_id)
            ->exists();

        abort_unless($existe, 403);

        session(['cliente_id_ativo' => $request->cliente_id]);

        return redirect()->route('portal.index');
    }

    /**
     * Limpa a unidade ativa e força nova seleção
     */
    public function trocarUnidade()
    {
        $user = Auth::user();

        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        session()->forget('cliente_id_ativo');

        return redirect()->route('portal.unidade');
    }

    /**
     * Imprimir orçamento (restrito à unidade ativa)
     */
    public function imprimirOrcamento($id)
    {
        $user = Auth::user();

        if (! $user || $user->tipo !== 'cliente') {
            abort(403);
        }

        $clienteId = session('cliente_id_ativo');

        if (! $clienteId) {
            return redirect()->route('portal.unidade');
        }

        // Buscar orçamento e verificar se pertence ao cliente ativo
        $orcamento = Orcamento::with([
            'empresa',
            'cliente',
            'cliente.emails',
            'cliente.telefones',
            'itens.item',
            'pagamentos',
            'taxasItens',
        ])->findOrFail($id);

        // Verificar se o orçamento pertence ao cliente ativo
        // Verifica tanto o cliente_id do orçamento quanto através das cobranças
        $pertenceAoCliente = $orcamento->cliente_id === $clienteId;

        if (! $pertenceAoCliente) {
            // Verificar se existe cobrança vinculada ao cliente ativo
            $cobrancaDoCliente = \App\Models\Cobranca::where('orcamento_id', $orcamento->id)
                ->where('cliente_id', $clienteId)
                ->exists();

            if (! $cobrancaDoCliente) {
                abort(403, 'Acesso não autorizado');
            }
        }

        // Gerar PDF usando o layout da empresa
        $view = 'orcamentos.'.$orcamento->empresa->layout_pdf;

        if (! view()->exists($view)) {
            abort(500, 'Layout de impressão não encontrado.');
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView($view, [
            'orcamento' => $orcamento,
            'empresa' => $orcamento->empresa,
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'orcamento_'.str_replace(
            ['/', '\\'],
            '-',
            $orcamento->numero_orcamento
        ).'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Exibe formulário de chamado via QR Code do ativo técnico
     */
    public function chamado($token)
    {
        $equipamento = \App\Models\Equipamento::where('qrcode_token', $token)
            ->with(['setor', 'responsavel', 'cliente'])
            ->firstOrFail();

        return view('portal.equipamentos.chamado-publico', compact('equipamento'));
    }

    /**
     * Processa formulário de chamado via QR Code do ativo técnico
     */
    public function storeChamadoQrCode(Request $request, $token)
    {
        $equipamento = \App\Models\Equipamento::where('qrcode_token', $token)
            ->with('cliente.empresas')
            ->firstOrFail();

        $request->validate([
            'descricao' => 'required|string|max:1000',
            'prioridade' => 'required|in:baixa,media,alta,urgente',
            'nome_solicitante' => 'nullable|string|max:255',
            'telefone_solicitante' => 'nullable|string|max:20',
            'email_solicitante' => 'nullable|email|max:255',
        ]);

        // Gerar número de atendimento
        $ultimoRegistro = \App\Models\Atendimento::orderBy('numero_atendimento', 'desc')->first();
        $numeroAtendimento = $ultimoRegistro ? $ultimoRegistro->numero_atendimento + 1 : 1;
        $prioridadeNormalizada = $this->normalizarPrioridadePortal((string) $request->input('prioridade'));

        $empresaAssuntoId = (int) optional($equipamento->cliente?->empresas?->first())->id;
        $assuntoId = $this->resolverAssuntoIdPortal('Chamado via QR Code', $empresaAssuntoId);

        if (! $assuntoId) {
            return redirect()->route('portal.equipamento.chamado', $token)
                ->with('error', 'Não foi possível vincular o assunto deste chamado.');
        }

        $atendimento = \App\Models\Atendimento::create([
            'numero_atendimento' => $numeroAtendimento,
            'cliente_id' => $equipamento->cliente_id,
            'equipamento_id' => $equipamento->id,
            'nome_solicitante' => $request->nome_solicitante ?? optional($equipamento->responsavel)->nome,
            'telefone_solicitante' => $request->telefone_solicitante ?? null,
            'email_solicitante' => $request->email_solicitante ?? null,
            'assunto_id' => $assuntoId,
            'descricao' => $request->descricao,
            'prioridade' => $prioridadeNormalizada,
            'empresa_id' => null,
            'funcionario_id' => null,
            'status_atual' => 'aberto',
            'data_atendimento' => now(),
        ]);

        return redirect()->route('portal.equipamento.chamado', $token)
            ->with('success', 'Chamado aberto com sucesso! Número: '.$numeroAtendimento);
    }

    private function resolverAssuntoIdPortal(string $nomeAssunto, int $empresaId): ?int
    {
        $nomeNormalizado = trim($nomeAssunto);
        if ($nomeNormalizado === '') {
            return null;
        }

        $assuntoExistente = Assunto::query()
            ->whereRaw('LOWER(nome) = ?', [Str::lower($nomeNormalizado)])
            ->when($empresaId > 0, fn ($query) => $query->where('empresa_id', $empresaId))
            ->first();

        if ($assuntoExistente) {
            return (int) $assuntoExistente->id;
        }

        if ($empresaId > 0) {
            $assunto = Assunto::create([
                'empresa_id' => $empresaId,
                'nome' => $nomeNormalizado,
                'tipo' => 'portal_cliente',
                'categoria' => 'chamado',
                'ativo' => true,
            ]);

            return (int) $assunto->id;
        }

        return null;
    }

    private function extrairDadosDescricaoChamadoPortal(string $descricao, ?string $assuntoRelacionamento = null): array
    {
        $assunto = trim((string) $assuntoRelacionamento);
        $corpo = $descricao;

        if (preg_match('/^Assunto:\s*(.+)$/m', $descricao, $assuntoMatch)) {
            $assuntoExtraido = trim((string) ($assuntoMatch[1] ?? ''));
            if ($assuntoExtraido !== '') {
                $assunto = $assuntoExtraido;
            }
        }

        $corpo = preg_replace('/^Assunto:\s*.+\n\n/m', '', $corpo, 1) ?? $corpo;

        $melhorHorario = null;
        if (preg_match('/\n\nMelhor horário para contato:\s*(.+)$/u', $corpo, $horarioMatch)) {
            $melhorHorario = trim((string) ($horarioMatch[1] ?? ''));
            $corpo = preg_replace('/\n\nMelhor horário para contato:\s*.+$/u', '', $corpo) ?? $corpo;
        }

        return [
            'assunto' => $assunto,
            'descricao' => trim($corpo),
            'melhor_horario_contato' => $melhorHorario,
        ];
    }

    private function normalizarPrioridadePortal(string $prioridade): string
    {
        $valor = strtolower(trim($prioridade));

        if ($valor === 'urgente') {
            return 'alta';
        }

        return in_array($valor, ['baixa', 'media', 'alta'], true) ? $valor : 'media';
    }
}
