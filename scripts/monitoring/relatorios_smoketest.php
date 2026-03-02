<?php

declare(strict_types=1);

use App\Services\Relatorios\PainelExecutivoService;
use App\Services\Relatorios\RelatorioComercialService;
use App\Services\Relatorios\RelatorioFinanceiroService;
use App\Services\Relatorios\RelatorioRHService;
use App\Services\Relatorios\RelatorioTecnicoService;
use Illuminate\Http\Request;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function resumo(string $titulo, array $dados): void
{
    echo "\n=== {$titulo} ===\n";
    $campos = [
        'periodo',
        'receita_total',
        'despesa_total',
        'lucro_liquido',
        'margem_percentual',
        'total_chamados',
        'taxa_conversao',
        'indice_absenteismo',
        'crescimento_vs_mes_anterior',
    ];

    foreach ($campos as $campo) {
        if (array_key_exists($campo, $dados)) {
            $valor = $dados[$campo];
            if (is_array($valor)) {
                echo $campo . ': ' . json_encode($valor, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            } else {
                echo $campo . ': ' . (string) $valor . PHP_EOL;
            }
        }
    }

    if (isset($dados['insights_automaticos']) && is_array($dados['insights_automaticos'])) {
        echo 'insights_automaticos: ' . count($dados['insights_automaticos']) . PHP_EOL;
    }
}

$empresaComDados = (int) (DB::table('empresas')->min('id') ?? 1);
$empresaSemDados = (int) (DB::table('empresas')->max('id') ?? $empresaComDados);

$centroCusto = DB::table('centros_custo')
    ->where('empresa_id', $empresaComDados)
    ->value('id');

$financeiro = app(RelatorioFinanceiroService::class);
$tecnico = app(RelatorioTecnicoService::class);
$comercial = app(RelatorioComercialService::class);
$rh = app(RelatorioRHService::class);
$painel = app(PainelExecutivoService::class);

$cenarios = [
    'periodo_1_dia' => [
        'empresa_id' => $empresaComDados,
        'data_inicio' => '2026-03-01',
        'data_fim' => '2026-03-01',
    ],
    'periodo_cruzando_mes' => [
        'empresa_id' => $empresaComDados,
        'data_inicio' => '2026-02-20',
        'data_fim' => '2026-03-02',
    ],
    'empresa_sem_dados' => [
        'empresa_id' => $empresaSemDados,
        'data_inicio' => '2026-01-01',
        'data_fim' => '2026-01-31',
    ],
    'alto_volume' => [
        'empresa_id' => $empresaComDados,
        'data_inicio' => '2020-01-01',
        'data_fim' => '2030-12-31',
    ],
];

if ($centroCusto) {
    $cenarios['com_centro_custo'] = [
        'empresa_id' => $empresaComDados,
        'data_inicio' => '2026-02-01',
        'data_fim' => '2026-03-01',
        'centro_custo_id' => (int) $centroCusto,
    ];
}

foreach ($cenarios as $nome => $filtros) {
    echo "\n##############################\n";
    echo "CENÁRIO: {$nome}\n";
    echo 'FILTROS: ' . json_encode($filtros, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    resumo('Financeiro', $financeiro->gerar($filtros));
    resumo('Técnico', $tecnico->gerar($filtros));
    resumo('Comercial', $comercial->gerar($filtros));
    resumo('RH', $rh->gerar($filtros));
    resumo('Painel Executivo', $painel->gerar($filtros));
}

echo "\n##############################\n";
echo "CONTAGENS (INDÍCIO DE VOLUME)\n";

$contagens = [
    'orcamentos_empresa' => DB::table('orcamentos')->where('empresa_id', $empresaComDados)->count(),
    'cobrancas_total' => DB::table('cobrancas')->count(),
    'contas_pagar_total' => DB::table('contas_pagar')->count(),
    'atendimentos_empresa' => DB::table('atendimentos')->where('empresa_id', $empresaComDados)->count(),
    'registro_pontos_total' => DB::table('registro_pontos_portal')->count(),
];

foreach ($contagens as $nome => $total) {
    echo $nome . ': ' . $total . PHP_EOL;
}

echo "\n##############################\n";
echo "VALIDAÇÃO HTTP (CONTROLLERS)\n";

$controllers = [
    'financeiro' => App\Http\Controllers\Relatorios\RelatorioFinanceiroController::class,
    'tecnico' => App\Http\Controllers\Relatorios\RelatorioTecnicoController::class,
    'comercial' => App\Http\Controllers\Relatorios\RelatorioComercialController::class,
    'rh' => App\Http\Controllers\Relatorios\RelatorioRHController::class,
    'painel' => App\Http\Controllers\Relatorios\PainelExecutivoController::class,
];

$payloadHttp = [
    'empresa_id' => $empresaComDados,
    'data_inicio' => '2026-02-20',
    'data_fim' => '2026-03-02',
];

foreach ($controllers as $nome => $controllerClass) {
    $controller = app($controllerClass);
    $request = Request::create('/api/relatorios/' . $nome, 'GET', $payloadHttp);
    $response = $controller($request);
    $json = json_decode($response->getContent(), true);

    echo $nome . ' status: ' . $response->getStatusCode() . PHP_EOL;
    echo $nome . ' periodo_presente: ' . (isset($json['periodo']) ? 'sim' : 'não') . PHP_EOL;
}

$explainQueries = [
    'financeiro' => [
        'sql' => 'EXPLAIN SELECT o.id FROM orcamentos o WHERE o.empresa_id = ? AND o.created_at BETWEEN ? AND ? LIMIT 10',
        'bindings' => [$empresaComDados, '2026-02-01 00:00:00', '2026-03-02 23:59:59'],
    ],
    'tecnico' => [
        'sql' => 'EXPLAIN SELECT a.id FROM atendimentos a WHERE a.empresa_id = ? AND a.created_at BETWEEN ? AND ? LIMIT 10',
        'bindings' => [$empresaComDados, '2026-02-01 00:00:00', '2026-03-02 23:59:59'],
    ],
    'movimentacoes' => [
        'sql' => Schema::hasColumn('movimentacoes_financeiras', 'empresa_id')
            ? 'EXPLAIN SELECT m.id FROM movimentacoes_financeiras m WHERE m.empresa_id = ? AND m.data_movimentacao BETWEEN ? AND ? LIMIT 10'
            : 'EXPLAIN SELECT m.id FROM movimentacoes_financeiras m WHERE m.data_movimentacao BETWEEN ? AND ? LIMIT 10',
        'bindings' => Schema::hasColumn('movimentacoes_financeiras', 'empresa_id')
            ? [$empresaComDados, '2026-02-01', '2026-03-02']
            : ['2026-02-01', '2026-03-02'],
    ],
];

echo "\n##############################\n";
echo "EXPLAIN\n";

foreach ($explainQueries as $nome => $query) {
    $rows = DB::select($query['sql'], $query['bindings']);
    echo "\n[{$nome}]\n";
    foreach ($rows as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}

echo "\nSMOKE TEST FINALIZADO\n";
