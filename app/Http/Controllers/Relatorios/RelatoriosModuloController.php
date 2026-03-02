<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\CentroCusto;
use App\Models\Empresa;
use App\Services\Relatorios\PainelExecutivoService;
use App\Services\Relatorios\RelatorioComercialService;
use App\Services\Relatorios\RelatorioFinanceiroService;
use App\Services\Relatorios\RelatorioRHService;
use App\Services\Relatorios\RelatorioTecnicoService;
use Illuminate\Http\Request;

class RelatoriosModuloController extends Controller
{
    public function __construct(
        private readonly RelatorioFinanceiroService $financeiroService,
        private readonly RelatorioTecnicoService $tecnicoService,
        private readonly RelatorioComercialService $comercialService,
        private readonly RelatorioRHService $rhService,
        private readonly PainelExecutivoService $painelExecutivoService,
    ) {}

    public function index(Request $request)
    {
        [$empresas, $centrosCusto, $filtros, $dados] = $this->montarDados($request);

        return view('relatorios.modulo', [
            'empresas' => $empresas,
            'centrosCusto' => $centrosCusto,
            'filtros' => $filtros,
            'dados' => $dados,
        ]);
    }

    public function imprimir(Request $request)
    {
        [$empresas, $centrosCusto, $filtros, $dados] = $this->montarDados($request);

        return view('relatorios.modulo-impressao', [
            'empresas' => $empresas,
            'centrosCusto' => $centrosCusto,
            'filtros' => $filtros,
            'dados' => $dados,
        ]);
    }

    private function montarDados(Request $request): array
    {
        $empresas = Empresa::query()
            ->select('id', 'nome_fantasia', 'razao_social')
            ->orderBy('nome_fantasia')
            ->get();

        $dataInicio = $request->input('data_inicio', $request->input('vencimento_inicio', now()->startOfMonth()->toDateString()));
        $dataFim = $request->input('data_fim', $request->input('vencimento_fim', now()->endOfMonth()->toDateString()));

        $filtros = [
            'empresa_id' => $request->filled('empresa_id') ? (int) $request->input('empresa_id') : null,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'centro_custo_id' => $request->filled('centro_custo_id') ? (int) $request->input('centro_custo_id') : null,
            'tipo' => $request->input('tipo', 'painel-executivo'),
        ];

        $centrosCusto = CentroCusto::query()
            ->when($filtros['empresa_id'], fn ($query) => $query->where('empresa_id', $filtros['empresa_id']))
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $input = [
            'empresa_id' => $filtros['empresa_id'],
            'data_inicio' => $filtros['data_inicio'],
            'data_fim' => $filtros['data_fim'],
            'centro_custo_id' => $filtros['centro_custo_id'],
        ];

        $dados = match ($filtros['tipo']) {
            'financeiro' => $this->financeiroService->gerar($input),
            'tecnico' => $this->tecnicoService->gerar($input),
            'comercial' => $this->comercialService->gerar($input),
            'rh' => $this->rhService->gerar($input),
            default => $this->painelExecutivoService->gerar($input),
        };

        return [$empresas, $centrosCusto, $filtros, $dados];
    }
}
