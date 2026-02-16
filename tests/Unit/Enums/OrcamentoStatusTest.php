<?php

namespace Tests\Unit\Enums;

use App\Enums\OrcamentoStatus;
use PHPUnit\Framework\TestCase;

class OrcamentoStatusTest extends TestCase
{
    public function test_label_para_cada_status(): void
    {
        $this->assertEquals('Rascunho', OrcamentoStatus::RASCUNHO->label());
        $this->assertEquals('Enviado', OrcamentoStatus::ENVIADO->label());
        $this->assertEquals('Aprovado', OrcamentoStatus::APROVADO->label());
        $this->assertEquals('Rejeitado', OrcamentoStatus::REJEITADO->label());
        $this->assertEquals('Financeiro', OrcamentoStatus::FINANCEIRO->label());
        $this->assertEquals('Executando', OrcamentoStatus::EXECUTANDO->label());
        $this->assertEquals('Concluído', OrcamentoStatus::CONCLUIDO->label());
        $this->assertEquals('Cancelado', OrcamentoStatus::CANCELADO->label());
    }

    public function test_values_retorna_array_de_valores(): void
    {
        $values = OrcamentoStatus::values();

        $this->assertContains('rascunho', $values);
        $this->assertContains('enviado', $values);
        $this->assertContains('aprovado', $values);
        $this->assertCount(8, $values);
    }

    public function test_transição_válida_rascunho_para_enviado(): void
    {
        $this->assertTrue(
            OrcamentoStatus::RASCUNHO->podeTransicionarPara(OrcamentoStatus::ENVIADO)
        );
    }

    public function test_transição_válida_rascunho_para_cancelado(): void
    {
        $this->assertTrue(
            OrcamentoStatus::RASCUNHO->podeTransicionarPara(OrcamentoStatus::CANCELADO)
        );
    }

    public function test_transição_inválida_rascunho_para_aprovado(): void
    {
        $this->assertFalse(
            OrcamentoStatus::RASCUNHO->podeTransicionarPara(OrcamentoStatus::APROVADO)
        );
    }

    public function test_transição_inválida_cancelado_para_qualquer(): void
    {
        $this->assertFalse(
            OrcamentoStatus::CANCELADO->podeTransicionarPara(OrcamentoStatus::RASCUNHO)
        );
        $this->assertFalse(
            OrcamentoStatus::CANCELADO->podeTransicionarPara(OrcamentoStatus::ENVIADO)
        );
    }

    public function test_transição_concluido_nao_pode_mudar(): void
    {
        $this->assertFalse(
            OrcamentoStatus::CONCLUIDO->podeTransicionarPara(OrcamentoStatus::CANCELADO)
        );
    }

    public function test_fluxo_completo_válido(): void
    {
        $status = OrcamentoStatus::RASCUNHO;

        $this->assertTrue($status->podeTransicionarPara(OrcamentoStatus::ENVIADO));

        $status = OrcamentoStatus::ENVIADO;
        $this->assertTrue($status->podeTransicionarPara(OrcamentoStatus::APROVADO));

        $status = OrcamentoStatus::APROVADO;
        $this->assertTrue($status->podeTransicionarPara(OrcamentoStatus::FINANCEIRO));

        $status = OrcamentoStatus::FINANCEIRO;
        $this->assertTrue($status->podeTransicionarPara(OrcamentoStatus::EXECUTANDO));

        $status = OrcamentoStatus::EXECUTANDO;
        $this->assertTrue($status->podeTransicionarPara(OrcamentoStatus::CONCLUIDO));
    }
}
