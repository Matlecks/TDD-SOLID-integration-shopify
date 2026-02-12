<?php

namespace Tests\Unit\Domains\Shopify\Actions;

use App\Domains\Shopify\Actions\InstallShopifyAppAction;
use App\Domains\Shopify\Requests\InstallShopifyAppRequest;
use App\Domains\Shopify\Services\ShopifyAuthService;
use Illuminate\Http\RedirectResponse;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstallShopifyAppActionTest extends TestCase
{
    private ShopifyAuthService $authService;
    private InstallShopifyAppAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = Mockery::mock(ShopifyAuthService::class);
        $this->action = new InstallShopifyAppAction($this->authService);
    }

    #[Test]
    public function it_redirects_to_shopify_install_url()
    {
        // Arrange
        $shopDomain = 'ndpmsu-41.myshopify.com';
        $authUrl = "https://{$shopDomain}/admin/oauth/authorize?client_id=test&scope=read_products";

        $request = Mockery::mock(InstallShopifyAppRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn(['shop' => $shopDomain]);

        $this->authService->shouldReceive('buildAuthUrl')
            ->with($shopDomain)
            ->once()
            ->andReturn($authUrl);

        // Act
        $response = $this->action->__invoke($request);

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($authUrl, $response->getTargetUrl());
    }

    #[Test]
    public function it_passes_validation_before_redirect()
    {
        // Arrange
        $request = Mockery::mock(InstallShopifyAppRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn(['shop' => 'valid-shop.myshopify.com']);

        $this->authService->shouldReceive('buildAuthUrl')
            ->once()
            ->andReturn('https://shopify.com/auth');

        // Assert no validation exception is thrown
        $this->action->__invoke($request);

        $this->assertTrue(true);
    }
}
