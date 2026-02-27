<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class RoleDashboardRedirectTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_redirects_admin_to_financeiro_dashboard(): void
    {
        $request = $this->mockLoginRequest();
        $user = new FakeUser('admin', false, ['admin']);

        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = (new AuthenticatedSessionController())->store($request);

        $this->assertSame('/financeiro/dashboard', parse_url($response->getTargetUrl(), PHP_URL_PATH));
    }

    public function test_login_redirects_comercial_to_dashboard_comercial(): void
    {
        $request = $this->mockLoginRequest();
        $user = new FakeUser('comercial', false, ['comercial']);

        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = (new AuthenticatedSessionController())->store($request);

        $this->assertSame('/dashboard-comercial', parse_url($response->getTargetUrl(), PHP_URL_PATH));
    }

    public function test_login_redirects_administrativo_to_dashboard_tecnico(): void
    {
        $request = $this->mockLoginRequest();
        $user = new FakeUser('administrativo', true, ['administrativo']);

        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = (new AuthenticatedSessionController())->store($request);

        $this->assertSame('/dashboard-tecnico', parse_url($response->getTargetUrl(), PHP_URL_PATH));
    }

    public function test_logo_redirect_targets_match_role_rules(): void
    {
        $adminHtml = $this->renderNavigationFor(new FakeUser('admin', false, ['admin']));
        $comercialHtml = $this->renderNavigationFor(new FakeUser('comercial', false, ['comercial']));
        $administrativoHtml = $this->renderNavigationFor(new FakeUser('administrativo', true, ['administrativo']));

        $this->assertSame('/financeiro/dashboard', $this->extractLogoPath($adminHtml));
        $this->assertSame('/dashboard-comercial', $this->extractLogoPath($comercialHtml));
        $this->assertSame('/dashboard-tecnico', $this->extractLogoPath($administrativoHtml));
    }

    private function mockLoginRequest(): LoginRequest
    {
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('authenticate')->once();

        $session = Mockery::mock();
        $session->shouldReceive('regenerate')->once();

        $request->shouldReceive('session')->andReturn($session);

        return $request;
    }

    private function renderNavigationFor(FakeUser $user): string
    {
        Auth::shouldReceive('user')->once()->andReturn($user);

        return view('layouts.navigation')->render();
    }

    private function extractLogoPath(string $html): string
    {
        $matched = preg_match('/<div class="flex items-center">\s*<a href="\s*(.*?)\s*"/s', $html, $matches);

        $this->assertSame(1, $matched);

        return parse_url(trim($matches[1]), PHP_URL_PATH);
    }
}

class FakeUser
{
    public string $name = 'Usuário Teste';

    public string $tipo;

    private bool $administrativo;

    private array $slugs;

    public function __construct(string $tipo, bool $administrativo, array $slugs)
    {
        $this->tipo = $tipo;
        $this->administrativo = $administrativo;
        $this->slugs = $slugs;
    }

    public function isAdmin(): bool
    {
        return in_array('admin', $this->slugs, true) || $this->tipo === 'admin';
    }

    public function isAdministrativo(): bool
    {
        return $this->administrativo || in_array('administrativo', $this->slugs, true) || $this->tipo === 'administrativo';
    }

    public function isAdminPanel(): bool
    {
        return $this->isAdmin() || $this->isAdministrativo();
    }

    public function perfis(): FakePerfisRelation
    {
        return new FakePerfisRelation($this->slugs);
    }
}

class FakePerfisRelation
{
    private array $slugs;

    private ?string $currentSlug = null;

    public function __construct(array $slugs)
    {
        $this->slugs = $slugs;
    }

    public function where(string $column, string $value): self
    {
        if ($column === 'slug') {
            $this->currentSlug = $value;
        }

        return $this;
    }

    public function exists(): bool
    {
        if ($this->currentSlug === null) {
            return false;
        }

        return in_array($this->currentSlug, $this->slugs, true);
    }
}
