<?php

use App\Http\Controllers\RelatorioComercialController;
use App\Http\Controllers\RelatorioCustosOrcamentosController;
use App\Http\Controllers\RelatorioFinanceiroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Relatórios
Route::middleware(['auth'])->group(function () {
    Route::get('/relatorios', function () {
        return view('relatorios.index');
    })->name('relatorios.index');
    Route::get('/relatorios/custos-orcamentos', [RelatorioCustosOrcamentosController::class, 'index'])->name('relatorios.custos-orcamentos');
    Route::get('relatorios/custos-gerencial', [\App\Http\Controllers\Relatorios\RelatorioCustoGerencialController::class, 'index'])->name('relatorios.custos-gerencial');
    Route::get('/relatorios/contas-receber', [RelatorioFinanceiroController::class, 'contasReceber'])
        ->name('relatorios.contas-receber');
    Route::get('/relatorios/contas-receber/json', [RelatorioFinanceiroController::class, 'contasReceberJson'])
        ->name('relatorios.contas-receber.json');
    Route::get('/relatorios/contas-pagar', [RelatorioFinanceiroController::class, 'contasPagar'])
        ->name('relatorios.contas-pagar');
    Route::get('/relatorios/contas-pagar/json', [RelatorioFinanceiroController::class, 'contasPagarJson'])
        ->name('relatorios.contas-pagar.json');
    Route::get('/relatorios/comercial', [RelatorioComercialController::class, 'index'])
        ->name('relatorios.comercial');

    // Cadastros
    Route::get('/cadastros', function () {
        return view('cadastros.index');
    })->name('cadastros.index');

    // Comercial
    Route::get('/comercial', function () {
        return view('comercial.index');
    })->name('comercial.index');

    // Gestão
    Route::get('/gestao', function () {
        return view('gestao.index');
    })->name('gestao.index');
});

use App\Http\Controllers\AssuntoController;
use App\Http\Controllers\AgendaTecnicaController;
use App\Http\Controllers\AtendimentoAndamentoFotoController;
use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\BoletoController;
use App\Http\Controllers\BuscaClienteController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\ContasFinanceirasController;
use App\Http\Controllers\ContasPagarController;
use App\Http\Controllers\ContasReceberController;
use App\Http\Controllers\DashboardAdmController;
use App\Http\Controllers\DashboardComercialController;
use App\Http\Controllers\DashboardFinanceiroController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\ItemComercialController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PortalFuncionarioController;
use App\Http\Controllers\PreClienteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Rh\FuncionarioRhDadosController;
use App\Http\Controllers\Rh\DashboardRhController;
use App\Http\Controllers\Rh\PontoJornadaController;
use App\Http\Controllers\UsuarioController;

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

