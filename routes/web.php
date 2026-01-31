<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\BoletoController;
use App\Http\Controllers\DashboardAdmController;
use App\Http\Controllers\DashboardComercialController;
use App\Http\Controllers\DashboardFinanceiroController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\AssuntoController;
use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\PortalFuncionarioController;
use App\Http\Controllers\AtendimentoAndamentoFotoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\PreClienteController;
use App\Http\Controllers\BuscaClienteController;
use App\Http\Controllers\ItemComercialController;
use App\Http\Controllers\ContasReceberController;
use App\Http\Controllers\ContasPagarController;
use App\Http\Controllers\ContasFinanceirasController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\FornecedorController;


/*
|--------------------------------------------------------------------------
| Redirecionamento inicial
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| API Routes (para modais)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/empresas', [EmpresaController::class, 'apiList']);
    Route::get('/clientes', [ClienteController::class, 'apiList']);
    Route::get('/contas-financeiras/{empresa_id}', [ContasFinanceirasController::class, 'apiListByEmpresa']);
    Route::get('/orcamentos-por-cliente/{cliente_id}', [\App\Http\Controllers\Api\FinanceiroOrcamentoController::class, 'orcamentosPorCliente']);
});

/*
|--------------------------------------------------------------------------
| ÁREA ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'primeiro_acesso'])->group(function () {

    // Dashboard ADMIN
    Route::get('/dashboard', [DashboardAdmController::class, 'index'])
        ->middleware('dashboard.admin')
        ->name('dashboard');

    Route::get('/dashboard/atendimentos', [DashboardAdmController::class, 'getAtendimentos'])
        ->middleware('dashboard.admin')
        ->name('dashboard.atendimentos');

    Route::get('/dashboard/exportar', [DashboardAdmController::class, 'exportar'])
        ->middleware('dashboard.admin')
        ->name('dashboard.exportar');

    // Dashboard COMERCIAL
    Route::get('/dashboard-comercial', [DashboardComercialController::class, 'index'])
        ->middleware('dashboard.comercial')
        ->name('dashboard.comercial');

    Route::get('/dashboard-comercial/orcamentos', [DashboardComercialController::class, 'getOrcamentos'])
        ->middleware('dashboard.comercial')
        ->name('dashboard.comercial.orcamentos');

    Route::get('/dashboard-comercial/exportar', [DashboardComercialController::class, 'exportar'])
        ->middleware('dashboard.comercial')
        ->name('dashboard.comercial.exportar');

    // Histórico de Orçamentos
    Route::get('/dashboard-comercial/orcamentos/{orcamento}/historicos', [DashboardComercialController::class, 'getHistoricos'])
        ->middleware('dashboard.comercial')
        ->name('dashboard.comercial.orcamentos.historicos');

    Route::post('/dashboard-comercial/orcamentos/{orcamento}/historicos', [DashboardComercialController::class, 'adicionarHistorico'])
        ->middleware('dashboard.comercial')
        ->name('dashboard.comercial.orcamentos.historicos.store');

    // Dashboard FINANCEIRO
    Route::get('/financeiro/dashboard', [DashboardFinanceiroController::class, 'index'])
        ->name('financeiro.dashboard');

    // Dashboard TÉCNICO (Gestor)
    Route::get('/dashboard-tecnico', [App\Http\Controllers\DashboardTecnicoController::class, 'index'])
        ->middleware('dashboard.admin')
        ->name('dashboard.tecnico');

    Route::get('/dashboard-tecnico/atendimentos', [App\Http\Controllers\DashboardTecnicoController::class, 'getAtendimentos'])
        ->middleware('dashboard.admin')
        ->name('dashboard.tecnico.atendimentos');

    Route::get('/dashboard-tecnico/atendimento/{atendimento}', [App\Http\Controllers\DashboardTecnicoController::class, 'detalhesAtendimento'])
        ->middleware('dashboard.admin')
        ->name('dashboard.tecnico.detalhes');

    Route::get('/dashboard-tecnico/atualizar', [App\Http\Controllers\DashboardTecnicoController::class, 'atualizarDados'])
        ->middleware('dashboard.admin')
        ->name('dashboard.tecnico.atualizar');

    // Orçamentos
    Route::resource('orcamentos', OrcamentoController::class)
        ->middleware('dashboard.comercial');

    Route::patch('/orcamentos/{orcamento}/status', [OrcamentoController::class, 'updateStatus'])
        ->name('orcamentos.updateStatus');

    Route::get('/orcamentos/{id}/imprimir', [OrcamentoController::class, 'imprimir'])
        ->name('orcamentos.imprimir');

    // Serviços e Produtos
    Route::get('/itemcomercial/buscar', [ItemComercialController::class, 'buscar'])
        ->middleware(['auth', 'dashboard.comercial'])
        ->name('itemcomercial.buscar');

    Route::resource('itemcomercial', ItemComercialController::class)
        ->parameters([
            'itemcomercial' => 'item_comercial'
        ])
        ->except(['show'])
        ->middleware(['dashboard.comercial']);

    // Cobranças
    Route::patch(
        '/cobrancas/{cobranca}/pagar',
        [CobrancaController::class, 'marcarComoPago']
    )->name('cobrancas.pagar');

    Route::resource('cobrancas', CobrancaController::class);

    // Busca unificada Cliente + Pré-Cliente (Orçamentos)
    Route::get('/busca-clientes', [BuscaClienteController::class, 'buscar'])
        ->middleware('dashboard.comercial')
        ->name('busca-clientes');

    // Clientes
    Route::get('/clientes/buscar', [ClienteController::class, 'buscar'])
        ->name('clientes.buscar');

    Route::resource('clientes', ClienteController::class);

    // Pré-Clientes (Admin e Comercial)
    Route::resource('pre-clientes', PreClienteController::class)
        ->middleware(['dashboard.comercial']);

    Route::post(
        '/pre-clientes/{preCliente}/converter',
        [\App\Http\Controllers\PreClienteController::class, 'converterParaCliente']
    )->name('pre-clientes.converter');

    // Empresas
    Route::resource('empresas', EmpresaController::class);

    // Funcionários
    Route::resource('funcionarios', FuncionarioController::class);

    // Assuntos
    Route::resource('assuntos', AssuntoController::class);

    // Atendimentos
    Route::resource('atendimentos', AtendimentoController::class)
        ->except(['show']);

    // Usuarios
    Route::resource('usuarios', UsuarioController::class)
        ->middleware('dashboard.comercial');


    Route::get(
        '/orcamentos/gerar-numero/{empresa}',
        [\App\Http\Controllers\OrcamentoController::class, 'gerarNumero']
    )->middleware('dashboard.comercial');


    Route::patch(
        '/atendimentos/{atendimento}/atualizar-campo',
        [AtendimentoController::class, 'atualizarCampo']
    );

    Route::post(
        '/atendimentos/{atendimento}/andamentos',
        [\App\Http\Controllers\AtendimentoAndamentoController::class, 'store']
    )->name('atendimentos.andamentos.store');


    Route::post(
        '/atendimentos/{atendimento}/atualizar-status',
        [\App\Http\Controllers\AtendimentoStatusController::class, 'update']
    )->name('atendimentos.status.update');


    // Upload de fotos
    Route::post(
        '/andamentos/{andamento}/fotos',
        [AtendimentoAndamentoFotoController::class, 'store']
    )->name('andamentos.fotos.store');


    // Upload de boletos
    Route::post(
        '/boletos/{cliente}/upload',
        [BoletoController::class, 'upload']
    )->name('boletos.upload');
});

/*
|--------------------------------------------------------------------------
| PORTAL DO FINANCEIRO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'financeiro', 'primeiro_acesso'])
    ->prefix('financeiro')
    ->name('financeiro.')
    ->group(function () {

        // Dashboard financeiro
        Route::get('/', [FinanceiroController::class, 'dashboard'])
            ->name('index');

        // Dashboard financeiro (COMENTADO - usando DashboardFinanceiroController)
        // Route::get('/dashboard', [FinanceiroController::class, 'dashboard'])
        //     ->name('dashboard');

        // Cobrar
        Route::get('/cobrar', [FinanceiroController::class, 'cobrar'])
            ->name('cobrar');

        // Contas a receber
        Route::get('/contas-a-receber', [ContasReceberController::class, 'index'])
            ->name('contasareceber');

        // Movimentação financeira
        Route::get(
            '/movimentacao',
            [ContasReceberController::class, 'movimentacao']
        )->name('movimentacao');

        // Contas a receber reabrir cobrançã
        Route::patch(
            '/contas-a-receber/{cobranca}/reabrir',
            [ContasReceberController::class, 'reabrir']
        )->name('contasareceber.reabrir');

        // Recibo de cobrança
        Route::get(
            '/cobrancas/{cobranca}/recibo',
            [ContasReceberController::class, 'recibo']
        )->name('cobrancas.recibo');

        // Estornar pagamento
        Route::patch(
            '/movimentacao/{cobranca}/estornar',
            [ContasReceberController::class, 'estornar']
        )->name('movimentacao.estornar');

        /*
        |----------------------------------------------------------------------
        | CONTAS FINANCEIRAS)
        |----------------------------------------------------------------------
        */
        Route::get('/contas-financeiras', [ContasFinanceirasController::class, 'index'])
            ->name('contas-financeiras.index');

        Route::get('/contas-financeiras/criar', [ContasFinanceirasController::class, 'create'])
            ->name('contas-financeiras.create');

        Route::post('/contas-financeiras', [ContasFinanceirasController::class, 'store'])
            ->name('contas-financeiras.store');

        Route::get('/contas-financeiras/{contaFinanceira}/editar', [ContasFinanceirasController::class, 'edit'])
            ->name('contas-financeiras.edit');

        Route::put('/contas-financeiras/{contaFinanceira}', [ContasFinanceirasController::class, 'update'])
            ->name('contas-financeiras.update');

        Route::delete('/contas-financeiras/{contaFinanceira}', [ContasFinanceirasController::class, 'destroy'])
            ->name('contas-financeiras.destroy');


        Route::patch(
            '/contas-a-receber/{cobranca}/pagar',
            [ContasReceberController::class, 'pagar']
        )->name('contasareceber.pagar');

        Route::delete(
            '/contas-a-receber/{cobranca}',
            [ContasReceberController::class, 'destroy']
        )->name('contasareceber.destroy');

        // Contas Fixas
        Route::post(
            '/contas-fixas',
            [ContasReceberController::class, 'storeContaFixa']
        )->name('contas-fixas.store');

        Route::get(
            '/contas-fixas/{contaFixa}',
            [ContasReceberController::class, 'getContaFixa']
        )->name('contas-fixas.get');

        Route::put(
            '/contas-fixas/{contaFixa}',
            [ContasReceberController::class, 'updateContaFixa']
        )->name('contas-fixas.update');

        // Pipeline do orçamento
        Route::post(
            '/orcamentos/{orcamento}/gerar-cobranca',
            [FinanceiroController::class, 'gerarCobranca']
        )->name('orcamentos.gerar-cobranca');

        // Anexos de Cobrança
        Route::post(
            '/cobrancas/{cobranca}/anexos',
            [ContasReceberController::class, 'uploadAnexo']
        )->name('cobrancas.anexos.upload');

        Route::get(
            '/cobrancas/{cobranca}/anexos',
            [ContasReceberController::class, 'listarAnexos']
        )->name('cobrancas.anexos.listar');

        Route::get(
            '/cobrancas/anexos/{anexo}/download',
            [ContasReceberController::class, 'downloadAnexo']
        )->name('cobrancas.anexos.download');

        Route::delete(
            '/cobrancas/anexos/{anexo}',
            [ContasReceberController::class, 'excluirAnexo']
        )->name('cobrancas.anexos.excluir');

        /*
        |----------------------------------------------------------------------
        | CONTAS A PAGAR
        |----------------------------------------------------------------------
        */
        Route::get('/contas-a-pagar', [ContasPagarController::class, 'index'])
            ->name('contasapagar');

        Route::post('/contas-a-pagar', [ContasPagarController::class, 'store'])
            ->name('contasapagar.store');

        Route::get('/contas-a-pagar/{conta}', [ContasPagarController::class, 'show'])
            ->name('contasapagar.show');

        Route::put('/contas-a-pagar/{conta}', [ContasPagarController::class, 'update'])
            ->name('contasapagar.update');

        Route::patch('/contas-a-pagar/{conta}/pagar', [ContasPagarController::class, 'marcarComoPago'])
            ->name('contasapagar.pagar');

        Route::patch('/contas-a-pagar/{conta}/estornar', [ContasPagarController::class, 'estornar'])
            ->name('contasapagar.estornar');

        Route::delete('/contas-a-pagar/{conta}', [ContasPagarController::class, 'destroy'])
            ->name('contasapagar.destroy');

        // Anexos de Contas a Pagar
        Route::get('/contas-pagar/{conta}/anexos', [ContasPagarController::class, 'getAnexos'])
            ->name('financeiro.contas-pagar.anexos.index');

        Route::post('/contas-pagar/{conta}/anexos', [ContasPagarController::class, 'storeAnexo'])
            ->name('financeiro.contas-pagar.anexos.store');

        Route::delete('/contas-pagar/anexos/{anexo}', [ContasPagarController::class, 'destroyAnexo'])
            ->name('financeiro.contas-pagar.anexos.destroy');

        Route::get('/contas-pagar/anexos/{anexo}/download', [ContasPagarController::class, 'downloadAnexo'])
            ->name('financeiro.contas-pagar.anexos.download');

        // Contas Fixas a Pagar
        Route::post('/contas-fixas-pagar', [ContasPagarController::class, 'storeContaFixa'])
            ->name('contasapagar.storeContaFixa');

        Route::get('/contas-fixas-pagar', [ContasPagarController::class, 'contasFixas'])
            ->name('contasapagar.contasFixas');

        Route::get('/contas-fixas-pagar/{contaFixa}', [ContasPagarController::class, 'showContaFixa'])
            ->name('contasapagar.showContaFixa');

        Route::put('/contas-fixas-pagar/{contaFixa}', [ContasPagarController::class, 'updateContaFixa'])
            ->name('contasapagar.updateContaFixa');

        Route::patch('/contas-fixas-pagar/{contaFixa}/desativar', [ContasPagarController::class, 'desativarContaFixa'])
            ->name('contasapagar.desativarContaFixa');

        Route::patch('/contas-fixas-pagar/{contaFixa}/ativar', [ContasPagarController::class, 'ativarContaFixa'])
            ->name('contasapagar.ativarContaFixa');

        // API para buscar subcategorias e contas
        Route::get('/api/subcategorias/{categoriaId}', [ContasPagarController::class, 'getSubcategorias'])
            ->name('api.subcategorias');

        Route::get('/api/contas/{subcategoriaId}', [ContasPagarController::class, 'getContas'])
            ->name('api.contas');
    });

