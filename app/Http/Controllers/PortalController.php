<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use App\Models\Cobranca;
use App\Models\Orcamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortalController extends Controller
{
    /**
     * Lista de Atendimentos do Cliente
     */
    public function atendimentos()
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
        // Busca todos os atendimentos do cliente
        $atendimentos = $cliente->atendimentos()->orderByDesc('created_at')->get();

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
        $assuntos = \App\Models\Assunto::orderBy('nome')->get();

        return view('portal.chamado.novo', compact('cliente', 'assuntos'));
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

        $request->validate([
            'assunto_id' => 'required|exists:assuntos,id',
            'descricao' => 'required|string',
            'prioridade' => 'required|in:baixa,media,alta,urgente',
        ]);

        // Gerar número de atendimento
        $ultimoRegistro = \App\Models\Atendimento::orderBy('numero_atendimento', 'desc')->first();
        $numeroAtendimento = $ultimoRegistro ? $ultimoRegistro->numero_atendimento + 1 : 1;

        $atendimento = \App\Models\Atendimento::create([
            'numero_atendimento' => $numeroAtendimento,
            'cliente_id' => $clienteId,
            'nome_solicitante' => $user->name,
            'telefone_solicitante' => $user->telefone ?? null,
            'email_solicitante' => $user->email,
            'assunto_id' => $request->assunto_id,
            'descricao' => $request->descricao,
            'prioridade' => $request->prioridade,
            'status_atual' => 'aberto',
            'data_atendimento' => now(),
        ]);

        return redirect()->route('portal.atendimentos')->with('success', 'Chamado aberto com sucesso!');
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

        return view('portal.index', compact(
            'cliente',
            'boletos',
            'totalBoletos',
            'totalNotas',
            'totalAtendimentosAbertos',
            'totalAtendimentosExecucao',
            'totalAtendimentosFinalizados'
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
}
