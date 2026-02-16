<?php

namespace Tests\Unit\Enums;

use App\Enums\CobrancaStatus;
use PHPUnit\Framework\TestCase;

class CobrancaStatusTest extends TestCase
{
    public function test_label_para_cada_status(): void
    {
        $this->assertEquals('Pendente', CobrancaStatus::PENDENTE->label());
        $this->assertEquals('Pago', CobrancaStatus::PAGO->label());
        $this->assertEquals('Vencido', CobrancaStatus::VENCIDO->label());
        $this->assertEquals('Cancelado', CobrancaStatus::CANCELADO->label());
        $this->assertEquals('Em Aberto', CobrancaStatus::EM_ABERTO->label());
    }

    public function test_values_retorna_array_de_valores(): void
    {
        $values = CobrancaStatus::values();

        $this->assertContains('pendente', $values);
        $this->assertContains('pago', $values);
        $this->assertContains('vencido', $values);
        $this->assertCount(5, $values);
    }

    public function test_is_pago_retorna_true(): void
    {
        $this->assertTrue(CobrancaStatus::PAGO->isPago());
        $this->assertFalse(CobrancaStatus::PENDENTE->isPago());
        $this->assertFalse(CobrancaStatus::VENCIDO->isPago());
    }

    public function test_is_pendente_retorna_true(): void
    {
        $this->assertTrue(CobrancaStatus::PENDENTE->isPendente());
        $this->assertFalse(CobrancaStatus::PAGO->isPendente());
    }

    public function test_is_vencido_retorna_true(): void
    {
        $this->assertTrue(CobrancaStatus::VENCIDO->isVencido());
        $this->assertFalse(CobrancaStatus::PAGO->isVencido());
    }
}