/*
    |--------------------------------------------------------------------------
    | FORNECEDORES
    |--------------------------------------------------------------------------
    */
Route::middleware(['auth'])->group(function () {
    Route::resource('fornecedores', FornecedorController::class)->parameters([
        'fornecedores' => 'fornecedor'
    ]);
    Route::get('/fornecedores/api/buscar-cnpj', [FornecedorController::class, 'buscarPorCnpj'])
        ->name('fornecedores.buscarCnpj');
});
/*
    |--------------------------------------------------------------------------
    | PORTAL DO CLIENTE
    |--------------------------------------------------------------------------
    */
Route::middleware(['auth', 'cliente', 'primeiro_acesso'])->group(function () {

    Route::get('/portal', [PortalController::class, 'index'])
        ->name('portal.index');

    Route::get('/portal/financeiro', [PortalController::class, 'financeiro'])
        ->name('portal.financeiro');

    Route::get('/portal/unidade', [PortalController::class, 'selecionarUnidade'])
        ->name('portal.unidade');

    Route::post('/portal/unidade', [PortalController::class, 'definirUnidade'])
        ->name('portal.unidade.definir');

    Route::post('/portal/trocar-unidade', [PortalController::class, 'trocarUnidade'])
        ->name('portal.trocar-unidade');

    Route::get(
        '/portal/boletos/{boleto}/download',
        [PortalController::class, 'downloadBoleto']
    )->name('portal.boletos.download');

    Route::get(
        '/portal/notas/{nota}/download',
        [PortalController::class, 'downloadNotaFiscal']
    )->name('portal.notas.download');

    // Download de anexos de cobrança (acessível para clientes)
    Route::get(
        '/portal/cobrancas/anexos/{anexo}/download',
        [ContasReceberController::class, 'downloadAnexo']
    )->name('portal.cobrancas.anexos.download');

    // Impressão de orçamento (acessível para clientes)
    Route::get(
        '/portal/orcamentos/{id}/imprimir',
        [PortalController::class, 'imprimirOrcamento']
    )->name('portal.orcamentos.imprimir');
});

