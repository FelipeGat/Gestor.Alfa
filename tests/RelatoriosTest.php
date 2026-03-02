<?php

namespace Tests;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RelatoriosTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $tabelasObrigatorias = [
            'users',
            'empresas',
            'contas_pagar',
            'cobrancas',
            'orcamentos',
            'centros_custo',
            'contas_financeiras',
        ];

        foreach ($tabelasObrigatorias as $tabela) {
            if (! Schema::hasTable($tabela)) {
                $this->markTestSkipped('Schema legado de relatórios não disponível no ambiente de teste atual.');
            }
        }

        $this->user = User::factory()->create([
            'tipo' => 'admin',
            'primeiro_acesso' => false,
        ]);
    }

    public function test_endpoints_relatorios_retorna_http_200_empresa_sem_dados(): void
    {
        $empresa = Empresa::query()->create([
            'razao_social' => 'Empresa Teste Relatórios',
            'nome_fantasia' => 'Teste Relatórios',
            'cnpj' => '12.345.678/0001-99',
        ]);

        $params = [
            'empresa_id' => $empresa->id,
            'data_inicio' => '2026-01-01',
            'data_fim' => '2026-01-31',
        ];

        $endpoints = [
            '/api/relatorios/financeiro',
            '/api/relatorios/tecnico',
            '/api/relatorios/comercial',
            '/api/relatorios/rh',
            '/api/relatorios/painel-executivo',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($this->user)->getJson($endpoint.'?'.http_build_query($params));

            $response->assertOk();
            $response->assertJsonStructure([
                'periodo' => ['data_inicio', 'data_fim'],
            ]);
        }
    }

    public function test_financeiro_com_centro_custo_periodo_unico_e_campos_numericos(): void
    {
        $empresa = Empresa::query()->create([
            'razao_social' => 'Empresa Centro Custo',
            'nome_fantasia' => 'Centro Custo',
            'cnpj' => '12.345.678/0002-70',
        ]);

        DB::table('centros_custo')->insert([
            'id' => 990001,
            'nome' => 'Centro A',
            'tipo' => 'GRUPO',
            'empresa_id' => $empresa->id,
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('contas_financeiras')->insert([
            'id' => 990001,
            'empresa_id' => $empresa->id,
            'nome' => 'Conta Teste',
            'tipo' => 'CONTA_CORRENTE',
            'saldo' => 2500.00,
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('orcamentos')->insert([
            'id' => 990001,
            'empresa_id' => $empresa->id,
            'numero_orcamento' => '990001/2026',
            'status' => 'aprovado',
            'valor_total' => 1000.00,
            'centro_custo_id' => 990001,
            'created_at' => '2026-01-15 10:00:00',
            'updated_at' => '2026-01-15 10:00:00',
        ]);

        DB::table('cobrancas')->insert([
            'orcamento_id' => 990001,
            'descricao' => 'Receita teste',
            'valor' => 1000.00,
            'juros_multa' => 0,
            'data_vencimento' => '2026-01-15',
            'status' => 'pago',
            'data_pagamento' => '2026-01-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('contas_pagar')->insert([
            'centro_custo_id' => 990001,
            'conta_id' => 1,
            'conta_financeira_id' => 990001,
            'orcamento_id' => 990001,
            'descricao' => 'Despesa teste',
            'valor' => 300.00,
            'juros_multa' => 0,
            'data_vencimento' => '2026-01-15',
            'status' => 'pago',
            'data_pagamento' => '2026-01-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/relatorios/financeiro?'.http_build_query([
            'empresa_id' => $empresa->id,
            'centro_custo_id' => 990001,
            'data_inicio' => '2026-01-15',
            'data_fim' => '2026-01-15',
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'receita_total',
            'despesa_total',
            'lucro_liquido',
            'margem_percentual',
            'receita_por_centro_custo',
            'despesa_por_centro_custo',
            'saldo_bancario_total',
            'contas_receber_em_aberto',
            'contas_pagar_em_aberto',
            'insights_automaticos',
        ]);

        $json = $response->json();

        $this->assertIsNumeric($json['receita_total']);
        $this->assertIsNumeric($json['despesa_total']);
        $this->assertIsNumeric($json['lucro_liquido']);
        $this->assertSame(1000.0, (float) $json['receita_total']);
        $this->assertSame(300.0, (float) $json['despesa_total']);
    }

    public function test_endpoints_aceitam_periodo_cruzando_meses_e_dados_nulos(): void
    {
        $empresa = Empresa::query()->create([
            'razao_social' => 'Empresa Cruzamento',
            'nome_fantasia' => 'Cruzamento',
            'cnpj' => '12.345.678/0003-50',
        ]);

        $params = [
            'empresa_id' => $empresa->id,
            'data_inicio' => '2026-01-28',
            'data_fim' => '2026-02-03',
        ];

        $response = $this->actingAs($this->user)->getJson('/api/relatorios/painel-executivo?'.http_build_query($params));

        $response->assertOk();
        $response->assertJsonStructure([
            'receita_total',
            'despesa_total',
            'lucro',
            'crescimento_vs_mes_anterior',
            'conversao_comercial',
            'receita_por_tecnico',
            'indice_absenteismo',
            'consolidado' => [
                'financeiro',
                'tecnico',
                'comercial',
                'rh',
            ],
        ]);
    }

    public function test_validacao_parametros_obrigatorios(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/relatorios/financeiro');

        $response->assertStatus(302);
    }
}