// API para buscar subcategorias e contas (fora de qualquer grupo prefixado, compatível com frontend)
Route::middleware(['auth', 'financeiro'])->group(function () {
    Route::get('/financeiro/api/subcategorias/{categoriaId}', [\App\Http\Controllers\ContasPagarController::class, 'getSubcategorias']);
    Route::get('/financeiro/api/contas/{subcategoriaId}', [\App\Http\Controllers\ContasPagarController::class, 'getContas']);
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
        ->middleware('financeiro')
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

    // RH (somente perfis administrativos)
    Route::middleware('rh.admin')
        ->prefix('rh')
        ->name('rh.')
        ->group(function () {
            Route::get('/', [DashboardRhController::class, 'index'])->name('dashboard');
            Route::get('/relatorios/{indicador}', [DashboardRhController::class, 'relatorio'])->name('relatorios.show');

            Route::resource('funcionarios', FuncionarioController::class)
                ->except(['show']);

            Route::post('funcionarios/{funcionario}/documentos', [FuncionarioRhDadosController::class, 'storeDocumento'])->name('funcionarios.documentos.store');
            Route::put('funcionarios/{funcionario}/documentos/{documento}', [FuncionarioRhDadosController::class, 'updateDocumento'])->name('funcionarios.documentos.update');
            Route::delete('funcionarios/{funcionario}/documentos/{documento}', [FuncionarioRhDadosController::class, 'destroyDocumento'])->name('funcionarios.documentos.destroy');

            Route::post('funcionarios/{funcionario}/epis', [FuncionarioRhDadosController::class, 'storeEpi'])->name('funcionarios.epis.store');
            Route::put('funcionarios/{funcionario}/epis/{funcionarioEpi}', [FuncionarioRhDadosController::class, 'updateEpi'])->name('funcionarios.epis.update');
            Route::delete('funcionarios/{funcionario}/epis/{funcionarioEpi}', [FuncionarioRhDadosController::class, 'destroyEpi'])->name('funcionarios.epis.destroy');

            Route::post('funcionarios/{funcionario}/beneficios', [FuncionarioRhDadosController::class, 'storeBeneficio'])->name('funcionarios.beneficios.store');
            Route::put('funcionarios/{funcionario}/beneficios/{beneficio}', [FuncionarioRhDadosController::class, 'updateBeneficio'])->name('funcionarios.beneficios.update');
            Route::delete('funcionarios/{funcionario}/beneficios/{beneficio}', [FuncionarioRhDadosController::class, 'destroyBeneficio'])->name('funcionarios.beneficios.destroy');

            Route::post('funcionarios/{funcionario}/jornadas', [FuncionarioRhDadosController::class, 'storeJornada'])->name('funcionarios.jornadas.store');
            Route::put('funcionarios/{funcionario}/jornadas/{funcionarioJornada}', [FuncionarioRhDadosController::class, 'updateJornada'])->name('funcionarios.jornadas.update');
            Route::delete('funcionarios/{funcionario}/jornadas/{funcionarioJornada}', [FuncionarioRhDadosController::class, 'destroyJornada'])->name('funcionarios.jornadas.destroy');

            Route::post('funcionarios/{funcionario}/ferias', [FuncionarioRhDadosController::class, 'storeFerias'])->name('funcionarios.ferias.store');
            Route::put('funcionarios/{funcionario}/ferias/{ferias}', [FuncionarioRhDadosController::class, 'updateFerias'])->name('funcionarios.ferias.update');
            Route::delete('funcionarios/{funcionario}/ferias/{ferias}', [FuncionarioRhDadosController::class, 'destroyFerias'])->name('funcionarios.ferias.destroy');

            Route::post('funcionarios/{funcionario}/advertencias', [FuncionarioRhDadosController::class, 'storeAdvertencia'])->name('funcionarios.advertencias.store');
            Route::put('funcionarios/{funcionario}/advertencias/{advertencia}', [FuncionarioRhDadosController::class, 'updateAdvertencia'])->name('funcionarios.advertencias.update');
            Route::delete('funcionarios/{funcionario}/advertencias/{advertencia}', [FuncionarioRhDadosController::class, 'destroyAdvertencia'])->name('funcionarios.advertencias.destroy');

            Route::get('/ponto-jornada', [PontoJornadaController::class, 'index'])
                ->name('ponto-jornada.index');
            Route::post('/ponto-jornada/ajustes', [PontoJornadaController::class, 'storeAjuste'])
                ->name('ponto-jornada.ajustes.store');
        });

    // Orçamentos
    Route::resource('orcamentos', OrcamentoController::class)
        ->middleware('dashboard.comercial');

    Route::patch('/orcamentos/{orcamento}/status', [OrcamentoController::class, 'updateStatus'])
        ->name('orcamentos.updateStatus');

    Route::post('/orcamentos/{orcamento}/agendar-tecnico', [AgendaTecnicaController::class, 'agendarOrcamento'])
        ->name('orcamentos.agendar-tecnico');

    Route::get('/orcamentos/{id}/imprimir', [OrcamentoController::class, 'imprimir'])
        ->name('orcamentos.imprimir');

    Route::post('/orcamentos/{orcamento}/duplicar', [OrcamentoController::class, 'duplicate'])
        ->name('orcamentos.duplicate');

    // Serviços e Produtos
    Route::get('/itemcomercial/buscar', [ItemComercialController::class, 'buscar'])
        ->middleware(['auth', 'dashboard.comercial'])
        ->name('itemcomercial.buscar');

    Route::resource('itemcomercial', ItemComercialController::class)
        ->parameters([
            'itemcomercial' => 'item_comercial',
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
    // Busca unificada Cliente + Pré-Cliente (Orçamentos)
    Route::get('/busca-clientes', [BuscaClienteController::class, 'buscar'])
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

    // Categorias Financeiras
    Route::get('/categorias', [\App\Http\Controllers\CategoriaController::class, 'index'])->name('categorias.index');
    Route::get('/categorias/criar', [\App\Http\Controllers\CategoriaController::class, 'create'])->name('categorias.create');
    Route::post('/categorias', [\App\Http\Controllers\CategoriaController::class, 'storeCategoria'])->name('categorias.store');
    Route::get('/categorias/{categoria}/editar', [\App\Http\Controllers\CategoriaController::class, 'edit'])->name('categorias.edit');
    Route::put('/categorias/{categoria}', [\App\Http\Controllers\CategoriaController::class, 'updateCategoria'])->name('categorias.update');
    Route::delete('/categorias/{categoria}', [\App\Http\Controllers\CategoriaController::class, 'destroyCategoria'])->name('categorias.destroy');

    // Subcategorias
    Route::get('/subcategorias/criar', [\App\Http\Controllers\CategoriaController::class, 'createSubcategoria'])->name('subcategorias.create');
    Route::post('/subcategorias', [\App\Http\Controllers\CategoriaController::class, 'storeSubcategoria'])->name('subcategorias.store');
    Route::get('/subcategorias/{subcategoria}/editar', [\App\Http\Controllers\CategoriaController::class, 'editSubcategoria'])->name('subcategorias.edit');
    Route::put('/subcategorias/{subcategoria}', [\App\Http\Controllers\CategoriaController::class, 'updateSubcategoria'])->name('subcategorias.update');
    Route::delete('/subcategorias/{subcategoria}', [\App\Http\Controllers\CategoriaController::class, 'destroySubcategoria'])->name('subcategorias.destroy');

    // Contas
    Route::get('/contas/criar', [\App\Http\Controllers\CategoriaController::class, 'createConta'])->name('contas.create');
    Route::post('/contas', [\App\Http\Controllers\CategoriaController::class, 'storeConta'])->name('contas.store');
    Route::get('/contas/{conta}/editar', [\App\Http\Controllers\CategoriaController::class, 'editConta'])->name('contas.edit');
    Route::put('/contas/{conta}', [\App\Http\Controllers\CategoriaController::class, 'updateConta'])->name('contas.update');
    Route::delete('/contas/{conta}', [\App\Http\Controllers\CategoriaController::class, 'destroyConta'])->name('contas.destroy');

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

    Route::get('/agenda-tecnica/disponibilidade', [AgendaTecnicaController::class, 'disponibilidade'])
        ->name('agenda-tecnica.disponibilidade');

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

        // Cancelar agendamento de cobrança
        Route::delete(
            '/cancelar-agendamento/{orcamento}',
            [FinanceiroController::class, 'cancelarAgendamento']
        )->name('cancelar-agendamento');

        // Home Financeiro (página de cards)
        Route::get('/home', function () {
            return view('financeiro.home');
        })->name('home');

        // Dashboard financeiro (redireciona para home)
        Route::get('/', function () {
            return redirect()->route('financeiro.home');
        })->name('index');

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

        Route::patch(
            '/contas-a-receber/baixa-multipla',
            [ContasReceberController::class, 'pagarMultiplas']
        )->name('contasareceber.baixa-multipla');

        Route::delete(
            '/contas-a-receber/{cobranca}',
            [ContasReceberController::class, 'destroy']
        )->name('contasareceber.destroy');

        // Contas Fixas
        Route::post(
            '/contas-fixas',
            [ContasReceberController::class, 'storeContaFixa']
        )->middleware('rate.forms')->name('contas-fixas.store');

        Route::get(
            '/contas-fixas/{contaFixa}',
            [ContasReceberController::class, 'getContaFixa']
        )->name('contas-fixas.get');

        Route::put(
            '/contas-fixas/{contaFixa}',
            [ContasReceberController::class, 'updateContaFixa']
        )->middleware('rate.forms')->name('contas-fixas.update');

        // Agendar cobrança de orçamento
        Route::post(
            '/agendar-cobranca/{orcamento}',
            [FinanceiroController::class, 'agendarCobranca']
        )->middleware('rate.forms')->name('agendar-cobranca');

        // Pipeline do orçamento
        Route::post(
            '/orcamentos/{orcamento}/gerar-cobranca',
            [FinanceiroController::class, 'gerarCobranca']
        )->middleware('rate.forms')->name('orcamentos.gerar-cobranca');

        // Anexos de Cobrança
        Route::post(
            '/cobrancas/{cobranca}/anexos',
            [ContasReceberController::class, 'uploadAnexo']
        )->middleware('rate.uploads')->name('cobrancas.anexos.upload');

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
            ->middleware('rate.forms')
            ->name('contasapagar.store');

        Route::get('/contas-a-pagar/{conta}', [ContasPagarController::class, 'show'])
            ->name('contasapagar.show');

        Route::put('/contas-a-pagar/{conta}', [ContasPagarController::class, 'update'])
            ->middleware('rate.forms')
            ->name('contasapagar.update');

        Route::patch('/contas-a-pagar/{conta}/pagar', [ContasPagarController::class, 'marcarComoPago'])
            ->middleware('rate.forms')
            ->name('contasapagar.pagar');

        // Baixa múltipla de contas a pagar
        Route::patch(
            '/contas-a-pagar/baixa-multipla',
            [ContasPagarController::class, 'pagarMultiplas']
        )->middleware('rate.forms')->name('contasapagar.baixa-multipla');

        Route::patch('/contas-a-pagar/{conta}/estornar', [ContasPagarController::class, 'estornar'])
            ->middleware('rate.forms')
            ->name('contasapagar.estornar');

        Route::delete('/contas-a-pagar/{conta}', [ContasPagarController::class, 'destroy'])
            ->middleware('rate.forms')
            ->name('contasapagar.destroy');

        // Anexos de Contas a Pagar
        Route::get('/contas-pagar/{conta}/anexos', [ContasPagarController::class, 'getAnexos'])
            ->name('financeiro.contas-pagar.anexos.index');

        Route::post('/contas-pagar/{conta}/anexos', [ContasPagarController::class, 'storeAnexo'])
            ->middleware('rate.uploads')
            ->name('financeiro.contas-pagar.anexos.store');

        Route::delete('/contas-pagar/anexos/{anexo}', [ContasPagarController::class, 'destroyAnexo'])
            ->name('financeiro.contas-pagar.anexos.destroy');

        Route::get('/contas-pagar/anexos/{anexo}/download', [ContasPagarController::class, 'downloadAnexo'])
            ->name('financeiro.contas-pagar.anexos.download');

        // Contas Fixas a Pagar
        Route::post('/contas-fixas-pagar', [ContasPagarController::class, 'storeContaFixa'])
            ->middleware('rate.forms')
            ->name('contasapagar.storeContaFixa');

        Route::get('/contas-fixas-pagar', [ContasPagarController::class, 'contasFixas'])
            ->name('contasapagar.contasFixas');

        Route::get('/contas-fixas-pagar/{contaFixa}', [ContasPagarController::class, 'showContaFixa'])
            ->name('contasapagar.showContaFixa');

        Route::put('/contas-fixas-pagar/{contaFixa}', [ContasPagarController::class, 'updateContaFixa'])
            ->middleware('rate.forms')
            ->name('contasapagar.updateContaFixa');

        Route::patch('/contas-fixas-pagar/{contaFixa}/desativar', [ContasPagarController::class, 'desativarContaFixa'])
            ->middleware('rate.forms')
            ->name('contasapagar.desativarContaFixa');

        Route::patch('/contas-fixas-pagar/{contaFixa}/ativar', [ContasPagarController::class, 'ativarContaFixa'])
            ->middleware('rate.forms')
            ->name('contasapagar.ativarContaFixa');

        // Ajuste Manual, Transferência e Injeção de Receita
        Route::post('/contas-financeiras/ajuste-manual', [FinanceiroController::class, 'ajusteManual'])
            ->name('contas-financeiras.ajuste-manual');
        Route::post('/contas-financeiras/transferencia', [FinanceiroController::class, 'transferencia'])
            ->name('contas-financeiras.transferencia');
        Route::post('/contas-financeiras/injecao-receita', [FinanceiroController::class, 'injecaoReceita'])
            ->name('contas-financeiras.injecao-receita');
    });

/*
    |--------------------------------------------------------------------------
    | FORNECEDORES
    |--------------------------------------------------------------------------
    */
Route::middleware(['auth'])->group(function () {
    Route::resource('fornecedores', FornecedorController::class)->parameters([
        'fornecedores' => 'fornecedor',
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
    Route::get('/portal/atendimentos', [PortalController::class, 'atendimentos'])
        ->name('portal.atendimentos');

    Route::get('/portal/chamado/novo', [PortalController::class, 'novoChamado'])
        ->name('portal.chamado.novo');

    Route::post('/portal/chamado/novo', [PortalController::class, 'storeChamado'])
        ->name('portal.chamado.store');

    Route::get('/portal', [PortalController::class, 'index'])
        ->name('portal.index');

    Route::get('/portal/financeiro', [PortalController::class, 'financeiro'])
        ->name('portal.financeiro');

    Route::get('/portal/boletos', [PortalController::class, 'boletos'])
        ->name('portal.boletos');

    Route::get('/portal/notas', [PortalController::class, 'notas'])
        ->name('portal.notas');

    Route::get('/portal/unidade', [PortalController::class, 'selecionarUnidade'])
        ->name('portal.unidade');

    Route::post('/portal/unidade', [PortalController::class, 'definirUnidade'])
        ->name('portal.unidade.definir');

    Route::post('/portal/trocar-unidade', [PortalController::class, 'trocarUnidade'])
        ->name('portal.trocar-unidade');

    // Equipamentos
    Route::get('/portal/equipamentos', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'index'])
        ->name('portal.equipamentos.index');

    Route::get('/portal/equipamentos/lista', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'lista'])
        ->name('portal.equipamentos.lista');

    Route::get('/portal/equipamentos/setores', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'setores'])
        ->name('portal.equipamentos.setores');

    Route::get('/portal/equipamentos/responsaveis', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'responsaveis'])
        ->name('portal.equipamentos.responsaveis');

    Route::get('/portal/equipamentos/pmoc', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'pmoc'])
        ->name('portal.equipamentos.pmoc');

    Route::get('/portal/equipamentos/{equipamento}', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'show'])
        ->name('portal.equipamentos.show');

    Route::post('/portal/equipamentos/{equipamento}/manutencao', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'registrarManutencao'])
        ->name('portal.equipamentos.manutencao.store');

    Route::post('/portal/equipamentos/{equipamento}/limpeza', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'registrarLimpeza'])
        ->name('portal.equipamentos.limpeza.store');

    Route::get('/portal/equipamentos/{equipamento}/qrcode', [\App\Http\Controllers\Portal\EquipamentoPortalController::class, 'qrcode'])
        ->name('portal.equipamentos.qrcode');

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
        Route::post('/atendimento/{atendimento}/incluir', [PortalFuncionarioController::class, 'incluirAtendimento'])->name('atendimento.incluir');

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
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            ],
        ], [
            'password.regex' => 'A senha deve conter pelo menos 8 caracteres, uma letra maiúscula, uma minúscula e um número.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
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
                    ? 'financeiro.dashboard'
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
Route::middleware(['throttle:10,1'])->group(function () {

    Route::get('/api/cnpj/{cnpj}', function ($cnpj) {

        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'CNPJ inválido',
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
                'message' => 'Erro ao consultar Receita Federal',
            ], 500);
        }
    });
});

require __DIR__.'/auth.php';