/*
|--------------------------------------------------------------------------
| PORTAL DO FUNCIONÁRIO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'funcionario', 'primeiro_acesso'])
    ->prefix('portal-funcionario')
    ->name('portal-funcionario.')
    ->group(function () {

        // Home do Portal
        Route::get('/', [PortalFuncionarioController::class, 'index'])->name('index');

        // Chamados (lista organizada por status)
        Route::get('/chamados', [PortalFuncionarioController::class, 'chamados'])->name('chamados');

        // Detalhes do Atendimento
        Route::get('/atendimento/{atendimento}', [PortalFuncionarioController::class, 'showAtendimento'])->name('atendimento.show');

        // Ações do Atendimento
        Route::post('/atendimento/{atendimento}/iniciar', [PortalFuncionarioController::class, 'iniciarAtendimento'])->name('atendimento.iniciar');
        Route::post('/atendimento/{atendimento}/pausar', [PortalFuncionarioController::class, 'pausarAtendimento'])->name('atendimento.pausar');
        Route::post('/atendimento/{atendimento}/retomar', [PortalFuncionarioController::class, 'retomarAtendimento'])->name('atendimento.retomar');
        Route::post('/atendimento/{atendimento}/finalizar', [PortalFuncionarioController::class, 'finalizarAtendimento'])->name('atendimento.finalizar');

        // Agenda Técnica
        Route::get('/agenda', [PortalFuncionarioController::class, 'agenda'])->name('agenda');

        // Documentos
        Route::get('/documentos', [PortalFuncionarioController::class, 'documentos'])->name('documentos');

        // Fotos de Andamento (mantido para compatibilidade)
        Route::post('/andamentos/{andamento}/fotos', [AtendimentoAndamentoFotoController::class, 'store'])->name('andamentos.fotos.store');
        Route::delete('/andamentos/fotos/{foto}', [AtendimentoAndamentoFotoController::class, 'destroy'])->name('andamentos.fotos.destroy');
    });


/*
|--------------------------------------------------------------------------
| PERFIL (todos logados)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


Route::get(
    '/empresas/{empresa}/assuntos',
    [EmpresaController::class, 'assuntos']
);



/*
|--------------------------------------------------------------------------
| PRIMEIRO ACESSO – TROCA DE SENHA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/primeiro-acesso', function () {
        return view('auth.primeiro-acesso');
    })->name('password.first');

    Route::post('/primeiro-acesso', function (Request $request) {

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->update([
            'password' => bcrypt($request->password),
            'primeiro_acesso' => false,
        ]);

        return redirect()->route(
            $user->tipo === 'comercial'
                ? 'dashboard.comercial'
                : ($user->isAdminPanel()
                    ? 'dashboard'
                    : ($user->tipo === 'cliente'
                        ? 'portal.index'
                        : 'portal-funcionario.index'))
        );
    })->name('password.first.store');
});


/*
|--------------------------------------------------------------------------
| API – Consulta CNPJ (Proxy ReceitaWS)
|--------------------------------------------------------------------------
*/
Route::get('/api/cnpj/{cnpj}', function ($cnpj) {

    $cnpj = preg_replace('/\D/', '', $cnpj);

    if (strlen($cnpj) !== 14) {
        return response()->json([
            'status' => 'ERROR',
            'message' => 'CNPJ inválido'
        ], 400);
    }

    try {

        $cacheKey = "cnpj_{$cnpj}";

        $data = Cache::remember($cacheKey, 60 * 60 * 24, function () use ($cnpj) {

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(15)
                ->get("https://www.receitaws.com.br/v1/cnpj/{$cnpj}");

            return $response->json();
        });

        return response()->json($data);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'message' => 'Erro ao consultar Receita Federal'
        ], 500);
    }
});


require __DIR__ . '/auth.php';
