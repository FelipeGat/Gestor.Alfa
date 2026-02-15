<?php

namespace Tests\Unit\Services\Financeiro;

use App\Models\Cobranca;
use App\Models\ContaFinanceira;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Services\Financeiro\ContaReceberService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContaReceberServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ContaReceberService();
        Cache::flush();
    }

    public function test_calcula_corretamente_o_total_a_receber()
    {
        // Arrange
        Cobranca::factory()->count(3)->create([
            'status' => 'em_aberto',
            'valor' => 150.00,
            'data_vencimento' => now()->addDays(5),
        ]);

        // Act
        $kpis = $this->service->calcularKPIs();

        // Assert
        $this->assertEquals(450.00, $kpis['a_receber']);
    }

    public function test_calcula_corretamente_o_total_recebido_no_mes()
    {
        // Arrange
        Cobranca::factory()->count(2)->create([
            'status' => 'pago',
            'valor' => 200.00,
            'data_vencimento' => now()->startOfMonth(),
            'data_pagamento' => now(),
        ]);

        // Act
        $kpis = $this->service->calcularKPIs();

        // Assert
        $this->assertEquals(400.00, $kpis['recebido']);
    }

    public function test_calcula_corretamente_o_total_vencido()
    {
        // Arrange
        Cobranca::factory()->create([
            'status' => 'em_aberto',
            'valor' => 250.00,
            'data_vencimento' => now()->subDays(5),
        ]);

        // Act
        $kpis = $this->service->calcularKPIs();

        // Assert
        $this->assertEquals(250.00, $kpis['vencido']);
    }

    public function test_calcula_corretamente_o_total_que_vence_hoje()
    {
        // Arrange
        Cobranca::factory()->create([
            'status' => 'em_aberto',
            'valor' => 300.00,
            'data_vencimento' => now(),
        ]);

        // Act
        $kpis = $this->service->calcularKPIs();

        // Assert
        $this->assertEquals(300.00, $kpis['recebe_hoje']);
    }

    public function test_utiliza_cache_para_melhorar_performance()
    {
        // Arrange
        Cobranca::factory()->create(['status' => 'em_aberto', 'valor' => 100.00]);
        
        // Act - First call
        $this->service->calcularKPIs();
        
        // Add new record
        Cobranca::factory()->create(['status' => 'em_aberto', 'valor' => 100.00]);
        
        // Act - Second call (should return cached value)
        $kpis = $this->service->calcularKPIs();
        
        // Assert - Should return cached value (only 100, not 200)
        $this->assertEquals(100.00, $kpis['a_receber']);
    }

    public function test_conta_corretamente_cobrancas_em_aberto()
    {
        // Arrange
        Cobranca::factory()->count(5)->create(['status' => 'em_aberto']);
        Cobranca::factory()->count(3)->create(['status' => 'pago']);

        // Act
        $contadores = $this->service->contarPorStatus();

        // Assert
        $this->assertEquals(5, $contadores['em_aberto']);
        $this->assertEquals(3, $contadores['pago']);
    }

    public function test_conta_corretamente_cobrancas_vencidas()
    {
        // Arrange
        Cobranca::factory()->count(2)->create([
            'status' => 'em_aberto',
            'data_vencimento' => now()->subDays(1),
        ]);
        Cobranca::factory()->count(3)->create([
            'status' => 'em_aberto',
            'data_vencimento' => now()->addDays(1),
        ]);

        // Act
        $contadores = $this->service->contarPorStatus();

        // Assert
        $this->assertEquals(2, $contadores['vencido']);
        $this->assertEquals(5, $contadores['em_aberto']);
    }

    public function test_cria_uma_nova_cobranca()
    {
        // Arrange
        $dados = [
            'descricao' => 'Cobrança Teste',
            'valor' => 500.00,
            'data_vencimento' => now()->addDays(15),
            'status' => 'em_aberto',
        ];

        // Act
        $cobranca = $this->service->criar($dados);

        // Assert
        $this->assertInstanceOf(Cobranca::class, $cobranca);
        $this->assertEquals('Cobrança Teste', $cobranca->descricao);
        $this->assertEquals(500.00, $cobranca->valor);
        $this->assertEquals(1, Cobranca::count());
    }

    public function test_limpa_o_cache_ao_criar_nova_cobranca()
    {
        // Arrange
        Cache::put('contas_receber_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);
        
        // Act
        $this->service->criar([
            'descricao' => 'Teste',
            'valor' => 100.00,
            'data_vencimento' => now(),
            'status' => 'em_aberto',
        ]);

        // Assert
        $this->assertNull(Cache::get('contas_receber_kpis_' . date('Y-m-d')));
    }

    public function test_atualiza_uma_cobranca_existente()
    {
        // Arrange
        $cobranca = Cobranca::factory()->create([
            'descricao' => 'Cobrança Antiga',
            'valor' => 100.00,
        ]);

        // Act
        $resultado = $this->service->atualizar($cobranca->id, [
            'descricao' => 'Cobrança Nova',
            'valor' => 250.00,
        ]);

        // Assert
        $this->assertEquals('Cobrança Nova', $resultado->descricao);
        $this->assertEquals(250.00, $resultado->valor);
        $this->assertEquals('Cobrança Nova', Cobranca::find($cobranca->id)->descricao);
    }

    public function test_retorna_null_quando_cobranca_nao_existe_na_atualizacao()
    {
        // Act
        $resultado = $this->service->atualizar(9999, ['descricao' => 'Teste']);

        // Assert
        $this->assertNull($resultado);
    }

    public function test_limpa_o_cache_ao_atualizar()
    {
        // Arrange
        $cobranca = Cobranca::factory()->create();
        Cache::put('contas_receber_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);
        
        // Act
        $this->service->atualizar($cobranca->id, ['descricao' => 'Novo']);

        // Assert
        $this->assertNull(Cache::get('contas_receber_kpis_' . date('Y-m-d')));
    }

    public function test_exclui_uma_cobranca_existente()
    {
        // Arrange
        $cobranca = Cobranca::factory()->create();

        // Act
        $resultado = $this->service->excluir($cobranca->id);

        // Assert
        $this->assertTrue($resultado);
        $this->assertEquals(0, Cobranca::count());
    }

    public function test_retorna_false_quando_cobranca_nao_existe_na_exclusao()
    {
        // Act
        $resultado = $this->service->excluir(9999);

        // Assert
        $this->assertFalse($resultado);
    }

    public function test_limpa_o_cache_ao_excluir()
    {
        // Arrange
        $cobranca = Cobranca::factory()->create();
        Cache::put('contas_receber_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);
        
        // Act
        $this->service->excluir($cobranca->id);

        // Assert
        $this->assertNull(Cache::get('contas_receber_kpis_' . date('Y-m-d')));
    }

    public function test_recebe_multiplas_cobrancas()
    {
        // Arrange
        $cobranca1 = Cobranca::factory()->create(['status' => 'em_aberto', 'valor' => 100.00]);
        $cobranca2 = Cobranca::factory()->create(['status' => 'em_aberto', 'valor' => 200.00]);
        $contaFinanceira = ContaFinanceira::factory()->create(['saldo' => 1000.00]);

        // Act
        $this->service->receber(
            [$cobranca1->id, $cobranca2->id],
            [
                'conta_financeira_id' => $contaFinanceira->id,
                'forma_pagamento' => 'pix',
                'data_pagamento' => now(),
            ]
        );

        // Assert
        $this->assertEquals('pago', Cobranca::find($cobranca1->id)->status);
        $this->assertEquals('pago', Cobranca::find($cobranca2->id)->status);
        $this->assertEquals(1300.00, ContaFinanceira::find($contaFinanceira->id)->saldo);
    }

    public function test_recebe_uma_unica_cobranca()
    {
        // Arrange
        $cobranca = Cobranca::factory()->create(['status' => 'em_aberto', 'valor' => 500.00]);
        $contaFinanceira = ContaFinanceira::factory()->create(['saldo' => 200.00]);

        // Act
        $this->service->receberUma(
            $cobranca,
            [
                'conta_financeira_id' => $contaFinanceira->id,
                'forma_pagamento' => 'boleto',
                'data_pagamento' => now(),
            ]
        );

        // Assert
        $this->assertEquals('pago', Cobranca::find($cobranca->id)->status);
        $this->assertEquals(700.00, ContaFinanceira::find($contaFinanceira->id)->saldo);
    }

    public function test_estorna_cobranca_recebida()
    {
        // Arrange
        $contaFinanceira = ContaFinanceira::factory()->create(['saldo' => 500.00]);
        $cobranca = Cobranca::factory()->create([
            'status' => 'pago',
            'valor' => 200.00,
            'conta_financeira_id' => $contaFinanceira->id,
            'data_pagamento' => now(),
        ]);

        // Act
        $this->service->estornar($cobranca);

        // Assert
        $this->assertEquals('em_aberto', Cobranca::find($cobranca->id)->status);
        $this->assertEquals(300.00, ContaFinanceira::find($contaFinanceira->id)->saldo);
    }

    public function test_limpa_o_cache_ao_receber_cobrancas()
    {
        // Arrange
        $cobranca = Cobranca::factory()->create(['status' => 'em_aberto']);
        Cache::put('contas_receber_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);

        // Act
        $this->service->receber(
            [$cobranca->id],
            [
                'conta_financeira_id' => null,
                'forma_pagamento' => 'pix',
                'data_pagamento' => now(),
            ]
        );

        // Assert
        $this->assertNull(Cache::get('contas_receber_kpis_' . date('Y-m-d')));
    }

    public function test_limpa_o_cache_ao_estornar_cobranca()
    {
        // Arrange
        $cobranca = Cobranca::factory()->create(['status' => 'pago']);
        Cache::put('contas_receber_kpis_' . date('Y-m-d'), ['test' => 'value'], 300);

        // Act
        $this->service->estornar($cobranca);

        // Assert
        $this->assertNull(Cache::get('contas_receber_kpis_' . date('Y-m-d')));
    }
}
