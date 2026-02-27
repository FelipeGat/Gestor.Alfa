<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioComercialTest extends TestCase
{
    use RefreshDatabase;

    public function test_relatorio_comercial_exige_autenticacao(): void
    {
        $response = $this->get(route('relatorios.comercial'));

        $response->assertRedirect(route('login'));
    }

    public function test_relatorio_comercial_renderiza_para_usuario_admin(): void
    {
        $user = User::factory()->create([
            'tipo' => 'admin',
            'primeiro_acesso' => false,
        ]);

        $response = $this->actingAs($user)->get(route('relatorios.comercial'));

        $response->assertOk();
        $response->assertSee('Relatório Comercial');
        $response->assertSee('Pipeline Comercial');
    }
}
