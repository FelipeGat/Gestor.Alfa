<?php

namespace Tests\Unit\Services\Financeiro;

use App\Models\ContaPagar;
use App\Models\ContaFinanceira;
use App\Models\Fornecedor;
use App\Models\CentroCusto;
use App\Services\Financeiro\ContaPagarService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContaPagarServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ContaPagarService();
        Cache::flush();
    }

    public function test_calcula_corretamente_o_total_a_pagar()
    {
        // Arrange
        ContaPagar::factory()->count(3)->create([
            'status' => 'em_aberto',
            'valor' => 100.00,
            'data_vencimento' => now()->addDays(5),
        ]);

        // Act
        $kpis = $this->service->calcularKPIs();

        // Assert
        $this->assertEquals(300.00, $kpis['a_pagar']);
    }

    public function test_calcula_corretamente_o_total_pago_no_mes()
    {
        // Arrange
        ContaPagar::factory()->count(2)->create([
            'status' => 'pago',
            'valor' => 150.00,
            'data_vencimento' => now()->startOfMonth(),
            'data_pagamento' => now(),
        ]);

        // Act
        $kpis = $this->service->calcularKPIs();

        // Assert
        $this->assertEquals(300.00, $kpis['pago']);
    }

    public function test_calcula_corretamente_o_total_vencido()
    {
        // Arrange
        ContaPagar::factory()->create([
            'status' => 'em_aberto',
            'valor' => 200.00,
            'data_vencimento' => now()->subDays(5),
        ]);

        // Act
        $kpis = $this->service->calcularKPIs();

        // Assert
        $this->assertEquals(200.00, $kpis['vencido']);
    }

    public function test_utiliza_cache_para_melhorar_performance()
    {
        // Arrange
        ContaPagar::factory()->create(['status' => 'em_aberto', 'valor' => 100.00]);
        
        // Act - First call
        $this->service->calcularKPIs();
        
        // Add new record
        ContaPagar::factory()->create(['status' => 'em_aberto', 'valor' => 100.00]);
        
        // Act - Second call (should return cached value)
        $kpis = $this->service->calcularKPIs();
        
        // Assert - Should return cached value (only 100, not 200)
        $this->assertEquals(100.00, $kpis['a_pagar']);
    }

    public function test_conta_corretamente_contas_em_aberto()
    {
        // Arrange
        ContaPagar::factory()->count(5)->create(['status' => 'em_aberto']);
        ContaPagar::factory()->count(3)->create(['status' => 'pago']);

        // Act
        $contadores = $this->service->contarPorStatus();

        // Assert
        $this->assertEquals(5, $contadores['em_aberto']);
        $this->assertEquals(3, $contadores['pago']);
    }

    public function test_cria_uma_nova_conta_a_pagar()
    {
        // Arrange
        $dados = [
            'descricao' => 'Conta de Luz',
            'valor' => 150.00,
            'data_vencimento' => now()->addDays(10),
            'status' => 'em_aberto',
        ];

        // Act
        $conta = $this->service->criar($dados);

        // Assert
        $this->assertInstanceOf(ContaPagar::class, $conta);
        $this->assertEquals('Conta de Luz', $conta->descricao);
        $this->assertEquals(150.00, $conta->valor);
        $this->assertEquals(1, ContaPagar::count());
    }

    public function test_limpa_o_cache_ao_criar_nova_conta()
    {
        // Arrange
        Cache::put('contas_pagar_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);
        
        // Act
        $this->service->criar([
            'descricao' => 'Teste',
            'valor' => 100.00,
            'data_vencimento' => now(),
            'status' => 'em_aberto',
        ]);

        // Assert
        $this->assertNull(Cache::get('contas_pagar_kpis_' . date('Y-m-d')));
    }

    public function test_atualiza_uma_conta_existente()
    {
        // Arrange
        $conta = ContaPagar::factory()->create([
            'descricao' => 'Conta Antiga',
            'valor' => 100.00,
        ]);

        // Act
        $resultado = $this->service->atualizar($conta->id, [
            'descricao' => 'Conta Nova',
            'valor' => 200.00,
        ]);

        // Assert
        $this->assertEquals('Conta Nova', $resultado->descricao);
        $this->assertEquals(200.00, $resultado->valor);
        $this->assertEquals('Conta Nova', ContaPagar::find($conta->id)->descricao);
    }

    public function test_retorna_null_quando_conta_nao_existe_na_atualizacao()
    {
        // Act
        $resultado = $this->service->atualizar(9999, ['descricao' => 'Teste']);

        // Assert
        $this->assertNull($resultado);
    }

    public function test_limpa_o_cache_ao_atualizar()
    {
        // Arrange
        $conta = ContaPagar::factory()->create();
        Cache::put('contas_pagar_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);
        
        // Act
        $this->service->atualizar($conta->id, ['descricao' => 'Novo']);

        // Assert
        $this->assertNull(Cache::get('contas_pagar_kpis_' . date('Y-m-d')));
    }

    public function test_exclui_uma_conta_existente()
    {
        // Arrange
        $conta = ContaPagar::factory()->create();

        // Act
        $resultado = $this->service->excluir($conta->id);

        // Assert
        $this->assertTrue($resultado);
        $this->assertEquals(0, ContaPagar::count());
    }

    public function test_retorna_false_quando_conta_nao_existe_na_exclusao()
    {
        // Act
        $resultado = $this->service->excluir(9999);

        // Assert
        $this->assertFalse($resultado);
    }

    public function test_limpa_o_cache_ao_excluir()
    {
        // Arrange
        $conta = ContaPagar::factory()->create();
        Cache::put('contas_pagar_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);
        
        // Act
        $this->service->excluir($conta->id);

        // Assert
        $this->assertNull(Cache::get('contas_pagar_kpis_' . date('Y-m-d')));
    }

    public function test_marca_multiplas_contas_como_pagas()
    {
        // Arrange
        $conta1 = ContaPagar::factory()->create(['status' => 'em_aberto', 'valor' => 100.00]);
        $conta2 = ContaPagar::factory()->create(['status' => 'em_aberto', 'valor' => 200.00]);
        $contaFinanceira = ContaFinanceira::factory()->create(['saldo' => 500.00]);

        // Act
        $this->service->pagar(
            [$conta1->id, $conta2->id],
            [
                'conta_financeira_id' => $contaFinanceira->id,
                'forma_pagamento' => 'pix',
                'data_pagamento' => now(),
            ]
        );

        // Assert
        $this->assertEquals('pago', ContaPagar::find($conta1->id)->status);
        $this->assertEquals('pago', ContaPagar::find($conta2->id)->status);
        $this->assertEquals(200.00, ContaFinanceira::find($contaFinanceira->id)->saldo);
    }

    public function test_limpa_o_cache_ao_realizar_pagamentos()
    {
        // Arrange
        $conta = ContaPagar::factory()->create(['status' => 'em_aberto']);
        Cache::put('contas_pagar_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);

        // Act
        $this->service->pagar(
            [$conta->id],
            [
                'conta_financeira_id' => null,
                'forma_pagamento' => 'pix',
                'data_pagamento' => now(),
            ]
        );

        // Assert
        $this->assertNull(Cache::get('contas_pagar_kpis_' . date('Y-m-d')));
    }
}
